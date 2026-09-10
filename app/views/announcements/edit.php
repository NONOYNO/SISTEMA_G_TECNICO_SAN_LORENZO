<?php
/** @var array<string,mixed> $announcement */
/** @var list<array<string,mixed>> $attachments */
require __DIR__ . '/create.php';

if (!empty($attachments)): ?>
<div class="card-soft p-3 mt-3">
    <h2 class="h6">Adjuntos actuales</h2>
    <ul class="list-unstyled mb-0">
        <?php foreach ($attachments as $file): ?>
            <?php
            $bytes = (int) ($file['size_bytes'] ?? 0);
            $sizeLabel = $bytes >= 1048576
                ? number_format($bytes / 1048576, 1) . ' MB'
                : number_format(max(1, $bytes / 1024), 0) . ' KB';
            ?>
            <li class="mb-1">
                <?php if (auth_can('files.download')): ?>
                    <a href="<?= e(url('/files/' . $file['id'] . '/download')) ?>">
                        <i class="bi bi-paperclip me-1"></i><?= e((string) $file['original_name']) ?>
                    </a>
                <?php else: ?>
                    <i class="bi bi-paperclip me-1"></i><?= e((string) $file['original_name']) ?>
                <?php endif; ?>
                <span class="text-muted">(<?= e($sizeLabel) ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
