<?php 
require VIEWS_PATH . '/layout/header.php'; 
require_once APP_PATH . '/Middleware/AuthMiddleware.php';
?>
<!-- admins -->

<div class="page-header">
    <div>
        <h1 class="page-title">Administradores</h1>
        <p class="page-subtitle"><?= count($admins) ?> administrador(es) del sistema</p>
    </div>
    <div class="page-header-actions">
        <button class="btn-export btn-export-pdf me-2" id="btnExportAdminsPdf">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
        </button>
        <button class="btn-export btn-export-csv me-2" id="btnExportAdminsExcel">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
        </button>
        <button class="btn-action btn-primary-action" id="btnNewAdmin">
            <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Admin
        </button>
    </div>
</div>

<!-- busqueda -->
<div class="search-bar-wrap">
    <i class="bi bi-search search-icon"></i>
    <input type="text" id="adminSearch" class="search-input"
           placeholder="Buscar por usuario, nombre o rol…">
</div>

<!-- tabla -->
<div class="card-panel">
    <?php if (empty($admins)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-shield-x"></i></div>
            <h3>Sin administradores</h3>
            <p>Agrega al menos un administrador para acceder al sistema.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table" id="adminsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Nombre Completo</th>
                    <th>Rol</th>
                    <th>Fecha de Creación</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($admins as $i => $adm): ?>
                <?php
                    $rol = $adm['rol'] ?? 'admin';
                    $isMega = ($adm['id'] == 1 || $rol === 'mega_admin');
                    $isSuper = ($rol === 'super_admin');
                ?>
                <tr class="searchable-row">
                    <td class="text-muted small"><?= $i + 1 ?></td>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar-sm <?= $adm['id'] == $_SESSION['admin_id'] ? 'current-user' : '' ?>">
                                <?= strtoupper(substr($adm['username'], 0, 1)) ?>
                            </div>
                            <div>
                                <strong>@<?= htmlspecialchars($adm['username']) ?></strong>
                                <?php if ($adm['id'] == $_SESSION['admin_id']): ?>
                                    <span class="badge-you">Tú</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($adm['nombre'] . ' ' . $adm['apellido']) ?></td>
                    <td>
                        <?php if ($isMega): ?>
                            <span class="badge bg-danger text-white"><i class="bi bi-shield-lock-fill me-1"></i>Mega Admin</span>
                        <?php elseif ($isSuper): ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Super Admin</span>
                        <?php else: ?>
                            <span class="badge bg-info text-dark"><i class="bi bi-person-fill me-1"></i>Admin</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small">
                        <?= date('d/m/Y', strtotime($adm['created_at'])) ?>
                    </td>
                    <td>
                        <span class="status-badge <?= $adm['activo'] ? 'badge-complete' : 'badge-inactive' ?>">
                            <?= $adm['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if ($isMega): ?>
                            <span class="badge bg-secondary opacity-75 py-2 px-3" title="El superusuario raíz es inmutable y no se puede modificar ni eliminar.">
                                <i class="bi bi-lock-fill me-1"></i>Inmutable
                            </span>
                        <?php else: ?>
                            <div class="action-btns justify-content-center">
                                <button class="btn-icon btn-edit"
                                        data-action="edit-admin"
                                        data-id="<?= $adm['id'] ?>"
                                        title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <?php if ($adm['id'] != $_SESSION['admin_id']): ?>
                                <button class="btn-icon btn-delete"
                                        data-action="delete-admin"
                                        data-id="<?= $adm['id'] ?>"
                                        data-name="<?= htmlspecialchars('@' . $adm['username']) ?>"
                                        title="Eliminar">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                                <?php else: ?>
                                <button class="btn-icon btn-delete" disabled title="No puede eliminar su propia cuenta">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Crear y editar Admin -->

<div class="modal fade" id="adminModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content custom-modal">
      <div class="modal-header custom-modal-header">
        <h5 class="modal-title" id="adminModalTitle">Nuevo Administrador</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="adminForm" novalidate>
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_token) ?>">
            <input type="hidden" name="id" id="adminId" value="">

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label-custom" for="aUsername">Usuario <span class="text-danger">*</span></label>
                    <input type="text" name="username" id="aUsername"
                           class="form-control-custom" placeholder="Ej: jperez"
                           autocomplete="off" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label-custom" for="aNombre">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="aNombre"
                           class="form-control-custom" placeholder="Nombre" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label-custom" for="aApellido">Apellido</label>
                    <input type="text" name="apellido" id="aApellido"
                           class="form-control-custom" placeholder="Apellido">
                </div>
                <div class="col-12">
                    <label class="form-label-custom" for="aRol">Rol de Administrador <span class="text-danger">*</span></label>
                    <select name="rol" id="aRol" class="form-control-custom" required>
                        <option value="admin">Administrador Estándar</option>
                        <option value="super_admin">Super Administrador (Máx. 1 activo)</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label-custom" for="aPassword">
                        Contraseña <span class="text-danger new-only">*</span>
                        <span class="form-hint edit-only d-none">(dejar en blanco para no cambiar)</span>
                    </label>
                    <div class="password-wrap">
                        <input type="password" name="password" id="aPassword"
                               class="form-control-custom" placeholder="Mínimo 6 caracteres"
                               autocomplete="new-password">
                        <button type="button" class="toggle-pass-modal" id="toggleAdminPass">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12 edit-only d-none">
                    <label class="form-label-custom">Estado</label>
                    <select name="activo" id="aActivo" class="form-control-custom">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="col-12 mt-2 pt-2 border-top">
                    <h6 class="text-secondary small fw-bold">Preguntas de Seguridad (Obligatorias)</h6>
                </div>
                <div class="col-12">
                    <label class="form-label-custom" for="aPregunta1">Pregunta de Seguridad 1 <span class="text-danger">*</span></label>
                    <input type="text" name="pregunta_1" id="aPregunta1"
                           class="form-control-custom" placeholder="Ej: ¿Cuál es el nombre de tu primera mascota?" required>
                </div>
                <div class="col-12">
                    <label class="form-label-custom" for="aRespuesta1">
                        Respuesta de Seguridad 1 <span class="text-danger new-only">*</span>
                        <span class="form-hint edit-only d-none">(dejar en blanco para no cambiar)</span>
                    </label>
                    <input type="text" name="respuesta_1" id="aRespuesta1"
                           class="form-control-custom" placeholder="Respuesta a la pregunta 1">
                </div>
                <div class="col-12">
                    <label class="form-label-custom" for="aPregunta2">Pregunta de Seguridad 2 <span class="text-danger">*</span></label>
                    <input type="text" name="pregunta_2" id="aPregunta2"
                           class="form-control-custom" placeholder="Ej: ¿Cuál es tu ciudad de nacimiento?" required>
                </div>
                <div class="col-12">
                    <label class="form-label-custom" for="aRespuesta2">
                        Respuesta de Seguridad 2 <span class="text-danger new-only">*</span>
                        <span class="form-hint edit-only d-none">(dejar en blanco para no cambiar)</span>
                    </label>
                    <input type="text" name="respuesta_2" id="aRespuesta2"
                           class="form-control-custom" placeholder="Respuesta a la pregunta 2">
                </div>
            </div>

            <div class="modal-form-error d-none" id="adminFormError"></div>
        </form>
      </div>
      <div class="modal-footer custom-modal-footer">
        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn-modal-save" id="btnSaveAdmin">
            <span class="btn-save-text"><i class="bi bi-check-lg me-1"></i>Guardar</span>
            <span class="btn-save-spinner d-none"><span class="spinner-border spinner-border-sm"></span></span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- eliminar -->
<div class="modal fade" id="deleteAdminModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content custom-modal">
      <div class="modal-body p-4 text-center">
        <div class="delete-modal-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <h5 class="mt-3 mb-2">¿Eliminar administrador?</h5>
        <p class="text-muted small mb-0">
            Esto eliminará la cuenta <strong id="deleteAdminName"></strong>
            permanentemente.
        </p>
        <input type="hidden" id="deleteAdminId">
        <input type="hidden" id="deleteAdminToken" value="<?= htmlspecialchars($_token) ?>">
      </div>
      <div class="modal-footer custom-modal-footer justify-content-center gap-2">
        <button class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn-modal-danger" id="btnConfirmDeleteAdmin">
            <i class="bi bi-trash-fill me-1"></i>Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Exportar a excel (SheetJS)
    document.getElementById('btnExportAdminsExcel')?.addEventListener('click', function() {
        const table = document.getElementById('adminsTable');
        if (!table) return;

        const wb = XLSX.utils.book_new();
        const data = [];
        
        // Cabeceras (omitir la columna de Acciones)
        const headers = [];
        const ths = table.querySelectorAll('thead th');
        for (let i = 0; i < ths.length - 1; i++) {
            headers.push(ths[i].innerText.trim());
        }
        data.push(headers);
        
        // Filas (omitir la columna de Acciones)
        const trs = table.querySelectorAll('tbody tr');
        trs.forEach(tr => {
            const row = [];
            const tds = tr.querySelectorAll('td');
            if (tds.length > 0) {
                for (let i = 0; i < tds.length - 1; i++) {
                    row.push(tds[i].innerText.trim());
                }
                data.push(row);
            }
        });
        
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Administradores");
        XLSX.writeFile(wb, "administradores.xlsx");
    });

    // Exportar a PDF (jsPDF)
    document.getElementById('btnExportAdminsPdf')?.addEventListener('click', function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(16);
        doc.text('Reporte de Administradores · SCAP', 14, 18);
        
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.text('Generado: ' + new Date().toLocaleString('es-VE'), 14, 25);
        
        const table = document.getElementById('adminsTable');
        if (!table) return;
        
        const headers = [];
        const ths = table.querySelectorAll('thead th');
        for (let i = 0; i < ths.length - 1; i++) {
            headers.push(ths[i].innerText.trim());
        }
        
        const rows = [];
        const trs = table.querySelectorAll('tbody tr');
        trs.forEach(tr => {
            const row = [];
            const tds = tr.querySelectorAll('td');
            if (tds.length > 0) {
                for (let i = 0; i < tds.length - 1; i++) {
                    row.push(tds[i].innerText.trim());
                }
                rows.push(row);
            }
        });
        
        doc.autoTable({
            head: [headers],
            body: rows,
            startY: 30,
            styles: { fontSize: 9, cellPadding: 3 },
            headStyles: { fillColor: [99, 102, 241], textColor: 255 },
            alternateRowStyles: { fillColor: [248, 250, 252] },
        });
        
        doc.save('administradores.pdf');
    });
});
</script>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
