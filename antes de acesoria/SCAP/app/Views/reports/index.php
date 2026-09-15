<?php require VIEWS_PATH . '/layout/header.php'; ?>

<!-- reportes de asistencia-->

<div class="page-header mb-3">
    <div>
        <h1 class="page-title"><i class="bi bi-bar-chart-line me-2"></i>Reportes de Asistencia</h1>
        <p class="page-subtitle">
            <?= date('d/m/Y', strtotime($from)) ?>
            <?= ($from !== $to) ? ' – ' . date('d/m/Y', strtotime($to)) : '' ?>
            &nbsp;·&nbsp; <strong><?= count($attendances) ?></strong> registro(s)
        </p>
    </div>
    <?php if (!empty($attendances)): ?>
    <div class="page-header-actions">
        <button class="btn-export btn-export-pdf me-2" id="btnHeaderExportPdf">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
        </button>
        <button class="btn-export btn-export-csv" id="btnHeaderExportExcel">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Filtros de Fecha -->
<div class="card-panel mb-3">
    <form method="GET" action="<?= BASE_URL ?>/reportes" id="reportFilterForm" class="report-filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label class="form-label-custom" for="from">Desde</label>
                <input type="date" name="from" id="from" class="form-control-custom"
                       value="<?= htmlspecialchars($from) ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="filter-group">
                <label class="form-label-custom" for="to">Hasta</label>
                <input type="date" name="to" id="to" class="form-control-custom"
                       value="<?= htmlspecialchars($to) ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-action btn-primary-action">
                    <i class="bi bi-funnel-fill me-1"></i> Filtrar
                </button>
                <a href="<?= BASE_URL ?>/reportes" class="btn-action btn-secondary-action">
                    <i class="bi bi-x-circle me-1"></i> Hoy
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Botones de exportación -->
<?php if (!empty($attendances)): ?>
<div class="export-bar">
    <span class="export-label"><i class="bi bi-download me-1"></i> Exportar:</span>
    <button class="btn-export btn-export-csv me-2" id="btnExportExcel" style="background-color:#dcfce7; color:#166534;">
        <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Excel
    </button>
    <a href="<?= BASE_URL ?>/reportes/exportar-csv?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>"
       class="btn-export btn-export-csv me-2" style="background-color:#f1f5f9; color:#475569;">
        <i class="bi bi-file-earmark-file-fill me-1"></i> CSV
    </a>
    <button class="btn-export btn-export-pdf" id="btnExportPdf">
        <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
    </button>
</div>
<?php endif; ?>

