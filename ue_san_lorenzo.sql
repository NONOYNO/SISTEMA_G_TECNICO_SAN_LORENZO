-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-09-2026 a las 23:52:23
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ue_san_lorenzo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `author_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `content` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `publish_at` datetime DEFAULT NULL,
  `expire_at` datetime DEFAULT NULL,
  `audience` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attachments`
--

CREATE TABLE `attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `attachable_type` varchar(50) NOT NULL,
  `attachable_id` bigint(20) UNSIGNED NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `extension` varchar(20) NOT NULL,
  `size_bytes` int(10) UNSIGNED NOT NULL,
  `disk_path` varchar(500) NOT NULL,
  `checksum_sha256` char(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity` varchar(50) DEFAULT NULL,
  `entity_id` bigint(20) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity`, `entity_id`, `old_values`, `new_values`, `ip`, `user_agent`, `created_at`) VALUES
(1, 1, 'LOGIN_SUCCESS', 'auth', 1, NULL, NULL, NULL, NULL, '2026-09-08 16:14:44'),
(2, 1, 'LOGIN_SUCCESS', 'auth', 1, NULL, NULL, NULL, NULL, '2026-09-08 16:14:44'),
(3, 1, 'LOGIN_FAILED', 'auth', 1, NULL, '{\"reason\":\"bad_password\",\"attempts\":1,\"login\":\"admin@uesanlorenzo.edu\"}', NULL, NULL, '2026-09-08 16:14:44'),
(4, NULL, 'LOGIN_FAILED', 'auth', NULL, NULL, '{\"login\":\"admin@sanlorenzo.edu.ec\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 16:24:57'),
(5, NULL, 'LOGIN_FAILED', 'auth', NULL, NULL, '{\"login\":\"admin@sanlorenzo.edu.ec\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 16:25:14'),
(6, 1, 'LOGIN_SUCCESS', 'auth', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 16:25:55'),
(7, 1, 'announcements.create', 'announcement', 1, NULL, '{\"title\":\"REGISTRO ÚNICO DE PROVEEDORES\",\"status\":\"published\",\"notifications_created\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 16:27:20'),
(8, 1, 'files.upload', 'attachment', 1, NULL, '{\"original_name\":\"01 SOLICITUD ESTUDIANTIL .docx\",\"attachable_type\":\"announcement\",\"attachable_id\":1,\"size_bytes\":86517}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 16:27:20'),
(9, NULL, 'LOGIN_FAILED', 'auth', NULL, NULL, '{\"login\":\"docente@sanlorenzo.edu.ec\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 16:28:51'),
(10, NULL, 'LOGIN_FAILED', 'auth', NULL, NULL, '{\"login\":\"docente@sanlorenzo.edu.ec\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 16:29:07'),
(11, 4, 'LOGIN_SUCCESS', 'auth', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 16:29:19'),
(12, 1, 'LOGIN_SUCCESS', 'auth', 1, NULL, NULL, NULL, NULL, '2026-09-08 16:29:41'),
(13, 4, 'LOGIN_SUCCESS', 'auth', 4, NULL, NULL, NULL, NULL, '2026-09-08 16:29:41'),
(14, 1, 'LOGIN_SUCCESS', 'auth', 1, NULL, NULL, '::1', 'curl/8.21.0', '2026-09-08 16:32:30'),
(15, 1, 'LOGIN_SUCCESS', 'auth', 1, NULL, NULL, NULL, NULL, '2026-09-08 16:33:52'),
(16, 1, 'LOGIN_FAILED', 'auth', 1, NULL, '{\"reason\":\"bad_password\",\"attempts\":1,\"login\":\"admin@uesanlorenzo.edu\"}', NULL, NULL, '2026-09-08 16:33:52'),
(17, 4, 'LOGIN_SUCCESS', 'auth', 4, NULL, NULL, NULL, NULL, '2026-09-08 16:33:52'),
(18, 4, 'files.download', 'attachment', 1, NULL, '{\"original_name\":\"01 SOLICITUD ESTUDIANTIL .docx\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 16:35:47'),
(19, 1, 'announcements.delete', 'announcement', 2, '{\"title\":\"Bienvenida al Portal Institucional\",\"status\":\"published\",\"attachments_removed\":0}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 16:45:38'),
(20, 1, 'announcements.delete', 'announcement', 1, '{\"title\":\"REGISTRO ÚNICO DE PROVEEDORES\",\"status\":\"published\",\"attachments_removed\":1}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 16:45:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  `applied_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`, `applied_at`) VALUES
(1, '20260908120000_create_users_table.php', 1, '2026-09-08 16:05:44'),
(2, '20260908120100_create_roles_table.php', 1, '2026-09-08 16:05:44'),
(3, '20260908120200_create_permissions_table.php', 1, '2026-09-08 16:05:44'),
(4, '20260908120300_create_user_roles_table.php', 1, '2026-09-08 16:05:44'),
(5, '20260908120400_create_role_permissions_table.php', 1, '2026-09-08 16:05:44'),
(6, '20260908120500_create_announcements_table.php', 1, '2026-09-08 16:05:44'),
(7, '20260908120600_create_notifications_table.php', 1, '2026-09-08 16:05:44'),
(8, '20260908120700_create_notification_reads_table.php', 1, '2026-09-08 16:05:45'),
(9, '20260908120800_create_attachments_table.php', 1, '2026-09-08 16:05:45'),
(10, '20260908120900_create_audit_logs_table.php', 1, '2026-09-08 16:05:45'),
(11, '20260908121000_create_sessions_table.php', 1, '2026-09-08 16:05:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `body` text DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `type` varchar(50) NOT NULL DEFAULT 'announcement',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notification_reads`
--

