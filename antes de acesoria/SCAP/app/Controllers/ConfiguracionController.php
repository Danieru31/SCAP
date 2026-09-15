<?php

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/Models/ConfiguracionModel.php';
require_once APP_PATH  . '/Models/User.php';

class ConfiguracionController extends Controller {

    private ConfiguracionModel $configModel;
    private User $userModel;

    public function __construct() {
        $this->configModel = new ConfiguracionModel();
        $this->userModel   = new User();
    }

    public function index(): void {
        $this->requireAuth();

        $admin = $this->userModel->findById((int)$_SESSION['admin_id']);
        $config = $this->configModel->obtenerConfiguracion();

        $this->render('configuracion.index', [
            'title'   => 'Configuración del Sistema',
            'admin'   => $admin,
            'config'  => $config,
            '_token'  => $this->csrfToken(),
        ]);
    }

    public function actualizarPerfil(): void {
        $this->requireAuth();
        $this->validateCsrf();

        $id = (int)$_SESSION['admin_id'];
        $nombre = trim($this->post('nombre'));
        $correo = trim($this->post('correo'));
        $password = $this->post('password');

        $admin = $this->userModel->findById($id);
        if (!$admin) {
            $this->redirect('configuracion');
        }

        $fotoNombre = null;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['foto']['tmp_name'];
            $fileName = $_FILES['foto']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = 'avatar_' . $id . '_' . time() . '.' . $fileExtension;
                $uploadFileDir = ROOT_PATH . '/public/uploads/avatars/';

                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $destPath = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $fotoNombre = $newFileName;
                }
            } else {
                $_SESSION['flash_error'] = "Formato de imagen no permitido (solo JPG y PNG).";
                $this->redirect('configuracion');
                return;
            }
        }

        $updateData = [
            'username'   => $admin['username'],
            'nombre'     => $nombre,
            'apellido'   => $admin['apellido'] ?? '',
            'activo'     => $admin['activo'] ?? 1,
            'pregunta_1' => $admin['pregunta_1'] ?? 'Sin pregunta',
            'pregunta_2' => $admin['pregunta_2'] ?? 'Sin pregunta',
            'password'   => $password,
        ];

        if ($fotoNombre) {
            $updateData['foto'] = $fotoNombre;
        }

        if ($this->userModel->update($id, $updateData)) {
            $_SESSION['admin_name'] = $nombre;
            if ($fotoNombre) {
                $_SESSION['admin_foto'] = $fotoNombre;
            }
            $_SESSION['flash_success'] = "Perfil actualizado correctamente.";
        } else {
            $_SESSION['flash_error'] = "No se pudo actualizar el perfil.";
        }

        $this->redirect('configuracion');
    }

    public function guardarHorarios(): void {
        $this->requireAuth();
        $this->validateCsrf();

        $horaEntrada = $this->post('hora_entrada');
        $horaSalida  = $this->post('hora_salida');
        $tolerancia  = (int) $this->post('margen_tolerancia');

        $this->configModel->actualizarHorarios($horaEntrada, $horaSalida, $tolerancia);
        $_SESSION['flash_success'] = "Horarios y tolerancias guardados exitosamente.";
        $this->redirect('configuracion');
    }

    public function guardarTema(): void {
        $this->requireAuth();
        $this->validateCsrf();

        $colorHeader  = $this->post('color_header');
        $colorSidebar = $this->post('color_sidebar');
        $colorFondo   = $this->post('color_fondo');

        $this->configModel->actualizarTema($colorHeader, $colorSidebar, $colorFondo);
        $_SESSION['color_header']  = $colorHeader;
        $_SESSION['color_sidebar'] = $colorSidebar;
        $_SESSION['color_fondo']   = $colorFondo;

        $_SESSION['flash_success'] = "Tema visual y fondo de pantalla guardados correctamente.";
        $this->redirect('configuracion');
    }

    public function guardarEncabezado(): void {
        $this->requireAuth();
        $this->validateCsrf();

        $encabezado = trim($this->post('encabezado'));
        $logoNombre = null;

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath   = $_FILES['logo']['tmp_name'];
            $fileName      = $_FILES['logo']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName   = 'logo_' . time() . '.' . $fileExtension;
                $uploadFileDir = ROOT_PATH . '/public/uploads/logo/';

                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $destPath = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $logoNombre = $newFileName;
                }
            } else {
                $_SESSION['flash_error'] = "Formato de imagen para el logo no permitido (solo JPG, PNG y WEBP).";
                $this->redirect('configuracion');
                return;
            }
        }

        if ($this->configModel->actualizarEncabezado($encabezado, $logoNombre)) {
            $_SESSION['flash_success'] = "Encabezado y logo institucional guardados exitosamente.";
        } else {
            $_SESSION['flash_error'] = "No se pudo actualizar el encabezado de la institución.";
        }

        $this->redirect('configuracion');
    }


    public function respaldarBD(): void {
        $this->requireAuth();

        $dbConfig = require CONFIG_PATH . '/database.php';
        
        try {
            $pdo = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8", $dbConfig['user'], $dbConfig['pass']);
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

            $dump = "-- RESPALDO DE BASE DE DATOS MySQL SCAP\n-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";

            foreach ($tables as $table) {
                $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
                $dump .= "\n\n" . $createTable['Create Table'] . ";\n\n";

                $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $values = array_map(function($val) use ($pdo) {
                        return is_null($val) ? "NULL" : $pdo->quote($val);
                    }, array_values($row));
                    $dump .= "INSERT INTO `$table` VALUES(" . implode(", ", $values) . ");\n";
                }
            }

            $filename = "backup_scap_" . date('Y_m_d_H_i_s') . ".sql";
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $dump;
            exit;

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error al generar el respaldo: " . $e->getMessage();
            $this->redirect('configuracion');
        }
    }
}