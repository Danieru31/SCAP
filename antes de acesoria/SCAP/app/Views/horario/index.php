<?php require VIEWS_PATH . '/layout/header.php'; ?>

<!-- Horario del Trabajador -->

<!-- Flash messages de sesión -->
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Cabecera de página -->
<div class="page-header">
    <div>
        <h1 class="page-title">Horario del Trabajador</h1>
        <p class="page-subtitle">Gestión de horarios por cargo, tolerancia global y asignación de áreas</p>
    </div>
    <div class="page-header-actions">
        <span class="badge-today">
            <i class="bi bi-clock-history me-1"></i>
            <?= date('d \d\e F \d\e Y') ?>
        </span>
    </div>
</div>

<?php if (empty($canEdit)): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4 shadow-sm" role="alert" style="border-left: 4px solid #f59e0b;">
        <i class="bi bi-eye-fill me-3 fs-3 text-warning"></i>
        <div>
            <strong>Modo Solo Lectura:</strong> Su usuario (Administrador estándar) solo tiene acceso de consulta a los horarios. Las funciones de creación, modificación y eliminación de horarios y asignaciones están deshabilitadas.
        </div>
    </div>
<?php endif; ?>

<!-- SECCIÓN 1: Configuración de Horarios por Cargo-->
<div class="card shadow-sm mb-4" id="seccion-horarios">
    <div class="card-header d-flex align-items-center gap-2" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border-radius:12px 12px 0 0;">
        <i class="bi bi-clock-fill fs-5"></i>
        <strong>Horarios por Departamento</strong>
        <?php if (!empty($canEdit)): ?>
        <button class="btn btn-sm btn-light ms-auto" id="btnNuevoHorario"
                style="color:#4f46e5;font-weight:600;">
            <i class="bi bi-plus-circle me-1"></i>Agregar Horario
        </button>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">

        <!-- Formulario de creación/edición de horario (oculto por defecto) -->
        <div id="formHorarioWrap" class="p-4 border-bottom bg-light d-none">
            <h6 class="fw-bold mb-3"><i class="bi bi-pencil-square me-1 text-primary"></i> Configurar Horario por Departamento</h6>
            <form id="formHorario" class="row g-3 align-items-end">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_token) ?>">

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Departamento <span class="text-danger">*</span></label>
                    <select name="cargo" id="hCargo" class="form-select" required>
                        <option value="">— Seleccione departamento —</option>
                        <?php foreach ($cargos as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Hora de Entrada <span class="text-danger">*</span></label>
                    <input type="time" name="hora_entrada" id="hEntrada" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Hora de Salida <span class="text-danger">*</span></label>
                    <input type="time" name="hora_salida" id="hSalida" class="form-control" required>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100" id="btnGuardarHorario">
                        <i class="bi bi-floppy-fill me-1"></i>Guardar
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnCancelarHorario" title="Cancelar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de horarios registrados -->
        <?php if (empty($horarios)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-clock display-4 opacity-25"></i>
                <p class="mt-2">Aún no hay horarios configurados por departamento.</p>
                <p class="small">Use el botón <strong>Agregar Horario</strong> para comenzar.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" id="tablaHorarios">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th><i class="bi bi-building me-1 text-primary"></i> Departamento</th>
                        <th><i class="bi bi-box-arrow-in-right me-1 text-success"></i> Hora Entrada</th>
                        <th><i class="bi bi-box-arrow-right me-1 text-danger"></i> Hora Salida</th>
                        <th><i class="bi bi-hourglass-split me-1 text-warning"></i> Jornada</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($horarios as $i => $h): ?>
                        <?php
                            // Calcular duración de jornada
                            $tE = strtotime($h['hora_entrada']);
                            $tS = strtotime($h['hora_salida']);
                            $diff = $tS - $tE;
                            $hrs  = floor($diff / 3600);
                            $mins = floor(($diff % 3600) / 60);
                            $jornada = "{$hrs}h {$mins}m";
                        ?>
                    <tr>
                        <td class="ps-4 text-muted small"><?= $i + 1 ?></td>
                        <td>
                            <span class="badge" style="background:#ede9fe;color:#6d28d9;font-size:.8rem;padding:.35em .7em;">
                                <?= htmlspecialchars($h['cargo']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="fw-semibold text-success">
                                <i class="bi bi-clock me-1"></i>
                                <?= date('h:i A', strtotime($h['hora_entrada'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="fw-semibold text-danger">
                                <i class="bi bi-clock me-1"></i>
                                <?= date('h:i A', strtotime($h['hora_salida'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= $jornada ?></span>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($canEdit)): ?>
                            <button class="btn btn-sm btn-outline-primary me-1 btn-editar-horario"
                                    title="Editar horario"
                                    data-id="<?= $h['id'] ?>"
                                    data-cargo="<?= htmlspecialchars($h['cargo']) ?>"
                                    data-entrada="<?= htmlspecialchars(substr($h['hora_entrada'],0,5)) ?>"
                                    data-salida="<?= htmlspecialchars(substr($h['hora_salida'],0,5)) ?>">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-eliminar-horario"
                                    title="Eliminar horario"
                                    data-id="<?= $h['id'] ?>"
                                    data-cargo="<?= htmlspecialchars($h['cargo']) ?>">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                            <?php else: ?>
                            <span class="badge bg-light text-muted border">Lectura</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!--SECCIÓN 2: Tolerancia Global-->
<div class="card shadow-sm mb-4" id="seccion-tolerancia">
    <div class="card-header d-flex align-items-center gap-2"
         style="background:linear-gradient(135deg,#0891b2,#06b6d4);color:#fff;border-radius:12px 12px 0 0;">
        <i class="bi bi-stopwatch-fill fs-5"></i>
        <strong>Tiempo de Tolerancia Global</strong>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            Define el margen de minutos permitido después de la hora de entrada antes de registrar tardanza.
            Este valor aplica a <strong>todo el personal</strong>.
        </p>
        <form id="formTolerancia" class="row g-3 align-items-end">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_token) ?>">

            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    Minutos de Tolerancia <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-stopwatch"></i></span>
                    <input type="number" name="tolerancia_minutos" id="tMinutos"
                           class="form-control"
                           min="0" max="120" step="1"
                           value="<?= (int) $tolerancia ?>"
                           <?= empty($canEdit) ? 'disabled' : '' ?>
                           required>
                    <span class="input-group-text">min.</span>
                </div>
                <div class="form-text">Rango permitido: 0 – 120 minutos.</div>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-info text-white w-100" <?= empty($canEdit) ? 'disabled' : '' ?>>
                    <i class="bi bi-floppy-fill me-1"></i>Guardar Tolerancia
                </button>
            </div>
        </form>
    </div>
</div>

<!--  SECCIÓN 3: Tabla de Personal y Asignación de Ubicación -->
<div class="card shadow-sm mb-4" id="seccion-asignaciones">
    <div class="card-header d-flex align-items-center gap-2"
         style="background:linear-gradient(135deg,#059669,#10b981);color:#fff;border-radius:12px 12px 0 0;">
        <i class="bi bi-people-fill fs-5"></i>
        <strong>Cargo del Personal y Departamento asignado</strong>
        <!-- Barra de búsqueda rápida -->
        <div class="ms-auto" style="min-width:220px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="search" id="buscadorPersonal" class="form-control border-0"
                       placeholder="Buscar personal…" autocomplete="off">
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" id="tablaAsignaciones">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th><i class="bi bi-person-fill me-1 text-primary"></i> Nombre y Apellido</th>
                        <th><i class="bi bi-briefcase-fill me-1 text-secondary"></i> Cargo Laboral</th>
                        <th><i class="bi bi-building me-1 text-info"></i> Departamento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($asignaciones)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-people display-4 opacity-25"></i>
                            <p class="mt-2">No hay personal registrado en el sistema.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($asignaciones as $i => $p): ?>
                    <tr>
                        <td class="ps-4 text-muted small"><?= $i + 1 ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:34px;height:34px;font-size:.85rem;font-weight:700;">
                                    <?= strtoupper(substr($p['nombres_apellidos'], 0, 1)) ?>
                                </div>
                                <span class="fw-semibold"><?= htmlspecialchars($p['nombres_apellidos']) ?></span>
                            </div>
                        </td>
                        <td>
                            <?php
                                $cargoColors = [
                                    'Docente'       => ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
                                    'Administrador' => ['bg'=>'#e0e7ff','color'=>'#4338ca'],
                                    'Director'      => ['bg'=>'#fce7f3','color'=>'#be185d'],
                                    'Vigilante'     => ['bg'=>'#fef3c7','color'=>'#92400e'],
                                    'Cocinero'      => ['bg'=>'#ffedd5','color'=>'#c2410c'],
                                    'Limpieza'      => ['bg'=>'#d1fae5','color'=>'#065f46'],
                                    'Ambientalista' => ['bg'=>'#dcfce7','color'=>'#166534'],
                                    'Obrero'        => ['bg'=>'#f1f5f9','color'=>'#334155'],
                                ];
                                $cc = $cargoColors[$p['cargo']] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
                            ?>
                            <span class="badge" style="background:<?= $cc['bg'] ?>;color:<?= $cc['color'] ?>;font-size:.78rem;padding:.35em .7em;">
                                <?= htmlspecialchars($p['cargo']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?= htmlspecialchars($p['departamento'] ?? 'General') ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!--Editar Asignación de Ubicación-->
<div class="modal fade" id="modalAsignacion" tabindex="-1" aria-labelledby="modalAsignacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header text-white"
                 style="background:linear-gradient(135deg,#059669,#10b981);">
                <h5 class="modal-title fw-bold" id="modalAsignacionLabel">
                    <i class="bi bi-geo-alt-fill me-2"></i>Asignar Ubicación al Trabajador
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formAsignacion">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_token) ?>">
                    <input type="hidden" name="worker_id" id="aWorkerId">

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">TRABAJADOR</label>
                        <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-2">
                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:38px;height:38px;font-size:1rem;font-weight:700;" id="aWorkerAvatar">A</div>
                            <div>
                                <div class="fw-semibold" id="aWorkerNombre">—</div>
                                <small class="text-muted" id="aWorkerCargo">—</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ubicación / Área <span class="text-danger">*</span></label>
                        <select name="ubicacion" id="aUbicacion" class="form-select" required>
                            <option value="">— Seleccione área —</option>
                            <?php foreach ($ubicaciones as $u): ?>
                                <option value="<?= htmlspecialchars($u) ?>"><?= htmlspecialchars($u) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Campo número de aula (solo visible cuando Aula es seleccionada) -->
                    <div class="mb-3 d-none" id="campoAula">
                        <label class="form-label fw-semibold">Número de Aula <span class="text-danger">*</span></label>
                        <input type="text" name="numero_aula" id="aNumeroAula"
                               class="form-control" maxlength="20"
                               placeholder="Ej: A-101, 3B, Sala 5…">
                        <div class="form-text">Requerido para la ubicación <strong>Aula</strong> (Docentes).</div>
                    </div>

                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnGuardarAsignacion">
                    <i class="bi bi-floppy-fill me-1"></i>Guardar Asignación
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Confirmar eliminación de horario-->
<div class="modal fade" id="modalEliminarHorario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow text-center p-4" style="border-radius:16px;">
            <div class="mb-3">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold">¿Eliminar horario?</h5>
            <p class="text-muted small mb-4">
                Se eliminará el horario del cargo <strong id="ehCargo"></strong>. Esta acción no se puede deshacer.
            </p>
            <input type="hidden" id="ehId">
            <div class="d-flex gap-2 justify-content-center">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" id="btnConfirmarEliminarHorario">
                    <i class="bi bi-trash-fill me-1"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

 <!--Confirmar eliminación de asignación-->
<div class="modal fade" id="modalEliminarAsignacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow text-center p-4" style="border-radius:16px;">
            <div class="mb-3">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold">¿Quitar asignación?</h5>
            <p class="text-muted small mb-4">
                Se quitará la ubicación asignada a <strong id="eaNombre"></strong>.
            </p>
            <input type="hidden" id="eaId">
            <div class="d-flex gap-2 justify-content-center">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" id="btnConfirmarEliminarAsignacion">
                    <i class="bi bi-trash-fill me-1"></i>Quitar
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     JavaScript del módulo
════════════════════════════════════════════════════════════ -->
<script>
(function () {
    'use strict';

    const BASE = '<?= BASE_URL ?>';
    const TOKEN = '<?= htmlspecialchars($_token) ?>';

    // ── Helpers ──────────────────────────────────────────────
    function showToast(msg, type = 'success') {
        const toast   = document.getElementById('globalToast');
        const body    = document.getElementById('globalToastBody');
        if (!toast || !body) { alert(msg); return; }
        body.textContent = msg;
        toast.className  = `toast align-items-center border-0 text-white bg-${type === 'success' ? 'success' : 'danger'}`;
        bootstrap.Toast.getOrCreateInstance(toast, { delay: 3500 }).show();
    }

    function ajaxPost(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data)
        }).then(r => r.json());
    }

    function reloadPage() { location.reload(); }

    // ── Sección Horarios por Cargo ────────────────────────────

    const formWrap = document.getElementById('formHorarioWrap');
    const formH    = document.getElementById('formHorario');
    const btnNuevo = document.getElementById('btnNuevoHorario');
    const btnCancelar = document.getElementById('btnCancelarHorario');

    btnNuevo.addEventListener('click', () => {
        formWrap.classList.remove('d-none');
        formH.reset();
        btnNuevo.classList.add('d-none');
    });

    btnCancelar.addEventListener('click', () => {
        formWrap.classList.add('d-none');
        btnNuevo.classList.remove('d-none');
        formH.reset();
    });

    // Editar horario → rellena el form
    document.querySelectorAll('.btn-editar-horario').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('hCargo').value   = btn.dataset.cargo;
            document.getElementById('hEntrada').value = btn.dataset.entrada;
            document.getElementById('hSalida').value  = btn.dataset.salida;
            formWrap.classList.remove('d-none');
            btnNuevo.classList.add('d-none');
            formWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Submit formulario horario
    formH.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(formH);
        const data = Object.fromEntries(fd.entries());
        data._token = TOKEN;
        try {
            const res = await ajaxPost(BASE + '/horario/guardar-horario-cargo', data);
            showToast(res.message, res.success ? 'success' : 'danger');
            if (res.success) reloadPage();
        } catch {
            showToast('Error de conexión. Inténtalo de nuevo.', 'danger');
        }
    });

    // Eliminar horario
    const modalEH   = new bootstrap.Modal(document.getElementById('modalEliminarHorario'));
    document.querySelectorAll('.btn-eliminar-horario').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('ehId').value    = btn.dataset.id;
            document.getElementById('ehCargo').textContent = btn.dataset.cargo;
            modalEH.show();
        });
    });

    document.getElementById('btnConfirmarEliminarHorario').addEventListener('click', async () => {
        const id = document.getElementById('ehId').value;
        try {
            const res = await ajaxPost(BASE + '/horario/eliminar-horario-cargo', { id, _token: TOKEN });
            showToast(res.message, res.success ? 'success' : 'danger');
            if (res.success) reloadPage();
        } catch {
            showToast('Error de conexión.', 'danger');
        } finally { modalEH.hide(); }
    });

    // ── Tolerancia Global ─────────────────────────────────────

    document.getElementById('formTolerancia').addEventListener('submit', async (e) => {
        e.preventDefault();
        const min = document.getElementById('tMinutos').value;
        try {
            const res = await ajaxPost(BASE + '/horario/guardar-tolerancia', {
                tolerancia_minutos: min, _token: TOKEN
            });
            showToast(res.message, res.success ? 'success' : 'danger');
        } catch {
            showToast('Error de conexión.', 'danger');
        }
    });

    // ── Asignaciones de Ubicación ─────────────────────────────

    const modalA    = new bootstrap.Modal(document.getElementById('modalAsignacion'));
    const selUbic   = document.getElementById('aUbicacion');
    const campoAula = document.getElementById('campoAula');

    // Mostrar/ocultar campo número de aula
    selUbic.addEventListener('change', () => {
        if (selUbic.value === 'Aula') {
            campoAula.classList.remove('d-none');
            document.getElementById('aNumeroAula').required = true;
        } else {
            campoAula.classList.add('d-none');
            document.getElementById('aNumeroAula').required = false;
            document.getElementById('aNumeroAula').value = '';
        }
    });

    // Abrir modal de asignación
    document.querySelectorAll('.btn-editar-asignacion').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            try {
                const res = await fetch(BASE + '/horario/datos-asignacion?id=' + id);
                const json = await res.json();
                if (!json.success) { showToast(json.message, 'danger'); return; }
                const d = json.data;
                document.getElementById('aWorkerId').value       = d.id;
                document.getElementById('aWorkerNombre').textContent = d.nombres_apellidos;
                document.getElementById('aWorkerCargo').textContent  = d.cargo;
                document.getElementById('aWorkerAvatar').textContent = d.nombres_apellidos.charAt(0).toUpperCase();
                selUbic.value = d.ubicacion || '';
                selUbic.dispatchEvent(new Event('change'));
                document.getElementById('aNumeroAula').value = d.numero_aula || '';
                modalA.show();
            } catch {
                showToast('Error al cargar datos del trabajador.', 'danger');
            }
        });
    });

    // Guardar asignación
    document.getElementById('btnGuardarAsignacion').addEventListener('click', async () => {
        const form = document.getElementById('formAsignacion');
        if (!form.checkValidity()) { form.reportValidity(); return; }
        const fd   = new FormData(form);
        const data = Object.fromEntries(fd.entries());
        data._token = TOKEN;
        try {
            const res = await ajaxPost(BASE + '/horario/guardar-asignacion', data);
            showToast(res.message, res.success ? 'success' : 'danger');
            if (res.success) { modalA.hide(); reloadPage(); }
        } catch {
            showToast('Error de conexión.', 'danger');
        }
    });

    // Eliminar asignación
    const modalEA = new bootstrap.Modal(document.getElementById('modalEliminarAsignacion'));
    document.querySelectorAll('.btn-eliminar-asignacion').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('eaId').value         = btn.dataset.id;
            document.getElementById('eaNombre').textContent = btn.dataset.nombre;
            modalEA.show();
        });
    });

    document.getElementById('btnConfirmarEliminarAsignacion').addEventListener('click', async () => {
        const id = document.getElementById('eaId').value;
        try {
            const res = await ajaxPost(BASE + '/horario/eliminar-asignacion', { id, _token: TOKEN });
            showToast(res.message, res.success ? 'success' : 'danger');
            if (res.success) reloadPage();
        } catch {
            showToast('Error de conexión.', 'danger');
        } finally { modalEA.hide(); }
    });

    // ── Búsqueda en tabla de personal ────────────────────────

    document.getElementById('buscadorPersonal').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('#tablaAsignaciones tbody tr').forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

})();
</script>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
