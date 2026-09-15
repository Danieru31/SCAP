<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preguntas de Seguridad · <?= APP_NAME ?></title>
    <!-- logo-->
    <link rel="shortcut icon" href="<?= BASE_URL ?>/public/images/logo.jpg" type="image/jpeg">
    <!-- Bootstra-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- iconos -->
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
            <div class="login-logo" style="background: none; box-shadow: none; border-radius: 12px; overflow: hidden; width: 80px; height: 80px; margin: 0 auto 14px;">
                <img src="<?= BASE_URL ?>/public/images/logo.jpg" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h1 class="login-title">Validar Identidad</h1>
            <p class="login-subtitle">Paso 2: Responda sus preguntas de seguridad</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert-custom alert-danger-custom" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/recuperar/preguntas" method="POST" autocomplete="off">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_token) ?>">

            <!-- Pregunta #1 -->
            <div class="mb-4">
                <label class="form-label text-black-50 small fw-bold mb-2 d-block">
                    <i class="bi bi-patch-question me-1 text-warning"></i>
                    <?= htmlspecialchars($pregunta_1) ?>
                </label>
                <div class="form-floating-custom">
                    <i class="bi bi-chat-left-dots-fill field-icon"></i>
                    <input type="text" name="respuesta_1" id="respuesta_1"
                           class="form-input" placeholder="Respuesta 1"
                           required autofocus autocomplete="off">
                    <label for="respuesta_1">Respuesta 1</label>
                </div>
            </div>

            <!-- Pregunta #2 -->
            <div class="mb-4">
                <label class="form-label text-black-50 small fw-bold mb-2 d-block">
                    <i class="bi bi-patch-question me-1 text-warning"></i>
                    <?= htmlspecialchars($pregunta_2) ?>
                </label>
                <div class="form-floating-custom">
                    <i class="bi bi-chat-left-dots-fill field-icon"></i>
                    <input type="text" name="respuesta_2" id="respuesta_2"
                           class="form-input" placeholder="Respuesta 2"
                           required autocomplete="off">
                    <label for="respuesta_2">Respuesta 2</label>
                </div>
            </div>

            <button type="submit" class="btn-login" style="background: linear-gradient(135deg, var(--info), var(--accent));">
                <span class="btn-text">
                    Verificar Respuestas <i class="bi bi-shield-check ms-1"></i>
                </span>
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/recuperar" class="small text-white-50 text-decoration-underline" style="font-size:13px;">
                <i class="bi bi-arrow-left"></i> Cambiar de Usuario
            </a>
        </div>
    </div>

    <p class="login-footer-text">
        <?= APP_NAME ?> v<?= APP_VERSION ?> &copy; <?= date('Y') ?>
    </p>
</div>

</body>
</html>
