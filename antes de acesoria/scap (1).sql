-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-09-2026 a las 06:04:27
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `scap`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `foto` varchar(255) DEFAULT 'default-avatar.png',
  `apellido` varchar(100) NOT NULL DEFAULT '',
  `rol` enum('mega_admin','super_admin','admin') NOT NULL DEFAULT 'admin',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pregunta_1` varchar(255) DEFAULT NULL,
  `respuesta_1` varchar(255) DEFAULT NULL,
  `pregunta_2` varchar(255) DEFAULT NULL,
  `respuesta_2` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `nombre`, `correo`, `foto`, `apellido`, `rol`, `activo`, `created_at`, `updated_at`, `pregunta_1`, `respuesta_1`, `pregunta_2`, `respuesta_2`) VALUES
(1, 'admin', '$2y$10$WpbEc30AiN77LkGcRqAj4OWwjMPo6VyGKpOUG7SlFWysL4fbyEXKK', 'Administrador', NULL, 'avatar_1_1787886319.jpg', 'Principal', 'mega_admin', 1, '2026-06-09 01:47:12', '2026-08-29 03:33:31', 'bebidas', '$2y$10$K2Wgr9K046p6mEqIx.UD6u2dvSiybu7R9t.9X3mJn3LUDprOhoBgq', 'segundo nombre', '$2y$10$9FugkxjjwwMCI4QKKPjZreYZgbH/EZBG9DF8hwTcIa34nmOAuFhHi'),
(2, 'melvin', '$2y$10$y5iBkBkcdeU5AKviXV/xBepcnAPfO3WcPhaWweuU9PcrApHAs86ei', 'melvin', NULL, 'default-avatar.png', 'claro', 'admin', 1, '2026-07-23 02:59:23', '2026-09-13 01:01:59', 'bebidas', '$2y$10$/SrKeM.10f7jB3QvqX7VpeK9DCmRqd1YJsfe37n9fr8tO3ET4d34u', 'segundo nombre', '$2y$10$laRkS5l7zFnZuzeJUkhQJ.quqw0.GXOst0V8YByaQLD3mqnm/RAx6');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attendance`
--

CREATE TABLE `attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `worker_id` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado_entrada` enum('A tiempo','Tolerancia','Tardanza','Justificado') DEFAULT 'A tiempo',
  `minutos_retraso` int(11) DEFAULT 0,
  `motivo_tardanza` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `nombre_empresa` varchar(150) NOT NULL DEFAULT 'Sistema SCAP',
  `encabezado` text DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `hora_entrada` time NOT NULL DEFAULT '08:00:00',
  `hora_salida` time NOT NULL DEFAULT '17:00:00',
  `margen_tolerancia` int(11) NOT NULL DEFAULT 15,
  `color_header` varchar(20) DEFAULT '#1e293b',
  `color_sidebar` varchar(20) DEFAULT '#0f172a',
  `color_fondo` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre_empresa`, `encabezado`, `logo_url`, `hora_entrada`, `hora_salida`, `margen_tolerancia`, `color_header`, `color_sidebar`, `color_fondo`, `updated_at`) VALUES
(1, 'Sistema SCAP', NULL, NULL, '08:00:00', '17:00:00', 15, '#ab1717', '#be123c', 'linear-gradient(135deg, #eef2ff 0%, #e0e7ff 50%, #f1f5f9 100%)', '2026-09-15 01:45:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_asignaciones`
--

CREATE TABLE `horarios_asignaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `worker_id` int(10) UNSIGNED NOT NULL,
  `ubicacion` varchar(100) NOT NULL,
  `numero_aula` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_cargo`
--

