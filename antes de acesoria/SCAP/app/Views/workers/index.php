<?php require VIEWS_PATH . '/layout/header.php'; ?>

<!-- personal-->

<div class="page-header mb-3">
    <div>
        <h1 class="page-title">Personal</h1>
        <p class="page-subtitle">Características del Personal (<?= count($workers) ?> registrado[s])</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= BASE_URL ?>/obreros/papelera" class="btn-action btn-secondary-action me-2">
            <i class="bi bi-trash3-fill me-1"></i> Papelera
        </a>
        <button class="btn-export btn-export-pdf me-2" id="btnExportWorkersPdf">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
        </button>
        <button class="btn-export btn-export-csv me-2" id="btnExportWorkersExcel">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
        </button>
        <button class="btn-action btn-primary-action" id="btnNewWorker"
                data-action="new">
            <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Obrero
        </button>
    </div>
</div>

<!-- Submenu de navegacion -->
<?php 
$currentSubTab = 'caracteristicas';
require VIEWS_PATH . '/workers/sub_nav.php'; 
?>


<!-- busqueda -->
<div class="search-bar-wrap">
    <i class="bi bi-search search-icon"></i>
    <input type="text" id="workerSearch" class="search-input"
           placeholder="Buscar por cédula, nombre, cargo…">
</div>

