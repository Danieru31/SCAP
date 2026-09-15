<?php
/* Registro público por cédula ajax, Vista de asistencias del día*/

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/Models/Worker.php';
require_once APP_PATH  . '/Models/HorarioModel.php';

class AttendanceController extends Controller
{
    private Worker     $workerModel;
    private Attendance $attendanceModel;
    private HorarioModel $horarioModel;

    public function __construct()
    {
        $this->workerModel     = new Worker();
        $this->attendanceModel = new Attendance();
        $this->horarioModel    = new HorarioModel();
    }

    //registrar asistencia
    public function register(): void
    {
        $cedula         = trim($this->post('cedula'));
        $motivoTardanza = trim($this->post('motivo_tardanza', ''));

        // Validar cédula 
        if (empty($cedula)) {
            $this->json(['success' => false, 'message' => 'Ingrese su número de cédula.']);
        }

        if (!preg_match('/^\d{6,10}$/', $cedula)) {
            $this->json(['success' => false,
                'message' => 'Cédula inválida. Solo números, entre 6 y 10 dígitos.']);
        }

        // Buscar trabajador
        $worker = $this->workerModel->findByCedula($cedula);
        if (!$worker) {
            $this->json(['success' => false,
                'message' => "La cédula <strong>$cedula</strong> no está registrada en el sistema.\nConsulta al administrador."]);
        }

        $fullName = trim($worker['nombre'] . ' ' . $worker['apellido']);
        $cargo    = $worker['cargo'] ?? 'Obrero';
        $time     = date('h:i A');

        // Obtener horario específico del cargo o valores base
        $horarioCargo = $this->horarioModel->getHorarioByCargo($cargo);
        $horaEntrada  = $horarioCargo['hora_entrada'] ?? '08:00:00';
        $horaSalida   = $horarioCargo['hora_salida']  ?? '17:00:00';
        $tolerancia   = $this->horarioModel->getTolerancia(); // Minutos de gracia

        $existing = $this->attendanceModel->getTodayByWorkerId($worker['id']);

        // 1. Si no tiene registro de ENTRADA hoy -> Intentar registrar entrada
        if (!$existing || empty($existing['hora_entrada'])) {
            $nowTs        = time();
            $todayDate    = date('Y-m-d');
            $entradaTs    = strtotime($todayDate . ' ' . $horaEntrada);
            $toleranciaTs = $entradaTs + ($tolerancia * 60);

            $estadoEntrada  = 'A tiempo';
            $minutosRetraso = 0;

            if ($nowTs <= $entradaTs) {
                $estadoEntrada = 'A tiempo';
            } elseif ($nowTs <= $toleranciaTs) {
                $estadoEntrada = 'Tolerancia';
            } else {
                // Llegada tardía (superó la tolerancia)
                $estadoEntrada  = 'Tardanza';
                $minutosRetraso = (int) round(($nowTs - $entradaTs) / 60);

                // Si NO ha enviado el motivo de la tardanza, pedirlo al cliente/kiosco
                if (empty($motivoTardanza)) {
                    $this->json([
                        'success'          => false,
                        'requires_motivo'  => true,
                        'worker_id'        => $worker['id'],
                        'worker_name'      => $fullName,
                        'cargo'            => $cargo,
                        'minutos_retraso'  => $minutosRetraso,
                        'message'          => "⚠️ Atención $fullName:\nLlegó con {$minutosRetraso} min. de retraso (tolerancia de {$tolerancia} min. superada).\n\nPor favor ingrese el motivo / justificativo de la tardanza."
                    ]);
                }
            }

            $stmt_ok = $this->attendanceModel->registerEntry(
                $worker['id'],
                $estadoEntrada,
                $minutosRetraso,
                !empty($motivoTardanza) ? $motivoTardanza : null
            );

            if ($stmt_ok) {
                $msgEstado = $estadoEntrada === 'Tardanza'
                    ? "⚠️ Entrada registrada con TARDANZA ({$minutosRetraso} min de retraso)\nMotivo: {$motivoTardanza}"
                    : ($estadoEntrada === 'Tolerancia' ? "🟡 Entrada registrada (Dentro de tolerancia)" : "✅ Entrada registrada a tiempo");

                $this->json([
                    'success' => true,
                    'type'    => 'entrada',
                    'estado'  => $estadoEntrada,
                    'message' => "{$msgEstado}\n\n👷 {$fullName}\n🕐 {$time}\n📋 Cargo: {$cargo}\n🏢 Dpto: {$worker['departamento']}"
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Error al registrar la entrada. Intente nuevamente.'], 500);
            }
        }

        // 2. Si YA tiene entrada registrada hoy -> Intentar registrar SALIDA
        if ($existing && !empty($existing['hora_entrada'])) {
            if (!empty($existing['hora_salida'])) {
                $prevSalida = date('h:i A', strtotime($existing['hora_salida']));
                $this->json([
                    'success' => false,
                    'message' => "Ya registró su entrada y salida por el día de hoy.\n\n👷 {$fullName}\n⬇️ Salida: {$prevSalida}"
                ]);
            }

            $stmt_ok = $this->attendanceModel->registerExit($worker['id']);

            if ($stmt_ok) {
                $entradaTime = date('h:i A', strtotime($existing['hora_entrada']));
                $this->json([
                    'success' => true,
                    'type'    => 'salida',
                    'message' => "✅ Salida registrada\n\n👷 {$fullName}\n⬆️ Entrada: {$entradaTime}\n⬇️ Salida: {$time}\n📋 Cargo: {$cargo}"
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Error al registrar la salida. Intente nuevamente.'], 500);
            }
        }
    }
    // asistencias, admin
    public function today(): void
    {
        $this->requireAuth();

        $attendances = $this->attendanceModel->getTodayAttendance();

        $this->render('attendance.today', [
            'title'       => 'Asistencias de Hoy',
            'attendances' => $attendances,
            'today'       => date('d/m/Y'),
            '_token'      => $this->csrfToken(),
        ]);
    }
}
