<?php
/* Controlador para la gestión del calendario anual de control laboral */

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/Models/WorkCalendar.php';
require_once APP_PATH  . '/Models/Worker.php';

class CalendarController extends Controller
{
    private WorkCalendar $model;
    private Worker $workerModel;

    public function __construct()
    {
        $this->model       = new WorkCalendar();
        $this->workerModel = new Worker();
    }

    /**
     * GET /calendario
     * Renderiza la vista principal del Calendario Anual de Control Laboral
     */
    public function index(): void
    {
        $this->requireAuth();

        $year    = (int) ($this->get('year') ?: date('Y'));
        $workers = $this->workerModel->getAll();

        $this->render('calendar.index', [
            'title'   => 'Calendario Anual de Control Laboral',
            'year'    => $year,
            'workers' => $workers,
            '_token'  => $this->csrfToken(),
        ]);
    }

    /**
     * GET /calendario/datos?year=2026
     * Retorna datos JSON con el calendario del año y la lista de trabajadores
     */
    public function getData(): void
    {
        $this->requireAuth();
        $year     = (int) ($this->get('year') ?: date('Y'));
        $calendar = $this->model->getByYear($year);
        $workers  = $this->workerModel->getAll();

        $this->json([
            'success'  => true,
            'year'     => $year,
            'calendar' => $calendar,
            'workers'  => $workers,
        ]);
    }

    /**
     * POST /calendario/laborables-masivo
     * Marca los días de Lunes a Viernes como laborables en el rango from–to indicado
     */
    public function bulkWorkdays(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $from = trim((string) $this->post('from'));
        $to   = trim((string) $this->post('to'));
        $nota = trim((string) $this->post('nota', 'Jornada Laborable Activa'));

        if (empty($from) || empty($to)) {
            $this->json(['success' => false, 'message' => 'Por favor selecciona la fecha de inicio y fin.'], 400);
            return;
        }

        if ($from > $to) {
            $this->json(['success' => false, 'message' => 'La fecha de inicio debe ser menor o igual a la fecha de fin.'], 400);
            return;
        }

        $count = $this->model->bulkMarkWorkdays($from, $to, $nota);

        $this->json([
            'success' => true,
            'message' => "Se marcaron $count días laborables (Lun-Vie) del $from al $to.",
            'count'   => $count,
        ]);
    }

    /**
     * POST /calendario/vacaciones
     * Asigna un periodo o rango de días de vacaciones
     */
    public function saveVacation(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $from     = trim((string) $this->post('from'));
        $to       = trim((string) $this->post('to'));
        $workerId = $this->post('worker_id') ? (int) $this->post('worker_id') : null;
        $nota     = trim((string) $this->post('nota'));

        if (empty($from) || empty($to)) {
            $this->json(['success' => false, 'message' => 'Por favor selecciona la fecha de inicio y fin.'], 400);
        }

        if ($from > $to) {
            $this->json(['success' => false, 'message' => 'La fecha de inicio debe ser menor o igual a la fecha de fin.'], 400);
        }

        $count = $this->model->saveVacation($from, $to, $workerId, $nota);

        $this->json([
            'success' => true,
            'message' => "Periodo de vacaciones registrado exitosamente ($count día(s)).",
            'count'   => $count,
        ]);
    }

    /**
     * POST /calendario/feriado
     * Registra un día feriado o festivo en una fecha específica
     */
    public function saveHoliday(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $fecha  = trim((string) $this->post('fecha'));
        $nombre = trim((string) $this->post('nombre'));
        $tipo   = trim((string) $this->post('tipo', 'Nacional'));

        if (empty($fecha) || empty($nombre)) {
            $this->json(['success' => false, 'message' => 'La fecha y el nombre del feriado son requeridos.'], 400);
        }

        $ok = $this->model->saveHoliday($fecha, $nombre, $tipo);

        $ok
            ? $this->json(['success' => true, 'message' => "Día feriado \"$nombre\" guardado con éxito."])
            : $this->json(['success' => false, 'message' => 'Error al guardar el feriado.'], 500);
    }

    /**
     * POST /calendario/cumpleanios
     * Registra y destaca el cumpleaños de un trabajador
     */
    public function saveBirthday(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $fecha    = trim((string) $this->post('fecha'));
        $workerId = (int) $this->post('worker_id');
        $nota     = trim((string) $this->post('nota'));

        if (empty($fecha) || !$workerId) {
            $this->json(['success' => false, 'message' => 'Selecciona la fecha y el trabajador.'], 400);
        }

        $worker = $this->workerModel->findById($workerId);
        $workerName = $worker ? ($worker['nombre'] . ' ' . $worker['apellido']) : 'Trabajador';

        $ok = $this->model->saveBirthday($fecha, $workerId, $nota ?: "Cumpleaños de $workerName");

        $ok
            ? $this->json(['success' => true, 'message' => "Cumpleaños de $workerName registrado."])
            : $this->json(['success' => false, 'message' => 'Error al registrar cumpleaños.'], 500);
    }

    /**
     * POST /calendario/emergencia
     * Registra suspensión de labores por decreto o decisión administrativa
     */
    public function saveEmergency(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $from        = trim((string) $this->post('from'));
        $to          = trim((string) $this->post('to')) ?: $from;
        $motivo      = trim((string) $this->post('motivo', 'Decreto Nacional'));
        $descripcion = trim((string) $this->post('descripcion'));

        if (empty($from)) {
            $this->json(['success' => false, 'message' => 'Selecciona la fecha de inicio.'], 400);
        }

        if ($from > $to) {
            $this->json(['success' => false, 'message' => 'La fecha de inicio no puede ser posterior a la de fin.'], 400);
        }

        $count = $this->model->saveEmergency($from, $to, $motivo, $descripcion);

        $this->json([
            'success' => true,
            'message' => "Día(s) de emergencia / suspensión registrados ($count día(s)).",
            'count'   => $count,
        ]);
    }

    /**
     * POST /calendario/eliminar-fecha
     * Elimina el estado de una fecha específica
     */
    public function deleteDate(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $fecha = trim((string) $this->post('fecha'));
        if (empty($fecha)) {
            $this->json(['success' => false, 'message' => 'Fecha no proporcionada.'], 400);
        }

        $ok = $this->model->deleteDate($fecha);

        $ok
            ? $this->json(['success' => true, 'message' => 'Estado de la fecha eliminado.'])
            : $this->json(['success' => false, 'message' => 'Error al eliminar fecha.'], 500);
    }

    /**
     * POST /calendario/limpiar-anio
     * Limpia todas las marcas del año actual
     */
    public function clearYear(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $year = (int) $this->post('year', date('Y'));
        $ok   = $this->model->clearYear($year);

        $ok
            ? $this->json(['success' => true, 'message' => "Se limpiaron todos los registros del año $year."])
            : $this->json(['success' => false, 'message' => 'Error al limpiar el año.'], 500);
    }
}
