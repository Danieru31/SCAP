<?php
//Panel principal del administrador

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/Models/Worker.php';
require_once APP_PATH  . '/Models/Attendance.php';
require_once APP_PATH  . '/Models/User.php';
require_once APP_PATH  . '/Models/Justification.php';

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $workerModel        = new Worker();
        $attendanceModel    = new Attendance();
        $userModel          = new User();
        $justificationModel = new Justification();

        $stats = [
            'total_obreros'          => $workerModel->countActive(),
            'asistencias_hoy'        => $attendanceModel->countToday(),
            'asistencias_completas'  => $attendanceModel->countTodayComplete(),
            'total_admins'           => $userModel->count(),
            'justificativos_pend'    => $justificationModel->countPending(),
        ];

        $this->render('dashboard.index', [
            'title'   => 'Dashboard',
            'stats'   => $stats,
            '_token'  => $this->csrfToken(),
        ]);
    }
}
