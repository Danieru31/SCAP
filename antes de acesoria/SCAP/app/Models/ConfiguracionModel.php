<?php

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once APP_PATH . '/Models/Database.php';

class ConfiguracionModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->asegurarColumnaEncabezado();
    }

    private function asegurarColumnaEncabezado(): void {
        try {
            // Verificar si la tabla existe
            $this->db->exec("CREATE TABLE IF NOT EXISTS configuracion (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                nombre_empresa VARCHAR(150) NOT NULL DEFAULT 'Sistema SCAP',
                encabezado TEXT NULL,
                logo_url VARCHAR(255) NULL,
                hora_entrada TIME NOT NULL DEFAULT '08:00:00',
                hora_salida TIME NOT NULL DEFAULT '17:00:00',
                margen_tolerancia INT NOT NULL DEFAULT 15,
                color_header VARCHAR(20) NOT NULL DEFAULT '#1e293b',
                color_sidebar VARCHAR(20) NOT NULL DEFAULT '#0f172a',
                color_fondo TEXT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Asegurar columna encabezado
            $checkEnc = $this->db->query("SHOW COLUMNS FROM configuracion LIKE 'encabezado'")->fetch();
            if (!$checkEnc) {
                $this->db->exec("ALTER TABLE configuracion ADD COLUMN encabezado TEXT NULL AFTER nombre_empresa");
            }

            // Asegurar columna color_fondo
            $checkBg = $this->db->query("SHOW COLUMNS FROM configuracion LIKE 'color_fondo'")->fetch();
            if (!$checkBg) {
                $this->db->exec("ALTER TABLE configuracion ADD COLUMN color_fondo TEXT NULL AFTER color_sidebar");
            }
        } catch (\PDOException $e) {
            // Silencioso si ya existe
        }
    }

    public function obtenerConfiguracion(): array {
        $stmt = $this->db->prepare("SELECT * FROM configuracion WHERE id = 1 LIMIT 1");
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $defaultEncabezado = "REPÚBLICA BOLIVARIANA DE VENEZUELA\nMINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN\nSISTEMA DE CONTROL DE ASISTENCIA (SCAP)";
        $defaultFondo = "linear-gradient(135deg, #eef2ff 0%, #e0e7ff 50%, #f1f5f9 100%)";

        if (!$res) {
            return [
                'nombre_empresa' => 'Sistema SCAP',
                'encabezado' => $defaultEncabezado,
                'logo_url' => null,
                'hora_entrada' => '08:00:00',
                'hora_salida' => '17:00:00',
                'margen_tolerancia' => 15,
                'color_header' => '#1e293b',
                'color_sidebar' => '#0f172a',
                'color_fondo' => $defaultFondo
            ];
        }

        if (empty($res['encabezado'])) {
            $res['encabezado'] = $defaultEncabezado;
        }

        if (empty($res['color_fondo'])) {
            $res['color_fondo'] = $defaultFondo;
        }

        return $res;
    }

    public function actualizarHorarios(string $entrada, string $salida, int $tolerancia): bool {
        $stmt = $this->db->prepare("UPDATE configuracion SET hora_entrada = ?, hora_salida = ?, margen_tolerancia = ? WHERE id = 1");
        return $stmt->execute([$entrada, $salida, $tolerancia]);
    }

    public function actualizarTema(string $header, string $sidebar, ?string $fondo = null): bool {
        if ($fondo !== null) {
            $stmt = $this->db->prepare("UPDATE configuracion SET color_header = ?, color_sidebar = ?, color_fondo = ? WHERE id = 1");
            return $stmt->execute([$header, $sidebar, $fondo]);
        }
        $stmt = $this->db->prepare("UPDATE configuracion SET color_header = ?, color_sidebar = ? WHERE id = 1");
        return $stmt->execute([$header, $sidebar]);
    }

    public function actualizarEncabezado(string $encabezado, ?string $logo = null): bool {
        // Verificar si la fila 1 existe
        $check = $this->db->query("SELECT id FROM configuracion WHERE id = 1")->fetch();
        if (!$check) {
            $stmt = $this->db->prepare("INSERT INTO configuracion (id, encabezado, logo_url) VALUES (1, ?, ?)");
            return $stmt->execute([$encabezado, $logo]);
        }

        if ($logo !== null && $logo !== '') {
            $stmt = $this->db->prepare("UPDATE configuracion SET encabezado = ?, logo_url = ? WHERE id = 1");
            return $stmt->execute([$encabezado, $logo]);
        }

        $stmt = $this->db->prepare("UPDATE configuracion SET encabezado = ? WHERE id = 1");
        return $stmt->execute([$encabezado]);
    }

    public function actualizarEmpresa(string $nombre, ?string $logo): bool {
        return $this->actualizarEncabezado($nombre, $logo);
    }
}