<?php
/*Analiza la URL y despacha al Controlador, Método correcto*/

defined('ROOT_PATH') or die('Acceso directo no permitido.');

class Router
{
    /** @var array<string, array<string, array{0:string,1:string}>> */
    private array $routes = [];

    public function __construct()
    {
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        // Auth 
        $this->add('GET',  '',                         'AuthController',      'index');
        $this->add('GET',  'login',                    'AuthController',      'login');
        $this->add('POST', 'login',                    'AuthController',      'authenticate');
        $this->add('GET',  'logout',                   'AuthController',      'logout');
        $this->add('GET',  'recuperar',                'AuthController',      'recoveryForm');
        $this->add('POST', 'recuperar',                'AuthController',      'verifyUser');
        $this->add('GET',  'recuperar/preguntas',      'AuthController',      'questionsForm');
        $this->add('POST', 'recuperar/preguntas',      'AuthController',      'verifyAnswers');
        $this->add('GET',  'recuperar/restablecer',    'AuthController',      'resetForm');
        $this->add('POST', 'recuperar/restablecer',    'AuthController',      'resetPassword');

        // Registro de asistencia (público, AJAX) 
        $this->add('POST', 'asistencia/registrar', 'AttendanceController', 'register');

        // Dashboard 
        $this->add('GET',  'dashboard',      'DashboardController', 'index');

        // Asistencias admin 
        $this->add('GET',  'asistencias',    'AttendanceController', 'today');

        // Obreros 
        $this->add('GET',  'obreros',                     'WorkerController', 'index');
        $this->add('GET',  'obreros/caracteristicas',     'WorkerController', 'characteristics');
        $this->add('GET',  'obreros/horas-trabajo',       'WorkerController', 'workHours');
        $this->add('GET',  'obreros/horas-datos',         'WorkerController', 'getWorkHoursData');
        $this->add('GET',  'obreros/datos',               'WorkerController', 'getData');
        $this->add('POST', 'obreros/crear',               'WorkerController', 'store');
        $this->add('POST', 'obreros/editar',              'WorkerController', 'update');
        $this->add('POST', 'obreros/eliminar',            'WorkerController', 'destroy');
        $this->add('GET',  'obreros/papelera',            'WorkerController', 'trash');
        $this->add('POST', 'obreros/restaurar',           'WorkerController', 'restore');
        $this->add('POST', 'obreros/eliminar-permanente', 'WorkerController', 'forceDelete');

        // Administradores 
        $this->add('GET',  'administradores',          'AdminController', 'index');
        $this->add('GET',  'administradores/datos',    'AdminController', 'getData');
        $this->add('POST', 'administradores/crear',    'AdminController', 'store');
        $this->add('POST', 'administradores/editar',   'AdminController', 'update');
        $this->add('POST', 'administradores/eliminar', 'AdminController', 'destroy');

        // Perfil de Administrador 
        $this->add('GET',  'perfil',                   'AdminController', 'profile');
        $this->add('POST', 'perfil/actualizar',        'AdminController', 'updateProfile');

        // Justificativos 
        $this->add('GET',  'justificativos',           'JustificationController', 'index');
        $this->add('GET',  'justificativos/datos',     'JustificationController', 'getData');
        $this->add('POST', 'justificativos/crear',     'JustificationController', 'store');
        $this->add('POST', 'justificativos/editar',    'JustificationController', 'update');
        $this->add('POST', 'justificativos/eliminar',  'JustificationController', 'destroy');

        // Reportes 
        $this->add('GET',  'reportes',               'ReportController', 'index');
        $this->add('GET',  'reportes/datos',         'ReportController', 'getData');
        $this->add('GET',  'reportes/exportar-csv',  'ReportController', 'exportCsv');

        // Calendario Anual de Control Laboral
        $this->add('GET',  'calendario',                 'CalendarController', 'index');
        $this->add('GET',  'calendario/datos',           'CalendarController', 'getData');
        $this->add('POST', 'calendario/laborables-masivo','CalendarController', 'bulkWorkdays');
        $this->add('POST', 'calendario/vacaciones',       'CalendarController', 'saveVacation');
        $this->add('POST', 'calendario/feriado',          'CalendarController', 'saveHoliday');
        $this->add('POST', 'calendario/cumpleanios',       'CalendarController', 'saveBirthday');
        $this->add('POST', 'calendario/emergencia',       'CalendarController', 'saveEmergency');
        $this->add('POST', 'calendario/eliminar-fecha',   'CalendarController', 'deleteDate');
        $this->add('POST', 'calendario/limpiar-anio',     'CalendarController', 'clearYear');
        
    // Configuraciones
    $this->add('GET',  'configuracion',                   'ConfiguracionController', 'index');
    $this->add('POST', 'configuracion/actualizar-perfil', 'ConfiguracionController', 'actualizarPerfil');
    $this->add('POST', 'configuracion/guardar-horarios',   'ConfiguracionController', 'guardarHorarios');
    $this->add('POST', 'configuracion/guardar-tema',       'ConfiguracionController', 'guardarTema');
    $this->add('POST', 'configuracion/guardar-encabezado', 'ConfiguracionController', 'guardarEncabezado');
    $this->add('POST', 'configuracion/guardar-empresa',    'ConfiguracionController', 'guardarEncabezado');
    $this->add('GET',  'configuracion/respaldar-bd',      'ConfiguracionController', 'respaldarBD');


        // Horario del Trabajador
        $this->add('GET',  'horario',                        'HorarioController', 'index');
        $this->add('POST', 'horario/guardar-horario-cargo',  'HorarioController', 'guardarHorarioCargo');
        $this->add('POST', 'horario/eliminar-horario-cargo', 'HorarioController', 'eliminarHorarioCargo');
        $this->add('POST', 'horario/guardar-tolerancia',     'HorarioController', 'guardarTolerancia');
        $this->add('GET',  'horario/datos-asignacion',       'HorarioController', 'getDatosAsignacion');
        $this->add('POST', 'horario/guardar-asignacion',     'HorarioController', 'guardarAsignacion');
        $this->add('POST', 'horario/eliminar-asignacion',    'HorarioController', 'eliminarAsignacion');
        }

    private function add(string $method, string $url, string $controller, string $action): void
    {
        $this->routes[strtoupper($method)][$url] = [$controller, $action];
    }

    public function dispatch(): void
    {
        $url    = trim($_GET['url'] ?? '', '/');
        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($this->routes[$method][$url])) {
            [$controllerName, $action] = $this->routes[$method][$url];
            $this->loadController($controllerName, $action);
            return;
        }

        // Fallback 404
        $this->notFound();
    }

    private function loadController(string $name, string $action): void
    {
        $file = APP_PATH . '/Controllers/' . $name . '.php';

        if (!file_exists($file)) {
            $this->notFound();
            return;
        }

        require_once $file;
        $controller = new $name();

        if (!method_exists($controller, $action)) {
            $this->notFound();
            return;
        }

        $controller->$action();
    }

    private function notFound(): void
    {
        http_response_code(404);
        require_once VIEWS_PATH . '/errors/404.php';
    }
}
