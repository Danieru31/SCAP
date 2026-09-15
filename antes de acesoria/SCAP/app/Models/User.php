<?php
// administradores modelos
defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once APP_PATH . '/Models/Database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->asegurarColumnaFoto();
        $this->asegurarColumnaRol();
    }

    private function asegurarColumnaFoto(): void {
        try {
            $check = $this->db->query("SHOW COLUMNS FROM admins LIKE 'foto'")->fetch();
            if (!$check) {
                $this->db->exec("ALTER TABLE admins ADD COLUMN foto VARCHAR(255) NULL AFTER respuesta_2");
            }
        } catch (\PDOException $e) {
            // Ignorar si ya existe
        }
    }

    private function asegurarColumnaRol(): void {
        try {
            $check = $this->db->query("SHOW COLUMNS FROM admins LIKE 'rol'")->fetch();
            if (!$check) {
                $this->db->exec("ALTER TABLE admins ADD COLUMN rol ENUM('mega_admin', 'super_admin', 'admin') NOT NULL DEFAULT 'admin' AFTER apellido");
            }
            // Asegurar que el superusuario raíz precreado (ID 1) mantenga siempre el rol mega_admin
            $this->db->exec("UPDATE admins SET rol = 'mega_admin' WHERE id = 1 AND rol != 'mega_admin'");
        } catch (\PDOException $e) {
            // Ignorar si ya existe o si la tabla aún no se ha creado
        }
    }

   //consultar
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM admins WHERE username = ? AND activo = 1 LIMIT 1"
        );
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, username, nombre, apellido, rol, foto, activo, created_at, pregunta_1, respuesta_1, pregunta_2, respuesta_2
             FROM admins WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(): array
    {
        return $this->db->query(
            "SELECT id, username, nombre, apellido, rol, foto, activo, created_at, pregunta_1, pregunta_2
             FROM admins ORDER BY FIELD(rol, 'mega_admin', 'super_admin', 'admin'), nombre, apellido"
        )->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    }

    /**
     * Verifica si ya existe un Super Administrador activo en la plataforma
     */
    public function hasActiveSuperAdmin(int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM admins WHERE rol = 'super_admin' AND activo = 1 AND id != ?"
        );
        $stmt->execute([$excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

//escribir
    public function create(array $data): bool
    {
        $rol = $data['rol'] ?? 'admin';
        // No se permite crear otro mega_admin bajo ninguna circunstancia
        if ($rol === 'mega_admin') {
            $rol = 'admin';
        }

        // Si se asigna super_admin, garantizar que solo exista 1 activo
        if ($rol === 'super_admin' && $this->hasActiveSuperAdmin()) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO admins (username, password, nombre, apellido, rol, foto, pregunta_1, respuesta_1, pregunta_2, respuesta_2)
             VALUES (:username, :password, :nombre, :apellido, :rol, :foto, :pregunta_1, :respuesta_1, :pregunta_2, :respuesta_2)"
        );
        return $stmt->execute([
            ':username'   => $data['username'],
            ':password'   => password_hash($data['password'], PASSWORD_BCRYPT),
            ':nombre'     => $data['nombre'],
            ':apellido'   => $data['apellido'] ?? '',
            ':rol'        => $rol,
            ':foto'       => $data['foto'] ?? null,
            ':pregunta_1' => $data['pregunta_1'],
            ':respuesta_1'=> password_hash(strtolower(trim($data['respuesta_1'])), PASSWORD_BCRYPT),
            ':pregunta_2' => $data['pregunta_2'],
            ':respuesta_2'=> password_hash(strtolower(trim($data['respuesta_2'])), PASSWORD_BCRYPT),
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $current = $this->findById($id);
        if (!$current) {
            return false;
        }

        // Protección inmutable para el mega_admin
        $isMegaAdmin = ($id === 1 || ($current['rol'] ?? '') === 'mega_admin');

        $fields = [
            'nombre'     => $data['nombre'],
            'apellido'   => $data['apellido'] ?? '',
            'pregunta_1' => $data['pregunta_1'],
            'pregunta_2' => $data['pregunta_2'],
        ];

        if ($isMegaAdmin) {
            // El mega_admin conserva su username, rol e inmutabilidad de activo (siempre 1)
            $fields['username'] = $current['username'];
            $fields['rol']      = 'mega_admin';
            $fields['activo']   = 1;
        } else {
            $fields['username'] = $data['username'];
            $newRol             = $data['rol'] ?? $current['rol'];
            if ($newRol === 'mega_admin') {
                $newRol = 'admin'; // No se permite ascender a mega_admin
            }
            $fields['rol']    = $newRol;
            $fields['activo'] = (int)($data['activo'] ?? 1);

            // Validar restricción de único super_admin activo
            if ($fields['rol'] === 'super_admin' && $fields['activo'] === 1 && $this->hasActiveSuperAdmin($id)) {
                return false;
            }
        }

        $sql = "UPDATE admins SET username=:username, nombre=:nombre, apellido=:apellido, rol=:rol, activo=:activo, pregunta_1=:pregunta_1, pregunta_2=:pregunta_2";
        
        if (!empty($data['foto'])) {
            $sql .= ", foto=:foto";
            $fields['foto'] = $data['foto'];
        }

        if (!empty($data['password'])) {
            $sql .= ", password=:password";
            $fields['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        if (!empty($data['respuesta_1'])) {
            $sql .= ", respuesta_1=:respuesta_1";
            $fields['respuesta_1'] = password_hash(strtolower(trim($data['respuesta_1'])), PASSWORD_BCRYPT);
        }
        
        if (!empty($data['respuesta_2'])) {
            $sql .= ", respuesta_2=:respuesta_2";
            $fields['respuesta_2'] = password_hash(strtolower(trim($data['respuesta_2'])), PASSWORD_BCRYPT);
        }
        
        $sql .= " WHERE id=:id";
        $fields['id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($fields);
    }

    public function delete(int $id): bool
    {
        $current = $this->findById($id);
        // Protección total del mega_admin: prohibido eliminar por código y validación
        if (!$current || $id === 1 || ($current['rol'] ?? '') === 'mega_admin') {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM admins WHERE id = ?");
        return $stmt->execute([$id]);
    }

   //validar
    public function usernameExists(string $username, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM admins WHERE username = ? AND id != ? LIMIT 1"
        );
        $stmt->execute([$username, $excludeId]);
        return (bool) $stmt->fetch();
    }
}
