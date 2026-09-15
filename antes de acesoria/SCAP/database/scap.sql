-- ============================================================
-- Sistema de Control de Asistencia del Personal
-- Script SQL - MySQL 8.x / XAMPP
-- ============================================================
-- INSTALACIÓN: Importar este archivo en phpMyAdmin, o
-- ejecutar: http://localhost/SCAP/setup.php
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

--Base de datos
CREATE DATABASE IF NOT EXISTS `scap`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `scap`;

-- ─────────────────────────────────────────────
-- Tabla: admins
-- ─────────────────────────────────────────────
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `username`    VARCHAR(50)     NOT NULL,
    `password`    VARCHAR(255)    NOT NULL COMMENT 'bcrypt hash',
    `nombre`      VARCHAR(100)    NOT NULL,
    `apellido`    VARCHAR(100)    NOT NULL DEFAULT '',
    `pregunta_1`  VARCHAR(255)    DEFAULT NULL,
    `respuesta_1` VARCHAR(255)    DEFAULT NULL,
    `pregunta_2`  VARCHAR(255)    DEFAULT NULL,
    `respuesta_2` VARCHAR(255)    DEFAULT NULL,
    `foto`        VARCHAR(255)    DEFAULT NULL,
    `activo`      TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- Tabla: workers (Personal Obrero)
-- ─────────────────────────────────────────────
DROP TABLE IF EXISTS `workers`;
CREATE TABLE `workers` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `cedula`       VARCHAR(15)     NOT NULL,
    `nombre`       VARCHAR(100)    NOT NULL,
    `apellido`     VARCHAR(100)    NOT NULL,
    `cargo`        VARCHAR(100)    NOT NULL DEFAULT 'Obrero',
    `departamento` VARCHAR(100)    NOT NULL DEFAULT 'General',
    `activo`       TINYINT(1)      NOT NULL DEFAULT 1,
    `deleted`      TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cedula` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: attendance (Registro de Asistencias)

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `worker_id`    INT UNSIGNED    NOT NULL,
    `fecha`        DATE            NOT NULL,
    `hora_entrada` TIME            NULL DEFAULT NULL,
    `hora_salida`  TIME            NULL DEFAULT NULL,
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_worker_fecha` (`worker_id`, `fecha`),
    CONSTRAINT `fk_attendance_worker`
        FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: justifications (Justificativos de Faltas/Permisos)

DROP TABLE IF EXISTS `justifications`;
CREATE TABLE `justifications` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `worker_id`    INT UNSIGNED    NOT NULL,
    `motivo`       TEXT            NOT NULL,
    `fecha`        DATE            NOT NULL,
    `estado`       ENUM('Pendiente', 'Aprobado', 'Rechazado') DEFAULT 'Pendiente',
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_justifications_worker`
        FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de prueba: Obreros

INSERT INTO `workers` (`cedula`, `nombre`, `apellido`, `cargo`, `departamento`) VALUES
('12345678', 'Carlos',   'Rodríguez',  'Electricista',    'Mantenimiento'),
('23456789', 'María',    'González',   'Pintora',         'Obras Civiles'),
('34567890', 'José',     'Martínez',   'Plomero',         'Mantenimiento'),
('45678901', 'Ana',      'López',      'Albañil',         'Obras Civiles'),
('56789012', 'Pedro',    'Ramírez',    'Carpintero',      'Taller'),
('67890123', 'Luisa',    'Torres',     'Obrera General',  'General'),
('78901234', 'Miguel',   'Hernández',  'Soldador',        'Taller'),
('89012345', 'Carmen',   'Flores',     'Jardinera',       'Áreas Verdes'),
('90123456', 'Roberto',  'Díaz',       'Conductor',       'Transporte'),
('01234567', 'Yolanda',  'Pérez',      'Limpieza',        'Servicios');

-- ─────────────────────────────────────────────
-- Tabla: configuracion
-- ─────────────────────────────────────────────
DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion` (
    `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nombre_empresa`    VARCHAR(150)    NOT NULL DEFAULT 'Sistema SCAP',
    `encabezado`        TEXT            NULL DEFAULT NULL,
    `logo_url`          VARCHAR(255)    NULL DEFAULT NULL,
    `hora_entrada`      TIME            NOT NULL DEFAULT '08:00:00',
    `hora_salida`       TIME            NOT NULL DEFAULT '17:00:00',
    `margen_tolerancia` INT             NOT NULL DEFAULT 15,
    `color_header`      VARCHAR(20)     NOT NULL DEFAULT '#1e293b',
    `color_sidebar`     VARCHAR(20)     NOT NULL DEFAULT '#0f172a',
    `color_fondo`       TEXT            NULL DEFAULT NULL,
    `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `configuracion` (`id`, `nombre_empresa`, `encabezado`, `logo_url`, `hora_entrada`, `hora_salida`, `margen_tolerancia`, `color_header`, `color_sidebar`, `color_fondo`)
VALUES (1, 'Sistema SCAP', 'REPÚBLICA BOLIVARIANA DE VENEZUELA\nMINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN\nSISTEMA DE CONTROL DE ASISTENCIA (SCAP)', NULL, '08:00:00', '17:00:00', 15, '#1e293b', '#0f172a', 'linear-gradient(135deg, #eef2ff 0%, #e0e7ff 50%, #f1f5f9 100%)')
ON DUPLICATE KEY UPDATE `id` = 1;

-- ─────────────────────────────────────────────
-- Tabla: horarios_cargo
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `horarios_cargo` (
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `cargo`        VARCHAR(100)     NOT NULL,
    `hora_entrada` TIME             NOT NULL,
    `hora_salida`  TIME             NOT NULL,
    `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cargo` (`cargo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- Tabla: horarios_asignaciones
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `horarios_asignaciones` (
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `worker_id`   INT(10) UNSIGNED NOT NULL,
    `ubicacion`   VARCHAR(100)     NOT NULL,
    `numero_aula` VARCHAR(20)      DEFAULT NULL,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_worker_asignacion` (`worker_id`),
    CONSTRAINT `fk_asignacion_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- El administrador se crea via setup.php
-- Usuario: admin | Contraseña: admin123


