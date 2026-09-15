<?php
// Migrador automático de esquema de Base de Datos para SCAP

defined('ROOT_PATH') or die('Acceso directo no permitido.');

class SchemaMigrator
{
    /**
     * Asegura que todas las tablas y estructuras de datos requeridas
     * existan en la base de datos sin importar la computadora donde se ejecute.
     */
    public static function run(PDO $db): void
    {
        static $executed = false;
        if ($executed) {
            return;
        }
        $executed = true;

        try {
            // 1. Tabla configuracion
            $db->exec("CREATE TABLE IF NOT EXISTS `configuracion` (
                `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `nombre_empresa`    VARCHAR(150) NOT NULL DEFAULT 'Sistema SCAP',
                `encabezado`        TEXT NULL,
                `logo_url`          VARCHAR(255) NULL,
                `hora_entrada`      TIME NOT NULL DEFAULT '08:00:00',
                `hora_salida`       TIME NOT NULL DEFAULT '17:00:00',
                `margen_tolerancia` INT NOT NULL DEFAULT 15,
                `color_header`      VARCHAR(20) NOT NULL DEFAULT '#1e293b',
                `color_sidebar`     VARCHAR(20) NOT NULL DEFAULT '#0f172a',
                `color_fondo`       TEXT NULL,
                `updated_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // Fila por defecto en configuracion
            $confCheck = $db->query("SELECT COUNT(*) FROM configuracion WHERE id = 1")->fetchColumn();
            if ($confCheck == 0) {
                $defaultEnc = "REPÚBLICA BOLIVARIANA DE VENEZUELA\nMINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN\nSISTEMA DE CONTROL DE ASISTENCIA (SCAP)";
                $defaultFondo = "linear-gradient(135deg, #eef2ff 0%, #e0e7ff 50%, #f1f5f9 100%)";
                $stmt = $db->prepare("INSERT INTO configuracion (id, nombre_empresa, encabezado, color_header, color_sidebar, color_fondo, margen_tolerancia) VALUES (1, 'Sistema SCAP', ?, '#1e293b', '#0f172a', ?, 15)");
                $stmt->execute([$defaultEnc, $defaultFondo]);
            }

            // 2. Tabla horarios_cargo
            $db->exec("CREATE TABLE IF NOT EXISTS `horarios_cargo` (
                `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `cargo`        VARCHAR(100) NOT NULL,
                `hora_entrada` TIME         NOT NULL,
                `hora_salida`  TIME         NOT NULL,
                `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_cargo` (`cargo`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // 3. Tabla horarios_asignaciones
            $db->exec("CREATE TABLE IF NOT EXISTS `horarios_asignaciones` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `worker_id`   INT UNSIGNED NOT NULL,
                `ubicacion`   VARCHAR(100) NOT NULL,
                `numero_aula` VARCHAR(20)  DEFAULT NULL,
                `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_worker_asignacion` (`worker_id`),
                CONSTRAINT `fk_asignacion_worker`
                    FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // 4. Tabla work_calendar
            $db->exec("CREATE TABLE IF NOT EXISTS `work_calendar` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `fecha` DATE NOT NULL,
                `tipo_estado` ENUM('laborable', 'vacaciones', 'feriado', 'cumpleanios', 'emergencia') NOT NULL,
                `motivo_emergencia` VARCHAR(100) NULL,
                `nota` TEXT NULL,
                `worker_id` INT UNSIGNED NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_fecha_worker` (`fecha`, `worker_id`),
                CONSTRAINT `fk_calendar_worker`
                    FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        } catch (\Exception $e) {
            // Silencioso para prevenir interrupción si la BD no permite ciertos DDL en runtime
        }
    }
}
