<?php
/**Script de Instalación
 * ─────────────────────────────
 * Visita: http://localhost/SCAP/setup.php
 * Ejecuta UNA SOLA VEZ para instalar el sistema.
 * ELIMINA O RENOMBRA ESTE ARCHIVO DESPUÉS DE USARLO.
 */

//  Configuración 
$host    = 'localhost';
$user    = 'root';
$pass    = '';          // Contraseña de MySQL (vacía en XAMPP por defecto)
$dbName  = 'scap';
$charset = 'utf8mb4';

$adminUser     = 'admin';
$adminPass     = 'admin123';  // ← Cambia esto antes de ejecutar en producción
$adminNombre   = 'Administrador';
$adminApellido = 'Principal';

//  Proceso de instalación 
$log = [];
$success = true;

try {
    // 1. Conectar sin seleccionar base de datos
    $pdo = new PDO(
        "mysql:host=$host;charset=$charset",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $log[] = ['ok', 'Conexión a MySQL establecida.'];

    // 2. Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");
    $log[] = ['ok', "Base de datos `$dbName` lista."];

    // 3. Crear tabla admins con rol, foto y preguntas de seguridad
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
        `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
        `username`    VARCHAR(50)     NOT NULL,
        `password`    VARCHAR(255)    NOT NULL,
        `nombre`      VARCHAR(100)    NOT NULL,
        `apellido`    VARCHAR(100)    NOT NULL DEFAULT '',
        `rol`         ENUM('mega_admin','super_admin','admin') NOT NULL DEFAULT 'admin',
        `activo`      TINYINT(1)      NOT NULL DEFAULT 1,
        `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `pregunta_1`  VARCHAR(255)    DEFAULT NULL,
        `respuesta_1` VARCHAR(255)    DEFAULT NULL,
        `pregunta_2`  VARCHAR(255)    DEFAULT NULL,
        `respuesta_2` VARCHAR(255)    DEFAULT NULL,
        `foto`        VARCHAR(255)    DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = ['ok', 'Tabla `admins` (con roles y preguntas de seguridad) creada.'];

    // 4. Crear tabla workers (con departamento y características completas)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `workers` (
        `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
        `cedula`            VARCHAR(15)     NOT NULL,
        `posee_documento`   ENUM('Sí','No') NOT NULL DEFAULT 'Sí',
        `nombres_apellidos` VARCHAR(150)    NOT NULL DEFAULT '',
        `nombre`            VARCHAR(100)    NOT NULL DEFAULT '',
        `apellido`          VARCHAR(100)    NOT NULL DEFAULT '',
        `cargo`             VARCHAR(100)    NOT NULL DEFAULT 'Obrero',
        `departamento`      VARCHAR(100)    NOT NULL DEFAULT 'General',
        `telefono`          VARCHAR(20)     NULL DEFAULT NULL,
        `grado_academico`   VARCHAR(50)     NULL DEFAULT NULL,
        `condicion_medica`  TEXT            NULL DEFAULT NULL,
        `anios_servicio`    INT UNSIGNED    NOT NULL DEFAULT 0,
        `activo`            TINYINT(1)      NOT NULL DEFAULT 1,
        `deleted`           TINYINT(1)      NOT NULL DEFAULT 0,
        `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_cedula` (`cedula`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = ['ok', 'Tabla `workers` (con departamento y campos completos) creada.'];

    // 5. Crear tabla attendance (con estado_entrada, minutos_retraso, motivo_tardanza)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `attendance` (
        `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
        `worker_id`       INT UNSIGNED    NOT NULL,
        `fecha`           DATE            NOT NULL,
        `hora_entrada`    TIME            NULL DEFAULT NULL,
        `hora_salida`     TIME            NULL DEFAULT NULL,
        `estado_entrada`  ENUM('A tiempo','Tolerancia','Tardanza','Justificado') DEFAULT 'A tiempo',
        `minutos_retraso` INT            DEFAULT 0,
        `motivo_tardanza` TEXT           DEFAULT NULL,
        `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_worker_fecha` (`worker_id`, `fecha`),
        CONSTRAINT `fk_attendance_worker`
            FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = ['ok', 'Tabla `attendance` creada.'];

    // 6. Crear tabla justifications
    $pdo->exec("CREATE TABLE IF NOT EXISTS `justifications` (
        `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
        `worker_id`     INT UNSIGNED    NOT NULL,
        `tipo_ausencia` ENUM('Enfermedad','Motivo personal') NOT NULL DEFAULT 'Enfermedad',
        `motivo`        TEXT            NOT NULL,
        `fecha_inicio`  DATE            NOT NULL,
        `fecha_fin`     DATE            NOT NULL,
        `documento`     VARCHAR(255)    DEFAULT NULL,
        `estado`        ENUM('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
        `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        CONSTRAINT `fk_justifications_worker`
            FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = ['ok', 'Tabla `justifications` creada.'];

    // 7. Crear tabla work_calendar
    $pdo->exec("CREATE TABLE IF NOT EXISTS `work_calendar` (
        `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
        `fecha`             DATE            NOT NULL,
        `tipo_estado`       ENUM('laborable','vacaciones','feriado','cumpleanios','emergencia') NOT NULL,
        `motivo_emergencia` VARCHAR(100)    DEFAULT NULL,
        `nota`              TEXT            DEFAULT NULL,
        `worker_id`         INT UNSIGNED    DEFAULT NULL,
        `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_fecha_worker` (`fecha`, `worker_id`),
        KEY `fk_calendar_worker` (`worker_id`),
        CONSTRAINT `fk_calendar_worker`
            FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = ['ok', 'Tabla `work_calendar` creada.'];

    // 8. Crear tabla configuracion
    $pdo->exec("CREATE TABLE IF NOT EXISTS `configuracion` (
        `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `nombre_empresa`    VARCHAR(150) NOT NULL DEFAULT 'Sistema SCAP',
        `encabezado`        TEXT NULL,
        `logo_url`          VARCHAR(255) NULL,
        `hora_entrada`      TIME NOT NULL DEFAULT '08:00:00',
        `hora_salida`       TIME NOT NULL DEFAULT '17:00:00',
        `margen_tolerancia` INT NOT NULL DEFAULT 15,
        `color_header`      VARCHAR(20) NOT NULL DEFAULT '#1e293b',
        `color_sidebar`     VARCHAR(20) NOT NULL DEFAULT '#0f172a',
        `color_fondo`       TEXT NULL,
        `updated_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = ['ok', 'Tabla `configuracion` creada.'];

    // Insertar fila de configuración por defecto si no existe
    $confCheck = $pdo->query("SELECT COUNT(*) FROM configuracion WHERE id = 1")->fetchColumn();
    if ($confCheck == 0) {
        $defaultEnc = "REPÚBLICA BOLIVARIANA DE VENEZUELA\nMINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN\nSISTEMA DE CONTROL DE ASISTENCIA (SCAP)";
        $defaultFondo = "linear-gradient(135deg, #eef2ff 0%, #e0e7ff 50%, #f1f5f9 100%)";
        $stmtConf = $pdo->prepare("INSERT INTO configuracion (id, nombre_empresa, encabezado, color_header, color_sidebar, color_fondo) VALUES (1, 'Sistema SCAP', ?, '#1e293b', '#0f172a', ?)");
        $stmtConf->execute([$defaultEnc, $defaultFondo]);
        $log[] = ['ok', 'Configuración inicial del sistema establecida.'];
    }

    // 9. Crear tablas del módulo Horario del Trabajador
    $pdo->exec("CREATE TABLE IF NOT EXISTS `horarios_cargo` (
        `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `cargo`        VARCHAR(100) NOT NULL,
        `hora_entrada` TIME         NOT NULL,
        `hora_salida`  TIME         NOT NULL,
        `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_cargo` (`cargo`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `horarios_asignaciones` (
        `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `worker_id`   INT UNSIGNED NOT NULL,
        `ubicacion`   VARCHAR(100) NOT NULL,
        `numero_aula` VARCHAR(20)  DEFAULT NULL,
        `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_worker_asignacion` (`worker_id`),
        CONSTRAINT `fk_asignacion_worker`
            FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = ['ok', 'Tablas de Horarios del Trabajador (`horarios_cargo` y `horarios_asignaciones`) creadas.'];

    // 10. Insertar personal de prueba (si no existen)
    $count = $pdo->query("SELECT COUNT(*) FROM workers")->fetchColumn();
    if ($count == 0) {
        $workers = [
            ['12345678','Carlos Rodríguez','Carlos','Rodríguez','Obrero','Mantenimiento','0414-1234567','Bachiller','Ninguna',5],
            ['23456789','María González','María','González','Docente','Ambientalista','0424-2345678','TSU','Ninguna',3],
            ['34567890','José Martínez','José','Martínez','Obrero','Cocinero','0412-3456789','Primaria','Hipertensión leve',8],
            ['45678901','Ana López','Ana','López','Administrador','Administrador','0416-4567890','Licenciado/Ingeniero','Alergia al polvo',2],
            ['56789012','Pedro Ramírez','Pedro','Ramírez','Obrero','Limpieza','0426-5678901','Ninguno','Ninguna',1],
        ];
        $stmt = $pdo->prepare(
            "INSERT INTO workers (cedula,nombres_apellidos,nombre,apellido,cargo,departamento,telefono,grado_academico,condicion_medica,anios_servicio) VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        foreach ($workers as $w) { $stmt->execute($w); }
        $log[] = ['ok', count($workers) . ' personal obrero de muestra insertados.'];
    } else {
        $log[] = ['info', 'La tabla `workers` ya tiene datos. Se omite inserción de muestra.'];
    }

    // 10. Crear súper administrador por defecto (mega_admin)
    $exists = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $exists->execute([$adminUser]);
    if (!$exists->fetch()) {
        $hash = password_hash($adminPass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            "INSERT INTO admins (username, password, nombre, apellido, rol) VALUES (?,?,?,?,'mega_admin')"
        );
        $stmt->execute([$adminUser, $hash, $adminNombre, $adminApellido]);
        $log[] = ['ok', "Administrador raíz creado → usuario: <strong>$adminUser</strong> | contraseña: <strong>$adminPass</strong> | rol: <strong>mega_admin</strong>"];
    } else {
        $log[] = ['info', "El usuario `$adminUser` ya existe. No se sobreescribió."];
    }

} catch (PDOException $e) {
    $log[] = ['error', 'Error PDO: ' . $e->getMessage()];
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Instalación · SCAP</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#1e1b4b,#312e81);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:#fff;border-radius:16px;padding:40px;max-width:600px;width:100%;box-shadow:0 25px 50px rgba(0,0,0,.4)}
.logo{text-align:center;margin-bottom:24px}
.logo h1{font-size:28px;font-weight:700;color:#1e1b4b}
.logo p{color:#64748b;font-size:14px;margin-top:4px}
.log-item{display:flex;gap:12px;padding:10px 14px;border-radius:8px;margin-bottom:8px;font-size:14px;align-items:flex-start}
.log-item.ok{background:#f0fdf4;border-left:3px solid #10b981;color:#166534}
.log-item.error{background:#fef2f2;border-left:3px solid #ef4444;color:#991b1b}
.log-item.info{background:#eff6ff;border-left:3px solid #3b82f6;color:#1d4ed8}
.icon{font-size:16px;flex-shrink:0;margin-top:1px}
.result{text-align:center;margin-top:24px;padding:20px;border-radius:12px}
.result.success{background:#f0fdf4;color:#166534}
.result.fail{background:#fef2f2;color:#991b1b}
.result h2{font-size:20px;font-weight:700;margin-bottom:8px}
.result p{font-size:14px;color:inherit;opacity:.8}
.btn{display:inline-block;margin-top:16px;padding:12px 28px;background:linear-gradient(135deg,#6366f1,#818cf8);color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;transition:opacity .2s}
.btn:hover{opacity:.9}
.warn{background:#fffbeb;border:1px solid #f59e0b;border-radius:8px;padding:12px 16px;color:#92400e;font-size:13px;margin-top:16px}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <h1>⚙️ SCAP · Instalación</h1>
    <p>Sistema de Control de Asistencia del Personal</p>
  </div>

  <div class="log-list">
    <?php foreach ($log as [$type, $msg]): ?>
      <div class="log-item <?= $type ?>">
        <span class="icon"><?= $type === 'ok' ? '✅' : ($type === 'error' ? '❌' : 'ℹ️') ?></span>
        <span><?= $msg ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="result <?= $success ? 'success' : 'fail' ?>">
    <?php if ($success): ?>
      <h2>✅ Instalación Exitosa</h2>
      <p>El sistema SCAP está listo para usarse.</p>
      <a href="/SCAP/" class="btn">Ir al Sistema →</a>
    <?php else: ?>
      <h2>❌ Error en la Instalación</h2>
      <p>Revisa los mensajes anteriores. Verifica que XAMPP esté corriendo y las credenciales sean correctas en <code>setup.php</code>.</p>
    <?php endif; ?>
  </div>

  <?php if ($success): ?>
  <div class="warn">
    ⚠️ <strong>Importante:</strong> Por seguridad, elimina o renombra <code>setup.php</code> después de la instalación.
    <br>Credenciales de acceso → Usuario: <strong>admin</strong> | Contraseña: <strong>admin123</strong>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
