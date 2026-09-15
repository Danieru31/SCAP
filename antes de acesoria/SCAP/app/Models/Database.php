<?php
//Conexión a Base de Datos 

defined('ROOT_PATH') or die('Acceso directo no permitido.');

class Database
{
    private static ?PDO $instance = null;

    //obtener instancia única
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST, DB_NAME, DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                require_once APP_PATH . '/Models/SchemaMigrator.php';
                SchemaMigrator::run(self::$instance);
            } catch (PDOException $e) {
                // En desarrollo mostrar error; en producción loguear y mostrar mensaje genérico
                http_response_code(500);
                die('<div style="font-family:sans-serif;padding:30px;color:#ef4444">
                    <h2>Error de conexión a la base de datos</h2>
                    <p>Asegúrate de que MySQL está corriendo en XAMPP y de que ejecutaste <code>setup.php</code>.</p>
                    <small>' . htmlspecialchars($e->getMessage()) . '</small>
                </div>');
            }
        }

        return self::$instance;
    }

    // Prevenir instanciación y clonación directa
    private function __construct() {}
    private function __clone()    {}
}
