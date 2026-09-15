<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso · <?= APP_NAME ?></title>
    <meta name="description" content="<?= APP_FULL_NAME ?>">
    <!--imagen logo-->
    <link rel="shortcut icon" href="<?= BASE_URL ?>/public/images/logo.jpg" type="image/jpeg">
    <!-- Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- conexion al css -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body class="login-page">

<!--  CONTENEDOR  -->
<div class="login-container">

    <!-- Tarjeta principal de login -->
    <div class="login-card" id="loginCard">

        <!-- Encabezado institucional (Mantenido intacto) -->
        <div class="login-brand">
            <div class="login-logo" style="background: none; box-shadow: none; border-radius: 16px; overflow: hidden; width: 100px; height: 100px; margin: 0 auto 16px;">
                <img src="<?= BASE_URL ?>/public/images/logo.jpg" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h1 class="login-title"><?= APP_NAME ?></h1>
            <p class="login-subtitle"><?= APP_FULL_NAME ?></p>
            <p class="login-school-name">Centro de Educación Inicial Gral. Pedro Zaraza</p>
        </div>

        <!-- Botones principales -->
        <div class="d-flex flex-column gap-3 mt-4">
            <!-- Boton de Iniciar Sesion (Abre ventana modal) -->
            <button type="button" class="btn-login" id="btnOpenAdminLogin" onclick="document.getElementById('adminLoginOverlay').classList.add('open'); document.body.style.overflow='hidden'; setTimeout(() => document.getElementById('username')?.focus(), 200);">
                <span class="btn-text" style="font-size: 18px; font-weight: 700;">
                    <i class="bi bi-box-arrow-in-right me-2 fs-5"></i>Iniciar Sesión
                </span>
            </button>

            <!-- Boton de Registro de Asistencia por Cedula -->
            <button type="button" class="btn-attendance" id="btnOpenAttendance" onclick="document.getElementById('attendanceOverlay').classList.add('open'); document.body.style.overflow='hidden'; setTimeout(() => document.getElementById('cedulaInput')?.focus(), 200);">
                <span class="btn-text" style="color: #ffffff; font-size: 18px; font-weight: 700;">
                    <i class="bi bi-fingerprint me-2 fs-5"></i>Registrar Asistencia por Cédula
                </span>
            </button>
        </div>

    </div>
    <!-- login card -->
    <p class="login-footer-text">
        <?= APP_NAME ?> v<?= APP_VERSION ?> &copy; <?= date('Y') ?>
    </p>
</div>

<!-- Ventana Modal para Iniciar Sesión Administrador -->
<div class="attendance-modal-overlay <?= !empty($error) ? 'open' : '' ?>" id="adminLoginOverlay">
    <div class="attendance-modal" id="adminLoginModal" role="dialog" aria-modal="true" aria-labelledby="adminModalTitle">

        <!-- Encabezado Modal -->
        <div class="att-modal-header">
            <div class="att-modal-icon" style="background: rgba(99, 102, 241, 0.2); color: #818cf8;">
                <i class="bi bi-person-lock"></i>
            </div>
            <div>
                <h2 class="att-modal-title" id="adminModalTitle">Iniciar Sesión</h2>
                <p class="att-modal-subtitle">Acceso de Administrador</p>
            </div>
            <button type="button" class="att-modal-close" id="btnCloseAdminLogin" aria-label="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Cuerpo del Formulario de Iniciar Sesión -->
        <div class="att-modal-body">

            <!-- Alerta de error si existe -->
            <?php if (!empty($error)): ?>
            <div class="alert-custom alert-danger-custom mb-3" id="loginError" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/login" method="POST" id="loginForm" novalidate>
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_token) ?>">

                <div class="form-floating-custom mb-3">
                    <i class="bi bi-person-fill field-icon"></i>
                    <input type="text" name="username" id="username"
                           class="form-input" placeholder="Nombre de Administrador"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           autocomplete="username" required>
                    <label for="username">Nombre de Administrador</label>
                </div>

                <div class="form-floating-custom mb-2">
                    <i class="bi bi-lock-fill field-icon"></i>
                    <input type="password" name="password" id="password"
                           class="form-input" placeholder="Contraseña"
                           autocomplete="current-password" required>
                    <label for="password">Contraseña</label>
                    <button type="button" class="toggle-pass" id="togglePass" tabindex="-1">
                        <i class="bi bi-eye-fill" id="togglePassIcon"></i>
                    </button>
                </div>

                <div class="text-end mb-4">
                    <a href="<?= BASE_URL ?>/recuperar" class="small text-decoration-underline" style="font-size:12px; color:#a5b4fc;">¿Olvidó su contraseña?</a>
                </div>

                <button type="submit" class="btn-login w-100" id="loginBtn">
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                    </span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span> Verificando...
                    </span>
                </button>
            </form>
        </div>

    </div>
