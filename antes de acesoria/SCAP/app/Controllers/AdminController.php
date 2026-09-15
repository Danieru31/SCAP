<?php
/* crud de Administradores del sistema */

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/Models/User.php';
require_once APP_PATH  . '/Middleware/AuthMiddleware.php';

class AdminController extends Controller
{
    private User $model;

    public function __construct()
    {
        $this->model = new User();
    }

    /* get de administradores */
    public function index(): void
    {
        $this->requireAuth();
        AuthMiddleware::checkRole([AuthMiddleware::ROL_MEGA_ADMIN, AuthMiddleware::ROL_SUPER_ADMIN]);

        $this->render('admins.index', [
            'title'   => 'Administradores',
            'admins'  => $this->model->getAll(),
            '_token'  => $this->csrfToken(),
        ]);
    }

    /* get de administradores para los datos?id= */
    public function getData(): void
    {
        $this->requireAuth();
        if (!AuthMiddleware::canManageAdmins()) {
            $this->json(['success' => false, 'message' => 'No posee permisos para consultar esta información.'], 403);
            return;
        }

        $id    = (int) $this->get('id');
        $admin = $this->model->findById($id);

        if ($admin) {
            unset($admin['password']);
            unset($admin['respuesta_1']);
            unset($admin['respuesta_2']);
            $this->json(['success' => true, 'data' => $admin]);
        } else {
            $this->json(['success' => false, 'message' => 'Administrador no encontrado.'], 404);
        }
    }

    /* para crear administradores */
    public function store(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        if (!AuthMiddleware::canManageAdmins()) {
            $this->json(['success' => false, 'message' => 'No posee permisos para crear administradores.'], 403);
            return;
        }

        $data = [
            'username'   => trim($this->post('username')),
            'password'   => $this->post('password'),
            'nombre'     => trim($this->post('nombre')),
            'apellido'   => trim($this->post('apellido')),
            'rol'        => trim($this->post('rol', AuthMiddleware::ROL_ADMIN)),
            'pregunta_1' => trim($this->post('pregunta_1')),
            'respuesta_1'=> trim($this->post('respuesta_1')),
            'pregunta_2' => trim($this->post('pregunta_2')),
            'respuesta_2'=> trim($this->post('respuesta_2')),
        ];

        if (empty($data['username']) || empty($data['password']) || empty($data['nombre']) ||
            empty($data['pregunta_1']) || empty($data['respuesta_1']) ||
            empty($data['pregunta_2']) || empty($data['respuesta_2'])) {
            $this->json(['success' => false,
                'message' => 'Todos los campos (incluyendo preguntas y respuestas de seguridad) son obligatorios para crear un administrador.']);
            return;
        }

        if (strlen($data['password']) < 6) {
            $this->json(['success' => false,
                'message' => 'La contraseña debe tener mínimo 6 caracteres.']);
            return;
        }

        // Restricción: No se puede crear otro mega_admin
        if ($data['rol'] === AuthMiddleware::ROL_MEGA_ADMIN) {
            $this->json(['success' => false,
                'message' => 'No se permite crear usuarios con el rol Mega Administrador.']);
            return;
        }

        // Validar máximo 1 super_admin activo
        if ($data['rol'] === AuthMiddleware::ROL_SUPER_ADMIN && $this->model->hasActiveSuperAdmin()) {
            $this->json(['success' => false,
                'message' => 'Ya existe un Super Administrador activo en la plataforma. Solo se permite 1 activo.']);
            return;
        }

        if ($this->model->usernameExists($data['username'])) {
            $this->json(['success' => false,
                'message' => "El usuario '{$data['username']}' ya está en uso."]);
            return;
        }

        $this->model->create($data)
            ? $this->json(['success' => true,  'message' => 'Administrador creado exitosamente.'])
            : $this->json(['success' => false, 'message' => 'Error al crear el administrador.'], 500);
    }

    /* editar administradores */
    public function update(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        if (!AuthMiddleware::canManageAdmins()) {
            $this->json(['success' => false, 'message' => 'No posee permisos para editar administradores.'], 403);
            return;
        }

        $id   = (int) $this->post('id');
        $targetAdmin = $this->model->findById($id);

        if (!$targetAdmin) {
            $this->json(['success' => false, 'message' => 'Administrador no encontrado.'], 404);
            return;
        }

        // Protección inmutable para mega_admin
        if ($id === 1 || ($targetAdmin['rol'] ?? '') === AuthMiddleware::ROL_MEGA_ADMIN) {
            $this->json(['success' => false, 'message' => 'El Mega Administrador es inmutable y no puede ser modificado por este módulo.'], 403);
            return;
        }

        $data = [
            'username'   => trim($this->post('username')),
            'password'   => $this->post('password'),   // Vacío = no se cambia
            'nombre'     => trim($this->post('nombre')),
            'apellido'   => trim($this->post('apellido')),
            'rol'        => trim($this->post('rol', $targetAdmin['rol'])),
            'activo'     => (int) $this->post('activo', 1),
            'pregunta_1' => trim($this->post('pregunta_1')),
            'respuesta_1'=> trim($this->post('respuesta_1')), // Vacío = no se cambia
            'pregunta_2' => trim($this->post('pregunta_2')),
            'respuesta_2'=> trim($this->post('respuesta_2')), // Vacío = no se cambia
        ];

        if (!$id || empty($data['username']) || empty($data['nombre']) ||
            empty($data['pregunta_1']) || empty($data['pregunta_2'])) {
            $this->json(['success' => false, 'message' => 'Datos incompletos. Las preguntas de seguridad son obligatorias.']);
            return;
        }

        /* Prevencion si el admin actual desactiva su propia cuenta */
        if ($id === (int)($_SESSION['admin_id']) && $data['activo'] === 0) {
            $this->json(['success' => false,
                'message' => 'No puede desactivar su propia cuenta.']);
            return;
        }

        // Validar que solo haya 1 super_admin activo
        if ($data['rol'] === AuthMiddleware::ROL_SUPER_ADMIN && $data['activo'] === 1 && $this->model->hasActiveSuperAdmin($id)) {
            $this->json(['success' => false,
                'message' => 'Ya existe un Super Administrador activo en la plataforma. Solo se permite 1 activo.']);
            return;
        }

        if (!empty($data['password']) && strlen($data['password']) < 6) {
            $this->json(['success' => false,
                'message' => 'La nueva contraseña debe tener mínimo 6 caracteres.']);
            return;
        }

        if ($this->model->usernameExists($data['username'], $id)) {
            $this->json(['success' => false,
                'message' => "El usuario '{$data['username']}' ya está en uso."]);
            return;
        }

        $this->model->update($id, $data)
            ? $this->json(['success' => true,  'message' => 'Administrador actualizado exitosamente.'])
            : $this->json(['success' => false, 'message' => 'Error al actualizar.'], 500);
    }

