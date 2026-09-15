<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= APP_FULL_NAME ?> – Panel de Administración">
    <meta name="robots" content="noindex, nofollow">
    <title><?= isset($title) ? htmlspecialchars($title) . ' · ' : '' ?><?= APP_NAME ?></title>

    <!-- logo -->
    <link rel="shortcut icon" href="<?= BASE_URL ?>/public/images/logo.jpg" type="image/jpeg">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- iconos  -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- css  -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">

    <?php
    if (!class_exists('ConfiguracionModel')) {
        require_once APP_PATH . '/Models/ConfiguracionModel.php';
    }
    $_sysConfig = (new ConfiguracionModel())->obtenerConfiguracion();
    $_sysLogoUrl = !empty($_sysConfig['logo_url']) 
        ? BASE_URL . '/public/uploads/logo/' . $_sysConfig['logo_url'] 
        : BASE_URL . '/public/images/logo.jpg';

    if (!isset($_SESSION['admin_foto']) && isset($_SESSION['admin_id'])) {
        if (!class_exists('User')) {
            require_once APP_PATH . '/Models/User.php';
        }
        $_uModel = new User();
        $_curAdmin = $_uModel->findById((int)$_SESSION['admin_id']);
        if ($_curAdmin && !empty($_curAdmin['foto'])) {
            $_SESSION['admin_foto'] = $_curAdmin['foto'];
        }
    }
    $_sysFoto = $_SESSION['admin_foto'] ?? null;

    $_sysHeaderBg = $_sysConfig['color_header'] ?? '#ffffff';
    $_headerIsDark = false;
    if (!empty($_sysHeaderBg)) {
        $_cleanHex = ltrim($_sysHeaderBg, '#');
        if (strlen($_cleanHex) === 3) {
            $_cleanHex = $_cleanHex[0].$_cleanHex[0].$_cleanHex[1].$_cleanHex[1].$_cleanHex[2].$_cleanHex[2];
        }
        if (strlen($_cleanHex) === 6) {
            $_r = hexdec(substr($_cleanHex, 0, 2));
            $_g = hexdec(substr($_cleanHex, 2, 2));
            $_b = hexdec(substr($_cleanHex, 4, 2));
            $_yiq = (($_r * 299) + ($_g * 587) + ($_b * 114)) / 1000;
            if ($_yiq < 128) {
                $_headerIsDark = true;
            }
        }
    }
    ?>
    <style>
        :root {
            <?php if (!empty($_sysConfig['color_header'])): ?>
            --topbar-bg: <?= htmlspecialchars($_sysConfig['color_header']) ?>;
            <?php endif; ?>
            <?php if (!empty($_sysConfig['color_sidebar'])): ?>
            --sidebar-bg-from: <?= htmlspecialchars($_sysConfig['color_sidebar']) ?>;
            --sidebar-bg-to: <?= htmlspecialchars($_sysConfig['color_sidebar']) ?>;
            <?php endif; ?>
            <?php if (!empty($_sysConfig['color_fondo'])): ?>
            --bg: <?= $_sysConfig['color_fondo'] ?>;
            <?php endif; ?>
        }
        body, .main-area, .page-content {
            background: var(--bg) !important;
            background-attachment: fixed !important;
        }
        .sidebar {
            background: linear-gradient(180deg, var(--sidebar-bg-from) 0%, var(--sidebar-bg-to) 100%) !important;
        }
    </style>

    <script>
        window.APP_CONFIG = {
            encabezado: <?= json_encode($_sysConfig['encabezado'] ?? '') ?>,
            logoUrl: <?= json_encode($_sysLogoUrl) ?>
        };
    </script>
</head>

<body>

<div class="app-wrapper">
    <!-- Sidebar -->
    <?php require VIEWS_PATH . '/layout/sidebar.php'; ?>

    <!-- Main Area -->
    <div class="main-area" id="mainArea">
        <!-- Topbar -->
        <header class="topbar <?= $_headerIsDark ? 'topbar--dark' : 'topbar--light' ?>" id="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle" title="Menú">
                    <i class="bi bi-list"></i>
                </button>
                <nav aria-label="breadcrumb" class="d-none d-md-flex">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Inicio</a></li>
                        <?php if (isset($title) && $title !== 'Dashboard'): ?>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($title) ?></li>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>
            <div class="topbar-right">
                <div class="topbar-time" id="topbarClock"></div>
                <div class="user-badge dropdown">
                    <button class="user-btn dropdown-toggle" data-bs-toggle="dropdown" id="userMenuBtn">
                        <?php if ($_sysFoto && is_file(ROOT_PATH . '/public/uploads/avatars/' . $_sysFoto)): ?>
                            <span class="user-avatar p-0 border border-2 border-white rounded-circle overflow-hidden d-inline-flex align-items-center justify-content-center" style="width:36px; height:36px; min-width:36px;">
                                <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars($_sysFoto) ?>" alt="Foto Perfil" style="width:100%; height:100%; object-fit:cover;">
                            </span>
                        <?php else: ?>
                            <span class="user-avatar">
                                <?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?>
                            </span>
                        <?php endif; ?>
                        <span class="user-name d-none d-md-inline">
                            <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text text-muted small">
                            @<?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?>
                        </span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/perfil">
                            <i class="bi bi-person-gear me-2"></i>Editar Perfil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout">
                            <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                        </a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page content -->
        <main class="page-content" id="pageContent">
