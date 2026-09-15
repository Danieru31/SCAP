<?php
require_once APP_PATH . '/Middleware/AuthMiddleware.php';

// Determinar ruta activa para resaltar en el menú
$currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (!function_exists('isActive')) {
    function isActive(string $path): string {
        global $currentUrl;
        return str_contains($currentUrl, $path) ? 'active' : '';
    }
}
?>
<!-- de lado menu-->
<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-brand">
        <div class="brand-icon" style="background: none; box-shadow: none;">
            <img src="<?= BASE_URL ?>/public/images/logo.jpg" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
        </div>
        <div class="brand-text">
            <span class="brand-name"><?= APP_NAME ?></span>
            <span class="brand-sub">Control de Asistencia</span>
        </div>
        <button class="sidebar-close d-lg-none" id="sidebarClose">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Navigacion -->
    <nav class="sidebar-nav">
        <div class="nav-section-label">Principal</div>

        <a href="<?= BASE_URL ?>/dashboard"
           class="nav-item <?= isActive('/dashboard') ?>">
            <i class="bi bi-grid-1x2-fill nav-icon"></i>
            <span>Menu</span>
        </a>

        <a href="<?= BASE_URL ?>/asistencias"
           class="nav-item <?= isActive('/asistencias') ?>">
            <i class="bi bi-calendar-check-fill nav-icon"></i>
            <span>Asistencias de Hoy</span>
        </a>

        <div class="nav-section-label mt-2">Personal</div>

        <a href="<?= BASE_URL ?>/obreros"
           class="nav-item <?= isActive('/obreros') ?>">
            <i class="bi bi-people-fill nav-icon"></i>
            <span>Personal</span>
        </a>

        <?php if (AuthMiddleware::canManageAdmins()): ?>
        <a href="<?= BASE_URL ?>/administradores"
           class="nav-item <?= isActive('/administradores') ?>">
            <i class="bi bi-shield-lock-fill nav-icon"></i>
            <span>Administradores</span>
        </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/justificativos"
           class="nav-item <?= isActive('/justificativos') ?>">
            <i class="bi bi-file-earmark-medical-fill nav-icon"></i>
            <span>Justificativos</span>
        </a>

        <a href="<?= BASE_URL ?>/reportes"
           class="nav-item <?= isActive('/reportes') ?>">
            <i class="bi bi-bar-chart-fill nav-icon"></i>
            <span>Reportes</span>
        </a>

        <div class="nav-section-label mt-2">Gestión de Horarios</div>

        <a href="<?= BASE_URL ?>/calendario"
           class="nav-item <?= isActive('/calendario') ?>">
            <i class="bi bi-calendar3-range-fill nav-icon"></i>
            <span>Calendario / Control de Días</span>
        </a>
 
        <!-- Horario del Trabajador -->
        <a href="<?= BASE_URL ?>/horario"
           class="nav-item <?= isActive('/horario') ?>">
            <i class="bi bi-clock-history nav-icon"></i>
            <span>Horario del Trabajador</span>
        </a>

        <div class="nav-section-label mt-2">Ajustes</div>

        <!-- Configuraciones -->
        <a href="<?= BASE_URL ?>/configuracion"
           class="nav-item <?= isActive('/configuracion') ?>">
            <i class="bi bi-gear-fill nav-icon"></i>
            <span>Configuraciones</span>
        </a>

    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-user-info">
            <div class="sidebar-avatar">
                <?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="sidebar-user-text">
                <span class="sidebar-user-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?></span>
                <span class="sidebar-user-role"><?= htmlspecialchars(AuthMiddleware::getRoleLabel()) ?></span>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/logout" class="sidebar-logout-btn" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</aside>

<!-- Overlay para mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
