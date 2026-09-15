<?php require VIEWS_PATH . '/layout/header.php'; ?>

<!-- justificativos -->

<div class="page-header">
    <div>
        <h1 class="page-title">Justificativos</h1>
        <p class="page-subtitle"><?= count($justifications) ?> justificativo(s) registrado(s)</p>
    </div>
    <div class="page-header-actions">
        <button class="btn-export btn-export-pdf me-2" id="btnExportJustificationsPdf">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
        </button>
        <button class="btn-export btn-export-csv me-2" id="btnExportJustificationsExcel">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
        </button>
        <button class="btn-action btn-primary-action" id="btnNewJustification">
            <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Justificativo
        </button>
    </div>
</div>

<!-- Barra de busqueda y filtros -->
<div class="card-panel mb-3 p-3">
    <div class="row g-3 align-items-center">
        <div class="col-md-6">
            <div class="search-bar-wrap mb-0">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="justSearch" class="search-input w-100" style="max-width:none;"
                       placeholder="Buscar por nombre, motivo o cargo...">
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <span class="export-label me-2">Filtrar Estado:</span>
            <button class="btn-action btn-secondary-action btn-sm active px-3 filter-status-btn" data-status="Todos">Todos</button>
            <button class="btn-action btn-secondary-action btn-sm px-3 filter-status-btn" data-status="Pendiente">Pendientes</button>
            <button class="btn-action btn-secondary-action btn-sm px-3 filter-status-btn" data-status="Aprobado">Aprobados</button>
            <button class="btn-action btn-secondary-action btn-sm px-3 filter-status-btn" data-status="Rechazado">Rechazados</button>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="card-panel">
    <?php if (empty($justifications)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-file-earmark-medical"></i></div>
            <h3>Sin justificativos registrados</h3>
            <p>Registre justificativos para ausencias médicas, permisos de obreros, etc.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table" id="justificationsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre del Obrero</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($justifications as $i => $j): ?>
                <?php
                $statusClass = $j['estado'] === 'Aprobado' ? 'badge-complete'
                             : ($j['estado'] === 'Rechazado' ? 'badge-inactive'
                                                             : 'badge-entry');
                ?>
                <tr class="searchable-row" data-estado="<?= $j['estado'] ?>">
                    <td class="text-muted small"><?= $i + 1 ?></td>
                    <td>
                        <div class="worker-cell">
                            <div class="worker-avatar-sm" style="background:<?= sprintf('#%06X', crc32($j['nombre']) & 0xFFFFFF) ?>22;color:<?= sprintf('#%06X', crc32($j['nombre']) & 0xFFFFFF) ?>">
                                <?= strtoupper(substr($j['nombre'], 0, 1)) ?>
                            </div>
                            <div class="worker-name">
                                <?= htmlspecialchars($j['nombre'] . ' ' . $j['apellido']) ?>
                            </div>
                        </div>
                    </td>
                    <td><strong><?= date('d/m/Y', strtotime($j['fecha_inicio'])) ?></strong></td>
                    <td><strong><?= date('d/m/Y', strtotime($j['fecha_fin'])) ?></strong></td>
                    <td style="max-width: 250px;" class="text-truncate" title="<?= htmlspecialchars($j['motivo']) ?>">
                        <?= htmlspecialchars($j['motivo']) ?>
                    </td>
                    <td>
                        <span class="status-badge <?= $statusClass ?>">
                            <?= htmlspecialchars($j['estado']) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="action-btns">
                            <button class="btn-icon btn-edit"
                                    data-action="edit-justification"
                                    data-id="<?= $j['id'] ?>"
                                    title="Editar">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button class="btn-icon btn-delete"
                                    data-action="delete-justification"
                                    data-id="<?= $j['id'] ?>"
                                    data-name="<?= htmlspecialchars($j['nombre'] . ' ' . $j['apellido'] . ' (' . date('d/m/Y', strtotime($j['fecha_inicio'])) . ' - ' . date('d/m/Y', strtotime($j['fecha_fin'])) . ')') ?>"
                                    title="Eliminar">
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

<!--Crear y Editar Justificativo-->

<div class="modal fade" id="justificationModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content custom-modal">
      <div class="modal-header custom-modal-header">
        <h5 class="modal-title" id="justModalTitle">Nuevo Justificativo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="justificationForm" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_token) ?>">
            <input type="hidden" name="id" id="justId" value="">

            <div class="row g-3">
                <!-- Seleccionar Obrero -->
                <div class="col-12">
                    <div class="form-field-header mb-1">
                        <span class="badge bg-light text-dark border fw-bold text-uppercase px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Selección de Trabajador</span>
                    </div>
                    <label class="form-label-custom fw-bold text-dark" for="jWorker">
                        Seleccionar Obrero <span class="text-danger">*</span>
                    </label>
                    <select name="worker_id" id="jWorker" class="form-control-custom" required>
                        <option value="">-- Seleccionar Obrero --</option>
                        <?php foreach ($workers as $w): ?>
                            <option value="<?= $w['id'] ?>">
                                <?= htmlspecialchars($w['apellido'] . ', ' . $w['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tipo de Ausencia -->
                <div class="col-12">
                    <div class="form-field-header mb-1">
                        <span class="badge bg-light text-dark border fw-bold text-uppercase px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Causal de Ausencia</span>
                    </div>
                    <label class="form-label-custom fw-bold text-dark" for="jTipoAusencia">
                        Tipo de Ausencia <span class="text-danger">*</span>
                    </label>
                    <select name="tipo_ausencia" id="jTipoAusencia" class="form-control-custom" required>
                        <option value="Enfermedad">Enfermedad</option>
                        <option value="Motivo personal">Motivo personal</option>
                    </select>
                </div>
                
                <!-- Fecha de Inicio -->
                <div class="col-md-6">
                    <div class="form-field-header mb-1">
                        <span class="badge bg-light text-dark border fw-bold text-uppercase px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Fecha Inicial</span>
                    </div>
                    <label class="form-label-custom fw-bold text-dark" for="jFechaInicio">
                        Fecha de Inicio <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="fecha_inicio" id="jFechaInicio" class="form-control-custom" required>
                </div>

                <!-- Fecha de Fin -->
                <div class="col-md-6">
                    <div class="form-field-header mb-1">
                        <span class="badge bg-light text-dark border fw-bold text-uppercase px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Fecha Final</span>
                    </div>
                    <label class="form-label-custom fw-bold text-dark" for="jFechaFin">
                        Fecha de Fin <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="fecha_fin" id="jFechaFin" class="form-control-custom" required>
                </div>

                <!-- Motivo / Descripción -->
                <div class="col-12">
                    <div class="form-field-header mb-1">
                        <span class="badge bg-light text-dark border fw-bold text-uppercase px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Explicación Detallada</span>
                    </div>
                    <label class="form-label-custom fw-bold text-dark" for="jMotivo">
                        Motivo / Descripción <span class="text-danger">*</span>
                    </label>
                    <textarea name="motivo" id="jMotivo" class="form-control-custom" style="height:90px; padding-top:8px;" 
                              placeholder="Ej: Reposo médico de 48 horas por cuadro gripal..." required></textarea>
                </div>

                <!-- Adjuntar Documento -->
                <div class="col-12">
                    <div class="form-field-header mb-1">
                        <span class="badge bg-light text-dark border fw-bold text-uppercase px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Comprobante Digital</span>
                    </div>
                    <label class="form-label-custom fw-bold text-dark" for="jDocumento">
                        Adjuntar Documento <span class="text-muted font-normal">(Opcional)</span>
                    </label>
                    <input type="file" name="documento" id="jDocumento" class="form-control-custom" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="form-text text-muted">Formatos permitidos: PDF, JPG, PNG (Máximo 5 MB).</small>
                    
                    <!-- Cuadro de Vista Previa -->
                    <div id="docPreviewContainer" class="mt-2 p-2 border rounded bg-light d-none align-items-center gap-3">
                        <div id="docPreviewMedia" class="d-flex align-items-center justify-content-center overflow-hidden rounded border" style="width: 70px; height: 70px; background: #eef2f7;">
                            <!-- Imagen o Icono PDF -->
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <span id="docPreviewName" class="d-block text-truncate fw-semibold small text-dark"></span>
                            <span id="docPreviewSize" class="d-block text-muted extra-small"></span>
                            <a id="docPreviewLink" href="#" target="_blank" class="text-primary small text-decoration-underline d-none">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Ver / Abrir documento
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Estado -->
                <div class="col-12">
                    <div class="form-field-header mb-1">
                        <span class="badge bg-light text-dark border fw-bold text-uppercase px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Dictamen / Evaluación</span>
                    </div>
                    <label class="form-label-custom fw-bold text-dark" for="jEstado">
                        Estado del Justificativo
                    </label>
                    <select name="estado" id="jEstado" class="form-control-custom">
                        <option value="Pendiente">Pendiente</option>
                        <option value="Aprobado">Aprobado</option>
                        <option value="Rechazado">Rechazado</option>
                    </select>
                </div>
            </div>

            <div class="modal-form-error d-none" id="justFormError"></div>
        </form>
      </div>
      <div class="modal-footer custom-modal-footer">
        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn-modal-save" id="btnSaveJustification">
            <span class="btn-save-text"><i class="bi bi-check-lg me-1"></i>Guardar</span>
            <span class="btn-save-spinner d-none"><span class="spinner-border spinner-border-sm"></span></span>
        </button>
      </div>
    </div>
  </div>
</div>

<!--Confirmar eliminación-->

<div class="modal fade" id="deleteJustModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content custom-modal">
      <div class="modal-body p-4 text-center">
        <div class="delete-modal-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <h5 class="mt-3 mb-2">¿Eliminar justificativo?</h5>
        <p class="text-muted small mb-0">
            Esta acción eliminará permanentemente el justificativo de <strong id="deleteJustName"></strong>. Esta acción no se puede deshacer.
        </p>
        <input type="hidden" id="deleteJustId">
      </div>
      <div class="modal-footer custom-modal-footer justify-content-center gap-2">
        <button class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn-modal-danger" id="btnConfirmDeleteJust">
            <i class="bi bi-trash-fill me-1"></i>Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- logica del modulo -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const justModalEl  = document.getElementById('justificationModal');
    const deleteModalEl = document.getElementById('deleteJustModal');
    if (!justModalEl) return;

    const justModal  = new bootstrap.Modal(justModalEl);
    const deleteModal = new bootstrap.Modal(deleteModalEl);

    const form      = document.getElementById('justificationForm');
    const idField   = document.getElementById('justId');
    const titleEl   = document.getElementById('justModalTitle');
    const saveBtn   = document.getElementById('btnSaveJustification');
    const errorEl   = document.getElementById('justFormError');

    const docInput     = document.getElementById('jDocumento');
    const docContainer = document.getElementById('docPreviewContainer');
    const docMedia     = document.getElementById('docPreviewMedia');
    const docName      = document.getElementById('docPreviewName');
    const docSize      = document.getElementById('docPreviewSize');
    const docLink      = document.getElementById('docPreviewLink');

    function resetDocPreview() {
        docContainer?.classList.add('d-none');
        docContainer?.classList.remove('d-flex');
        if (docMedia) docMedia.innerHTML = '';
        if (docName) docName.textContent = '';
        if (docSize) docSize.textContent = '';
        if (docLink) {
            docLink.href = '#';
            docLink.classList.add('d-none');
        }
    }

    // Escuchar selección de nuevo archivo
    docInput?.addEventListener('change', function() {
        if (!this.files || !this.files[0]) {
            resetDocPreview();
            return;
        }

        const file = this.files[0];
        const ext  = file.name.split('.').pop().toLowerCase();
        
        docName.textContent = file.name;
        docSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        docLink.classList.add('d-none');

        if (['jpg', 'jpeg', 'png'].includes(ext)) {
            const reader = new FileReader();
            reader.onload = function(e) {
                docMedia.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;" alt="Vista previa">`;
            };
            reader.readAsDataURL(file);
        } else if (ext === 'pdf') {
            docMedia.innerHTML = `<i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size:2rem;"></i>`;
        } else {
            docMedia.innerHTML = `<i class="bi bi-file-earmark-text-fill text-secondary" style="font-size:2rem;"></i>`;
        }

        docContainer.classList.remove('d-none');
        docContainer.classList.add('d-flex');
    });

    // Abrir nuevo justificativo
    document.getElementById('btnNewJustification')?.addEventListener('click', () => {
        form.reset();
        idField.value = '';
        titleEl.textContent = 'Nuevo Justificativo';
        clearFormError(errorEl);
        document.getElementById('jTipoAusencia').value = 'Enfermedad';
        document.getElementById('jEstado').value = 'Pendiente';
        resetDocPreview();
        justModal.show();
    });

    //editar y borrar
    document.addEventListener('click', async e => {
        const target = e.target.closest('[data-action]');
        if (!target) return;
        
        const action = target.dataset.action;
        const id     = target.dataset.id;
        const name   = target.dataset.name;

        if (action === 'edit-justification') {
            const data = await apiFetch(`${window.SCAP?.baseUrl}/justificativos/datos?id=${id}`);
            if (!data.success) { showToast(data.message, 'error'); return; }

            const j = data.data;
            form.reset();
            resetDocPreview();

            idField.value                                 = j.id;
            document.getElementById('jWorker').value      = j.worker_id;
            document.getElementById('jTipoAusencia').value= j.tipo_ausencia || 'Enfermedad';
            document.getElementById('jFechaInicio').value = j.fecha_inicio;
            document.getElementById('jFechaFin').value    = j.fecha_fin;
            document.getElementById('jMotivo').value      = j.motivo;
            document.getElementById('jEstado').value      = j.estado;

            if (j.documento) {
                const url = `${window.SCAP?.baseUrl}/uploads/justificaciones/${j.documento}`;
                const ext = j.documento.split('.').pop().toLowerCase();
                
                docName.textContent = j.documento;
                docSize.textContent = 'Archivo subido';
                docLink.href = url;
                docLink.classList.remove('d-none');

                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    docMedia.innerHTML = `<img src="${url}" style="width:100%;height:100%;object-fit:cover;" alt="Vista previa">`;
                } else if (ext === 'pdf') {
                    docMedia.innerHTML = `<i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size:2rem;"></i>`;
                } else {
                    docMedia.innerHTML = `<i class="bi bi-file-earmark-text-fill text-secondary" style="font-size:2rem;"></i>`;
                }

                docContainer.classList.remove('d-none');
                docContainer.classList.add('d-flex');
            }

            titleEl.textContent = 'Editar Justificativo';
            clearFormError(errorEl);
            justModal.show();
        }

        if (action === 'delete-justification') {
            document.getElementById('deleteJustId').value = id;
            document.getElementById('deleteJustName').textContent = name;
            deleteModal.show();
        }
    });

    // Guardar crear o editar
    saveBtn?.addEventListener('click', async () => {
        clearFormError(errorEl);
        
        // Validar tamaño de archivo en el cliente (5MB)
        const fileInput = document.getElementById('jDocumento');
        if (fileInput && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const maxBytes = 5 * 1024 * 1024;
            if (file.size > maxBytes) {
                showFormError(errorEl, 'El archivo adjunto supera el tamaño máximo permitido de 5 MB.');
                return;
            }
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['pdf', 'jpg', 'jpeg', 'png'].includes(ext)) {
                showFormError(errorEl, 'Formato no permitido. Solo se aceptan archivos PDF, JPG y PNG.');
                return;
            }
        }

        const isEdit = !!idField.value;
        const url    = `${window.SCAP?.baseUrl}/justificativos/${isEdit ? 'editar' : 'crear'}`;
        const fd     = new FormData(form);

        btnLoading(saveBtn, true);
        const data = await apiFetch(url, { method: 'POST', body: fd });
        btnLoading(saveBtn, false);

        if (data.success) {
            justModal.hide();
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showFormError(errorEl, data.message);
        }
    });

    // confirmar eliminación
    document.getElementById('btnConfirmDeleteJust')?.addEventListener('click', async () => {
        const id = document.getElementById('deleteJustId').value;
        const fd = new FormData();
        fd.set('id', id);
        fd.set('_token', window.SCAP?.token);

        const btn = document.getElementById('btnConfirmDeleteJust');
        btnLoading(btn, true);

        const data = await apiFetch(`${window.SCAP?.baseUrl}/justificativos/eliminar`, {
            method: 'POST',
            body: fd
        });

        btnLoading(btn, false);
        deleteModal.hide();

        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            showToast(data.message, 'error');
        }
    });

    // Búsqueda en vivo
    const searchInp = document.getElementById('justSearch');
    const table = document.getElementById('justificationsTable');
    
    function filterTable() {
        const q = searchInp?.value.toLowerCase().trim() || '';
        const statusActive = document.querySelector('.filter-status-btn.active').dataset.status;
        
        table?.querySelectorAll('tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.dataset.estado;
            
            const matchText   = text.includes(q);
            const matchStatus = (statusActive === 'Todos' || rowStatus === statusActive);
            
            row.style.display = (matchText && matchStatus) ? '' : 'none';
        });
    }

    searchInp?.addEventListener('input', filterTable);

    // Botones de filtro
    document.querySelectorAll('.filter-status-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-status-btn').forEach(b => b.classList.remove('active', 'btn-primary-action'));
            btn.classList.add('active');
            filterTable();
        });
    });

    // Exportación a excel (SheetJS) 
    document.getElementById('btnExportJustificationsExcel')?.addEventListener('click', function() {
        if (!table) return;

        const wb = XLSX.utils.book_new();
        
        // Cabeceras
        const headers = [];
        const ths = table.querySelectorAll('thead th');
        for (let i = 0; i < ths.length - 1; i++) {
            headers.push(ths[i].innerText.trim());
        }
        
        // Filas
        const rows = [];
        const trs = table.querySelectorAll('tbody tr');
        trs.forEach(tr => {
            if (tr.style.display !== 'none') {
                const row = [];
                const tds = tr.querySelectorAll('td');
                if (tds.length > 0) {
                    for (let i = 0; i < tds.length - 1; i++) {
                        if (i === 1) {
                            const nameEl = tds[i].querySelector('.worker-name');
                            row.push(nameEl ? nameEl.innerText.trim() : tds[i].innerText.trim());
                        } else {
                            row.push(tds[i].innerText.trim());
                        }
                    }
                    rows.push(row);
                }
            }
        });

        const data = window.prepareExcelDataWithHeader 
            ? window.prepareExcelDataWithHeader(headers, rows)
            : [headers, ...rows];
        
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Justificativos");
        XLSX.writeFile(wb, "justificativos.xlsx");
    });

    // Exportación PDF (jsPDF) 
    document.getElementById('btnExportJustificationsPdf')?.addEventListener('click', async function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        
        if (!table) return;

        // Renderizar encabezado institucional y logo
        const title = 'Reporte de Justificativos de Inasistencia';
        const startY = window.renderPdfHeader 
            ? await window.renderPdfHeader(doc, title)
            : 30;
        
        const headers = [];
        const ths = table.querySelectorAll('thead th');
        for (let i = 0; i < ths.length - 1; i++) {
            headers.push(ths[i].innerText.trim());
        }
        
        const rows = [];
        const trs = table.querySelectorAll('tbody tr');
        trs.forEach(tr => {
            if (tr.style.display !== 'none') {
                const row = [];
                const tds = tr.querySelectorAll('td');
                if (tds.length > 0) {
                    for (let i = 0; i < tds.length - 1; i++) {
                        if (i === 1) {
                            const nameEl = tds[i].querySelector('.worker-name');
                            row.push(nameEl ? nameEl.innerText.trim() : tds[i].innerText.trim());
                        } else {
                            row.push(tds[i].innerText.trim());
                        }
                    }
                    rows.push(row);
                }
            }
        });
        
        doc.autoTable({
            head: [headers],
            body: rows,
            startY: startY,
            styles: { fontSize: 8, cellPadding: 2 },
            headStyles: { fillColor: [99, 102, 241], textColor: 255 },
            alternateRowStyles: { fillColor: [248, 250, 252] },
        });
        
        doc.save('justificativos.pdf');
    });
});
</script>


<?php require VIEWS_PATH . '/layout/footer.php'; ?>
