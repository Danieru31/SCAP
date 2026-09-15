<?php
///Generación y exportación de reportes de asistencia

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/Models/Attendance.php';

class ReportController extends Controller
{
    private Attendance $model;

    public function __construct()
    {
        $this->model = new Attendance();
    }

    ///reportes
    public function index(): void
    {
        $this->requireAuth();

        $from = $this->get('from') ?: date('Y-m-d');
        $to   = $this->get('to')   ?: date('Y-m-d');

        // fechas
        $from = date('Y-m-d', strtotime($from));
        $to   = date('Y-m-d', strtotime($to));

        // No permitir rango mayor a 1 año
        if (strtotime($to) - strtotime($from) > 365 * 86400) {
            $from = date('Y-m-d', strtotime($to . ' -365 days'));
        }

        $attendances = $this->model->getByDateRange($from, $to);

        $this->render('reports.index', [
            'title'       => 'Reportes',
            'attendances' => $attendances,
            'from'        => $from,
            'to'          => $to,
            '_token'      => $this->csrfToken(),
        ]);
    }

    //reportes actualización dinámica
    public function getData(): void
    {
        $this->requireAuth();

        $from = date('Y-m-d', strtotime($this->get('from') ?: date('Y-m-d')));
        $to   = date('Y-m-d', strtotime($this->get('to')   ?: date('Y-m-d')));

        $this->json([
            'success' => true,
            'data'    => $this->model->getByDateRange($from, $to),
        ]);
    }

    ///reportes exportar descarga CSV
    public function exportCsv(): void
    {
        $this->requireAuth();

        $from = date('Y-m-d', strtotime($this->get('from') ?: date('Y-m-d')));
        $to   = date('Y-m-d', strtotime($this->get('to')   ?: date('Y-m-d')));

        $records  = $this->model->getByDateRange($from, $to);
        $filename = 'reporte_asistencia_' . $from . '_a_' . $to . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $fp = fopen('php://output', 'w');

        //compatibilidad con Excel
        fputs($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabecera
        fputcsv($fp, [
            'Fecha', 'Cédula', 'Nombre', 'Apellido',
            'Cargo', 'Departamento', 'Hora Entrada', 'Hora Salida', 'Estado'
        ]);

        foreach ($records as $row) {
            if ($row['hora_entrada'] && $row['hora_salida']) {
                $estado = 'Completo';
            } elseif ($row['hora_entrada']) {
                $estado = 'Solo Entrada';
            } else {
                $estado = 'Sin Registro';
            }

            fputcsv($fp, [
                date('d/m/Y', strtotime($row['fecha'])),
                $row['cedula'],
                $row['nombre'],
                $row['apellido'],
                $row['cargo'],
                $row['departamento'],
                $row['hora_entrada'] ? date('h:i A', strtotime($row['hora_entrada'])) : '–',
                $row['hora_salida']  ? date('h:i A', strtotime($row['hora_salida']))  : '–',
                $estado,
            ]);
        }

        fclose($fp);
        exit;
    }
}