<!-- Tabla de resultados -->
<div class="card-panel" id="reportTableWrap">
    <?php if (empty($attendances)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-bar-chart"></i></div>
            <h3>Sin resultados</h3>
            <p>No se encontraron registros de asistencia para el período seleccionado.</p>
        </div>
    <?php else: ?>

    <!-- Resumen estadistico -->
    <?php
    $total     = count($attendances);
    $completos = count(array_filter($attendances, fn($a) => $a['hora_entrada'] && $a['hora_salida']));
    $soloEnt   = count(array_filter($attendances, fn($a) => $a['hora_entrada'] && !$a['hora_salida']));
    $pct       = $total > 0 ? round($completos / $total * 100) : 0;
    ?>
    <div class="report-summary-row">
        <div class="report-summary-item">
            <span class="rs-number"><?= $total ?></span>
            <span class="rs-label">Total Registros</span>
        </div>
        <div class="report-summary-item">
            <span class="rs-number text-success"><?= $completos ?></span>
            <span class="rs-label">Completos</span>
        </div>
        <div class="report-summary-item">
            <span class="rs-number text-warning"><?= $soloEnt ?></span>
            <span class="rs-label">Solo Entrada</span>
        </div>
        <div class="report-summary-item">
            <span class="rs-number text-primary"><?= $pct ?>%</span>
            <span class="rs-label">Completitud</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="reportTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Cédula</th>
                    <th>Nombre Completo</th>
                    <th>Cargo</th>
                    <th>Departamento</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($attendances as $i => $att): ?>
                <?php
                $hasEntrada  = !empty($att['hora_entrada']);
                $hasSalida   = !empty($att['hora_salida']);
                $statusClass = $hasEntrada && $hasSalida ? 'badge-complete'
                             : ($hasEntrada              ? 'badge-entry'
                                                        : 'badge-pending');
                $statusText  = $hasEntrada && $hasSalida ? 'Completo'
                             : ($hasEntrada              ? 'Solo Entrada'
                                                        : 'Sin Registro');
                ?>
                <tr>
                    <td class="text-muted small"><?= $i + 1 ?></td>
                    <td><strong><?= date('d/m/Y', strtotime($att['fecha'])) ?></strong></td>
                    <td><?= htmlspecialchars($att['cedula']) ?></td>
                    <td><?= htmlspecialchars($att['nombre'] . ' ' . $att['apellido']) ?></td>
                    <td><?= htmlspecialchars($att['cargo']) ?></td>
                    <td><?= htmlspecialchars($att['departamento']) ?></td>
                    <td>
                        <?= $hasEntrada
                            ? '<span class="time-badge time-entrada"><i class="bi bi-box-arrow-in-right"></i> '
                                . date('h:i A', strtotime($att['hora_entrada'])) . '</span>'
                            : '<span class="text-muted">–</span>' ?>
                    </td>
                    <td>
                        <?= $hasSalida
                            ? '<span class="time-badge time-salida"><i class="bi bi-box-arrow-right"></i> '
                                . date('h:i A', strtotime($att['hora_salida'])) . '</span>'
                            : '<span class="text-muted">–</span>' ?>
                    </td>
                    <td><span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Script para PDF y Excel con Encabezado & Logo -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    function exportToExcel() {
        const table = document.getElementById('reportTable');
        if (!table) return;

        const wb = XLSX.utils.book_new();
        
        // Cabeceras
        const headers = [];
        const ths = table.querySelectorAll('thead th');
        ths.forEach(th => headers.push(th.innerText.trim()));
        
        // Filas
        const rows = [];
        const trs = table.querySelectorAll('tbody tr');
        trs.forEach(tr => {
            const row = [];
            const tds = tr.querySelectorAll('td');
            if (tds.length > 0) {
                tds.forEach(td => row.push(td.innerText.trim()));
                rows.push(row);
            }
        });

        const data = window.prepareExcelDataWithHeader 
            ? window.prepareExcelDataWithHeader(headers, rows)
            : [headers, ...rows];
        
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Asistencia");
        XLSX.writeFile(wb, "reporte_asistencia_<?= $from ?>_<?= $to ?>.xlsx");
    }

    async function exportToPdf() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

        const table = document.getElementById('reportTable');
        if (!table) return;

        // Renderizar encabezado institucional y logo
        const title = 'Reporte de Asistencia (Período: <?= date('d/m/Y', strtotime($from)) ?> – <?= date('d/m/Y', strtotime($to)) ?>)';
        const startY = window.renderPdfHeader 
            ? await window.renderPdfHeader(doc, title)
            : 38;

        const headers = [...table.querySelectorAll('thead th')].map(th => th.innerText.trim());
        const rows    = [...table.querySelectorAll('tbody tr')].map(tr =>
            [...tr.querySelectorAll('td')].map(td => td.innerText.trim())
        );

        doc.autoTable({
            head: [headers],
            body: rows,
            startY: startY,
            styles: { fontSize: 8, cellPadding: 2 },
            headStyles: { fillColor: [99, 102, 241], textColor: 255 },
            alternateRowStyles: { fillColor: [248, 250, 252] },
        });

        doc.save('reporte_asistencia_<?= $from ?>_<?= $to ?>.pdf');
    }

    // Bind event listeners
    document.getElementById('btnExportExcel')?.addEventListener('click', exportToExcel);
    document.getElementById('btnHeaderExportExcel')?.addEventListener('click', exportToExcel);

    document.getElementById('btnExportPdf')?.addEventListener('click', exportToPdf);
    document.getElementById('btnHeaderExportPdf')?.addEventListener('click', exportToPdf);
});
</script>


<?php require VIEWS_PATH . '/layout/footer.php'; ?>
