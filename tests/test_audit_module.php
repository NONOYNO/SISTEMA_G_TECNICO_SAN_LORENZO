<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\AnnouncementService;
use App\Services\FileUploadService;
use App\Helpers\Database;

$passed = 0;
$failed = 0;

function assert_test(bool $cond, string $label): void
{
    global $passed, $failed;
    if ($cond) {
        echo "[PASS] {$label}\n";
        $passed++;
    } else {
        echo "[FAIL] {$label}\n";
        $failed++;
    }
}

$pdo = Database::connection();
$audit = new AuditService($pdo);
$auth = new AuthService();

// 1. Comprobar estadísticas dinámicas
$stats = $audit->getStatistics();
assert_test(isset($stats['total_events']) && $stats['total_events'] > 0, 'Estadísticas: total_events mayor a cero');
assert_test(isset($stats['total_logins']) && $stats['total_logins'] > 0, 'Estadísticas: total_logins calculado dinámicamente');
assert_test(isset($stats['total_announcements']), 'Estadísticas: total_announcements presente');
assert_test(isset($stats['total_modifications']), 'Estadísticas: total_modifications presente');

// 2. Comprobar que los registros existentes se muestran y no se borró ninguno
$allPaginated = $audit->paginate([], 1, 10);
assert_test($allPaginated['total'] === $stats['total_events'], 'Paginación: total coincide con estadísticas globales');
assert_test(count($allPaginated['items']) > 0, 'Paginación: items cargados correctamente');

