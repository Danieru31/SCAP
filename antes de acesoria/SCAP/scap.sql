-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-06-2026 a las 05:30:00
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

INSERT INTO `admins` (`id`, `username`, `password`, `nombre`, `apellido`, `rol`, `activo`, `created_at`, `updated_at`, `pregunta_1`, `respuesta_1`, `pregunta_2`, `respuesta_2`) VALUES
(1, 'admin', '$2y$10$ieZUROsfzfFGSpNhwi.0COFTJOOC/5gzPsD8WJz0QVtP639Y3/2zS', 'Administrador', 'Principal', 'mega_admin', 1, '2026-06-09 01:47:12', '2026-06-09 01:47:12', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attendance`
--

CREATE TABLE `attendance` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `worker_id` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `estado_entrada` enum('A tiempo','Tolerancia','Tardanza','Justificado') DEFAULT 'A tiempo',
  `minutos_retraso` int(11) DEFAULT 0,
  `motivo_tardanza` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_worker_fecha` (`worker_id`,`fecha`),
  CONSTRAINT `fk_attendance_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `justifications`
--

CREATE TABLE `justifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `worker_id` int(10) UNSIGNED NOT NULL,
  `tipo_ausencia` enum('Enfermedad','Motivo personal') NOT NULL DEFAULT 'Enfermedad',
  `motivo` text NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `estado` enum('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `justifications`
--

INSERT INTO `justifications` (`id`, `worker_id`, `tipo_ausencia`, `motivo`, `fecha_inicio`, `fecha_fin`, `documento`, `estado`, `created_at`, `updated_at`) VALUES
(1, 9, 'Enfermedad', 'gripe', '2026-06-20', '2026-06-22', NULL, 'Aprobado', '2026-06-20 23:03:52', '2026-06-20 23:03:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `work_calendar`
--

CREATE TABLE `work_calendar` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `tipo_estado` enum('laborable','vacaciones','feriado','cumpleanios','emergencia') NOT NULL,
  `motivo_emergencia` varchar(100) DEFAULT NULL,
  `nota` text DEFAULT NULL,
  `worker_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_fecha_worker` (`fecha`, `worker_id`),
  KEY `fk_calendar_worker` (`worker_id`),
  CONSTRAINT `fk_calendar_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre_empresa` varchar(150) NOT NULL DEFAULT 'Sistema SCAP',
  `encabezado` text DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `hora_entrada` time NOT NULL DEFAULT '08:00:00',
  `hora_salida` time NOT NULL DEFAULT '17:00:00',
  `margen_tolerancia` int(11) NOT NULL DEFAULT 15,
  `color_header` varchar(20) NOT NULL DEFAULT '#1e293b',
  `color_sidebar` varchar(20) NOT NULL DEFAULT '#0f172a',
  `color_fondo` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre_empresa`, `encabezado`, `logo_url`, `hora_entrada`, `hora_salida`, `margen_tolerancia`, `color_header`, `color_sidebar`, `color_fondo`) VALUES
(1, 'Sistema SCAP', 'REPÚBLICA BOLIVARIANA DE VENEZUELA\r\nMINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN\r\nSISTEMA DE CONTROL DE ASISTENCIA (SCAP)', NULL, '08:00:00', '17:00:00', 15, '#1e293b', '#0f172a', 'linear-gradient(135deg, #eef2ff 0%, #e0e7ff 50%, #f1f5f9 100%)');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_cargo`
--

CREATE TABLE IF NOT EXISTS `horarios_cargo` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cargo` varchar(100) NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cargo` (`cargo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_asignaciones`
--

CREATE TABLE IF NOT EXISTS `horarios_asignaciones` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `worker_id` int(10) UNSIGNED NOT NULL,
  `ubicacion` varchar(100) NOT NULL,
  `numero_aula` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_worker_asignacion` (`worker_id`),
  CONSTRAINT `fk_asignacion_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `workers`
--

CREATE TABLE `workers` (
  `id` int(10) UNSIGNED NOT NULL,
  `cedula` varchar(15) NOT NULL,
  `posee_documento` enum('Sí','No') NOT NULL DEFAULT 'Sí',
  `nombres_apellidos` varchar(150) NOT NULL,
  `nombre` varchar(100) NOT NULL DEFAULT '',
  `apellido` varchar(100) NOT NULL DEFAULT '',
  `cargo` varchar(100) NOT NULL DEFAULT 'Obrero',
  `departamento` varchar(100) NOT NULL DEFAULT 'General',
  `telefono` varchar(20) DEFAULT NULL,
  `grado_academico` varchar(50) DEFAULT NULL,
  `condicion_medica` text DEFAULT NULL,
  `anios_servicio` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `workers`
--

INSERT INTO `workers` (`id`, `cedula`, `posee_documento`, `nombres_apellidos`, `nombre`, `apellido`, `cargo`, `departamento`, `telefono`, `grado_academico`, `condicion_medica`, `anios_servicio`, `activo`, `deleted`, `created_at`, `updated_at`) VALUES
(1, '12345678', 'Sí', 'Carlos Rodríguez', 'Carlos', 'Rodríguez', 'Electricista', 'Mantenimiento', '0414-1234567', 'Bachiller', 'Ninguna', 5, 1, 0, '2026-06-09 01:47:12', '2026-06-09 01:47:12'),
(2, '23456789', 'Sí', 'María González', 'María', 'González', 'Pintora', 'Obras Civiles', '0424-2345678', 'TSU', 'Ninguna', 3, 1, 0, '2026-06-09 01:47:12', '2026-06-09 01:47:12'),
(3, '34567890', 'Sí', 'José Martínez', 'José', 'Martínez', 'Plomero', 'Mantenimiento', '0412-3456789', 'Primaria', 'Hipertensión leve', 8, 1, 0, '2026-06-09 01:47:12', '2026-06-09 01:47:12'),
(4, '45678901', 'Sí', 'Ana López', 'Ana', 'López', 'Albañil', 'Obras Civiles', '0416-4567890', 'Licenciado/Ingeniero', 'Alergia al polvo', 2, 1, 0, '2026-06-09 01:47:12', '2026-06-09 01:47:12'),
(5, '56789012', 'No', 'Pedro Ramírez', 'Pedro', 'Ramírez', 'Carpintero', 'Taller', '0426-5678901', 'Ninguno', 'Ninguna', 1, 1, 0, '2026-06-09 01:47:12', '2026-06-09 01:47:12');

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
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `justifications`
--
ALTER TABLE `justifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `workers`
--
ALTER TABLE `workers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `justifications`
--
ALTER TABLE `justifications`
  ADD CONSTRAINT `fk_justifications_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
