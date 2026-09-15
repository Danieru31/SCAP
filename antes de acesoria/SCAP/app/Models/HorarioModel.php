<?php
// Modelo de Horario del Trabajador por Cargo

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once APP_PATH . '/Models/Database.php';

class HorarioModel
{
    private PDO $db;

    // Cargos válidos del sistema
    public const CARGOS = [
        'Docente',
        'Administrador',
        'Director',
        'Vigilante',
        'Cocinero',
        'Limpieza',
        'Ambientalista',
        'Obrero',
    ];

    // Áreas / Ubicaciones disponibles por cargo
    public const UBICACIONES = [
        'Cocina',
        'Limpieza',
        'Ambientalista',
        'Vigilante',
        'Administración',
        'Dirección',
        'Aula',       // Para docentes (se complementa con número de aula)
        'General',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS `horarios_cargo` (
                `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `cargo`        VARCHAR(100)     NOT NULL,
                `hora_entrada` TIME             NOT NULL,
                `hora_salida`  TIME             NOT NULL,
                `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_cargo` (`cargo`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $this->db->exec("CREATE TABLE IF NOT EXISTS `horarios_asignaciones` (
                `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `worker_id`   INT(10) UNSIGNED NOT NULL,
                `ubicacion`   VARCHAR(100)     NOT NULL,
                `numero_aula` VARCHAR(20)      DEFAULT NULL,
                `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_worker_asignacion` (`worker_id`),
                CONSTRAINT `fk_asignacion_worker`
                    FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        } catch (\Exception $e) {
            // Silencioso si ya existen
        }
    }

    /** Obtiene todos los cargos disponibles (tanto los base como los registrados en la tabla workers) */
    public function getAllCargos(): array
    {
        $cargos = self::CARGOS;
        try {
            $stmt = $this->db->query("SELECT DISTINCT cargo FROM workers WHERE cargo IS NOT NULL AND TRIM(cargo) != ''");
            $dbCargos = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $cargos = array_unique(array_merge($cargos, $dbCargos));
            sort($cargos);
        } catch (\Exception $e) {
            // fallback a lista base
        }
        return array_values($cargos);
    }

    // ─── Horarios por Cargo ───────────────────────────────────────────────────

    /** Obtiene todos los horarios registrados por cargo */
    public function getHorariosCargo(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM horarios_cargo ORDER BY cargo ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Obtiene el horario de un cargo específico */
    public function getHorarioByCargo(string $cargo): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM horarios_cargo WHERE cargo = ? LIMIT 1"
        );
        $stmt->execute([$cargo]);
        return $stmt->fetch() ?: null;
    }

    /** Inserta o actualiza el horario de un cargo (UPSERT) */
    public function upsertHorarioCargo(string $cargo, string $horaEntrada, string $horaSalida): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO horarios_cargo (cargo, hora_entrada, hora_salida)
             VALUES (:cargo, :entrada, :salida)
             ON DUPLICATE KEY UPDATE
                hora_entrada = VALUES(hora_entrada),
                hora_salida  = VALUES(hora_salida),
                updated_at   = CURRENT_TIMESTAMP"
        );
        return $stmt->execute([
            ':cargo'   => $cargo,
            ':entrada' => $horaEntrada,
            ':salida'  => $horaSalida,
        ]);
    }

    /** Elimina el horario personalizado de un cargo */
    public function deleteHorarioCargo(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM horarios_cargo WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ─── Tolerancia Global ────────────────────────────────────────────────────

    /** Lee la tolerancia global (tabla configuracion existente) */
    public function getTolerancia(): int
    {
        $stmt = $this->db->prepare(
            "SELECT margen_tolerancia FROM configuracion WHERE id = 1 LIMIT 1"
        );
        $stmt->execute();
        $row = $stmt->fetch();
        return (int) ($row['margen_tolerancia'] ?? 15);
    }

    /** Guarda la tolerancia global */
    public function saveTolerancia(int $minutos): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO configuracion (id, margen_tolerancia) VALUES (1, :minutos)
             ON DUPLICATE KEY UPDATE margen_tolerancia = :minutos2"
        );
        return $stmt->execute([':minutos' => $minutos, ':minutos2' => $minutos]);
    }

    // ─── Asignaciones de Ubicación ────────────────────────────────────────────

    /** Lista todos los trabajadores con su asignación de ubicación actual */
    public function getAsignaciones(): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                w.id,
                COALESCE(NULLIF(w.nombres_apellidos,''), TRIM(CONCAT(w.nombre,' ',w.apellido))) AS nombres_apellidos,
                w.cargo,
                COALESCE(NULLIF(w.departamento, ''), 'General') AS departamento,
                COALESCE(a.ubicacion, 'Sin asignar')  AS ubicacion,
                COALESCE(a.numero_aula, '')            AS numero_aula,
                a.id AS asignacion_id
             FROM workers w
             LEFT JOIN horarios_asignaciones a ON w.id = a.worker_id
             WHERE w.deleted = 0
             ORDER BY w.apellido, w.nombre"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Obtiene una asignación individual por ID */
    public function getAsignacionById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, 
                COALESCE(NULLIF(w.nombres_apellidos,''), TRIM(CONCAT(w.nombre,' ',w.apellido))) AS nombres_apellidos,
                w.cargo
             FROM horarios_asignaciones a
             JOIN workers w ON a.worker_id = w.id
             WHERE a.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Inserta o actualiza la asignación de un trabajador */
    public function upsertAsignacion(int $workerId, string $ubicacion, string $numeroAula = ''): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO horarios_asignaciones (worker_id, ubicacion, numero_aula)
             VALUES (:wid, :ubicacion, :aula)
             ON DUPLICATE KEY UPDATE
                ubicacion   = VALUES(ubicacion),
                numero_aula = VALUES(numero_aula),
                updated_at  = CURRENT_TIMESTAMP"
        );
        return $stmt->execute([
            ':wid'      => $workerId,
            ':ubicacion' => $ubicacion,
            ':aula'     => $numeroAula,
        ]);
    }

    /** Elimina la asignación de un trabajador */
    public function deleteAsignacion(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM horarios_asignaciones WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /** Obtiene datos de un worker para modal de edición */
    public function getWorkerById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT w.id,
                COALESCE(NULLIF(w.nombres_apellidos,''), TRIM(CONCAT(w.nombre,' ',w.apellido))) AS nombres_apellidos,
                w.cargo,
                COALESCE(a.ubicacion,'')    AS ubicacion,
                COALESCE(a.numero_aula,'')  AS numero_aula,
                a.id AS asignacion_id
             FROM workers w
             LEFT JOIN horarios_asignaciones a ON w.id = a.worker_id
             WHERE w.id = ? AND w.deleted = 0 LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
