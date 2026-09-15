<?php
// Controlador de Horario del Trabajador por Cargo

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/Models/HorarioModel.php';
require_once APP_PATH  . '/Middleware/AuthMiddleware.php';

class HorarioController extends Controller
{
    private HorarioModel $model;

    public function __construct()
    {
        $this->model = new HorarioModel();
    }

    // ─── Vista principal ──────────────────────────────────────────────────────

    public function index(): void
    {
        $this->requireAuth();

        $this->render('horario.index', [
            'title'       => 'Horario del Trabajador',
            'horarios'    => $this->model->getHorariosCargo(),
            'asignaciones'=> $this->model->getAsignaciones(),
            'tolerancia'  => $this->model->getTolerancia(),
            'cargos'      => $this->model->getAllCargos(),
            'ubicaciones' => HorarioModel::UBICACIONES,
            'canEdit'     => AuthMiddleware::canEditSchedule(),
            '_token'      => $this->csrfToken(),
        ]);
    }

    // ─── Horarios por Cargo ───────────────────────────────────────────────────

    /** Guarda (UPSERT) el horario de un cargo */
    public function guardarHorarioCargo(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        if (!AuthMiddleware::canEditSchedule()) {
            $this->json(['success' => false, 'message' => 'No posee permisos para modificar el Horario del Trabajador. Módulo en modo solo lectura.'], 403);
            return;
        }

        $cargo       = trim($this->post('cargo', ''));
        $horaEntrada = trim($this->post('hora_entrada', ''));
        $horaSalida  = trim($this->post('hora_salida', ''));

        // Validación de campos obligatorios
        if (empty($cargo) || empty($horaEntrada) || empty($horaSalida)) {
            $this->json(['success' => false, 'message' => 'El cargo, la hora de entrada y la hora de salida son obligatorios.']);
            return;
        }

        // Validar que el cargo no contenga caracteres extraños
        if (strlen($cargo) > 100) {
            $this->json(['success' => false, 'message' => 'El nombre del cargo es demasiado largo.']);
            return;
        }

        // Normalizar formato de hora HH:MM -> HH:MM:00 si aplica
        if (preg_match('/^\d{2}:\d{2}$/', $horaEntrada)) {
            $horaEntrada .= ':00';
        }
        if (preg_match('/^\d{2}:\d{2}$/', $horaSalida)) {
            $horaSalida .= ':00';
        }

        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $horaEntrada) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $horaSalida)) {
            $this->json(['success' => false, 'message' => 'El formato de hora no es válido (HH:MM).']);
            return;
        }

        // La hora de salida debe ser posterior a la hora de entrada
        if ($horaSalida <= $horaEntrada) {
            $this->json(['success' => false, 'message' => 'La hora de salida debe ser posterior a la hora de entrada.']);
            return;
        }

        $this->model->upsertHorarioCargo($cargo, $horaEntrada, $horaSalida)
            ? $this->json(['success' => true,  'message' => "Horario para «{$cargo}» guardado exitosamente."])
            : $this->json(['success' => false, 'message' => 'Error al guardar el horario.'], 500);
    }

    /** Elimina el horario personalizado de un cargo */
    public function eliminarHorarioCargo(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        if (!AuthMiddleware::canEditSchedule()) {
            $this->json(['success' => false, 'message' => 'No posee permisos para modificar el Horario del Trabajador. Módulo en modo solo lectura.'], 403);
            return;
        }

        $id = (int) $this->post('id');
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID de horario inválido.']);
            return;
        }

        $this->model->deleteHorarioCargo($id)
            ? $this->json(['success' => true,  'message' => 'Horario eliminado correctamente.'])
            : $this->json(['success' => false, 'message' => 'Error al eliminar el horario.'], 500);
    }

    // ─── Tolerancia Global ────────────────────────────────────────────────────

    /** Guarda la tolerancia global de llegada */
    public function guardarTolerancia(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        if (!AuthMiddleware::canEditSchedule()) {
            $this->json(['success' => false, 'message' => 'No posee permisos para modificar la tolerancia. Módulo en modo solo lectura.'], 403);
            return;
        }

        $minutos = (int) $this->post('tolerancia_minutos', 0);

        if ($minutos < 0 || $minutos > 120) {
            $this->json(['success' => false, 'message' => 'El tiempo de tolerancia debe estar entre 0 y 120 minutos.']);
            return;
        }

        $this->model->saveTolerancia($minutos)
            ? $this->json(['success' => true,  'message' => "Tolerancia de {$minutos} min. guardada exitosamente."])
            : $this->json(['success' => false, 'message' => 'Error al guardar la tolerancia.'], 500);
    }

    // ─── Asignaciones ─────────────────────────────────────────────────────────

    /** Devuelve JSON con los datos de un trabajador para el modal de edición */
    public function getDatosAsignacion(): void
    {
        $this->requireAuth();

        $id = (int) $this->get('id');
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $worker = $this->model->getWorkerById($id);
        $worker
            ? $this->json(['success' => true, 'data' => $worker])
            : $this->json(['success' => false, 'message' => 'Trabajador no encontrado.'], 404);
    }

    /** Guarda la ubicación/área asignada al trabajador */
    public function guardarAsignacion(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        if (!AuthMiddleware::canEditSchedule()) {
            $this->json(['success' => false, 'message' => 'No posee permisos para modificar las asignaciones. Módulo en modo solo lectura.'], 403);
            return;
        }

        $workerId  = (int) $this->post('worker_id');
        $ubicacion = trim($this->post('ubicacion', ''));
        $aula      = trim($this->post('numero_aula', ''));

        if (!$workerId) {
            $this->json(['success' => false, 'message' => 'Trabajador no válido.']);
            return;
        }

        if (empty($ubicacion)) {
            $this->json(['success' => false, 'message' => 'La ubicación/área de asignación es obligatoria.']);
            return;
        }

        if (!in_array($ubicacion, HorarioModel::UBICACIONES, true)) {
            $this->json(['success' => false, 'message' => 'La ubicación seleccionada no es válida.']);
            return;
        }

        // Si la ubicación es Aula, el número de aula es obligatorio
        if ($ubicacion === 'Aula' && empty($aula)) {
            $this->json(['success' => false, 'message' => 'Debe especificar el número de aula para esta asignación.']);
            return;
        }

        $this->model->upsertAsignacion($workerId, $ubicacion, $aula)
            ? $this->json(['success' => true,  'message' => 'Asignación guardada correctamente.'])
            : $this->json(['success' => false, 'message' => 'Error al guardar la asignación.'], 500);
    }

    /** Elimina la asignación de ubicación de un trabajador */
    public function eliminarAsignacion(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        if (!AuthMiddleware::canEditSchedule()) {
            $this->json(['success' => false, 'message' => 'No posee permisos para eliminar asignaciones. Módulo en modo solo lectura.'], 403);
            return;
        }

        $id = (int) $this->post('id');
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID de asignación inválido.']);
            return;
        }

        $this->model->deleteAsignacion($id)
            ? $this->json(['success' => true,  'message' => 'Asignación eliminada correctamente.'])
            : $this->json(['success' => false, 'message' => 'Error al eliminar la asignación.'], 500);
    }
}