CREATE TABLE `notification_reads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `read_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `group_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `group_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'users.view', 'users', 'Ver usuarios', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(2, 'users.create', 'users', 'Crear usuarios', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(3, 'users.edit', 'users', 'Editar usuarios', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(4, 'users.delete', 'users', 'Eliminar usuarios', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(5, 'roles.view', 'roles', 'Ver roles', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(6, 'roles.create', 'roles', 'Crear roles', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(7, 'roles.edit', 'roles', 'Editar roles', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(8, 'roles.delete', 'roles', 'Eliminar roles', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(9, 'announcements.view', 'announcements', 'Ver avisos', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(10, 'announcements.create', 'announcements', 'Crear avisos', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(11, 'announcements.edit', 'announcements', 'Editar avisos', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(12, 'announcements.delete', 'announcements', 'Eliminar avisos', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(13, 'announcements.publish', 'announcements', 'Publicar/archivar avisos', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(14, 'notifications.view', 'notifications', 'Ver notificaciones', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(15, 'notifications.create', 'notifications', 'Crear notificaciones', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(16, 'files.upload', 'files', 'Subir archivos', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(17, 'files.download', 'files', 'Descargar archivos', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(18, 'files.delete', 'files', 'Eliminar archivos', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(19, 'reports.view', 'reports', 'Ver reportes', '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(20, 'audit.view', 'audit', 'Ver auditoría', '2026-09-08 16:05:45', '2026-09-08 16:06:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'ADMIN', 'Administrador', 'Acceso total al portal institucional', 1, '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(2, 'RECTOR', 'Rector', 'Consulta gerencial y reportes', 1, '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(3, 'VICERRECTOR', 'Vicerrector', 'Gestión de avisos, notificaciones y archivos', 1, '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(4, 'DOCENTE', 'Docente', 'Consulta de avisos y notificaciones', 1, '2026-09-08 16:05:45', '2026-09-08 16:06:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 10, '2026-09-08 16:05:45'),
(2, 1, 12, '2026-09-08 16:05:45'),
(3, 1, 11, '2026-09-08 16:05:45'),
(4, 1, 13, '2026-09-08 16:05:45'),
(5, 1, 9, '2026-09-08 16:05:45'),
(6, 1, 20, '2026-09-08 16:05:45'),
(7, 1, 18, '2026-09-08 16:05:45'),
(8, 1, 17, '2026-09-08 16:05:45'),
(9, 1, 16, '2026-09-08 16:05:45'),
(10, 1, 15, '2026-09-08 16:05:45'),
(11, 1, 14, '2026-09-08 16:05:45'),
(12, 1, 19, '2026-09-08 16:05:45'),
(13, 1, 6, '2026-09-08 16:05:45'),
(14, 1, 8, '2026-09-08 16:05:45'),
(15, 1, 7, '2026-09-08 16:05:45'),
(16, 1, 5, '2026-09-08 16:05:45'),
(17, 1, 2, '2026-09-08 16:05:45'),
(18, 1, 4, '2026-09-08 16:05:45'),
(19, 1, 3, '2026-09-08 16:05:45'),
(20, 1, 1, '2026-09-08 16:05:45'),
(21, 3, 9, '2026-09-08 16:05:45'),
(22, 3, 10, '2026-09-08 16:05:45'),
(23, 3, 11, '2026-09-08 16:05:45'),
(24, 3, 12, '2026-09-08 16:05:45'),
(25, 3, 13, '2026-09-08 16:05:45'),
(26, 3, 14, '2026-09-08 16:05:45'),
(27, 3, 15, '2026-09-08 16:05:45'),
(28, 3, 16, '2026-09-08 16:05:45'),
(29, 3, 17, '2026-09-08 16:05:45'),
(30, 3, 18, '2026-09-08 16:05:45'),
(31, 3, 19, '2026-09-08 16:05:45'),
(32, 2, 9, '2026-09-08 16:05:45'),
(33, 2, 14, '2026-09-08 16:05:45'),
(34, 2, 17, '2026-09-08 16:05:45'),
(35, 2, 19, '2026-09-08 16:05:45'),
(36, 2, 1, '2026-09-08 16:05:45'),
(37, 4, 9, '2026-09-08 16:05:45'),
(38, 4, 14, '2026-09-08 16:05:45'),
(39, 4, 17, '2026-09-08 16:05:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'pending',
  `last_login_at` datetime DEFAULT NULL,
  `failed_login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `phone`, `status`, `last_login_at`, `failed_login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@uesanlorenzo.edu', '$2y$10$marlpQYDIcEzGAxSQqBr1...IdqXE8IoKimJIpkRumHqppRn9wsem', 'Admin', 'Sistema', NULL, 'active', '2026-09-08 16:33:52', 1, NULL, '2026-09-08 16:05:45', '2026-09-08 16:33:52'),
(2, 'rector', 'rector@uesanlorenzo.edu', '$2y$10$rCOcHP04dUyRfIaPKmg6Se8STf2istvmJUQGUlsnbMnkRyytO3pw6', 'Rector', 'Institucional', NULL, 'active', NULL, 0, NULL, '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(3, 'vicerrector', 'vicerrector@uesanlorenzo.edu', '$2y$10$arnMCgXggKTQI7N508BrJ.w9zHrf6DOOq7bsfZC..W1zJW/VOyf3u', 'Vicerrector', 'Académico', NULL, 'active', NULL, 0, NULL, '2026-09-08 16:05:45', '2026-09-08 16:06:41'),
(4, 'docente', 'docente@uesanlorenzo.edu', '$2y$10$bwx.1RaOu9mhd/FQ9wKewewye0U/ca0cmvU8fka163BuhW1qEhcaW', 'Docente', 'Demo', NULL, 'active', '2026-09-08 16:33:52', 0, NULL, '2026-09-08 16:05:45', '2026-09-08 16:33:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_roles`
--

CREATE TABLE `user_roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `created_at`) VALUES
(1, 1, 1, '2026-09-08 16:05:45'),
(2, 2, 2, '2026-09-08 16:05:45'),
(3, 3, 3, '2026-09-08 16:05:45'),
(4, 4, 4, '2026-09-08 16:05:45');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_status_index` (`status`),
  ADD KEY `announcements_publish_at_index` (`publish_at`),
  ADD KEY `announcements_author_id_index` (`author_id`),
  ADD KEY `announcements_priority_index` (`priority`);

--
-- Indices de la tabla `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attachments_stored_name_unique` (`stored_name`),
  ADD KEY `attachments_attachable_index` (`attachable_type`,`attachable_id`),
  ADD KEY `attachments_uploaded_by_index` (`uploaded_by`);

--
-- Indices de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_index` (`user_id`),
  ADD KEY `audit_logs_action_index` (`action`),
  ADD KEY `audit_logs_created_at_index` (`created_at`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `migrations_migration_unique` (`migration`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `notifications_announcement_id_index` (`announcement_id`),
  ADD KEY `notifications_priority_index` (`priority`);

--
-- Indices de la tabla `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notification_reads_notification_id_user_id_unique` (`notification_id`,`user_id`),
  ADD KEY `notification_reads_user_id_read_at_index` (`user_id`,`read_at`);

--
-- Indices de la tabla `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD KEY `permissions_group_name_index` (`group_name`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indices de la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permissions_role_id_permission_id_unique` (`role_id`,`permission_id`),
  ADD KEY `role_permissions_permission_id_index` (`permission_id`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_status_index` (`status`),
  ADD KEY `users_name_index` (`last_name`,`first_name`);

--
-- Indices de la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_roles_user_id_role_id_unique` (`user_id`,`role_id`),
  ADD KEY `user_roles_role_id_index` (`role_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `notification_reads`
--
ALTER TABLE `notification_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD CONSTRAINT `notification_reads_notification_id_foreign` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notification_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
