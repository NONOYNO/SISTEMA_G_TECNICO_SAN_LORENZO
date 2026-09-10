<?php

declare(strict_types=1);

/**
 * Seeder opcional de aviso de demostración.
 * Uso: php database/seeders/DemoAnnouncementSeeder.php
 */

require dirname(__DIR__) . '/bootstrap.php';

$root = database_project_root();
$env = database_load_env($root);
$pdo = database_pdo($env);

$author = $pdo->query("SELECT id FROM users WHERE username = 'vicerrector' LIMIT 1")->fetchColumn();
if ($author === false) {
    fwrite(STDERR, "No existe usuario vicerrector. Ejecute php database/seed.php primero.\n");
    exit(1);
}

$exists = $pdo->query("SELECT id FROM announcements WHERE title = 'Bienvenida al Portal Institucional' LIMIT 1")->fetchColumn();
if ($exists !== false) {
    echo "Aviso demo ya existe (id={$exists}).\n";
    exit(0);
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare(
        "INSERT INTO announcements
            (author_id, title, description, content, category, priority, status, publish_at, expire_at, audience, created_at, updated_at)
         VALUES
            (?, 'Bienvenida al Portal Institucional', 'Comunicado inicial para el personal docente',
             'Se informa al personal que el Portal Informativo de la Unidad Educativa Fiscomisional San Lorenzo se encuentra disponible. Consulte avisos y notificaciones desde su panel.',
             'Institucional', 'high', 'published', NOW(), NULL, 'ALL', NOW(), NOW())"
    );
    $stmt->execute([(int) $author]);
    $announcementId = (int) $pdo->lastInsertId();

    $recipients = $pdo->query(
        "SELECT DISTINCT u.id
         FROM users u
         INNER JOIN user_roles ur ON ur.user_id = u.id
         INNER JOIN roles r ON r.id = ur.role_id
         WHERE u.status = 'active'"
    )->fetchAll(PDO::FETCH_COLUMN);

    $ins = $pdo->prepare(
        "INSERT INTO notifications (user_id, announcement_id, title, body, priority, type, created_at, updated_at)
         VALUES (?, ?, 'Bienvenida al Portal Institucional', 'Hay un nuevo aviso institucional publicado.', 'high', 'announcement', NOW(), NOW())"
    );

    foreach ($recipients as $userId) {
        $ins->execute([(int) $userId, $announcementId]);
    }

    $pdo->commit();
    echo "Aviso demo creado id={$announcementId}, notificaciones=" . count($recipients) . "\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