// 3. Comprobar que LOGIN_SUCCESS continúa funcionando
$adminUser = $auth->attempt('admin@uesanlorenzo.edu', 'Admin123!');
assert_test($adminUser !== false, 'LOGIN_SUCCESS autenticado');
$lastLogin = $pdo->query("SELECT * FROM audit_logs WHERE action = 'LOGIN_SUCCESS' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
assert_test($lastLogin !== false && (int)$lastLogin['user_id'] === 1, 'LOGIN_SUCCESS registrado en audit_logs');

// 4. Comprobar que LOGOUT continúa funcionando
$auth->logout(1);
$lastLogout = $pdo->query("SELECT * FROM audit_logs WHERE action = 'LOGOUT' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
assert_test($lastLogout !== false && (int)$lastLogout['user_id'] === 1, 'LOGOUT registrado en audit_logs');

// 5. Crear un aviso y verificar evento
$_SESSION['user'] = $adminUser;
$announcementService = new AnnouncementService($pdo);
$newAviso = $announcementService->create([
    'title' => 'AVISO DE PRUEBA AUDITORÍA AUTOMATIZADA',
    'description' => 'Descripción de prueba para auditoría',
    'content' => 'Contenido de prueba para verificar trazabilidad completa en auditoría.',
    'category' => 'General',
    'priority' => 'low',
    'status' => 'draft',
    'audience' => 'DOCENTE',
], 1);
$avisoId = (int)$newAviso['id'];
$createLog = $pdo->query("SELECT * FROM audit_logs WHERE entity = 'announcement' AND entity_id = {$avisoId} AND action = 'announcements.create'")->fetch(PDO::FETCH_ASSOC);
assert_test($createLog !== false, 'Creación de aviso: evento announcements.create generado');

// 6. Editar el aviso y verificar evento
$updatedAviso = $announcementService->update($avisoId, [
    'title' => 'AVISO DE PRUEBA AUDITORÍA AUTOMATIZADA MODIFICADO',
    'description' => 'Descripción modificada',
    'content' => 'Contenido modificado para verificar trazabilidad.',
    'category' => 'General',
    'priority' => 'medium',
    'status' => 'draft',
    'audience' => 'DOCENTE',
]);
$updateLog = $pdo->query("SELECT * FROM audit_logs WHERE entity = 'announcement' AND entity_id = {$avisoId} AND action = 'announcements.update'")->fetch(PDO::FETCH_ASSOC);
assert_test($updateLog !== false, 'Edición de aviso: evento announcements.update generado');

// 7. Publicar el aviso y verificar que preserva el título
$publishedAviso = $announcementService->publish($avisoId);
$publishLog = $pdo->query("SELECT * FROM audit_logs WHERE entity = 'announcement' AND entity_id = {$avisoId} AND action = 'announcements.publish'")->fetch(PDO::FETCH_ASSOC);
assert_test($publishLog !== false && str_contains((string)$publishLog['new_values'], 'AVISO DE PRUEBA'), 'Publicación de aviso: evento generado y preserva título');

// 8. Archivar el aviso y verificar que preserva el título
$archivedAviso = $announcementService->archive($avisoId);
$archiveLog = $pdo->query("SELECT * FROM audit_logs WHERE entity = 'announcement' AND entity_id = {$avisoId} AND action = 'announcements.archive'")->fetch(PDO::FETCH_ASSOC);
assert_test($archiveLog !== false && str_contains((string)$archiveLog['old_values'], 'AVISO DE PRUEBA'), 'Archivado de aviso: evento generado y preserva título');

// 9. Eliminar el aviso y verificar evento
$announcementService->delete($avisoId);
$deleteLog = $pdo->query("SELECT * FROM audit_logs WHERE entity = 'announcement' AND entity_id = {$avisoId} AND action = 'announcements.delete'")->fetch(PDO::FETCH_ASSOC);
assert_test($deleteLog !== false, 'Eliminación de aviso: evento announcements.delete generado');

// 10. Probar enriquecimiento de entidad y títulos en la auditoría
$deleteEntry = $audit->enrichLogEntry($deleteLog);
assert_test(str_contains($deleteEntry['entity_display'], 'AVISO DE PRUEBA'), 'Enriquecimiento: título recuperado de old_values sin inventar información');
assert_test($deleteEntry['action_label'] === 'Aviso eliminado', 'Enriquecimiento: acción traducida a etiqueta legible "Aviso eliminado"');

// 11. Probar filtros
$filterByEntity = $audit->paginate(['entity' => 'announcement'], 1, 10);
assert_test($filterByEntity['total'] >= 10, 'Filtro por entidad: devuelve registros de anuncios');

$filterByAction = $audit->paginate(['action' => 'LOGIN_SUCCESS'], 1, 10);
assert_test($filterByAction['total'] >= 30, 'Filtro por acción: devuelve registros de LOGIN_SUCCESS');

$filterByUser = $audit->paginate(['user_id' => 1], 1, 10);
assert_test($filterByUser['total'] >= 20, 'Filtro por usuario: devuelve eventos del usuario admin');

$filterBySearch = $audit->paginate(['q' => 'Admin'], 1, 10);
assert_test($filterBySearch['total'] >= 20, 'Filtro por búsqueda libre: encuentra coincidencias');

// 12. Probar dropdowns de filtros
$filterUsers = $audit->getFilterUsers();
assert_test(count($filterUsers) > 0, 'Opciones de filtro: lista de usuarios obtenida');

$filterActions = $audit->getFilterActions();
assert_test(count($filterActions) > 0, 'Opciones de filtro: lista de acciones obtenida');

$filterEntities = $audit->getFilterEntities();
assert_test(count($filterEntities) > 0, 'Opciones de filtro: lista de entidades obtenida');

// 13. Probar seguridad: solo lectura, verificación de permisos
assert_test(in_array('audit.view', $adminUser['permissions'] ?? []), 'ADMIN tiene permiso audit.view');
$docenteUser = $auth->attempt('docente', 'Docente123!');
assert_test(!in_array('audit.view', $docenteUser['permissions'] ?? []), 'DOCENTE NO tiene permiso audit.view');

echo "\nResultado pruebas auditoría: {$passed} passed, {$failed} failed\n";
exit($failed > 0 ? 1 : 0);