<!-- Tablas -->
<div class="card-panel">
    <?php if (empty($workers)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-people"></i></div>
            <h3>Sin personal registrado</h3>
            <p>Haga clic en "Nuevo Obrero" para agregar el primer registro.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table" id="workersTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Doc. Gobierno</th>
                    <th>Cédula</th>
                    <th>Nombres y Apellidos</th>
                    <th>Cargo</th>
                    <th>Departamento</th>
                    <th>Teléfono</th>
                    <th>Grado Académico</th>
                    <th>Condición Médica</th>
                    <th class="text-center">Años Servicio</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($workers as $i => $w): ?>
                <?php $fullName = !empty($w['nombres_apellidos']) ? $w['nombres_apellidos'] : trim($w['nombre'] . ' ' . $w['apellido']); ?>
                <tr class="searchable-row">
                    <td class="text-muted small"><?= $i + 1 ?></td>
                    <td>
                        <?php if (($w['posee_documento'] ?? 'Sí') === 'Sí'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i>Sí</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle-fill me-1"></i>No</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($w['cedula']) ?></strong></td>
                    <td>
                        <div class="worker-cell">
                            <div class="worker-avatar-sm" style="background:<?= sprintf('#%06X', crc32($fullName) & 0xFFFFFF) ?>22;color:<?= sprintf('#%06X', crc32($fullName) & 0xFFFFFF) ?>">
                                <?= strtoupper(substr($fullName, 0, 1)) ?>
                            </div>
                            <div class="worker-name fw-semibold">
                                <?= htmlspecialchars($fullName) ?>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-indigo-subtle text-primary border border-indigo-subtle"><?= htmlspecialchars($w['cargo']) ?></span></td>
                    <td><span class="badge bg-secondary-subtle text-dark border"><?= htmlspecialchars($w['departamento'] ?? 'General') ?></span></td>
                    <td><?= !empty($w['telefono']) ? htmlspecialchars($w['telefono']) : '<span class="text-muted small">N/A</span>' ?></td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($w['grado_academico'] ?? 'Ninguno') ?></span></td>
                    <td>
                        <span class="d-inline-block text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($w['condicion_medica'] ?? 'Ninguna') ?>">
                            <?= htmlspecialchars($w['condicion_medica'] ?? 'Ninguna') ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary text-white px-2 py-1">
                            <?= (int)($w['anios_servicio'] ?? 0) ?> yr(s)
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?= $w['activo'] ? 'badge-complete' : 'badge-inactive' ?>">
                            <?= $w['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="action-btns">
                            <button class="btn-icon btn-edit"
                                    data-action="edit-worker"
                                    data-id="<?= $w['id'] ?>"
                                    title="Editar">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button class="btn-icon btn-delete"
                                    data-action="delete-worker"
                                    data-id="<?= $w['id'] ?>"
                                    data-name="<?= htmlspecialchars($fullName) ?>"
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

<!--Crear o editar personal-->
<div class="modal fade" id="workerModal" tabindex="-1" aria-labelledby="workerModalTitle">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content custom-modal">
      <div class="modal-header custom-modal-header">
        <h5 class="modal-title" id="workerModalTitle">Nuevo Personal</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="workerForm" novalidate>
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_token) ?>">
            <input type="hidden" name="id" id="workerId" value="">

            <div class="row g-3">
                <!--Posee documento emitido por el gobierno -->
                <div class="col-md-6">
                    <div class="field-header mb-1">
                        <span class="fw-bold text-dark d-block mb-0"><i class="bi bi-card-heading text-indigo me-1"></i> Documento Oficial</span>
                        <small class="text-muted small d-block">Indique si el Persomal posee documento legal del gobierno</small>
                    </div>
                    <label class="form-label-custom" for="wPoseeDocumento">Posee documento emitido por el gobierno <span class="text-danger">*</span></label>
                    <select name="posee_documento" id="wPoseeDocumento" class="form-control-custom" required>
                        <option value="Sí">Sí</option>
                        <option value="No">No</option>
                    </select>
                </div>

                <!--Cedula de identidad -->
                <div class="col-md-6">
                    <div class="field-header mb-1">
                        <span class="fw-bold text-dark d-block mb-0"><i class="bi bi-person-badge text-indigo me-1"></i> Identificación</span>
                        <small class="text-muted small d-block">Cédula de Identidad única</small>
                    </div>
                    <label class="form-label-custom" for="wCedula">Cédula de Identidad <span class="text-danger">*</span></label>
                    <input type="text" name="cedula" id="wCedula"
                           class="form-control-custom" inputmode="numeric"
                           maxlength="15" placeholder="Ingresar Número de C.I" required>
                </div>

                <!--Nombres y apellidos -->
                <div class="col-12">
                    <div class="field-header mb-1">
                        <span class="fw-bold text-dark d-block mb-0"><i class="bi bi-person-vcard text-indigo me-1"></i> Datos Personales</span>
                        <small class="text-muted small d-block">Nombres y Apellidos completos</small>
                    </div>
                    <label class="form-label-custom" for="wNombresApellidos">Nombres y Apellidos <span class="text-danger">*</span></label>
                    <input type="text" name="nombres_apellidos" id="wNombresApellidos"
                           class="form-control-custom" placeholder="Ingresar Nombre Completo" required>
                </div>

                <!-- Cargo -->
                <div class="col-md-6">
                    <div class="field-header mb-1">
                        <span class="fw-bold text-dark d-block mb-0"><i class="bi bi-briefcase text-indigo me-1"></i> Cargo Laboral</span>
                        <small class="text-muted small d-block">Elegir Cargor</small>
                    </div>
                    <label class="form-label-custom" for="wCargo">Cargo <span class="text-danger">*</span></label>
                    <select name="cargo" id="wCargo" class="form-control-custom" required>
                        <option value="Obrero">Obrero</option>
                        <option value="Docente">Docente</option>
                        <option value="Administrador">Administrador</option>
                    </select>
                </div>

                <!-- Departamento -->
                <div class="col-md-6">
                    <div class="field-header mb-1">
                        <span class="fw-bold text-dark d-block mb-0"><i class="bi bi-building text-indigo me-1"></i> Departamento</span>
                        <small class="text-muted small d-block">Puesto laboral</small>
                    </div>
                    <label class="form-label-custom" for="wDepartamento">Departamento <span class="text-danger">*</span></label>
                    <select name="departamento" id="wDepartamento" class="form-control-custom" required>
                        <option value="">— Seleccione departamento —</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Ambientalista">Ambientalista</option>
                        <option value="Cocinero">Cocinero</option>
                        <option value="Director">Director</option>
                        <option value="Docente">Docente</option>
                        <option value="Limpieza">Limpieza</option>
                        <option value="Obrero">Obrero</option>
                        <option value="Vigilante">Vigilante</option>
                    </select>
                </div>

                <!--Número telefonico de contacto -->
                <div class="col-md-6">
                    <div class="field-header mb-1">
                        <span class="fw-bold text-dark d-block mb-0"><i class="bi bi-telephone text-indigo me-1"></i> Contacto Telefónico</span>
                        <small class="text-muted small d-block">Número de teléfono</small>
                    </div>
                    <label class="form-label-custom" for="wTelefono">Número telefónico de contacto</label>
                    <input type="tel" name="telefono" id="wTelefono"
                           class="form-control-custom" placeholder="0414-1234567">
                </div>

                <!--Grado academico -->
                <div class="col-md-6">
                    <div class="field-header mb-1">
                        <span class="fw-bold text-dark d-block mb-0"><i class="bi bi-mortarboard text-indigo me-1"></i> Nivel Académico</span>
                        <small class="text-muted small d-block">Indicar el grado academico alcanzado</small>
                    </div>
                    <label class="form-label-custom" for="wGradoAcademico">Grado académico</label>
                    <select name="grado_academico" id="wGradoAcademico" class="form-control-custom">
                        <option value="Ninguno">Ninguno</option>
                        <option value="Primaria">Primaria</option>
                        <option value="Bachiller">Bachiller</option>
                        <option value="TSU">TSU</option>
                        <option value="Licenciado/Ingeniero">Licenciado/Ingeniero</option>
                    </select>
                </div>

                <!--Años de servicio en trabajo -->
                <div class="col-md-6">
                    <div class="field-header mb-1">
                        <span class="fw-bold text-dark d-block mb-0"><i class="bi bi-hourglass-split text-indigo me-1"></i> Experiencia Laboral</span>
                        <small class="text-muted small d-block">Años de trabajo en la institucion</small>
                    </div>
                    <label class="form-label-custom" for="wAniosServicio">Años de servicio</label>
                    <input type="number" name="anios_servicio" id="wAniosServicio"
                           class="form-control-custom" min="0" step="1" value="0" placeholder="¿Cuantos años tienes trabajando?">
                </div>

                <!--Condición medica -->
                <div class="col-12">
                    <div class="field-header mb-1">
                        <span class="fw-bold text-dark d-block mb-0"><i class="bi bi-heart-pulse text-indigo me-1"></i> Estado de Salud</span>
                        <small class="text-muted small d-block">Especificar alergias, condiciones médicas o 'Ninguna'</small>
                    </div>
                    <label class="form-label-custom" for="wCondicionMedica">Condición médica</label>
                    <textarea name="condicion_medica" id="wCondicionMedica"
                              class="form-control-custom" rows="2" placeholder="Indicar alergias, enfermedades o 'Ninguna'"></textarea>
                </div>

                <!-- estado -->
                <div class="col-12 edit-only d-none">
                    <div class="field-header mb-1">
                        <span class="fw-bold text-dark d-block mb-0"><i class="bi bi-toggle-on text-indigo me-1"></i> Estado del Registro</span>
                        <small class="text-muted small d-block">Indique si el obrero se encuentra activo en el sistema</small>
                    </div>
                    <label class="form-label-custom">Estado</label>
                    <select name="activo" id="wActivo" class="form-control-custom">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="modal-form-error d-none" id="workerFormError"></div>
        </form>
      </div>
      <div class="modal-footer custom-modal-footer">
        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn-modal-save" id="btnSaveWorker">
            <span class="btn-save-text"><i class="bi bi-check-lg me-1"></i>Guardar</span>
            <span class="btn-save-spinner d-none"><span class="spinner-border spinner-border-sm"></span></span>
        </button>
      </div>
    </div>
  </div>
</div>

<!--Confirmar eliminacion-->

<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content custom-modal">
      <div class="modal-body p-4 text-center">
        <div class="delete-modal-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <h5 class="mt-3 mb-2">¿Eliminar obrero?</h5>
        <p class="text-muted small mb-0">
            Esta acción eliminará a <strong id="deleteWorkerName"></strong>
            y todos sus registros de asistencia. No se puede deshacer.
        </p>
        <input type="hidden" id="deleteWorkerId">
        <input type="hidden" id="deleteWorkerToken" value="<?= htmlspecialchars($_token) ?>">
      </div>
      <div class="modal-footer custom-modal-footer justify-content-center gap-2">
        <button class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn-modal-danger" id="btnConfirmDelete">
            <i class="bi bi-trash-fill me-1"></i>Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Exportar a excel
    document.getElementById('btnExportWorkersExcel')?.addEventListener('click', function() {
        const table = document.getElementById('workersTable');
        if (!table) return;

        const wb = XLSX.utils.book_new();
        
        // Cabeceras, omitir la columna de Acciones
        const headers = [];
        const ths = table.querySelectorAll('thead th');
        for (let i = 0; i < ths.length - 1; i++) {
            headers.push(ths[i].innerText.trim());
        }
        
        // Filas, omitir la columna de Acciones
        const rows = [];
        const trs = table.querySelectorAll('tbody tr');
        trs.forEach(tr => {
            const row = [];
            const tds = tr.querySelectorAll('td');
            if (tds.length > 0) {
                for (let i = 0; i < tds.length - 1; i++) {
                    if (i === 2) {
                        const nameEl = tds[i].querySelector('.worker-name');
                        row.push(nameEl ? nameEl.innerText.trim() : tds[i].innerText.trim());
                    } else {
                        row.push(tds[i].innerText.trim());
                    }
                }
                rows.push(row);
            }
        });

        const data = window.prepareExcelDataWithHeader 
            ? window.prepareExcelDataWithHeader(headers, rows)
            : [headers, ...rows];
        
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Personal Obrero");
        XLSX.writeFile(wb, "personal_obrero.xlsx");
    });

    // Exportar a PDF
    document.getElementById('btnExportWorkersPdf')?.addEventListener('click', async function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        
        const table = document.getElementById('workersTable');
        if (!table) return;

        // Renderizar encabezado institucional y logo
        const title = 'Reporte de Personal Obrero';
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
            const row = [];
            const tds = tr.querySelectorAll('td');
            if (tds.length > 0) {
                for (let i = 0; i < tds.length - 1; i++) {
                    if (i === 2) {
                        const nameEl = tds[i].querySelector('.worker-name');
                        row.push(nameEl ? nameEl.innerText.trim() : tds[i].innerText.trim());
                    } else {
                        row.push(tds[i].innerText.trim());
                    }
                }
                rows.push(row);
            }
        });
        
        doc.autoTable({
            head: [headers],
            body: rows,
            startY: startY,
            styles: { fontSize: 9, cellPadding: 3 },
            headStyles: { fillColor: [99, 102, 241], textColor: 255 },
            alternateRowStyles: { fillColor: [248, 250, 252] },
        });
        
        doc.save('personal_obrero.pdf');
    });
});
</script>


<?php require VIEWS_PATH . '/layout/footer.php'; ?>
