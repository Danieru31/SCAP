/**
 * SCAP - app.js
 * JavaScript principal: sidebar, modales, CRUD vía AJAX, reloj, asistencia.
 */

/* ══════════════════════════════════════════════════════════
   INICIALIZACIÓN GLOBAL
   ══════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initClock();
    initLoginPage();
    initWorkersPage();
    initAdminsPage();
    initTableSearch();
    applyTopbarContrast();
});

/* ══════════════════════════════════════════════════════════
   HELPERS
   ══════════════════════════════════════════════════════════ */
const BASE = window.SCAP?.baseUrl || '';
const TOKEN = () => window.SCAP?.token || '';

/** Auto-evaluar contraste de letras en el topbar según el color de fondo */
function applyTopbarContrast() {
    const topbar = document.getElementById('topbar');
    if (!topbar) return;

    const bg = window.getComputedStyle(topbar).backgroundColor;
    const rgbMatch = bg ? bg.match(/\d+/g) : null;
    if (rgbMatch && rgbMatch.length >= 3) {
        const r = parseInt(rgbMatch[0], 10);
        const g = parseInt(rgbMatch[1], 10);
        const b = parseInt(rgbMatch[2], 10);
        const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
        const isDark = yiq < 128;
        topbar.classList.toggle('topbar--dark', isDark);
        topbar.classList.toggle('topbar--light', !isDark);
    }
}
window.applyTopbarContrast = applyTopbarContrast;

/** Fetch genérico con JSON */
async function apiFetch(url, options = {}) {
    try {
        const resp = await fetch(url, {
            headers: { 'Accept': 'application/json', ...(options.headers || {}) },
            ...options,
        });
        return await resp.json();
    } catch (e) {
        return { success: false, message: 'Error de conexión: ' + e.message };
    }
}

/** Construir FormData con _token */
function buildForm(formEl) {
    const fd = new FormData(formEl);
    if (!fd.get('_token')) fd.set('_token', TOKEN());
    return fd;
}

/** Toast de Bootstrap */
function showToast(message, type = 'success') {
    const toastEl   = document.getElementById('globalToast');
    const toastBody = document.getElementById('globalToastBody');
    if (!toastEl || !toastBody) return;

    toastEl.className = 'toast align-items-center border-0 text-white';
    const colors = { success: 'bg-success', error: 'bg-danger', warning: 'bg-warning text-dark', info: 'bg-info text-dark' };
    toastEl.classList.add(colors[type] || 'bg-success');
    toastBody.textContent = message;

    const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 4000 });
    toast.show();
}

/** Activar/desactivar spinner en botón */
function btnLoading(btn, loading = true) {
    const textEl    = btn.querySelector('.btn-save-text, .btn-text');
    const spinnerEl = btn.querySelector('.btn-save-spinner, .btn-spinner');
    if (textEl)    textEl.classList.toggle('d-none', loading);
    if (spinnerEl) spinnerEl.classList.toggle('d-none', !loading);
    btn.disabled = loading;
}

/** Mostrar error en formulario modal */
function showFormError(errorEl, message) {
    if (!errorEl) return;
    errorEl.textContent = message;
    errorEl.classList.remove('d-none');
}
function clearFormError(errorEl) {
    if (!errorEl) return;
    errorEl.textContent = '';
    errorEl.classList.add('d-none');
}

/* ══════════════════════════════════════════════════════════
   SIDEBAR (Toggle y Overlay)
   ══════════════════════════════════════════════════════════ */
function initSidebar() {
    const sidebar  = document.getElementById('sidebar');
    const toggle   = document.getElementById('sidebarToggle');
    const close    = document.getElementById('sidebarClose');
    const overlay  = document.getElementById('sidebarOverlay');

    // Restore desktop sidebar collapsed state on page load
    if (window.innerWidth > 991 && localStorage.getItem('sidebar-collapsed') === '1') {
        document.body.classList.add('collapsed-sidebar');
    }

    function openSidebar() {
        sidebar?.classList.add('open');
        overlay?.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('open');
        document.body.style.overflow = '';
    }

    toggle?.addEventListener('click', () => {
        if (window.innerWidth > 991) {
            document.body.classList.toggle('collapsed-sidebar');
            const isCollapsed = document.body.classList.contains('collapsed-sidebar');
            localStorage.setItem('sidebar-collapsed', isCollapsed ? '1' : '0');
        } else {
            sidebar?.classList.contains('open') ? closeSidebar() : openSidebar();
        }
    });
    close?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Cerrar con Escape
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });
}

