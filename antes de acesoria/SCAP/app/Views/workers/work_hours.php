<?php require VIEWS_PATH . '/layout/header.php'; ?>

<!-- Personal Horas de trabajos-->

<div class="page-header mb-3">
    <div>
        <h1 class="page-title"><i class="bi bi-clock-history me-2"></i>Horas Trabajadas del Personal</h1>
        <p class="page-subtitle">Registro Diario y Acumulado Semanal de Horas Trabajadas</p>
    </div>
    <div class="page-header-actions">
        <button class="btn-export btn-export-pdf me-2" id="btnExportWorkHoursPdf">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
        </button>
        <button class="btn-export btn-export-csv" id="btnExportWorkHoursExcel">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
        </button>
    </div>
</div>

<!-- Submenu de Navegación -->
<?php 
$currentSubTab = 'horas';
require VIEWS_PATH . '/workers/sub_nav.php'; 
?>

<!-- Filtros y controles -->
<div class="card-panel mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-md-4">
            <label class="form-label-custom fw-semibold mb-1" for="hoursDateFilter">
                <i class="bi bi-calendar-event me-1"></i>Fecha de Consulta:
            </label>
            <input type="date" id="hoursDateFilter" class="form-control-custom" value="<?= htmlspecialchars($fecha) ?>">
        </div>
        <div class="col-md-5">
            <label class="form-label-custom fw-semibold mb-1" for="hoursSearch">
                <i class="bi bi-search me-1"></i>Buscar Obrero:
            </label>
            <input type="text" id="hoursSearch" class="form-control-custom" placeholder="Buscar por cédula, nombres o cargo…">
        </div>
        <div class="col-md-3 text-md-end pt-md-4">
            <span class="badge bg-light text-dark p-2 border">
                <i class="bi bi-calendar-range me-1"></i>Semana: <strong><?= date('d/m/Y', strtotime($lunes)) ?></strong> al <strong><?= date('d/m/Y', strtotime($viernes)) ?></strong>
            </span>
        </div>
    </div>
</div>

