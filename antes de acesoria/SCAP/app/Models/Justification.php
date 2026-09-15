<?php
//Justificativos modelos

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once APP_PATH . '/Models/Database.php';

class Justification
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

//consultas
    public function getAll(): array
    {
        $sql = "SELECT j.*, w.cedula, w.nombre, w.apellido, w.cargo, w.departamento 
                FROM justifications j
                JOIN workers w ON j.worker_id = w.id
                ORDER BY j.fecha_inicio DESC, w.apellido, w.nombre";
        return $this->db->query($sql)->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT j.*, w.cedula, w.nombre, w.apellido 
             FROM justifications j
             JOIN workers w ON j.worker_id = w.id
             WHERE j.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function countPending(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM justifications WHERE estado = 'Pendiente'"
        )->fetchColumn();
    }

//escribir
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO justifications (worker_id, tipo_ausencia, motivo, fecha_inicio, fecha_fin, documento, estado)
             VALUES (:worker_id, :tipo_ausencia, :motivo, :fecha_inicio, :fecha_fin, :documento, :estado)"
        );
        return $stmt->execute([
            ':worker_id'     => (int)$data['worker_id'],
            ':tipo_ausencia' => $data['tipo_ausencia'] ?? 'Enfermedad',
            ':motivo'        => $data['motivo'],
            ':fecha_inicio'  => $data['fecha_inicio'],
            ':fecha_fin'     => $data['fecha_fin'],
            ':documento'     => $data['documento'] ?? null,
            ':estado'        => $data['estado'] ?? 'Pendiente',
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE justifications
             SET worker_id = :worker_id, tipo_ausencia = :tipo_ausencia, motivo = :motivo, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, documento = :documento, estado = :estado
             WHERE id = :id"
        );
        return $stmt->execute([
            ':worker_id'     => (int)$data['worker_id'],
            ':tipo_ausencia' => $data['tipo_ausencia'],
            ':motivo'        => $data['motivo'],
            ':fecha_inicio'  => $data['fecha_inicio'],
            ':fecha_fin'     => $data['fecha_fin'],
            ':documento'     => $data['documento'] ?? null,
            ':estado'        => $data['estado'],
            ':id'            => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM justifications WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