    /* eliminar administradores */
    public function destroy(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        if (!AuthMiddleware::canManageAdmins()) {
            $this->json(['success' => false, 'message' => 'No posee permisos para eliminar administradores.'], 403);
            return;
        }

        $id = (int) $this->post('id');
        $targetAdmin = $this->model->findById($id);

        if (!$targetAdmin) {
            $this->json(['success' => false, 'message' => 'Administrador no encontrado.'], 404);
            return;
        }

        // Protección inmutable para mega_admin
        if ($id === 1 || ($targetAdmin['rol'] ?? '') === AuthMiddleware::ROL_MEGA_ADMIN) {
            $this->json(['success' => false, 'message' => 'El Mega Administrador es inmutable y no puede ser eliminado bajo ninguna circunstancia.'], 403);
            return;
        }

        if ($id === (int)($_SESSION['admin_id'])) {
            $this->json(['success' => false,
                'message' => 'No puede eliminar su propia cuenta.']);
            return;
        }

        $this->model->delete($id)
            ? $this->json(['success' => true,  'message' => 'Administrador eliminado exitosamente.'])
            : $this->json(['success' => false, 'message' => 'Error al eliminar.'], 500);
    }

    /* perfil */
    public function profile(): void
    {
        $this->requireAuth();
        $admin = $this->model->findById((int)$_SESSION['admin_id']);

        if (!$admin) {
            $this->redirect('dashboard');
        }

        $this->render('admins.profile', [
            'title'  => 'Editar Perfil',
            'admin'  => $admin,
            '_token' => $this->csrfToken(),
        ]);
    }

    /* actualizar el perfil */
    public function updateProfile(): void
    {
        $this->requireAuth();
        $this->validateCsrf();

        $id = (int)$_SESSION['admin_id'];
        
        $stmt = Database::getInstance()->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $admin = $stmt->fetch();

        if (!$admin) {
            $this->json(['success' => false, 'message' => 'Administrador no encontrado.']);
            return;
        }

        $currentPassword = $this->post('current_password');
        $newPassword     = $this->post('new_password');
        $confirmPassword = $this->post('confirm_password');
        $pregunta1       = trim($this->post('pregunta_1'));
        $respuesta1      = trim($this->post('respuesta_1'));
        $pregunta2       = trim($this->post('pregunta_2'));
        $respuesta2      = trim($this->post('respuesta_2'));

        if (empty($currentPassword) || !password_verify($currentPassword, $admin['password'])) {
            $this->json(['success' => false, 'message' => 'La contraseña actual es incorrecta.']);
            return;
        }

        if (empty($pregunta1) || empty($pregunta2)) {
            $this->json(['success' => false, 'message' => 'Las preguntas de seguridad son obligatorias.']);
            return;
        }

        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                $this->json(['success' => false, 'message' => 'La nueva contraseña debe tener mínimo 6 caracteres.']);
                return;
            }
            if ($newPassword !== $confirmPassword) {
                $this->json(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden.']);
                return;
            }
        }

        $fotoNombre = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath   = $_FILES['foto']['tmp_name'];
            $fileName      = $_FILES['foto']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $fotoNombre    = 'avatar_' . $id . '_' . time() . '.' . $fileExtension;
                $uploadFileDir = ROOT_PATH . '/public/uploads/avatars/';

                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $destPath = $uploadFileDir . $fotoNombre;
                if (!move_uploaded_file($fileTmpPath, $destPath)) {
                    $fotoNombre = null;
                }
            } else {
                $this->json(['success' => false, 'message' => 'Formato de foto no permitido. Solo se aceptan archivos JPG y PNG.']);
                return;
            }
        }

        $updateData = [
            'username'   => $admin['username'],
            'nombre'     => $admin['nombre'],
            'apellido'   => $admin['apellido'],
            'rol'        => $admin['rol'],
            'activo'     => $admin['activo'],
            'pregunta_1' => $pregunta1,
            'pregunta_2' => $pregunta2,
            'password'   => $newPassword,
            'respuesta_1'=> $respuesta1,
            'respuesta_2'=> $respuesta2,
        ];

        if ($fotoNombre) {
            $updateData['foto'] = $fotoNombre;
        }

        if ($this->model->update($id, $updateData)) {
            if ($fotoNombre) {
                $_SESSION['admin_foto'] = $fotoNombre;
            }
            $this->json(['success' => true, 'message' => 'Perfil actualizado exitosamente.']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al actualizar el perfil en la base de datos.']);
        }
    }
}