<!-- Tabla de Horas de Trabajo -->
<div class="card-panel">
    <div class="table-responsive">
        <table class="data-table" id="workHoursTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cédula</th>
                    <th>Nombres y Apellidos</th>
                    <th>Cargo</th>
                    <th>Entrada (Hoy)</th>
                    <th>Salida (Hoy)</th>
                    <th class="text-center">Horas Diarias</th>
                    <th class="text-center">Acumulado Semanal (Lun - Vie)</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody id="workHoursTableBody">
            <?php if (empty($report)): ?>
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        No hay registros de obreros para la fecha seleccionada.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($report as $i => $r): ?>
                    <tr class="searchable-row">
                        <td class="text-muted small"><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($r['cedula']) ?></strong></td>
                        <td>
                            <div class="worker-cell">
                                <div class="worker-avatar-sm" style="background:<?= sprintf('#%06X', crc32($r['nombres_apellidos']) & 0xFFFFFF) ?>22;color:<?= sprintf('#%06X', crc32($r['nombres_apellidos']) & 0xFFFFFF) ?>">
                                    <?= strtoupper(substr($r['nombres_apellidos'], 0, 1)) ?>
                                </div>
                                <div class="worker-name fw-semibold">
                                    <?= htmlspecialchars($r['nombres_apellidos']) ?>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($r['cargo']) ?></td>
                        <td>
                            <?php if (!empty($r['hora_entrada_dia'])): ?>
                                <span class="badge bg-info-subtle text-info border border-info-subtle">
                                    <i class="bi bi-box-arrow-in-right me-1"></i><?= date('h:i A', strtotime($r['hora_entrada_dia'])) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">--:--</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($r['hora_salida_dia'])): ?>
                                <span class="badge bg-indigo-subtle text-primary border border-indigo-subtle">
                                    <i class="bi bi-box-arrow-right me-1"></i><?= date('h:i A', strtotime($r['hora_salida_dia'])) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">--:--</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold <?= $r['segundos_dia'] > 0 ? 'text-success' : 'text-muted' ?>">
                                <?= $r['horas_dia_fmt'] ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary text-white fs-6 px-3 py-1">
                                <i class="bi bi-clock-history me-1"></i><?= $r['horas_semana_fmt'] ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($r['hora_entrada_dia']) && !empty($r['hora_salida_dia'])): ?>
                                <span class="status-badge badge-complete">Completo</span>
                            <?php elseif (!empty($r['hora_entrada_dia'])): ?>
                                <span class="status-badge bg-warning text-dark">En Jornada</span>
                            <?php else: ?>
                                <span class="status-badge badge-inactive">Sin Marcaje</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('hoursDateFilter');
    const searchInput = document.getElementById('hoursSearch');
    const table = document.getElementById('workHoursTable');

    // Cambiar fecha y recargar página con parámetro
    dateInput?.addEventListener('change', () => {
        window.location.href = `<?= BASE_URL ?>/obreros/horas-trabajo?fecha=${dateInput.value}`;
    });

    // Búsqueda instantánea en tabla
    searchInput?.addEventListener('input', () => {
        const q = searchInput.value.toLowerCase().trim();
        table?.querySelectorAll('tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    });

    // Extraer datos de la tabla para exportar
    function getTableData() {
        if (!table) return { headers: [], rows: [] };
        
        const headers = [];
        const ths = table.querySelectorAll('thead th');
        ths.forEach(th => headers.push(th.innerText.trim()));

        const rows = [];
        const trs = table.querySelectorAll('tbody tr');
        trs.forEach(tr => {
            if (tr.style.display === 'none') return;
            const tds = tr.querySelectorAll('td');
            if (tds.length > 1) {
                const row = [];
                tds.forEach((td, idx) => {
                    if (idx === 2) {
                        const nameEl = td.querySelector('.worker-name');
                        row.push(nameEl ? nameEl.innerText.trim() : td.innerText.trim());
                    } else {
                        row.push(td.innerText.trim());
                    }
                });
                rows.push(row);
            }
        });

        return { headers, rows };
    }

    // Exportar a Excel
    document.getElementById('btnExportWorkHoursExcel')?.addEventListener('click', () => {
        const { headers, rows } = getTableData();
        if (rows.length === 0) {
            if (typeof showToast === 'function') showToast('No hay registros para exportar', 'warning');
            return;
        }

        const wb = XLSX.utils.book_new();
        const data = window.prepareExcelDataWithHeader 
            ? window.prepareExcelDataWithHeader(headers, rows)
            : [headers, ...rows];

        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Horas Trabajadas");
        
        const fechaVal = dateInput ? dateInput.value : 'reporte';
        XLSX.writeFile(wb, `horas_trabajadas_${fechaVal}.xlsx`);
    });

    // Exportar a PDF
    document.getElementById('btnExportWorkHoursPdf')?.addEventListener('click', async () => {
        const { headers, rows } = getTableData();
        if (rows.length === 0) {
            if (typeof showToast === 'function') showToast('No hay registros para exportar', 'warning');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

        const fechaVal = dateInput ? dateInput.value : '';
        const title = 'Reporte de Horas Trabajadas (' + fechaVal + ')';

        const startY = window.renderPdfHeader 
            ? await window.renderPdfHeader(doc, title)
            : 30;

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        doc.setTextColor(71, 85, 105);
        doc.text('Semana del <?= date('d/m/Y', strtotime($lunes)) ?> al <?= date('d/m/Y', strtotime($viernes)) ?>', 14, startY - 2);

        doc.autoTable({
            head: [headers],
            body: rows,
            startY: startY + 3,
            styles: { fontSize: 8.5, cellPadding: 2.5 },
            headStyles: { fillColor: [99, 102, 241], textColor: 255 },
            alternateRowStyles: { fillColor: [248, 250, 252] },
        });

        doc.save(`horas_trabajadas_${fechaVal}.pdf`);
    });
});
</script>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