</div>

<!-- Registro de asistencia por cedula -->
<div class="attendance-modal-overlay" id="attendanceOverlay">
    <div class="attendance-modal" id="attendanceModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <!-- Encabezado -->
        <div class="att-modal-header">
            <div class="att-modal-icon">
                <i class="bi bi-fingerprint"></i>
            </div>
            <div>
                <h2 class="att-modal-title" id="modalTitle">Registrar Asistencia</h2>
                <p class="att-modal-subtitle">Ingrese su número de cédula</p>
            </div>
            <button class="att-modal-close" id="btnCloseAttendance" aria-label="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Cuerpo del formulario -->
        <div class="att-modal-body" id="attModalForm">
            <div class="cedula-input-wrap">
                <i class="bi bi-card-text cedula-icon"></i>
                <input type="text" id="cedulaInput" class="cedula-input"
                       inputmode="numeric" pattern="[0-9]*"
                       maxlength="10" placeholder="Ej: 12345678"
                       autocomplete="off">
            </div>
            <p class="cedula-hint">Solo números, sin puntos ni guiones</p>

            <!-- Sección opcional para Justificativo de Tardanza (oculta por defecto) -->
            <div id="motivoTardanzaWrap" class="d-none mt-3 mb-3 text-start">
                <div class="alert alert-warning p-2 small mb-2" id="motivoAlertText">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Ha superado el margen de tolerancia. Por favor indique la razón.
                </div>
                <label class="form-label fw-bold small text-muted">Motivo / Justificativo de Llegada Tardía <span class="text-danger">*</span></label>
                <textarea id="motivoInput" class="form-control" rows="2" placeholder="Describa brevemente la razón de su llegada tarde..."></textarea>
            </div>

            <button class="btn-register-cedula" id="btnRegister">
                <i class="bi bi-check-circle-fill me-2"></i>
                Registrar
            </button>

            <div class="att-schedule-info">
                <div class="att-schedule-badge entrada">
                    <i class="bi bi-sunrise me-1"></i>
                    <strong>Entrada:</strong> <?= ENTRADA_INICIO ?> – <?= ENTRADA_FIN ?>
                </div>
                <div class="att-schedule-badge salida">
                    <i class="bi bi-sunset me-1"></i>
                    <strong>Salida:</strong> <?= SALIDA_INICIO ?> – <?= SALIDA_FIN ?>
                </div>
            </div>
        </div>

        <!-- Cuerpo de resultado -->
        <div class="att-modal-result d-none" id="attModalResult">
            <div class="result-icon" id="resultIcon"></div>
            <pre class="result-message" id="resultMessage"></pre>
            <button class="btn-new-register" id="btnNewRegister">
                <i class="bi bi-arrow-repeat me-2"></i>Nuevo Registro
            </button>
        </div>

    </div>
</div>

<!-- scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.SCAP = { baseUrl: '<?= BASE_URL ?>', token: '<?= htmlspecialchars($_token) ?>' };
</script>
<script src="<?= BASE_URL ?>/public/js/app.js"></script>

<!-- Fullscreen Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay d-none">
    <div class="loading-content">
        <div class="loading-logo-wrap">
            <img src="<?= BASE_URL ?>/public/images/logo.jpg" alt="Logo" class="loading-logo-img">
            <div class="loading-spinner-circle"></div>
        </div>
        <p class="loading-text mt-3">Iniciando sesión, por favor espere...</p>
    </div>
</div>

</body>
</html>
