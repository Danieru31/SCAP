<?php
/**
 * SCAP - Modelo: Calendario Laboral (work_calendar)
 */

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once APP_PATH . '/Models/Database.php';

class WorkCalendar
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTableExists();
    }

    /**
     * Asegura la creación de la tabla `work_calendar` en MySQL
     */
    private function ensureTableExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `work_calendar` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `fecha` DATE NOT NULL,
            `tipo_estado` ENUM('laborable','vacaciones','feriado','cumpleanios','emergencia') NOT NULL,
            `motivo_emergencia` VARCHAR(100) DEFAULT NULL,
            `nota` TEXT DEFAULT NULL,
            `worker_id` INT UNSIGNED DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_fecha` (`fecha`),
            KEY `fk_calendar_worker` (`worker_id`),
            CONSTRAINT `fk_calendar_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->exec($sql);
    }

    /**
     * Obtiene todos los registros del calendario para un año específico
     */
    public function getByYear(int $year): array
    {
        $sql = "SELECT c.*, 
                       w.nombre AS worker_nombre, 
                       w.apellido AS worker_apellido, 
                       w.cargo AS worker_cargo, 
                       w.departamento AS worker_departamento
                FROM work_calendar c
                LEFT JOIN workers w ON c.worker_id = w.id
                WHERE YEAR(c.fecha) = :year
                ORDER BY c.fecha ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':year' => $year]);
        $rows = $stmt->fetchAll();

        // Indexar por fecha para rápida lectura en JavaScript/PHP
        $calendar = [];
        foreach ($rows as $row) {
            $key = $row['fecha'];
            $calendar[$key] = [
                'id'                => $row['id'],
                'fecha'             => $row['fecha'],
                'tipo_estado'       => $row['tipo_estado'],
                'motivo_emergencia' => $row['motivo_emergencia'],
                'nota'              => $row['nota'],
                'worker_id'         => $row['worker_id'],
                'worker_nombre'     => $row['worker_nombre'] ? ($row['worker_nombre'] . ' ' . $row['worker_apellido']) : null,
                'worker_cargo'      => $row['worker_cargo'] ?? null,
            ];
        }
        return $calendar;
    }

    /**
     * Marca los días de Lunes a Viernes como laborables dentro de un rango de fechas
     */
    public function bulkMarkWorkdays(string $from, string $to, string $nota = 'Jornada Laborable Activa'): int
    {
        $startDate = new DateTime($from);
        $endDate   = new DateTime($to);
        $interval  = new DateInterval('P1D');
        $period    = new DatePeriod($startDate, $interval, (clone $endDate)->modify('+1 day'));

        $stmtSave = $this->db->prepare(
            "INSERT INTO work_calendar (fecha, tipo_estado, nota)
             VALUES (:fecha, 'laborable', :nota)
             ON DUPLICATE KEY UPDATE
                tipo_estado = 'laborable',
                nota = VALUES(nota),
                motivo_emergencia = NULL,
                worker_id = NULL"
        );

        $count = 0;
        $this->db->beginTransaction();
        try {
            foreach ($period as $dt) {
                $dayOfWeek = (int) $dt->format('N'); // 1 = Lunes, ..., 7 = Domingo
                if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                    $stmtSave->execute([
                        ':fecha' => $dt->format('Y-m-d'),
                        ':nota'  => $nota ?: 'Jornada Laborable Activa',
                    ]);
                    $count++;
                }
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $count;
    }

    /**
     * Registra un rango o días de vacaciones
     */
    public function saveVacation(string $from, string $to, ?int $workerId, string $nota): int
    {
        $startDate = new DateTime($from);
        $endDate   = new DateTime($to);
        $interval  = new DateInterval('P1D');
        $period    = new DatePeriod($startDate, $interval, (clone $endDate)->modify('+1 day'));

        $stmtSave = $this->db->prepare(
            "INSERT INTO work_calendar (fecha, tipo_estado, worker_id, nota)
             VALUES (:fecha, 'vacaciones', :worker_id, :nota)
             ON DUPLICATE KEY UPDATE 
                tipo_estado = 'vacaciones', 
                worker_id = VALUES(worker_id),
                nota = VALUES(nota), 
                motivo_emergencia = NULL"
        );

        $count = 0;
        $this->db->beginTransaction();
        try {
            foreach ($period as $dt) {
                $stmtSave->execute([
                    ':fecha'     => $dt->format('Y-m-d'),
                    ':worker_id' => $workerId,
                    ':nota'      => $nota ?: 'Periodo Vacacional',
                ]);
                $count++;
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $count;
    }

    /**
     * Registra un día feriado
     */
    public function saveHoliday(string $fecha, string $nombre, string $tipo): bool
    {
        $nota = $nombre . ($tipo ? " ($tipo)" : '');
        $stmt = $this->db->prepare(
            "INSERT INTO work_calendar (fecha, tipo_estado, nota)
             VALUES (:fecha, 'feriado', :nota)
             ON DUPLICATE KEY UPDATE 
                tipo_estado = 'feriado', 
                nota = VALUES(nota), 
                motivo_emergencia = NULL,
                worker_id = NULL"
        );
        return $stmt->execute([
            ':fecha' => $fecha,
            ':nota'  => $nota,
        ]);
    }

    /**
     * Registra el cumpleaños de un trabajador en una fecha
     */
    public function saveBirthday(string $fecha, int $workerId, string $nota): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO work_calendar (fecha, tipo_estado, worker_id, nota)
             VALUES (:fecha, 'cumpleanios', :worker_id, :nota)
             ON DUPLICATE KEY UPDATE 
                tipo_estado = 'cumpleanios', 
                worker_id = VALUES(worker_id),
                nota = VALUES(nota), 
                motivo_emergencia = NULL"
        );
        return $stmt->execute([
            ':fecha'     => $fecha,
            ':worker_id' => $workerId,
            ':nota'      => $nota ?: 'Cumpleaños de personal',
        ]);
    }

    /**
     * Registra una suspensión por emergencia o decreto
     */
    public function saveEmergency(string $from, string $to, string $motivo, string $descripcion): int
    {
        $startDate = new DateTime($from);
        $endDate   = new DateTime($to);
        $interval  = new DateInterval('P1D');
        $period    = new DatePeriod($startDate, $interval, (clone $endDate)->modify('+1 day'));

        $stmtSave = $this->db->prepare(
            "INSERT INTO work_calendar (fecha, tipo_estado, motivo_emergencia, nota)
             VALUES (:fecha, 'emergencia', :motivo, :nota)
             ON DUPLICATE KEY UPDATE 
                tipo_estado = 'emergencia', 
                motivo_emergencia = VALUES(motivo), 
                nota = VALUES(nota)"
        );

        $count = 0;
        $this->db->beginTransaction();
        try {
            foreach ($period as $dt) {
                $stmtSave->execute([
                    ':fecha'  => $dt->format('Y-m-d'),
                    ':motivo' => $motivo,
                    ':nota'   => $descripcion ?: 'Suspensión de actividades laborales',
                ]);
                $count++;
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $count;
    }

    /**
     * Elimina el estado marcado para una fecha
     */
    public function deleteDate(string $fecha): bool
    {
        $stmt = $this->db->prepare("DELETE FROM work_calendar WHERE fecha = ?");
        return $stmt->execute([$fecha]);
    }

    /**
     * Limpia todas las marcas de un año completo
     */
    public function clearYear(int $year): bool
    {
        $stmt = $this->db->prepare("DELETE FROM work_calendar WHERE YEAR(fecha) = ?");
        return $stmt->execute([$year]);
    }
}
