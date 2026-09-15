<?php require VIEWS_PATH . '/layout/header.php'; ?>

<!-- papelera del personal-->

<div class="page-header">
    <div>
        <h1 class="page-title">Papelera de Personal</h1>
        <p class="page-subtitle"><?= count($workers) ?> obrero(s) eliminado(s) temporalmente</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= BASE_URL ?>/obreros" class="btn-action btn-secondary-action">
            <i class="bi bi-arrow-left me-1"></i> Volver a Personal
        </a>
    </div>
</div>

<!-- Busqueda -->
<div class="search-bar-wrap">
    <i class="bi bi-search search-icon"></i>
    <input type="text" id="trashSearch" class="search-input"
           placeholder="Buscar en papelera...">
</div>

<!-- Tabla -->
<div class="card-panel">
    <?php if (empty($workers)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-trash3"></i></div>
            <h3>La papelera está vacía</h3>
            <p>No se encontraron obreros eliminados temporalmente.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table" id="trashTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cédula</th>
                    <th>Nombre Completo</th>
                    <th>Cargo</th>
                    <th>Departamento</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($workers as $i => $w): ?>
                <tr class="searchable-row">
                    <td class="text-muted small"><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($w['cedula']) ?></strong></td>
                    <td>
                        <div class="worker-cell">
                            <div class="worker-avatar-sm" style="background:<?= sprintf('#%06X', crc32($w['nombre']) & 0xFFFFFF) ?>22;color:<?= sprintf('#%06X', crc32($w['nombre']) & 0xFFFFFF) ?>">
                                <?= strtoupper(substr($w['nombre'], 0, 1)) ?>
                            </div>
                            <div class="worker-name">
                                <?= htmlspecialchars($w['nombre'] . ' ' . $w['apellido']) ?>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($w['cargo']) ?></td>
                    <td><?= htmlspecialchars($w['departamento']) ?></td>
                    <td class="text-center">
                        <div class="action-btns">
                            <button class="btn-icon btn-edit"
                                    data-action="restore-worker"
                                    data-id="<?= $w['id'] ?>"
                                    data-name="<?= htmlspecialchars($w['nombre'] . ' ' . $w['apellido']) ?>"
                                    style="background-color: var(--success-light); color: var(--success)"
                                    title="Restaurar">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                            <button class="btn-icon btn-delete"
                                    data-action="force-delete-worker"
                                    data-id="<?= $w['id'] ?>"
                                    data-name="<?= htmlspecialchars($w['nombre'] . ' ' . $w['apellido']) ?>"
                                    title="Eliminar Permanentemente">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!--Confirmar eliminación por siempre jamas-->

<div class="modal fade" id="forceDeleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content custom-modal">
      <div class="modal-body p-4 text-center">
        <div class="delete-modal-icon">
            <i class="bi bi-exclamation-octagon-fill"></i>
        </div>
        <h5 class="mt-3 mb-2">¿Eliminación permanente?</h5>
        <p class="text-danger small mb-0 fw-bold">
            ¡ADVERTENCIA!
        </p>
        <p class="text-muted small mb-0">
            Esta acción eliminará de forma irreversible a <strong id="forceDeleteWorkerName"></strong> y todo su historial de asistencia.
        </p>
        <input type="hidden" id="forceDeleteWorkerId">
      </div>
      <div class="modal-footer custom-modal-footer justify-content-center gap-2">
        <button class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn-modal-danger" id="btnConfirmForceDelete">
            <i class="bi bi-trash-fill me-1"></i>Eliminar Permanentemente
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Script para el comportamiento de la Papelera -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const forceDeleteModalEl = document.getElementById('forceDeleteModal');
    if (!forceDeleteModalEl) return;
    
    const forceDeleteModal = new bootstrap.Modal(forceDeleteModalEl);
    
    // Busqueda en la papelera
    const searchInp = document.getElementById('trashSearch');
    const table = document.getElementById('trashTable');
    searchInp?.addEventListener('input', () => {
        const q = searchInp.value.toLowerCase().trim();
        table.querySelectorAll('tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    });

    // restauración y eliminación permanente
    document.addEventListener('click', async e => {
        const target = e.target.closest('[data-action]');
        if (!target) return;
        
        const action = target.dataset.action;
        const id     = target.dataset.id;
        const name   = target.dataset.name;
        
        if (action === 'restore-worker') {
            if (confirm(`¿Está seguro de que desea restaurar a ${name}?`)) {
                const fd = new FormData();
                fd.set('id', id);
                fd.set('_token', window.SCAP?.token);
                
                const data = await apiFetch(`${window.SCAP?.baseUrl}/obreros/restaurar`, {
                    method: 'POST',
                    body: fd
                });
                
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 900);
                } else {
                    showToast(data.message, 'error');
                }
            }
        }
        
        if (action === 'force-delete-worker') {
            document.getElementById('forceDeleteWorkerId').value = id;
            document.getElementById('forceDeleteWorkerName').textContent = name;
            forceDeleteModal.show();
        }
    });

    // Confirmar eliminación permanente
    document.getElementById('btnConfirmForceDelete')?.addEventListener('click', async () => {
        const id = document.getElementById('forceDeleteWorkerId').value;
        const fd = new FormData();
        fd.set('id', id);
        fd.set('_token', window.SCAP?.token);
        
        const btn = document.getElementById('btnConfirmForceDelete');
        btnLoading(btn, true);
        
        const data = await apiFetch(`${window.SCAP?.baseUrl}/obreros/eliminar-permanente`, {
            method: 'POST',
            body: fd
        });
        
        btnLoading(btn, false);
        forceDeleteModal.hide();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showToast(data.message, 'error');
        }
    });
});
</script>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
