<?php
defined('ROOT_PATH') or die('Acceso directo no permitido.');

class AuthMiddleware
{
    public const ROL_MEGA_ADMIN  = 'mega_admin';
    public const ROL_SUPER_ADMIN = 'super_admin';
    public const ROL_ADMIN       = 'admin';

    /**
     * Obtener el rol del usuario autenticado en la sesión activa.
     */
    public static function getRole(): string
    {
        return $_SESSION['admin_rol'] ?? self::ROL_ADMIN;
    }

    /**
     * Validar si el usuario en sesión posee uno de los roles permitidos.
     * Si no cumple los requisitos, detiene el flujo o redirige adecuadamente.
     */
    public static function checkRole(array $rolesPermitidos): void
    {
        if (empty($_SESSION['admin_id'])) {
            if (self::isAjaxRequest()) {
                http_response_code(401);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Sesión no iniciada o expirada.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $_SESSION['intended'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $rolActual = self::getRole();
        if (!in_array($rolActual, $rolesPermitidos, true)) {
            if (self::isAjaxRequest()) {
                http_response_code(403);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Acceso denegado: No cuenta con los permisos requeridos.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    /**
     * Evalúa si el usuario actual tiene permisos para crear/modificar/eliminar Administradores.
     * Permitido únicamente para mega_admin y super_admin.
     */
    public static function canManageAdmins(): bool
    {
        $rol = self::getRole();
        return in_array($rol, [self::ROL_MEGA_ADMIN, self::ROL_SUPER_ADMIN], true);
    }

    /**
     * Evalúa si el usuario actual tiene permisos para modificar el módulo Horario del Trabajador.
     * Permitido únicamente para mega_admin y super_admin.
     */
    public static function canEditSchedule(): bool
    {
        $rol = self::getRole();
        return in_array($rol, [self::ROL_MEGA_ADMIN, self::ROL_SUPER_ADMIN], true);
    }

    /**
     * Retorna el texto representativo del rol para presentación en vistas.
     */
    public static function getRoleLabel(?string $rol = null): string
    {
        $rol = $rol ?? self::getRole();
        return match ($rol) {
            self::ROL_MEGA_ADMIN  => 'Mega Administrador',
            self::ROL_SUPER_ADMIN => 'Super Administrador',
            self::ROL_ADMIN       => 'Administrador',
            default               => 'Administrador',
        };
    }

    /**
     * Detecta si la solicitud entrante es de tipo AJAX o API JSON.
     */
    private static function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || ($_SERVER['REQUEST_METHOD'] === 'POST');
    }
}
