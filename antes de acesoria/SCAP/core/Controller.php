<?php
/*Controlador Base */

defined('ROOT_PATH') or die('Acceso directo no permitido.');

abstract class Controller
{
    // Renderizado de vistas 
    protected function render(string $view, array $data = []): void
    {
        // Convertir "modulo.vista" → "modulo/vista.php"
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            die("Vista no encontrada: <code>$viewPath</code>");
        }

        // Exponer variables al scope de la vista
        extract($data, EXTR_SKIP);

        require $viewPath;
    }

    //  Redirección interna 
    protected function redirect(string $path = ''): void
    {
        $path = ltrim($path, '/');
        header('Location: ' . BASE_URL . '/' . $path);
        exit;
    }

    //  Respuesta JSON 
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    //  Autenticación 
    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    protected function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            // Guardar URL de destino para redirigir al hacer login
            $_SESSION['intended'] = $_SERVER['REQUEST_URI'];
            $this->redirect('login');
        }
    }

    // CSRF Token 
    protected function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function validateCsrf(): void
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $postToken    = $_POST['_token'] ?? '';

        if (empty($sessionToken) || empty($postToken) || !hash_equals($sessionToken, $postToken)) {
            $this->json([
                'success' => false,
                'message' => 'Sesión expirada o token inválido. Por favor recarga la página (F5) e inténtalo de nuevo.'
            ], 403);
        }
    }

    // Helpers de input 
    protected function post(string $key, mixed $default = ''): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function get(string $key, mixed $default = ''): mixed
    {
        return $_GET[$key] ?? $default;
    }
}
