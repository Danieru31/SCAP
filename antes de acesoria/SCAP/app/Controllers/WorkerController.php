<?php
// CRUD completo de Personal Obrero

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/Models/Worker.php';

class WorkerController extends Controller
{
    private Worker $model;

    public function __construct()
    {
        $this->model = new Worker();
    }

    //obreros
    public function index(): void
    {
        $this->characteristics();
    }

    ///caracteristicas del obreros
    public function characteristics(): void
    {
        $this->requireAuth();
        $this->render('workers.index', [
            'title'   => 'Características del Obrero · SCAP',
            'workers' => $this->model->getAll(),
            '_token'  => $this->csrfToken(),
        ]);
    }

    //horas de trabajo obreros 
    public function workHours(): void
    {
        $this->requireAuth();
        $fecha  = trim($this->get('fecha', date('Y-m-d')));
        $data   = $this->model->getWorkHoursReport($fecha);
        $report = $data['report'];

        // Formatear segundos a horas y minutos legibles
        foreach ($report as &$row) {
            $row['horas_dia_fmt']    = $this->formatSecondsToHours((int)$row['segundos_dia']);
            $row['horas_semana_fmt'] = $this->formatSecondsToHours((int)$row['segundos_semana']);
        }

        $this->render('workers.work_hours', [
            'title'   => 'Horas de Trabajo · SCAP',
            'fecha'   => $data['fecha'],
            'lunes'   => $data['lunes'],
            'viernes' => $data['viernes'],
            'report'  => $report,
            '_token'  => $this->csrfToken(),
        ]);
    }

    //horas de trabajo obreros por fecha
    public function getWorkHoursData(): void
    {
        $this->requireAuth();
        $fecha  = trim($this->get('fecha', date('Y-m-d')));
        $data   = $this->model->getWorkHoursReport($fecha);
        $report = $data['report'];

        foreach ($report as &$row) {
            $row['horas_dia_fmt']    = $this->formatSecondsToHours((int)$row['segundos_dia']);
            $row['horas_semana_fmt'] = $this->formatSecondsToHours((int)$row['segundos_semana']);
        }

        $this->json([
            'success' => true,
            'fecha'   => $data['fecha'],
            'lunes'   => $data['lunes'],
            'viernes' => $data['viernes'],
            'data'    => $report,
        ]);
    }

    private function formatSecondsToHours(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0h 0m';
        }
        $hours   = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return "{$hours}h {$minutes}m";
    }

    //obreros editar
    public function getData(): void
    {
        $this->requireAuth();
        $id     = (int) $this->get('id');
        $worker = $this->model->findById($id);

        $worker
            ? $this->json(['success' => true, 'data' => $worker])
            : $this->json(['success' => false, 'message' => 'Obrero no encontrado.'], 404);
    }

    // crear obreros
    public function store(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $validation = $this->extractAndValidateData();

        if (!$validation['success']) {
            $this->json(['success' => false, 'message' => $validation['message']]);
            return;
        }

        $data = $validation['data'];

        if ($this->model->cedulaExists($data['cedula'])) {
            $this->json(['success' => false,
                'message' => "Ya existe un obrero registrado con la cédula {$data['cedula']}."]);
            return;
        }

        $this->model->create($data)
            ? $this->json(['success' => true,  'message' => 'Obrero creado exitosamente.'])
            : $this->json(['success' => false, 'message' => 'Error al crear el obrero.'], 500);
    }

