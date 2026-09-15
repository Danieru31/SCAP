<?php
/* Gestiona login, autentifica y cierre de sesión*/

defined('ROOT_PATH') or die('Acceso directo no permitido.');

require_once CORE_PATH  . '/Controller.php';
require_once APP_PATH   . '/Models/User.php';

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /*redirige según estado de sesión */
    public function index(): void
    {
        $this->isLoggedIn()
            ? $this->redirect('dashboard')
            : $this->redirect('login');
    }

    /*login, muestra el formulario */
    public function login(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        $this->render('auth.login', [
            '_token' => $this->csrfToken(),
            'error'  => $error,
        ]);
    }

    /*login, procesa credenciales */
    public function authenticate(): void
    {
        $username = trim($this->post('username'));
        $password = $this->post('password');

        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Por favor, complete todos los campos.';
            $this->redirect('login');
            return;
        }

        $user = $this->userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['admin_id']       = $user['id'];
            $_SESSION['admin_name']     = $user['nombre'] . ' ' . $user['apellido'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_rol']      = $user['rol'] ?? 'admin';
            $_SESSION['admin_foto']     = $user['foto'] ?? null;

            // Redirigir a la URL que quería acceder
            $intended = $_SESSION['intended'] ?? null;
            unset($_SESSION['intended']);

            if ($intended) {
                header('Location: ' . $intended);
                exit;
            }

            $this->redirect('dashboard');
        } else {
            $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
            $this->redirect('login');
        }
    }

    //logout destruye la sesión
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('login');
    }

    //recuperar
    public function recoveryForm(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }

        $error = $_SESSION['recovery_error'] ?? null;
        unset($_SESSION['recovery_error']);

        $this->render('auth.recuperar', [
            '_token' => $this->csrfToken(),
            'error'  => $error,
        ]);
    }

    //recuperar usuario, verifica existencia y preguntas de seguridad
    public function verifyUser(): void
    {
        $username = trim($this->post('username'));

        if (empty($username)) {
            $_SESSION['recovery_error'] = 'Por favor, ingrese su usuario.';
            $this->redirect('recuperar');
            return;
        }

        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            $_SESSION['recovery_error'] = 'El usuario no existe o no está activo.';
            $this->redirect('recuperar');
            return;
        }

        if (empty($user['pregunta_1']) || empty($user['respuesta_1']) || empty($user['pregunta_2']) || empty($user['respuesta_2'])) {
            $_SESSION['recovery_error'] = 'Este usuario no tiene configuradas preguntas de seguridad. Contacte al administrador principal.';
            $this->redirect('recuperar');
            return;
        }

        $_SESSION['recovery_username'] = $username;
        $this->redirect('recuperar/preguntas');
    }

    //preguntas de seguridad, muestra el formulario
    public function questionsForm(): void
    {
        if (empty($_SESSION['recovery_username'])) {
            $this->redirect('recuperar');
        }

        $username = $_SESSION['recovery_username'];
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            $this->redirect('recuperar');
        }

        $error = $_SESSION['recovery_error'] ?? null;
        unset($_SESSION['recovery_error']);

        $this->render('auth.preguntas', [
            '_token'     => $this->csrfToken(),
            'pregunta_1' => $user['pregunta_1'],
            'pregunta_2' => $user['pregunta_2'],
            'error'      => $error,
        ]);
    }

    //recuperar las preguntas
    public function verifyAnswers(): void
    {
        if (empty($_SESSION['recovery_username'])) {
            $this->redirect('recuperar');
        }

        $username = $_SESSION['recovery_username'];
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            $this->redirect('recuperar');
        }

        $ans1 = $this->post('respuesta_1');
        $ans2 = $this->post('respuesta_2');

        if (empty($ans1) || empty($ans2)) {
            $_SESSION['recovery_error'] = 'Por favor, responda ambas preguntas.';
            $this->redirect('recuperar/preguntas');
            return;
        }

        if (password_verify(strtolower(trim($ans1)), $user['respuesta_1']) &&
            password_verify(strtolower(trim($ans2)), $user['respuesta_2'])) {
            $_SESSION['recovery_verified'] = true;
            $this->redirect('recuperar/restablecer');
        } else {
            $_SESSION['recovery_error'] = 'Respuestas de seguridad incorrectas.';
            $this->redirect('recuperar/preguntas');
        }
    }

    //recuperar y restablecer
    public function resetForm(): void
    {
        if (empty($_SESSION['recovery_username']) || empty($_SESSION['recovery_verified'])) {
            $this->redirect('recuperar');
        }

        $error = $_SESSION['recovery_error'] ?? null;
        unset($_SESSION['recovery_error']);

        $this->render('auth.restablecer', [
            '_token' => $this->csrfToken(),
            'error'  => $error,
        ]);
    }

    //recuperar y restablecer
    public function resetPassword(): void
    {
        if (empty($_SESSION['recovery_username']) || empty($_SESSION['recovery_verified'])) {
            $this->redirect('recuperar');
        }

        $username = $_SESSION['recovery_username'];
        $password = $this->post('password');
        $confirm  = $this->post('confirm_password');

        if (empty($password) || empty($confirm)) {
            $_SESSION['recovery_error'] = 'Por favor, complete todos los campos.';
            $this->redirect('recuperar/restablecer');
            return;
        }

        if (strlen($password) < 6) {
            $_SESSION['recovery_error'] = 'La contraseña debe tener al menos 6 caracteres.';
            $this->redirect('recuperar/restablecer');
            return;
        }

        if ($password !== $confirm) {
            $_SESSION['recovery_error'] = 'Las contraseñas no coinciden.';
            $this->redirect('recuperar/restablecer');
            return;
        }

        $user = $this->userModel->findByUsername($username);
        if (!$user) {
            $this->redirect('recuperar');
        }

        // Modificar contraseña
        $updateSuccess = $this->userModel->update($user['id'], [
            'username'   => $user['username'],
            'nombre'     => $user['nombre'],
            'apellido'   => $user['apellido'],
            'activo'     => $user['activo'],
            'pregunta_1' => $user['pregunta_1'],
            'pregunta_2' => $user['pregunta_2'],
            'password'   => $password,
        ]);

        if ($updateSuccess) {
            unset($_SESSION['recovery_username']);
            unset($_SESSION['recovery_verified']);
            $_SESSION['login_error'] = 'Contraseña restablecida con éxito. Inicie sesión ahora.';
            $this->redirect('login');
        } else {
            $_SESSION['recovery_error'] = 'Error al actualizar la contraseña en el servidor.';
            $this->redirect('recuperar/restablecer');
        }
    }
}