/* ══════════════════════════════════════════════════════════
   RELOJ EN TIEMPO REAL
   ══════════════════════════════════════════════════════════ */
function initClock() {
    const clockEls = [
        document.getElementById('topbarClock'),
        document.getElementById('liveClock'),
    ].filter(Boolean);

    function tick() {
        const now = new Date();
        const str = now.toLocaleTimeString('es-VE', {
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        });
        clockEls.forEach(el => el.textContent = str);
    }
    tick();
    setInterval(tick, 1000);
}

/* ══════════════════════════════════════════════════════════
   LOGIN PAGE: Attendance Modal + Toggle Password
   ══════════════════════════════════════════════════════════ */
function initLoginPage() {
    // ── Toggle password ──────────────────────────────────
    const passInput  = document.getElementById('password');
    const toggleBtn  = document.getElementById('togglePass');
    const toggleIcon = document.getElementById('togglePassIcon');

    toggleBtn?.addEventListener('click', () => {
        const isPass = passInput.type === 'password';
        passInput.type = isPass ? 'text' : 'password';
        toggleIcon.className = isPass ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
    });

    // ── Login form spinner ────────────────────────────────
    const loginForm = document.getElementById('loginForm');
    const loginBtn  = document.getElementById('loginBtn');
    loginForm?.addEventListener('submit', () => btnLoading(loginBtn, true));

    // ── Attendance Modal ──────────────────────────────────
    const overlay       = document.getElementById('attendanceOverlay');
    const modal         = document.getElementById('attendanceModal');
    const btnOpen       = document.getElementById('btnOpenAttendance');
    const btnClose      = document.getElementById('btnCloseAttendance');
    const cedulaInput   = document.getElementById('cedulaInput');
    const btnRegister   = document.getElementById('btnRegister');
    const formSection   = document.getElementById('attModalForm');
    const resultSection = document.getElementById('attModalResult');
    const resultIcon    = document.getElementById('resultIcon');
    const resultMsg     = document.getElementById('resultMessage');
    const btnNewReg     = document.getElementById('btnNewRegister');

    if (!overlay) return; // No estamos en login page

    function openModal() {
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        resetAttModal();
        setTimeout(() => cedulaInput?.focus(), 300);
    }

    function closeModal() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    const motivoWrap  = document.getElementById('motivoTardanzaWrap');
    const motivoInput = document.getElementById('motivoInput');
    const motivoAlert = document.getElementById('motivoAlertText');

    function resetAttModal() {
        if (cedulaInput) cedulaInput.value = '';
        if (motivoInput) motivoInput.value = '';
        motivoWrap?.classList.add('d-none');
        formSection?.classList.remove('d-none');
        resultSection?.classList.add('d-none');
    }

    btnOpen?.addEventListener('click', openModal);
    btnClose?.addEventListener('click', closeModal);
    overlay?.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
    btnNewReg?.addEventListener('click', resetAttModal);

    // Solo dígitos en cédula
    cedulaInput?.addEventListener('input', () => {
        cedulaInput.value = cedulaInput.value.replace(/\D/g, '');
    });

    // Enter en cédula → registrar
    cedulaInput?.addEventListener('keydown', e => {
        if (e.key === 'Enter') btnRegister?.click();
    });

    // Registrar asistencia
    btnRegister?.addEventListener('click', async () => {
        const cedula = cedulaInput?.value.trim();
        if (!cedula) {
            cedulaInput?.classList.add('border-danger');
            setTimeout(() => cedulaInput?.classList.remove('border-danger'), 1500);
            return;
        }

        const motivo = motivoInput?.value.trim() || '';

        // Si la sección de tardanza está visible y el motivo está vacío, exigir el motivo
        if (motivoWrap && !motivoWrap.classList.contains('d-none') && !motivo) {
            motivoInput?.classList.add('border-danger');
            setTimeout(() => motivoInput?.classList.remove('border-danger'), 1500);
            motivoInput?.focus();
            return;
        }

        btnLoading(btnRegister, true);

        const fd = new FormData();
        fd.set('cedula', cedula);
        if (motivo) {
            fd.set('motivo_tardanza', motivo);
        }

        const data = await apiFetch(`${BASE}/asistencia/registrar`, { method: 'POST', body: fd });

        btnLoading(btnRegister, false);

        // Caso: Requiere justificativo de tardanza
        if (!data.success && data.requires_motivo) {
            if (motivoWrap && motivoAlert) {
                motivoWrap.classList.remove('d-none');
                motivoAlert.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>${data.worker_name}</strong> (${data.cargo}): Llegada tardía con ${data.minutos_retraso} min. de retraso. Por favor ingrese el motivo.`;
                setTimeout(() => motivoInput?.focus(), 200);
            }
            return;
        }

        // Mostrar resultado final
        formSection?.classList.add('d-none');
        resultSection?.classList.remove('d-none');

        if (data.success) {
            const type = data.type || 'entrada';
            resultIcon.innerHTML = type === 'entrada'
                ? '<span style="color:#22d3ee">⬆️</span>'
                : '<span style="color:#818cf8">⬇️</span>';
            resultMsg.style.borderLeft = `4px solid ${type === 'entrada' ? '#22d3ee' : '#818cf8'}`;
        } else {
            resultIcon.innerHTML = data.out_of_schedule ? '⏰' : '❌';
            resultMsg.style.borderLeft = '4px solid #ef4444';
        }

        resultMsg.textContent = data.message || 'Respuesta inesperada del servidor.';
    });

    // ── Admin Login Modal ────────────────────────────────
    const adminOverlay  = document.getElementById('adminLoginOverlay');
    const btnOpenAdmin  = document.getElementById('btnOpenAdminLogin');
    const btnCloseAdmin = document.getElementById('btnCloseAdminLogin');
    const usernameInput = document.getElementById('username');

    function openAdminModal() {
        adminOverlay?.classList.add('open');
        document.body.style.overflow = 'hidden';
        setTimeout(() => usernameInput?.focus(), 300);
    }

    function closeAdminModal() {
        adminOverlay?.classList.remove('open');
        document.body.style.overflow = '';
    }

    btnOpenAdmin?.addEventListener('click', openAdminModal);
    btnCloseAdmin?.addEventListener('click', closeAdminModal);
    adminOverlay?.addEventListener('click', e => { if (e.target === adminOverlay) closeAdminModal(); });

    // ESC para cerrar modales
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            if (adminOverlay?.classList.contains('open')) closeAdminModal();
            if (overlay?.classList.contains('open')) closeModal();
        }
    });
}

/* ══════════════════════════════════════════════════════════
   BÚSQUEDA EN TABLA
   ══════════════════════════════════════════════════════════ */
function initTableSearch() {
    const searches = [
        { inputId: 'workerSearch', tableId: 'workersTable' },
        { inputId: 'adminSearch',  tableId: 'adminsTable'  },
    ];

    searches.forEach(({ inputId, tableId }) => {
        const inp   = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        if (!inp || !table) return;

        inp.addEventListener('input', () => {
            const q = inp.value.toLowerCase().trim();
            table.querySelectorAll('tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    });
}

/* ══════════════════════════════════════════════════════════
   OBREROS - CRUD
   ══════════════════════════════════════════════════════════ */
function initWorkersPage() {
    const workerModalEl = document.getElementById('workerModal');
    const deleteModalEl = document.getElementById('deleteModal');
    if (!workerModalEl) return; // No estamos en la página de obreros

    const workerModal = new bootstrap.Modal(workerModalEl);
    const deleteModal = new bootstrap.Modal(deleteModalEl);

    const form      = document.getElementById('workerForm');
    const idField   = document.getElementById('workerId');
    const titleEl   = document.getElementById('workerModalTitle');
    const saveBtn   = document.getElementById('btnSaveWorker');
    const errorEl   = document.getElementById('workerFormError');
    const editOnlys = workerModalEl.querySelectorAll('.edit-only');

    // ── Abrir modal nuevo ──────────────────────────────
    document.getElementById('btnNewWorker')?.addEventListener('click', () => {
        form.reset();
        idField.value = '';
        titleEl.textContent = 'Nuevo Obrero';
        clearFormError(errorEl);
        editOnlys.forEach(el => el.classList.add('d-none'));
        // Valores por defecto
        if (document.getElementById('wPoseeDocumento')) document.getElementById('wPoseeDocumento').value = 'Sí';
        if (document.getElementById('wCargo')) document.getElementById('wCargo').value = 'Obrero';
        if (document.getElementById('wDepartamento')) document.getElementById('wDepartamento').value = '';
        if (document.getElementById('wGradoAcademico')) document.getElementById('wGradoAcademico').value = 'Ninguno';
        if (document.getElementById('wAniosServicio')) document.getElementById('wAniosServicio').value = '0';
        workerModal.show();
    });

    // ── Delegación de eventos (editar y eliminar) ──────
    document.addEventListener('click', async e => {
        const target = e.target.closest('[data-action]');
        if (!target) return;
        const action = target.dataset.action;
        const id     = target.dataset.id;

        // ── Editar ─────────────────────────────────────
        if (action === 'edit-worker') {
            const data = await apiFetch(`${BASE}/obreros/datos?id=${id}`);
            if (!data.success) { showToast(data.message, 'error'); return; }

            const w = data.data;
            form.reset();
            idField.value = w.id;

            if (document.getElementById('wPoseeDocumento')) document.getElementById('wPoseeDocumento').value = w.posee_documento || 'Sí';
            if (document.getElementById('wCedula')) document.getElementById('wCedula').value = w.cedula || '';
            if (document.getElementById('wNombresApellidos')) {
                document.getElementById('wNombresApellidos').value = w.nombres_apellidos || ((w.nombre || '') + ' ' + (w.apellido || '')).trim();
            }
            if (document.getElementById('wCargo')) document.getElementById('wCargo').value = w.cargo || 'Obrero';
            if (document.getElementById('wDepartamento')) document.getElementById('wDepartamento').value = w.departamento || '';
            if (document.getElementById('wTelefono')) document.getElementById('wTelefono').value = w.telefono || '';
            if (document.getElementById('wGradoAcademico')) document.getElementById('wGradoAcademico').value = w.grado_academico || 'Ninguno';
            if (document.getElementById('wAniosServicio')) document.getElementById('wAniosServicio').value = w.anios_servicio !== undefined ? w.anios_servicio : 0;
            if (document.getElementById('wCondicionMedica')) document.getElementById('wCondicionMedica').value = w.condicion_medica || 'Ninguna';
            if (document.getElementById('wActivo')) document.getElementById('wActivo').value = w.activo;

            titleEl.textContent = 'Editar Obrero';
            editOnlys.forEach(el => el.classList.remove('d-none'));
            clearFormError(errorEl);
            workerModal.show();
        }

        // ── Eliminar ───────────────────────────────────
        if (action === 'delete-worker') {
            document.getElementById('deleteWorkerId').value  = id;
            document.getElementById('deleteWorkerName').textContent = target.dataset.name;
            deleteModal.show();
        }
    });

    // ── Guardar (crear o editar) ────────────────────────
    saveBtn?.addEventListener('click', async () => {
        clearFormError(errorEl);
        const isEdit  = !!idField.value;
        const url     = `${BASE}/obreros/${isEdit ? 'editar' : 'crear'}`;
        const fd      = buildForm(form);

        btnLoading(saveBtn, true);
        const data = await apiFetch(url, { method: 'POST', body: fd });
        btnLoading(saveBtn, false);

        if (data.success) {
            workerModal.hide();
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showFormError(errorEl, data.message);
        }
    });

    // ── Confirmar eliminación ───────────────────────────
    document.getElementById('btnConfirmDelete')?.addEventListener('click', async () => {
        const id = document.getElementById('deleteWorkerId').value;
        const fd = new FormData();
        fd.set('id', id);
        fd.set('_token', TOKEN());

        const data = await apiFetch(`${BASE}/obreros/eliminar`, { method: 'POST', body: fd });

        if (data.success) {
            deleteModal.hide();
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showToast(data.message, 'error');
        }
    });
}

/* ══════════════════════════════════════════════════════════
   ADMINISTRADORES - CRUD
   ══════════════════════════════════════════════════════════ */
function initAdminsPage() {
    const adminModalEl  = document.getElementById('adminModal');
    const deleteModalEl = document.getElementById('deleteAdminModal');
    if (!adminModalEl) return;

    const adminModal  = new bootstrap.Modal(adminModalEl);
    const deleteModal = new bootstrap.Modal(deleteModalEl);

    const form      = document.getElementById('adminForm');
    const idField   = document.getElementById('adminId');
    const titleEl   = document.getElementById('adminModalTitle');
    const saveBtn   = document.getElementById('btnSaveAdmin');
    const errorEl   = document.getElementById('adminFormError');
    const editOnlys = adminModalEl.querySelectorAll('.edit-only');
    const newOnlys  = adminModalEl.querySelectorAll('.new-only');

    // Toggle password en modal admin
    document.getElementById('toggleAdminPass')?.addEventListener('click', () => {
        const inp  = document.getElementById('aPassword');
        const icon = document.querySelector('#toggleAdminPass i');
        if (!inp) return;
        const isPass = inp.type === 'password';
        inp.type = isPass ? 'text' : 'password';
        icon.className = isPass ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
    });

    // Abrir modal nuevo
    document.getElementById('btnNewAdmin')?.addEventListener('click', () => {
        form.reset();
        idField.value = '';
        titleEl.textContent = 'Nuevo Administrador';
        clearFormError(errorEl);
        editOnlys.forEach(el => el.classList.add('d-none'));
        newOnlys.forEach(el  => el.classList.remove('d-none'));
        if (document.getElementById('aRol')) document.getElementById('aRol').value = 'admin';
        document.getElementById('aPregunta1').value = '';
        document.getElementById('aRespuesta1').value = '';
        document.getElementById('aPregunta2').value = '';
        document.getElementById('aRespuesta2').value = '';
        adminModal.show();
    });

    // Delegación de eventos
    document.addEventListener('click', async e => {
        const target = e.target.closest('[data-action]');
        if (!target) return;
        const action = target.dataset.action;
        const id     = target.dataset.id;

        if (action === 'edit-admin') {
            const data = await apiFetch(`${BASE}/administradores/datos?id=${id}`);
            if (!data.success) { showToast(data.message, 'error'); return; }

            const adm = data.data;
            form.reset();
            idField.value                              = adm.id;
            document.getElementById('aUsername').value = adm.username;
            document.getElementById('aNombre').value   = adm.nombre;
            document.getElementById('aApellido').value = adm.apellido;
            if (document.getElementById('aRol')) document.getElementById('aRol').value = adm.rol || 'admin';
            document.getElementById('aActivo').value   = adm.activo;
            document.getElementById('aPassword').value = '';
            document.getElementById('aPregunta1').value = adm.pregunta_1 || '';
            document.getElementById('aRespuesta1').value = '';
            document.getElementById('aPregunta2').value = adm.pregunta_2 || '';
            document.getElementById('aRespuesta2').value = '';

            titleEl.textContent = 'Editar Administrador';
            editOnlys.forEach(el => el.classList.remove('d-none'));
            newOnlys.forEach(el  => el.classList.add('d-none'));
            clearFormError(errorEl);
            adminModal.show();
        }

        if (action === 'delete-admin') {
            document.getElementById('deleteAdminId').value   = id;
            document.getElementById('deleteAdminName').textContent = target.dataset.name;
            deleteModal.show();
        }
    });

    // Guardar
    saveBtn?.addEventListener('click', async () => {
        clearFormError(errorEl);
        const isEdit = !!idField.value;
        const url    = `${BASE}/administradores/${isEdit ? 'editar' : 'crear'}`;
        const fd     = buildForm(form);

        btnLoading(saveBtn, true);
        const data = await apiFetch(url, { method: 'POST', body: fd });
        btnLoading(saveBtn, false);

        if (data.success) {
            adminModal.hide();
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showFormError(errorEl, data.message);
        }
    });

    // Confirmar eliminación
    document.getElementById('btnConfirmDeleteAdmin')?.addEventListener('click', async () => {
        const id = document.getElementById('deleteAdminId').value;
        const fd = new FormData();
        fd.set('id', id);
        fd.set('_token', TOKEN());

        const data = await apiFetch(`${BASE}/administradores/eliminar`, { method: 'POST', body: fd });

        if (data.success) {
            deleteModal.hide();
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showToast(data.message, 'error');
            deleteModal.hide();
        }
    });
}