// editar obreros
    public function update(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $id = (int) $this->post('id');
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID de obrero no válido.']);
            return;
        }

        $validation = $this->extractAndValidateData(true);

        if (!$validation['success']) {
            $this->json(['success' => false, 'message' => $validation['message']]);
            return;
        }

        $data = $validation['data'];

        if ($this->model->cedulaExists($data['cedula'], $id)) {
            $this->json(['success' => false,
                'message' => "Ya existe otro obrero registrado con la cédula {$data['cedula']}."]);
            return;
        }

        $this->model->update($id, $data)
            ? $this->json(['success' => true,  'message' => 'Obrero actualizado exitosamente.'])
            : $this->json(['success' => false, 'message' => 'Error al actualizar.'], 500);
    }

    // editar obreros
    public function destroy(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $id = (int) $this->post('id');
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $this->model->delete($id)
            ? $this->json(['success' => true,  'message' => 'Obrero enviado a la papelera.'])
            : $this->json(['success' => false, 'message' => 'Error al eliminar.'], 500);
    }

    //papelera de obreros
    public function trash(): void
    {
        $this->requireAuth();
        $this->render('workers.trash', [
            'title'   => 'Papelera de Personal',
            'workers' => $this->model->getDeleted(),
            '_token'  => $this->csrfToken(),
        ]);
    }

   // restaurar obreros
    public function restore(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $id = (int) $this->post('id');
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $this->model->restore($id)
            ? $this->json(['success' => true,  'message' => 'Obrero restaurado exitosamente.'])
            : $this->json(['success' => false, 'message' => 'Error al restaurar.'], 500);
    }

    //eliminar obreros por siempre jamas
    public function forceDelete(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $id = (int) $this->post('id');
        if (!$id) {
            $this->json(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $this->model->forceDelete($id)
            ? $this->json(['success' => true,  'message' => 'Obrero eliminado permanentemente.'])
            : $this->json(['success' => false, 'message' => 'Error al eliminar.'], 500);
    }

    //Helpers
    private function extractAndValidateData(bool $withActivo = false): array
    {
        //Posee documento emitido por el gobierno si o no
        $poseeDocumento = trim($this->post('posee_documento', 'Sí'));
        if (!in_array($poseeDocumento, ['Sí', 'No'], true)) {
            $poseeDocumento = 'Sí';
        }

        //nombres y apellidos obligatorio
        $nombresApellidos = trim($this->post('nombres_apellidos', ''));
        if (empty($nombresApellidos)) {
            $nom = trim($this->post('nombre', ''));
            $ape = trim($this->post('apellido', ''));
            $nombresApellidos = trim("$nom $ape");
        }

        if (empty($nombresApellidos)) {
            return ['success' => false, 'message' => 'El campo Nombres y Apellidos es obligatorio.'];
        }

        $nombresApellidos = htmlspecialchars($nombresApellidos, ENT_QUOTES, 'UTF-8');

        // Partir para compatibilidad con columnas de nombre y apellido
        $partes = preg_split('/\s+/', $nombresApellidos, 2);
        $nombre   = $partes[0] ?? '';
        $apellido = $partes[1] ?? $partes[0];

        //Cédula de Identidad obligatorio y único
        $cedula = trim($this->post('cedula', ''));
        if (empty($cedula)) {
            return ['success' => false, 'message' => 'La Cédula de Identidad es obligatoria.'];
        }
        if (!preg_match('/^[VvEe0-9\.\-]{6,15}$/', $cedula)) {
            return ['success' => false, 'message' => 'El formato de la Cédula de Identidad no es válido.'];
        }
        $cedula = htmlspecialchars($cedula, ENT_QUOTES, 'UTF-8');

        //Cargo 
        $cargosValidos = ['Obrero', 'Docente', 'Administrador'];
        $cargo = trim($this->post('cargo', 'Obrero'));
        if (!in_array($cargo, $cargosValidos, true)) {
            $cargo = 'Obrero';
        }

        // Departamento
        $departamentosValidos = [
            'Administrador',
            'Ambientalista',
            'Cocinero',
            'Director',
            'Docente',
            'Limpieza',
            'Obrero',
            'Vigilante'
        ];
        $departamento = trim($this->post('departamento', ''));
        if (empty($departamento)) {
            return ['success' => false, 'message' => 'El campo Departamento es obligatorio.'];
        }
        if (!in_array($departamento, $departamentosValidos, true)) {
            return ['success' => false, 'message' => 'El departamento seleccionado no es válido.'];
        }

        // Número telefónico de contacto
        $telefono = trim($this->post('telefono', ''));
        if (!empty($telefono)) {
            if (!preg_match('/^[0-9\+\-\s\(\)]{7,20}$/', $telefono)) {
                return ['success' => false, 'message' => 'El número telefónico de contacto no tiene un formato válido (Ej: 0414-1234567).'];
            }
            $telefono = htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8');
        } else {
            $telefono = null;
        }

        //Grado académico 
        $gradosValidos = ['Ninguno', 'Primaria', 'Bachiller', 'TSU', 'Licenciado/Ingeniero'];
        $gradoAcademico = trim($this->post('grado_academico', 'Ninguno'));
        if (!in_array($gradoAcademico, $gradosValidos, true)) {
            $gradoAcademico = 'Ninguno';
        }

        //Condición médica texto
        $condicionMedica = trim($this->post('condicion_medica', ''));
        $condicionMedica = !empty($condicionMedica) ? htmlspecialchars($condicionMedica, ENT_QUOTES, 'UTF-8') : 'Ninguna';

        //Años de servicio
        $aniosServicioRaw = $this->post('anios_servicio', '0');
        if (!is_numeric($aniosServicioRaw) || (int)$aniosServicioRaw < 0) {
            return ['success' => false, 'message' => 'Los Años de servicio deben ser un número entero mayor o igual a 0.'];
        }
        $aniosServicio = (int) $aniosServicioRaw;

        $data = [
            'cedula'            => $cedula,
            'posee_documento'   => $poseeDocumento,
            'nombres_apellidos' => $nombresApellidos,
            'nombre'            => $nombre,
            'apellido'          => $apellido,
            'cargo'             => $cargo,
            'departamento'      => $departamento,
            'telefono'          => $telefono,
            'grado_academico'   => $gradoAcademico,
            'condicion_medica'  => $condicionMedica,
            'anios_servicio'    => $aniosServicio,
        ];

        if ($withActivo) {
            $data['activo'] = (int) $this->post('activo', 1);
        }

        return ['success' => true, 'data' => $data];
    }
}
