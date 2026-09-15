<?php
//Personal Obrero modulo

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once APP_PATH . '/Models/Database.php';

class Worker
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
//consultas

    public function getAll(bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM workers WHERE deleted = 0";
        if ($activeOnly) {
            $sql .= " AND activo = 1";
        }
        $sql .= " ORDER BY apellido, nombre";
        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM workers WHERE id = ? AND deleted = 0 LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByCedula(string $cedula, bool $activeOnly = true): ?array
    {
        $sql = "SELECT * FROM workers WHERE cedula = ? AND deleted = 0";
        if ($activeOnly) $sql .= " AND activo = 1";
        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cedula]);
        return $stmt->fetch() ?: null;
    }

    public function countActive(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM workers WHERE activo = 1 AND deleted = 0"
        )->fetchColumn();
    }

//escritura
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO workers (
                cedula, posee_documento, nombres_apellidos, nombre, apellido,
                cargo, departamento, telefono, grado_academico, condicion_medica, anios_servicio
             ) VALUES (
                :cedula, :posee_documento, :nombres_apellidos, :nombre, :apellido,
                :cargo, :departamento, :telefono, :grado_academico, :condicion_medica, :anios_servicio
             )"
        );
        return $stmt->execute([
            ':cedula'            => $data['cedula'],
            ':posee_documento'   => $data['posee_documento'] ?? 'Sí',
            ':nombres_apellidos' => $data['nombres_apellidos'],
            ':nombre'            => $data['nombre'] ?? '',
            ':apellido'          => $data['apellido'] ?? '',
            ':cargo'             => $data['cargo'] ?? 'Obrero',
            ':departamento'      => $data['departamento'] ?? 'General',
            ':telefono'          => $data['telefono'] ?? null,
            ':grado_academico'   => $data['grado_academico'] ?? 'Ninguno',
            ':condicion_medica'  => $data['condicion_medica'] ?? 'Ninguna',
            ':anios_servicio'    => (int) ($data['anios_servicio'] ?? 0),
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE workers
             SET cedula=:cedula,
                 posee_documento=:posee_documento,
                 nombres_apellidos=:nombres_apellidos,
                 nombre=:nombre,
                 apellido=:apellido,
                 cargo=:cargo,
                 departamento=:departamento,
                 telefono=:telefono,
                 grado_academico=:grado_academico,
                 condicion_medica=:condicion_medica,
                 anios_servicio=:anios_servicio,
                 activo=:activo
             WHERE id=:id"
        );
        return $stmt->execute([
            ':cedula'            => $data['cedula'],
            ':posee_documento'   => $data['posee_documento'] ?? 'Sí',
            ':nombres_apellidos' => $data['nombres_apellidos'],
            ':nombre'            => $data['nombre'] ?? '',
            ':apellido'          => $data['apellido'] ?? '',
            ':cargo'             => $data['cargo'] ?? 'Obrero',
            ':departamento'      => $data['departamento'] ?? 'General',
            ':telefono'          => $data['telefono'] ?? null,
            ':grado_academico'   => $data['grado_academico'] ?? 'Ninguno',
            ':condicion_medica'  => $data['condicion_medica'] ?? 'Ninguna',
            ':anios_servicio'    => (int) ($data['anios_servicio'] ?? 0),
            ':activo'            => (int) ($data['activo'] ?? 1),
            ':id'                 => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        // Soft delete: cambiar deleted a 1
        $stmt = $this->db->prepare("UPDATE workers SET deleted = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getDeleted(): array
    {
        return $this->db->query(
            "SELECT * FROM workers WHERE deleted = 1 ORDER BY apellido, nombre"
        )->fetchAll();
    }

    public function restore(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE workers SET deleted = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function forceDelete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM workers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    //validacion

    public function cedulaExists(string $cedula, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM workers WHERE cedula = ? AND id != ? LIMIT 1"
        );
        $stmt->execute([$cedula, $excludeId]);
        return (bool) $stmt->fetch();
    }
    /**
     *Reporte de Horas Trabajadas:
     *Diario: Horas calculadas entre hora_entrada y hora_salida del día.
     *Semanal: Horas acumuladas de Lunes a Viernes de la semana correspondiente.
     */
    public function getWorkHoursReport(string $targetDate = ''): array
    {
        $targetDate = !empty($targetDate) ? $targetDate : date('Y-m-d');
        $timestamp  = strtotime($targetDate);

        // Lunes (1) a Viernes (5) de la semana
        $dayOfWeek = (int) date('N', $timestamp);
        $mondayTs  = strtotime('-' . ($dayOfWeek - 1) . ' days', $timestamp);
        $fridayTs  = strtotime('+' . (5 - $dayOfWeek) . ' days', $timestamp);

        $monday = date('Y-m-d', $mondayTs);
        $friday = date('Y-m-d', $fridayTs);

        $sql = "SELECT 
                    w.id AS worker_id,
                    w.cedula,
                    COALESCE(NULLIF(w.nombres_apellidos, ''), TRIM(CONCAT(w.nombre, ' ', w.apellido))) AS nombres_apellidos,
                    w.cargo,
                    w.activo,
                    
                    -- Marcajes del día seleccionado
                    MAX(CASE WHEN a.fecha = :td1 THEN a.hora_entrada END) AS hora_entrada_dia,
                    MAX(CASE WHEN a.fecha = :td2 THEN a.hora_salida END) AS hora_salida_dia,
                    
                    -- Segundos laborados en el día
                    COALESCE(
                        SUM(CASE WHEN a.fecha = :td3 AND a.hora_entrada IS NOT NULL AND a.hora_salida IS NOT NULL 
                            THEN TIMESTAMPDIFF(SECOND, CONCAT(a.fecha, ' ', a.hora_entrada), CONCAT(a.fecha, ' ', a.hora_salida)) 
                            ELSE 0 END), 0
                    ) AS segundos_dia,
                    
                    -- Segundos acumulados en la semana (Lunes a Viernes)
                    COALESCE(
                        SUM(CASE WHEN a.fecha BETWEEN :m1 AND :f1 AND a.hora_entrada IS NOT NULL AND a.hora_salida IS NOT NULL 
                            THEN TIMESTAMPDIFF(SECOND, CONCAT(a.fecha, ' ', a.hora_entrada), CONCAT(a.fecha, ' ', a.hora_salida)) 
                            ELSE 0 END), 0
                    ) AS segundos_semana

                FROM workers w
                LEFT JOIN attendance a ON w.id = a.worker_id AND a.fecha BETWEEN :m2 AND :f2
                WHERE w.deleted = 0
                GROUP BY w.id, w.cedula, w.nombres_apellidos, w.nombre, w.apellido, w.cargo, w.activo
                ORDER BY nombres_apellidos ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':td1' => $targetDate,
            ':td2' => $targetDate,
            ':td3' => $targetDate,
            ':m1'  => $monday,
            ':f1'  => $friday,
            ':m2'  => $monday,
            ':f2'  => $friday,
        ]);

        return [
            'fecha'   => $targetDate,
            'lunes'   => $monday,
            'viernes' => $friday,
            'report'  => $stmt->fetchAll(),
        ];
    }
}
