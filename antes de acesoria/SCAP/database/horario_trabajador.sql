-- ============================================================
-- MÓDULO: Horario del Trabajador
-- Tablas necesarias para el módulo de Horario del Trabajador
-- Ejecutar en la base de datos: scap
-- ============================================================

-- -----------------------------------------------------------
-- Tabla: horarios_cargo
-- Almacena la hora de entrada y salida definida por cargo.
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `horarios_cargo` (
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `cargo`        VARCHAR(100)     NOT NULL,
    `hora_entrada` TIME             NOT NULL,
    `hora_salida`  TIME             NOT NULL,
    `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cargo` (`cargo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Horarios de entrada y salida definidos por cargo laboral';


-- -----------------------------------------------------------
-- Tabla: horarios_asignaciones
-- Almacena la ubicación/área asignada a cada trabajador.
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `horarios_asignaciones` (
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `worker_id`   INT(10) UNSIGNED NOT NULL,
    `ubicacion`   VARCHAR(100)     NOT NULL
                  COMMENT 'Cocina, Limpieza, Ambientalista, Vigilante, Administración, Dirección, Aula, General',
    `numero_aula` VARCHAR(20)      DEFAULT NULL
                  COMMENT 'Número de aula (solo cuando ubicacion = Aula)',
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_worker_asignacion` (`worker_id`),
    CONSTRAINT `fk_asignacion_worker`
        FOREIGN KEY (`worker_id`)
        REFERENCES `workers` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Área o ubicación asignada a cada trabajador';


-- -----------------------------------------------------------
-- Datos de ejemplo: horarios base por cargo
-- (Opcional – descomenta si deseas datos iniciales)
-- -----------------------------------------------------------
/*
INSERT INTO `horarios_cargo` (`cargo`, `hora_entrada`, `hora_salida`) VALUES
  ('Docente',        '07:00', '13:00'),
  ('Administrador',  '08:00', '16:00'),
  ('Director',       '07:30', '15:30'),
  ('Vigilante',      '06:00', '14:00'),
  ('Cocinero',       '06:00', '12:00'),
  ('Limpieza',       '06:00', '14:00'),
  ('Ambientalista',  '07:00', '15:00'),
  ('Obrero',         '07:00', '15:00')
ON DUPLICATE KEY UPDATE hora_entrada = VALUES(hora_entrada), hora_salida = VALUES(hora_salida);
*/
