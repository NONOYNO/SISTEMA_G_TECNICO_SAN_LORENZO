<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AnnouncementService;
use App\Services\FileUploadService;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class AnnouncementController extends Controller
{
    private AnnouncementService $announcements;
    private FileUploadService $files;

    public function __construct()
    {
        $this->announcements = new AnnouncementService();
        $this->files = new FileUploadService();
    }

    public function index(): void
    {
        $viewer = $this->viewerContext();
        $statusFilter = trim((string) ($_GET['status'] ?? ''));
        if ($statusFilter === '' || !$viewer['can_manage']) {
            $statusFilter = null;
        }

        $items = $this->announcements->listForViewer($viewer, $statusFilter);

        $this->view('announcements.index', [
            'title' => 'Avisos',
            'announcements' => $items,
            'statusFilter' => $statusFilter,
            'canManage' => $viewer['can_manage'],
            'canCreate' => auth_can('announcements.create'),
            'canPublish' => auth_can('announcements.publish'),
            'canDelete' => auth_can('announcements.delete'),
        ]);
    }

    public function create(): void
    {
        $this->view('announcements.create', [
            'title' => 'Nuevo aviso',
            'announcement' => null,
            'canPublish' => auth_can('announcements.publish'),
        ]);
    }

    public function store(): void
    {
        $input = $this->inputFromRequest();
        flash_input($input);

        try {
            $userId = auth_id();
            if ($userId === null) {
                abort(403);
            }

            $created = $this->announcements->create($input, $userId);

            $uploadWarning = $this->handleAttachmentUploads((int) $created['id'], $userId);

            clear_old_input();
            $msg = 'Aviso creado correctamente.';
            if (!empty($created['notifications_created'])) {
                $msg .= ' Notificaciones enviadas: ' . (int) $created['notifications_created'] . '.';
            }
            if ($uploadWarning !== null) {
                flash('warning', $msg . ' ' . $uploadWarning);
                $this->redirect('/announcements/' . $created['id'] . '/edit');
            }
            flash('success', $msg);
            $this->redirect('/announcements/' . $created['id']);
        } catch (InvalidArgumentException $e) {
            flash('error', $e->getMessage());
            $this->redirect('/announcements/create');
        } catch (Throwable $e) {
            flash('error', 'No se pudo crear el aviso.');
            $this->redirect('/announcements/create');
        }
    }

    public function show(string $id): void
    {
        $announcement = $this->announcements->find((int) $id);
        if ($announcement === null) {
            abort(404, 'Aviso no encontrado.');
        }

        $viewer = $this->viewerContext();
        if (!$this->announcements->canView($announcement, $viewer)) {
            abort(403, 'No tiene acceso a este aviso.');
        }

        $attachments = $this->files->listFor('announcement', (int) $id);

        $this->view('announcements.show', [
            'title' => (string) $announcement['title'],
            'announcement' => $announcement,
            'attachments' => $attachments,
            'canManage' => $viewer['can_manage'],
            'canEdit' => auth_can('announcements.edit'),
            'canDelete' => auth_can('announcements.delete'),
            'canPublish' => auth_can('announcements.publish'),
        ]);
    }

    public function edit(string $id): void
    {
        $announcement = $this->announcements->find((int) $id);
        if ($announcement === null) {
            abort(404, 'Aviso no encontrado.');
        }

        $attachments = $this->files->listFor('announcement', (int) $id);

        $this->view('announcements.edit', [
            'title' => 'Editar aviso',
            'announcement' => $announcement,
            'attachments' => $attachments,
            'canPublish' => auth_can('announcements.publish'),
        ]);
    }

    public function update(string $id): void
    {
        $input = $this->inputFromRequest();
        flash_input($input);

        try {
            $updated = $this->announcements->update((int) $id, $input);
            $userId = auth_id();

            $uploadWarning = null;
            if ($userId !== null) {
                $uploadWarning = $this->handleAttachmentUploads((int) $id, $userId);
            }

            clear_old_input();
            if ($uploadWarning !== null) {
                flash('warning', 'Aviso actualizado. ' . $uploadWarning);
            } else {
                flash('success', 'Aviso actualizado correctamente.');
            }
            $this->redirect('/announcements/' . $updated['id']);
        } catch (InvalidArgumentException $e) {
            flash('error', $e->getMessage());
            $this->redirect('/announcements/' . $id . '/edit');
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            $this->redirect('/announcements');
        } catch (Throwable) {
            flash('error', 'No se pudo actualizar el aviso.');
            $this->redirect('/announcements/' . $id . '/edit');
        }
    }

    public function destroy(string $id): void
    {
        try {
            $this->announcements->delete((int) $id);
            flash('success', 'Aviso eliminado correctamente.');
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
        } catch (Throwable $e) {
            error_log('AnnouncementController::destroy: ' . $e->getMessage());
            flash('error', 'No se pudo eliminar el aviso.');
        }

        $this->redirect('/announcements');
    }

    public function publish(string $id): void
    {
        try {
            $published = $this->announcements->publish((int) $id);
            $count = (int) ($published['notifications_created'] ?? 0);
            flash('success', "Aviso publicado. Notificaciones creadas: {$count}.");
            $this->redirect('/announcements/' . $id);
        } catch (InvalidArgumentException $e) {
            flash('error', $e->getMessage());
            $this->redirect('/announcements/' . $id);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            $this->redirect('/announcements');
        } catch (Throwable) {
            flash('error', 'No se pudo publicar el aviso.');
            $this->redirect('/announcements/' . $id);
        }
    }

    public function archive(string $id): void
    {
        try {
            $this->announcements->archive((int) $id);
            flash('success', 'Aviso archivado.');
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
        } catch (Throwable) {
            flash('error', 'No se pudo archivar el aviso.');
        }

        $this->redirect('/announcements/' . $id);
    }

    /**
     * @return string|null Mensaje de advertencia si hubo fallos parciales
     */
    private function handleAttachmentUploads(int $announcementId, int $userId): ?string
    {
        if (!auth_can('files.upload')) {
            return null;
        }

        $field = $_FILES['attachments'] ?? $_FILES['attachment'] ?? null;
        if (!is_array($field)) {
            return null;
        }

        $files = FileUploadService::normalizeFilesField($field);
        if ($files === []) {
            return null;
        }

        $result = $this->files->uploadMany($field, 'announcement', $announcementId, $userId);
        $ok = count($result['uploaded']);
        $fail = count($result['errors']);

        if ($fail === 0) {
            if ($ok > 0) {
                flash('info', $ok === 1 ? '1 archivo adjunto subido.' : "{$ok} archivos adjuntos subidos.");
            }
            return null;
        }

        $detail = implode(' | ', array_slice($result['errors'], 0, 5));
        if ($ok > 0) {
            return "Se subieron {$ok} archivo(s); fallaron {$fail}: {$detail}";
        }

        return "No se pudieron subir los adjuntos: {$detail}";
    }

    /**
     * @return array{roles:list<string>,can_manage:bool,user_id:int}
     */
    private function viewerContext(): array
    {
        $user = auth_user() ?? [];
        $roles = is_array($user['roles'] ?? null) ? array_map('strval', $user['roles']) : [];

        return [
            'roles' => $roles,
            'can_manage' => auth_can('announcements.create')
                || auth_can('announcements.edit')
                || auth_can('announcements.publish'),
            'user_id' => (int) ($user['id'] ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inputFromRequest(): array
    {
        $audience = $_POST['audience'] ?? '';
        if (is_array($audience)) {
            $parts = array_values(array_filter(array_map(
                static fn ($r): string => strtoupper(trim((string) $r)),
                $audience
            )));
            $audience = in_array('ALL', $parts, true) ? 'ALL' : implode(',', $parts);
        }

        return [
            'title' => (string) ($_POST['title'] ?? ''),
            'description' => (string) ($_POST['description'] ?? ''),
            'content' => (string) ($_POST['content'] ?? ''),
            'category' => (string) ($_POST['category'] ?? ''),
            'priority' => (string) ($_POST['priority'] ?? 'medium'),
            'status' => (string) ($_POST['status'] ?? 'draft'),
            'publish_at' => (string) ($_POST['publish_at'] ?? ''),
            'expire_at' => (string) ($_POST['expire_at'] ?? ''),
            'audience' => (string) $audience,
        ];
    }
}
