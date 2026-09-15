<?php require VIEWS_PATH . '/layout/header.php'; ?>

<!-- menu-->

<!-- bienvenida  -->
<div class="page-header">
    <div>
        <h1 class="page-title">Menú Principal</h1>
        <p class="page-subtitle">Bienvenido, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrador') ?></p>
    </div>
    <div class="page-header-actions">
        <span class="badge-today">
            <i class="bi bi-calendar3 me-1"></i>
            <?= date('d \d\e F \d\e Y') ?>
        </span>
    </div>
</div>

<!--  Tarjetas de estadisticas  -->
<div class="stats-grid">

    <div class="stat-card stat-card--blue">
        <div class="stat-card-icon">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="stat-card-body">
            <div class="stat-number"><?= number_format($stats['total_obreros']) ?></div>
            <div class="stat-label">Personal Activo</div>
        </div>
        <div class="stat-card-trend">
            <i class="bi bi-person-workspace"></i>
        </div>
    </div>

    <div class="stat-card stat-card--green">
        <div class="stat-card-icon">
            <i class="bi bi-calendar-check-fill"></i>
        </div>
        <div class="stat-card-body">
            <div class="stat-number"><?= number_format($stats['asistencias_hoy']) ?></div>
            <div class="stat-label">Asistencias Hoy</div>
        </div>
        <div class="stat-card-trend">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
    </div>

    <div class="stat-card stat-card--purple">
        <div class="stat-card-icon">
            <i class="bi bi-check2-all"></i>
        </div>
        <div class="stat-card-body">
            <div class="stat-number"><?= number_format($stats['asistencias_completas']) ?></div>
            <div class="stat-label">Registros Completos</div>
        </div>
        <div class="stat-card-trend">
            <i class="bi bi-clipboard-check"></i>
        </div>
    </div>

    <div class="stat-card stat-card--orange">
        <div class="stat-card-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <div class="stat-card-body">
            <div class="stat-number"><?= number_format($stats['total_admins']) ?></div>
            <div class="stat-label">Administradores</div>
        </div>
        <div class="stat-card-trend">
            <i class="bi bi-person-badge"></i>
        </div>
    </div>

</div>

<!--  modulos de acceso rapido  -->
<h2 class="section-title mt-4">Módulos del Sistema</h2>

<div class="modules-grid">

    <a href="<?= BASE_URL ?>/asistencias" class="module-card">
        <div class="module-icon module-icon--teal">
            <i class="bi bi-calendar-check-fill"></i>
        </div>
        <div class="module-info">
            <h3>Asistencias de Hoy</h3>
            <p>Visualiza en tiempo real las entradas y salidas del personal registradas hoy.</p>
        </div>
        <div class="module-arrow"><i class="bi bi-arrow-right-circle-fill"></i></div>
    </a>

    <a href="<?= BASE_URL ?>/obreros" class="module-card">
        <div class="module-icon module-icon--blue">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="module-info">
            <h3>Personal</h3>
            <p>Gestiona el registro completo del personal (cédula, nombre, cargo, departamento).</p>
        </div>
        <div class="module-arrow"><i class="bi bi-arrow-right-circle-fill"></i></div>
    </a>

    <a href="<?= BASE_URL ?>/administradores" class="module-card">
        <div class="module-icon module-icon--purple">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <div class="module-info">
            <h3>Administradores</h3>
            <p>Administra las cuentas con acceso al sistema (crear, editar, eliminar).</p>
        </div>
        <div class="module-arrow"><i class="bi bi-arrow-right-circle-fill"></i></div>
    </a>

    <a href="<?= BASE_URL ?>/justificativos" class="module-card">
        <div class="module-icon" style="background:#ccfbf1;color:#0d9488">
            <i class="bi bi-file-earmark-medical-fill"></i>
        </div>
        <div class="module-info">
            <h3>Justificativos</h3>
            <p>
                Gestiona justificativos médicos y permisos del personal.
                <?php if ($stats['justificativos_pend'] > 0): ?>
                    <span class="badge bg-danger rounded-pill ms-1" style="font-size:10px;"><?= $stats['justificativos_pend'] ?> pendiente(s)</span>
                <?php endif; ?>
            </p>
        </div>
        <div class="module-arrow"><i class="bi bi-arrow-right-circle-fill"></i></div>
    </a>

    <a href="<?= BASE_URL ?>/reportes" class="module-card">
        <div class="module-icon module-icon--orange">
            <i class="bi bi-bar-chart-fill"></i>
        </div>
        <div class="module-info">
            <h3>Reportes</h3>
            <p>Genera reportes por rango de fechas y exporta a CSV o PDF.</p>
        </div>
        <div class="module-arrow"><i class="bi bi-arrow-right-circle-fill"></i></div>
    </a>
    
    <!-- Card Módulo de Configuraciones -->
<a href="<?= BASE_URL ?>/configuracion" class="module-card">
    <div class="module-icon" style="background:#e0e7ff; color:#4f46e5;">
        <i class="bi bi-gear-wide-connected"></i>
    </div>
    <div class="module-info">
        <h3>Configuraciones</h3>
        <p>Ajustes generales, personalización de colores, horario de asistencia y perfil de usuario.</p>
    </div>
    <div class="module-arrow"><i class="bi bi-arrow-right-circle-fill"></i></div>
</a>

    <!-- Card Módulo Horario del Trabajador -->
<a href="<?= BASE_URL ?>/horario" class="module-card">
    <div class="module-icon" style="background:#d1fae5; color:#059669;">
        <i class="bi bi-clock-history"></i>
    </div>
    <div class="module-info">
        <h3>Horario del Trabajador</h3>
        <p>Configura horarios por cargo, tolerancia de llegada y asignación de áreas del personal.</p>
    </div>
    <div class="module-arrow"><i class="bi bi-arrow-right-circle-fill"></i></div>
</a>

</div>
<!--
<div class="schedule-banner mt-4">
    <div class="schedule-banner-icon">
        <i class="bi bi-clock-history"></i>
    </div>
    <div class="schedule-banner-content">
        <strong>Horario de Registro de Asistencia</strong>
        <span>
            Entrada: <?= ENTRADA_INICIO ?> – <?= ENTRADA_FIN ?> &nbsp;|&nbsp;
            Salida: <?= SALIDA_INICIO ?> – <?= SALIDA_FIN ?>
        </span>
    </div>
    <div class="live-clock-large" id="liveClock"></div>
</div>

 /*<?php require VIEWS_PATH . '/layout/footer.php'; ?>*/
                