CREATE TABLE `horarios_cargo` (
  `id` int(10) UNSIGNED NOT NULL,
  `cargo` varchar(100) NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horarios_cargo`
--

INSERT INTO `horarios_cargo` (`id`, `cargo`, `hora_entrada`, `hora_salida`, `created_at`, `updated_at`) VALUES
(1, 'Docente', '07:00:00', '13:00:00', '2026-08-20 00:12:16', '2026-08-20 00:17:23'),
(2, 'Obrero', '08:00:00', '16:00:00', '2026-08-20 00:12:16', '2026-08-20 00:12:16'),
(4, 'Administrador', '07:18:00', '13:18:00', '2026-08-27 00:18:44', '2026-08-27 00:18:44'),
(5, 'Cocinero', '10:51:00', '22:51:00', '2026-09-01 02:51:16', '2026-09-01 02:51:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `justifications`
--

CREATE TABLE `justifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `worker_id` int(10) UNSIGNED NOT NULL,
  `tipo_ausencia` varchar(100) DEFAULT NULL,
  `motivo` text NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `fecha` date NOT NULL,
  `estado` enum('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `justifications`
--

INSERT INTO `justifications` (`id`, `worker_id`, `tipo_ausencia`, `motivo`, `fecha_inicio`, `fecha_fin`, `documento`, `fecha`, `estado`, `created_at`, `updated_at`) VALUES
(5, 17, 'Reposo Médico', 'Reposo médico de 48 horas por cuadro gripal', '2026-07-26', '2026-07-28', NULL, '2026-07-26', 'Pendiente', '2026-07-27 01:57:07', '2026-07-27 01:57:07'),
(6, 18, NULL, 'gripe', '2026-08-01', '2026-08-13', NULL, '0000-00-00', 'Aprobado', '2026-08-10 20:25:29', '2026-08-10 20:25:29'),
(7, 19, 'Enfermedad', 'gripe', '2026-08-06', '2026-08-11', 'justif_1786394980_61db171d.png', '0000-00-00', 'Pendiente', '2026-08-10 20:49:40', '2026-08-10 20:49:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `workers`
--

CREATE TABLE `workers` (
  `id` int(10) UNSIGNED NOT NULL,
  `cedula` varchar(15) NOT NULL,
  `posee_documento` enum('Sí','No') NOT NULL DEFAULT 'Sí',
  `nombres_apellidos` varchar(150) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `cargo` varchar(100) NOT NULL DEFAULT 'Obrero',
  `telefono` varchar(20) DEFAULT NULL,
  `grado_academico` varchar(50) DEFAULT NULL,
  `condicion_medica` text DEFAULT NULL,
  `anios_servicio` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `departamento` varchar(100) NOT NULL DEFAULT 'General',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `workers`
--

INSERT INTO `workers` (`id`, `cedula`, `posee_documento`, `nombres_apellidos`, `nombre`, `apellido`, `cargo`, `telefono`, `grado_academico`, `condicion_medica`, `anios_servicio`, `departamento`, `activo`, `deleted`, `created_at`, `updated_at`) VALUES
(17, '31073726', 'Sí', 'juan manuel alfa omega', 'juan', 'manuel alfa omega', 'Docente', '04124785698', 'TSU', 'alergias al polen', 5, 'Docente', 1, 0, '2026-07-26 03:33:42', '2026-09-05 00:08:42'),
(18, '31073727', 'Sí', 'melvin jesus claro avila', 'melvin', 'jesus claro avila', 'Administrador', '04243458496', 'TSU', 'ninguna', 0, 'Administrador', 1, 0, '2026-07-26 15:52:14', '2026-09-05 00:08:29'),
(19, '12121212', 'Sí', 'jesus alberto alfa omega', 'jesus', 'alberto alfa omega', 'Obrero', '04163831242', 'Bachiller', 'Dificultad para caminar', 2, 'Cocinero', 1, 0, '2026-07-26 16:13:09', '2026-09-05 00:08:14'),
(20, '987654321', 'Sí', 'manuel jose sierra montes', 'manuel', 'jose sierra montes', 'Obrero', '02121212123', 'Licenciado/Ingeniero', 'Ninguna', 3, 'Vigilante', 1, 0, '2026-09-08 23:10:21', '2026-09-08 23:10:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `work_calendar`
--

CREATE TABLE `work_calendar` (
  `id` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `tipo_estado` enum('laborable','vacaciones','feriado','cumpleanios','emergencia') NOT NULL,
  `motivo_emergencia` varchar(100) DEFAULT NULL,
  `nota` text DEFAULT NULL,
  `worker_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `work_calendar`
--

INSERT INTO `work_calendar` (`id`, `fecha`, `tipo_estado`, `motivo_emergencia`, `nota`, `worker_id`, `created_at`, `updated_at`) VALUES
(1063, '2026-01-05', 'laborable', NULL, 'clases', NULL, '2026-08-14 22:47:17', '2026-08-14 22:47:17'),
(1064, '2026-01-06', 'vacaciones', NULL, 'Periodo Vacacional', NULL, '2026-08-14 22:47:40', '2026-08-14 22:47:40'),
(1065, '2026-01-07', 'vacaciones', NULL, 'Periodo Vacacional', NULL, '2026-08-14 22:47:40', '2026-08-14 22:47:40'),
(1066, '2026-01-08', 'vacaciones', NULL, 'Periodo Vacacional', NULL, '2026-08-14 22:47:40', '2026-08-14 22:47:40'),
(1067, '2026-01-09', 'vacaciones', NULL, 'Periodo Vacacional', NULL, '2026-08-14 22:47:40', '2026-08-14 22:47:40'),
(1068, '2026-01-10', 'vacaciones', NULL, 'Periodo Vacacional', NULL, '2026-08-14 22:47:40', '2026-08-14 22:47:40'),
(1069, '2026-01-11', 'vacaciones', NULL, 'Periodo Vacacional', NULL, '2026-08-14 22:47:40', '2026-08-14 22:47:40'),
(1070, '2026-01-12', 'vacaciones', NULL, 'Periodo Vacacional', NULL, '2026-08-14 22:47:40', '2026-08-14 22:47:40'),
(1071, '2026-01-13', 'vacaciones', NULL, 'Periodo Vacacional', NULL, '2026-08-14 22:47:40', '2026-08-14 22:47:40'),
(1072, '2026-01-14', 'vacaciones', NULL, 'Periodo Vacacional', NULL, '2026-08-14 22:47:40', '2026-08-14 22:47:40'),
(1073, '2026-01-15', 'vacaciones', NULL, 'Periodo Vacacional', NULL, '2026-08-14 22:47:40', '2026-08-14 22:47:40');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_username` (`username`);

--
-- Indices de la tabla `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_worker_fecha` (`worker_id`,`fecha`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `horarios_asignaciones`
--
ALTER TABLE `horarios_asignaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_worker_asignacion` (`worker_id`);

--
-- Indices de la tabla `horarios_cargo`
--
ALTER TABLE `horarios_cargo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_cargo` (`cargo`);

--
-- Indices de la tabla `justifications`
--
ALTER TABLE `justifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_justifications_worker` (`worker_id`);

--
-- Indices de la tabla `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_cedula` (`cedula`);

--
-- Indices de la tabla `work_calendar`
--
ALTER TABLE `work_calendar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `fk_calendar_worker` (`worker_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `horarios_asignaciones`
--
ALTER TABLE `horarios_asignaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `horarios_cargo`
--
ALTER TABLE `horarios_cargo`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `justifications`
--
ALTER TABLE `justifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `workers`
--
ALTER TABLE `workers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `work_calendar`
--
ALTER TABLE `work_calendar`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1074;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `horarios_asignaciones`
--
ALTER TABLE `horarios_asignaciones`
  ADD CONSTRAINT `fk_asignacion_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `justifications`
--
ALTER TABLE `justifications`
  ADD CONSTRAINT `fk_justifications_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `work_calendar`
--
ALTER TABLE `work_calendar`
  ADD CONSTRAINT `fk_calendar_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
