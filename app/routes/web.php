<?php

declare(strict_types=1);

use App\Controllers\AnnouncementController;
use App\Controllers\AuditController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\FileController;
use App\Controllers\HomeController;
use App\Controllers\NotificationController;
use App\Controllers\ProfileController;
use App\Controllers\RoleController;
use App\Controllers\UserController;
use App\Router;

/** @var Router $router */
$router = $router ?? new Router();

// Inicio
$router->get('/', [HomeController::class, 'index']);

// Autenticación
$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest', 'csrf']);
$router->get('/register', [AuthController::class, 'showRegister'], ['guest']);
$router->post('/register', [AuthController::class, 'register'], ['guest', 'csrf']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth', 'csrf']);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);

// Usuarios
$router->get('/users', [UserController::class, 'index'], ['auth', 'permission:users.view']);
$router->get('/users/create', [UserController::class, 'create'], ['auth', 'permission:users.create']);
$router->post('/users', [UserController::class, 'store'], ['auth', 'csrf', 'permission:users.create']);
$router->get('/users/{id}/edit', [UserController::class, 'edit'], ['auth', 'permission:users.edit']);
$router->post('/users/{id}', [UserController::class, 'update'], ['auth', 'csrf', 'permission:users.edit']);
$router->post('/users/{id}/delete', [UserController::class, 'destroy'], ['auth', 'csrf', 'permission:users.delete']);

// Roles
$router->get('/roles', [RoleController::class, 'index'], ['auth', 'permission:roles.view']);
$router->get('/roles/create', [RoleController::class, 'create'], ['auth', 'permission:roles.create']);
$router->post('/roles', [RoleController::class, 'store'], ['auth', 'csrf', 'permission:roles.create']);
$router->get('/roles/{id}/edit', [RoleController::class, 'edit'], ['auth', 'permission:roles.edit']);
$router->post('/roles/{id}', [RoleController::class, 'update'], ['auth', 'csrf', 'permission:roles.edit']);
$router->post('/roles/{id}/delete', [RoleController::class, 'destroy'], ['auth', 'csrf', 'permission:roles.delete']);

// Avisos
$router->get('/announcements', [AnnouncementController::class, 'index'], ['auth', 'permission:announcements.view']);
$router->get('/announcements/create', [AnnouncementController::class, 'create'], ['auth', 'permission:announcements.create']);
$router->post('/announcements', [AnnouncementController::class, 'store'], ['auth', 'csrf', 'permission:announcements.create']);
$router->get('/announcements/{id}', [AnnouncementController::class, 'show'], ['auth', 'permission:announcements.view']);
$router->get('/announcements/{id}/edit', [AnnouncementController::class, 'edit'], ['auth', 'permission:announcements.edit']);
$router->post('/announcements/{id}', [AnnouncementController::class, 'update'], ['auth', 'csrf', 'permission:announcements.edit']);
$router->post('/announcements/{id}/publish', [AnnouncementController::class, 'publish'], ['auth', 'csrf', 'permission:announcements.publish']);
$router->post('/announcements/{id}/archive', [AnnouncementController::class, 'archive'], ['auth', 'csrf', 'permission:announcements.publish']);
$router->post('/announcements/{id}/delete', [AnnouncementController::class, 'destroy'], ['auth', 'csrf', 'permission:announcements.delete']);

// Notificaciones
$router->get('/notifications', [NotificationController::class, 'index'], ['auth', 'permission:notifications.view']);
$router->get('/notifications/unread-count', [NotificationController::class, 'unreadCount'], ['auth', 'permission:notifications.view']);
$router->post('/notifications/{id}/read', [NotificationController::class, 'markRead'], ['auth', 'csrf', 'permission:notifications.view']);

// Perfil
$router->get('/profile', [ProfileController::class, 'show'], ['auth']);
$router->post('/profile', [ProfileController::class, 'update'], ['auth', 'csrf']);

// Archivos (descarga autenticada)
$router->get('/files/{id}/download', [FileController::class, 'download'], ['auth', 'permission:files.download']);

// Auditoría (admin)
$router->get('/audit', [AuditController::class, 'index'], ['auth', 'permission:audit.view']);

return $router;
