<?php
// Modelo: Asistencias

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once APP_PATH . '/Models/Database.php';

class Attendance
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM attendance")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('estado_entrada', $cols, true)) {
                $this->db->exec("ALTER TABLE attendance ADD COLUMN estado_entrada ENUM('A tiempo', 'Tolerancia', 'Tardanza', 'Justificado') DEFAULT 'A tiempo'");
            }
            if (!in_array('minutos_retraso', $cols, true)) {
                $this->db->exec("ALTER TABLE attendance ADD COLUMN minutos_retraso INT DEFAULT 0");
            }
            if (!in_array('motivo_tardanza', $cols, true)) {
                $this->db->exec("ALTER TABLE attendance ADD COLUMN motivo_tardanza TEXT DEFAULT NULL");
            }
        } catch (\Exception $e) {
            // ignorar si ya existen
        }
    }

    //Consultas

    /* Asistencias del día de hoy con datos del obrero */
    public function getTodayAttendance(): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.fecha, a.hora_entrada, a.hora_salida, a.estado_entrada, a.minutos_retraso, a.motivo_tardanza,
                    w.cedula, w.nombre, w.apellido, w.cargo, w.departamento
             FROM attendance a
             JOIN workers w ON a.worker_id = w.id
             WHERE a.fecha = CURDATE()
             ORDER BY COALESCE(a.hora_entrada, a.hora_salida)"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /* Registro de hoy de un obrero específico */
    public function getTodayByWorkerId(int $workerId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM attendance WHERE worker_id = ? AND fecha = CURDATE() LIMIT 1"
        );
        $stmt->execute([$workerId]);
        return $stmt->fetch() ?: null;
    }

    /* Asistencias en un rango de fechas con datos del obrero **/
    public function getByDateRange(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.fecha, a.hora_entrada, a.hora_salida, a.estado_entrada, a.minutos_retraso, a.motivo_tardanza,
                    w.cedula, w.nombre, w.apellido, w.cargo, w.departamento
             FROM attendance a
             JOIN workers w ON a.worker_id = w.id
             WHERE a.fecha BETWEEN :from AND :to
             ORDER BY a.fecha DESC, w.apellido, w.nombre"
        );
        $stmt->execute([':from' => $from, ':to' => $to]);
        return $stmt->fetchAll();
    }

    /* Cantidad de registros de asistencia del día */
    public function countToday(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM attendance WHERE fecha = CURDATE()"
        )->fetchColumn();
    }

    // Cantidad de asistencias completas entrada + salida del día
    public function countTodayComplete(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM attendance
             WHERE fecha = CURDATE()
               AND hora_entrada IS NOT NULL
               AND hora_salida  IS NOT NULL"
        )->fetchColumn();
    }

    /**
     * Registrar ENTRADA del día de hoy con estado, retraso y motivo
     */
    public function registerEntry(int $workerId, string $estadoEntrada = 'A tiempo', int $minutosRetraso = 0, ?string $motivoTardanza = null): bool
    {
        $existing = $this->getTodayByWorkerId($workerId);
        if ($existing && !empty($existing['hora_entrada'])) {
            return false;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO attendance (worker_id, fecha, hora_entrada, estado_entrada, minutos_retraso, motivo_tardanza)
             VALUES (?, CURDATE(), CURTIME(), ?, ?, ?)"
        );
        return $stmt->execute([$workerId, $estadoEntrada, $minutosRetraso, $motivoTardanza]);
    }

    /**
     * Registrar SALIDA del día de hoy.
     * Actualiza hora_salida = NOW() en el registro existente.
     */
    public function registerExit(int $workerId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE attendance SET hora_salida = CURTIME()
             WHERE worker_id = ? AND fecha = CURDATE()"
        );
        return $stmt->execute([$workerId]);
    }
}
