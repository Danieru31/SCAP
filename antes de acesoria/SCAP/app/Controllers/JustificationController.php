<?php
//cru de Justificativos de asistencia

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/Models/Justification.php';
require_once APP_PATH  . '/Models/Worker.php';

class JustificationController extends Controller
{
    private Justification $model;
    private Worker $workerModel;

    public function __construct()
    {
        $this->model       = new Justification();
        $this->workerModel = new Worker();
    }

    //justificativos
    public function index(): void
    {
        $this->requireAuth();
        
        $this->render('justifications.index', [
            'title'          => 'Justificativos',
            'justifications' => $this->model->getAll(),
            'workers'        => $this->workerModel->getAll(true), // Solo obreros activos
            '_token'         => $this->csrfToken(),
        ]);
    }

    //justificativos, datos?id=
    public function getData(): void
    {
        $this->requireAuth();
        $id = (int)$this->get('id');
        $just = $this->model->findById($id);

        $just
            ? $this->json(['success' => true, 'data' => $just])
            : $this->json(['success' => false, 'message' => 'Justificativo no encontrado.'], 404);
    }

    //crear justificativos
    public function store(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $worker_id     = (int)$this->post('worker_id');
        $tipo_ausencia = $this->post('tipo_ausencia');
        $motivo        = trim($this->post('motivo'));
        $fecha_inicio  = $this->post('fecha_inicio');
        $fecha_fin     = $this->post('fecha_fin');
        $estado        = $this->post('estado', 'Pendiente');

        $allowedTypes = ['Enfermedad', 'Motivo personal'];
        if (!in_array($tipo_ausencia, $allowedTypes, true)) {
            $tipo_ausencia = 'Enfermedad';
        }

        if (empty($worker_id) || empty($motivo) || empty($fecha_inicio) || empty($fecha_fin)) {
            $this->json(['success' => false, 'message' => 'Personal, Motivo, Fecha de inicio y Fecha de fin son obligatorios.']);
        }

        if ($fecha_fin < $fecha_inicio) {
            $this->json(['success' => false, 'message' => 'La fecha de fin no puede ser anterior a la fecha de inicio.']);
        }

        $documento = $this->handleFileUpload();

        $data = [
            'worker_id'     => $worker_id,
            'tipo_ausencia' => $tipo_ausencia,
            'motivo'        => $motivo,
            'fecha_inicio'  => $fecha_inicio,
            'fecha_fin'     => $fecha_fin,
            'documento'     => $documento,
            'estado'        => $estado,
        ];

        $this->model->create($data)
            ? $this->json(['success' => true, 'message' => 'Justificativo registrado exitosamente.'])
            : $this->json(['success' => false, 'message' => 'Error al registrar el justificativo.'], 500);
    }

    ///editarjustificativos
    public function update(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $id            = (int)$this->post('id');
        $worker_id     = (int)$this->post('worker_id');
        $tipo_ausencia = $this->post('tipo_ausencia');
        $motivo        = trim($this->post('motivo'));
        $fecha_inicio  = $this->post('fecha_inicio');
        $fecha_fin     = $this->post('fecha_fin');
        $estado        = $this->post('estado');

        $allowedTypes = ['Enfermedad', 'Motivo personal'];
        if (!in_array($tipo_ausencia, $allowedTypes, true)) {
            $tipo_ausencia = 'Enfermedad';
        }

        if (!$id || empty($worker_id) || empty($motivo) || empty($fecha_inicio) || empty($fecha_fin) || empty($estado)) {
            $this->json(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
        }

        if ($fecha_fin < $fecha_inicio) {
            $this->json(['success' => false, 'message' => 'La fecha de fin no puede ser anterior a la fecha de inicio.']);
        }

        $existing = $this->model->findById($id);
        $documento = $this->handleFileUpload($existing['documento'] ?? null);

        $data = [
            'worker_id'     => $worker_id,
            'tipo_ausencia' => $tipo_ausencia,
            'motivo'        => $motivo,
            'fecha_inicio'  => $fecha_inicio,
            'fecha_fin'     => $fecha_fin,
            'documento'     => $documento,
            'estado'        => $estado,
        ];

        $this->model->update($id, $data)
            ? $this->json(['success' => true, 'message' => 'Justificativo actualizado exitosamente.'])
            : $this->json(['success' => false, 'message' => 'Error al actualizar el justificativo.'], 500);
    }

    private function handleFileUpload(?string $currentFile = null): ?string
    {
        if (!isset($_FILES['documento']) || $_FILES['documento']['error'] === UPLOAD_ERR_NO_FILE) {
            return $currentFile;
        }

        $file = $_FILES['documento'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'Error al subir el archivo adjunto.']);
        }

        // Límite de 5 MB
        $maxSizeBytes = 5 * 1024 * 1024;
        if ($file['size'] > $maxSizeBytes) {
            $this->json(['success' => false, 'message' => 'El archivo supera el tamaño máximo permitido de 5 MB.']);
        }

        // Formatos permitidos PDF, JPG, PNG
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions, true)) {
            $this->json(['success' => false, 'message' => 'Formato no permitido. Solo se aceptan archivos PDF, JPG y PNG.']);
        }

        $uploadDir = ROOT_PATH . '/public/uploads/justificaciones/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'justif_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->json(['success' => false, 'message' => 'Error al guardar el archivo adjunto en el servidor.'], 500);
        }

        return $filename;
    }

    //eliminar justificativos
    public function destroy(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $id = (int)$this->post('id');
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID inválido.']);
        }

        $this->model->delete($id)
            ? $this->json(['success' => true, 'message' => 'Justificativo eliminado permanentemente.'])
            : $this->json(['success' => false, 'message' => 'Error al eliminar el justificativo.'], 500);
    }
}
