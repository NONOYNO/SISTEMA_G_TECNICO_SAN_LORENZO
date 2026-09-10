<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

final class FileUploadService
{
    private const ALLOWED = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'zip'];
    private const FORBIDDEN = ['php', 'phtml', 'phar', 'exe', 'bat', 'cmd', 'sh', 'cgi', 'php3', 'php4', 'php5', 'pht', 'shtml'];

    private PDO $pdo;
    private AuditService $audit;
    private string $storageRoot;

    public function __construct(?PDO $pdo = null, ?AuditService $audit = null)
    {
        $this->pdo = $pdo ?? Database::connection();
        $this->audit = $audit ?? new AuditService($this->pdo);
        $this->storageRoot = base_path('storage/uploads');
    }

    public static function maxUploadMb(): int
    {
        return max(1, (int) env('UPLOAD_MAX_MB', config('app.upload_max_mb', 512)));
    }

    /**
     * Normaliza $_FILES['attachments'] o un ítem único a lista de archivos.
     *
     * @param array<string, mixed> $filesField
     * @return list<array{name:string,type:string,tmp_name:string,error:int,size:int}>
     */
    public static function normalizeFilesField(array $filesField): array
    {
        if (!isset($filesField['name'])) {
            return [];
        }

        // Un solo archivo: name es string
        if (!is_array($filesField['name'])) {
            $name = (string) $filesField['name'];
            if ($name === '') {
                return [];
            }

            return [[
                'name' => $name,
                'type' => (string) ($filesField['type'] ?? ''),
                'tmp_name' => (string) ($filesField['tmp_name'] ?? ''),
                'error' => (int) ($filesField['error'] ?? UPLOAD_ERR_NO_FILE),
                'size' => (int) ($filesField['size'] ?? 0),
            ]];
        }

        $out = [];
        foreach ($filesField['name'] as $i => $name) {
            $name = (string) $name;
            if ($name === '') {
                continue;
            }
            $out[] = [
                'name' => $name,
                'type' => (string) ($filesField['type'][$i] ?? ''),
                'tmp_name' => (string) ($filesField['tmp_name'][$i] ?? ''),
                'error' => (int) ($filesField['error'][$i] ?? UPLOAD_ERR_NO_FILE),
                'size' => (int) ($filesField['size'][$i] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * Sube uno o varios archivos desde un campo $_FILES.
     *
     * @param array<string, mixed> $filesField
     * @return array{uploaded:list<array<string,mixed>>, errors:list<string>}
     */
    public function uploadMany(array $filesField, string $attachableType, int $attachableId, int $userId): array
    {
        $uploaded = [];
        $errors = [];

        foreach (self::normalizeFilesField($filesField) as $file) {
            try {
                $uploaded[] = $this->upload($file, $attachableType, $attachableId, $userId);
            } catch (Throwable $e) {
                $label = $file['name'] !== '' ? $file['name'] : 'archivo';
                $errors[] = $label . ': ' . $e->getMessage();
            }
        }

        return ['uploaded' => $uploaded, 'errors' => $errors];
    }

    /**
     * @param array<string, mixed> $file $_FILES item (un archivo)
     * @return array<string, mixed>
     */
    public function upload(array $file, string $attachableType, int $attachableId, int $userId): array
    {
        if (!auth_can('files.upload')) {
            throw new RuntimeException('No tiene permiso para subir archivos.');
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException($this->uploadErrorMessage((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE)));
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        $original = (string) ($file['name'] ?? 'archivo');
        $size = (int) ($file['size'] ?? 0);

        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new InvalidArgumentException('Archivo de subida inválido.');
        }

        $maxMb = self::maxUploadMb();
        $maxBytes = $maxMb * 1024 * 1024;

        if ($size <= 0 || $size > $maxBytes) {
            throw new InvalidArgumentException("El archivo supera el tamaño máximo de {$maxMb} MB.");
        }

        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $this->assertExtensionAllowed($extension, $original);

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: 'application/octet-stream';

        if (!$this->mimeMatchesExtension($mime, $extension)) {
            throw new InvalidArgumentException('El tipo MIME del archivo no coincide con la extensión permitida.');
        }

        if (!is_dir($this->storageRoot) && !mkdir($this->storageRoot, 0755, true) && !is_dir($this->storageRoot)) {
            throw new RuntimeException('No se pudo crear el directorio de almacenamiento.');
        }

        $year = date('Y');
        $month = date('m');
        $relativeDir = $year . '/' . $month;
        $absoluteDir = $this->storageRoot . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $month;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            throw new RuntimeException('No se pudo crear el directorio de destino.');
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $storedName;
        $diskPath = 'storage/uploads/' . $relativeDir . '/' . $storedName;

        if (!move_uploaded_file($tmp, $absolutePath)) {
            throw new RuntimeException('No se pudo guardar el archivo en disco.');
        }

        $checksum = hash_file('sha256', $absolutePath) ?: null;

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO attachments
                    (uploaded_by, attachable_type, attachable_id, original_name, stored_name,
                     mime_type, extension, size_bytes, disk_path, checksum_sha256, created_at, updated_at)
                 VALUES
                    (:uploaded_by, :attachable_type, :attachable_id, :original_name, :stored_name,
                     :mime_type, :extension, :size_bytes, :disk_path, :checksum_sha256, NOW(), NOW())'
            );

            $safeOriginal = $this->sanitizeOriginalName($original);

            $stmt->execute([
                'uploaded_by' => $userId,
                'attachable_type' => $attachableType,
                'attachable_id' => $attachableId,
                'original_name' => $safeOriginal,
                'stored_name' => $storedName,
                'mime_type' => $mime,
                'extension' => $extension,
                'size_bytes' => $size,
                'disk_path' => $diskPath,
                'checksum_sha256' => $checksum,
            ]);

            $id = (int) $this->pdo->lastInsertId();
            $attachment = $this->find($id);

            if ($attachment === null) {
                throw new RuntimeException('No se registró el adjunto.');
            }

            $this->audit->log($userId, 'files.upload', 'attachment', $id, null, [
                'original_name' => $safeOriginal,
                'attachable_type' => $attachableType,
                'attachable_id' => $attachableId,
                'size_bytes' => $size,
            ]);

            return $attachment;
        } catch (\Throwable $e) {
            if (is_file($absolutePath)) {
                @unlink($absolutePath);
            }
            throw $e;
        }
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM attachments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listFor(string $attachableType, int $attachableId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, original_name, mime_type, extension, size_bytes, created_at
             FROM attachments
             WHERE attachable_type = :type AND attachable_id = :id
             ORDER BY id ASC'
        );
        $stmt->execute([
            'type' => $attachableType,
            'id' => $attachableId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Elimina adjuntos de una entidad (archivos en disco + filas BD).
     */
    public function deleteForAttachable(string $attachableType, int $attachableId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, disk_path, original_name FROM attachments
             WHERE attachable_type = :type AND attachable_id = :id'
        );
        $stmt->execute([
            'type' => $attachableType,
            'id' => $attachableId,
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $deleted = 0;
        $realBase = realpath($this->storageRoot);

        foreach ($rows as $row) {
            $relative = str_replace(['\\', '..'], ['/', ''], (string) ($row['disk_path'] ?? ''));
            $absolute = base_path($relative);
            $realFile = realpath($absolute);

            if ($realBase !== false && $realFile !== false && str_starts_with($realFile, $realBase) && is_file($realFile)) {
                @unlink($realFile);
            }

            $del = $this->pdo->prepare('DELETE FROM attachments WHERE id = :id');
            $del->execute(['id' => (int) $row['id']]);
            $deleted++;

            $this->audit->log(auth_id(), 'files.delete', 'attachment', (int) $row['id'], [
                'original_name' => $row['original_name'] ?? null,
                'attachable_type' => $attachableType,
                'attachable_id' => $attachableId,
            ], null);
        }

        return $deleted;
    }

    /**
     * Stream download; never returns on success.
     */
    public function download(int $id): void
    {
        if (!auth_can('files.download')) {
            abort(403, 'No tiene permiso para descargar archivos.');
        }

        $attachment = $this->find($id);
        if ($attachment === null) {
            abort(404, 'Archivo no encontrado.');
        }

        $relative = str_replace(['\\', '..'], ['/', ''], (string) $attachment['disk_path']);
        $absolute = base_path($relative);
        $realBase = realpath($this->storageRoot);
        $realFile = realpath($absolute);

        if ($realBase === false || $realFile === false || !str_starts_with($realFile, $realBase) || !is_file($realFile)) {
            abort(404, 'Archivo no disponible en almacenamiento.');
        }

        $this->audit->log(auth_id(), 'files.download', 'attachment', $id, null, [
            'original_name' => $attachment['original_name'],
        ]);

        $filename = $this->sanitizeOriginalName((string) $attachment['original_name']);
        $mime = (string) ($attachment['mime_type'] ?: 'application/octet-stream');

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($realFile));
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', $filename) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');

        readfile($realFile);
        exit;
    }

    private function assertExtensionAllowed(string $extension, string $originalName): void
    {
        if ($extension === '' || in_array($extension, self::FORBIDDEN, true)) {
            throw new InvalidArgumentException('Extensión de archivo no permitida.');
        }

        if (!in_array($extension, self::ALLOWED, true)) {
            throw new InvalidArgumentException('Extensión de archivo no permitida.');
        }

        $lower = strtolower($originalName);
        foreach (self::FORBIDDEN as $bad) {
            if (str_contains($lower, '.' . $bad . '.')) {
                throw new InvalidArgumentException('Nombre de archivo potencialmente peligroso.');
            }
        }
    }

    private function mimeMatchesExtension(string $mime, string $extension): bool
    {
        $map = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword', 'application/octet-stream'],
            'docx' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
                'application/octet-stream',
            ],
            'xls' => ['application/vnd.ms-excel', 'application/octet-stream'],
            'xlsx' => [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip',
                'application/octet-stream',
            ],
            'ppt' => ['application/vnd.ms-powerpoint', 'application/octet-stream'],
            'pptx' => [
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/zip',
                'application/octet-stream',
            ],
            'txt' => ['text/plain', 'application/octet-stream'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'zip' => ['application/zip', 'application/x-zip-compressed', 'application/octet-stream'],
        ];

        $allowed = $map[$extension] ?? [];

        return in_array($mime, $allowed, true);
    }

    private function sanitizeOriginalName(string $name): string
    {
        $name = basename(str_replace(["\0", '\\'], '', $name));
        $name = preg_replace('/[^\p{L}\p{N}\.\-_ ]+/u', '_', $name) ?: 'archivo';

        return mb_substr($name, 0, 255);
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño permitido.',
            UPLOAD_ERR_PARTIAL => 'La subida del archivo se interrumpió.',
            UPLOAD_ERR_NO_FILE => 'No se recibió ningún archivo.',
            default => 'Error al subir el archivo.',
        };
    }
}
