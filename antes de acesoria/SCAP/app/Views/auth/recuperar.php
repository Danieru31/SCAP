<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Acceso · <?= APP_NAME ?></title>
    <!-- logo -->
    <link rel="shortcut icon" href="<?= BASE_URL ?>/public/images/logo.jpg" type="image/jpeg">
    <!-- Bootstra -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- imconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- css-->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body class="login-page">

<div class="login-bg">
    <div class="login-orb orb-1"></div>
    <div class="login-orb orb-2"></div>
    <div class="login-orb orb-3"></div>
</div>

<div class="login-container">
    <div class="login-card">
        <div class="login-brand">
            <div class="login-logo" style="background: linear-gradient(135deg, var(--info), var(--accent));">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1 class="login-title">Recuperar Acceso</h1>
            <p class="login-subtitle">Paso 1: Ingrese su nombre de usuario</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert-custom alert-danger-custom" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/recuperar" method="POST">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_token) ?>">

            <div class="form-floating-custom">
                <i class="bi bi-person-fill field-icon"></i>
                <input type="text" name="username" id="username"
                       class="form-input" placeholder="Usuario"
                       required autofocus>
                <label for="username">Usuario</label>
            </div>

            <button type="submit" class="btn-login" style="background: linear-gradient(135deg, var(--info), var(--accent));">
                <span class="btn-text">
                    Continuar <i class="bi bi-arrow-right-short ms-1"></i>
                </span>
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/login" class="small text-white-50 text-decoration-underline" style="font-size:13px;">
                <i class="bi bi-chevron-left"></i> Volver al Inicio de Sesión
            </a>
        </div>
    </div>

    <p class="login-footer-text">
        <?= APP_NAME ?> v<?= APP_VERSION ?> &copy; <?= date('Y') ?>
    </p>
</div>

</body>
</html>
