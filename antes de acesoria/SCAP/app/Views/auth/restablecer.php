<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña · <?= APP_NAME ?></title>
    <!-- logo -->
    <link rel="shortcut icon" href="<?= BASE_URL ?>/public/images/logo.jpg" type="image/jpeg">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- iconos  -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- css  -->
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
            <div class="login-logo" style="background: linear-gradient(135deg, var(--success), var(--accent));">
                <i class="bi bi-key-fill"></i>
            </div>
            <h1 class="login-title">Nueva Contraseña</h1>
            <p class="login-subtitle">Paso 3: Ingrese su nueva clave de acceso</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert-custom alert-danger-custom" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/recuperar/restablecer" method="POST">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_token) ?>">

            <div class="form-floating-custom mb-3">
                <i class="bi bi-lock-fill field-icon"></i>
                <input type="password" name="password" id="password"
                       class="form-input" placeholder="Nueva Contraseña"
                       required autofocus autocomplete="new-password">
                <label for="password">Nueva Contraseña</label>
                <button type="button" class="toggle-pass" id="togglePass" tabindex="-1">
                    <i class="bi bi-eye-fill" id="togglePassIcon"></i>
                </button>
            </div>

            <div class="form-floating-custom mb-4">
                <i class="bi bi-lock-fill field-icon"></i>
                <input type="password" name="confirm_password" id="confirm_password"
                       class="form-input" placeholder="Confirmar Contraseña"
                       required autocomplete="new-password">
                <label for="confirm_password">Confirmar Contraseña</label>
                <button type="button" class="toggle-pass" id="toggleConfirmPass" tabindex="-1">
                    <i class="bi bi-eye-fill" id="toggleConfirmPassIcon"></i>
                </button>
            </div>

            <button type="submit" class="btn-login" style="background: linear-gradient(135deg, var(--info), var(--accent));">
                <span class="btn-text">
                    Restablecer Contraseña <i class="bi bi-check-all ms-1"></i>
                </span>
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/login" class="small text-black-50 text-decoration-underline" style="font-size:13px;">
                Cancelar y Volver al Login
            </a>
        </div>
    </div>

    <p class="login-footer-text">
        <?= APP_NAME ?> v<?= APP_VERSION ?> &copy; <?= date('Y') ?>
    </p>
</div>

<script>
// Toggle Password Visibility para clave nueva y confirmación
document.addEventListener('DOMContentLoaded', () => {
    const bindToggle = (inputName, btnId, iconId) => {
        const passInput  = document.getElementById(inputName);
        const toggleBtn  = document.getElementById(btnId);
        const toggleIcon = document.getElementById(iconId);

        toggleBtn?.addEventListener('click', () => {
            const isPass = passInput.type === 'password';
            passInput.type = isPass ? 'text' : 'password';
            toggleIcon.className = isPass ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
        });
    };

    bindToggle('password', 'togglePass', 'togglePassIcon');
    bindToggle('confirm_password', 'toggleConfirmPass', 'toggleConfirmPassIcon');
});
</script>

</body>
</html>
