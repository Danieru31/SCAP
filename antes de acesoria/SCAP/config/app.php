<?php
 //Configuración general de la aplicación

defined('ROOT_PATH') or die('Acceso directo no permitido.');

//  Información de la App 
define('APP_NAME',      'SCAP');
define('APP_FULL_NAME', 'Sistema de Control de Asistencia del Personal');
define('APP_VERSION',   '1.0.0');
define('APP_INST',      'Institución');   // Nombre de la institución (personalizable)

//  URL base (ajustar si el proyecto está en subcarpeta) 
define('BASE_URL', '/SCAP');

//  Zona horaria 
define('TIMEZONE', 'America/Caracas');
date_default_timezone_set(TIMEZONE);

//  Reglas de negocio: Horario de Asistencia 
// Formato HH:MM (24 horas)
define('ENTRADA_INICIO', '06:00');   // 6:00 AM
define('ENTRADA_FIN',    '07:00');   // 7:00 AM
define('SALIDA_INICIO',  '11:30');   // 11:30 AM
define('SALIDA_FIN',     '12:00');   // 12:00 PM

//  Paginación 
define('RECORDS_PER_PAGE', 15);

//  Sesión 
define('SESSION_LIFETIME', 28800); // 8 horas en segundos
