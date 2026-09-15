<?php require VIEWS_PATH . '/layout/header.php'; ?>

<!-- Asistencias de Hoy-->

<div class="page-header">
    <div>
        <h1 class="page-title">Asistencias de Hoy</h1>
        <p class="page-subtitle">
            <i class="bi bi-calendar3 me-1"></i><?= $today ?>
            &nbsp;·&nbsp;
            <span id="attendanceCount"><?= count($attendances) ?></span> registro(s)
        </p>
    </div>
    <div class="page-header-actions">
        <div class="refresh-info" id="refreshInfo">
            <i class="bi bi-arrow-repeat me-1"></i>
            Actualizando en <span id="refreshCountdown">30</span>s
        </div>
        <button class="btn-export btn-export-pdf me-2" id="btnExportTodayPdf">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
        </button>
        <button class="btn-export btn-export-csv me-2" id="btnExportTodayExcel">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
        </button>
        <button class="btn-action btn-primary-action" id="btnRefreshNow">
            <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
        </button>
    </div>
</div>

<!-- Resumen rapido -->
<div class="attendance-summary">
    <?php
    $total    = count($attendances);
    $entradas = array_filter($attendances, fn($a) => $a['hora_entrada']);
    $salidas  = array_filter($attendances, fn($a) => $a['hora_salida']);
    $completos = array_filter($attendances, fn($a) => $a['hora_entrada'] && $a['hora_salida']);
    ?>
    <div class="summary-badge summary-total">
        <i class="bi bi-people-fill"></i>
        <span><strong><?= $total ?></strong> Total</span>
    </div>
    <div class="summary-badge summary-entrada">
        <i class="bi bi-box-arrow-in-right"></i>
        <span><strong><?= count($entradas) ?></strong> Entradas</span>
    </div>
    <div class="summary-badge summary-salida">
        <i class="bi bi-box-arrow-right"></i>
        <span><strong><?= count($salidas) ?></strong> Salidas</span>
    </div>
    <div class="summary-badge summary-completo">
        <i class="bi bi-check2-circle"></i>
        <span><strong><?= count($completos) ?></strong> Completos</span>
    </div>
</div>

<!-- tablas -->
<div class="card-panel" id="attendanceTableWrap">
    <?php if (empty($attendances)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-calendar-x"></i></div>
            <h3>Sin registros de hoy</h3>
            <p>Aún no hay asistencias registradas para el día de hoy.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table" id="attendanceTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cédula</th>
                    <th>Nombre Completo</th>
                    <th>Cargo</th>
                    <th>Departamento</th>
                    <th>Hora Entrada</th>
                    <th>Hora Salida</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="attendanceTbody">
            <?php foreach ($attendances as $i => $att): ?>
                <?php
                $hasEntrada  = !empty($att['hora_entrada']);
                $hasSalida   = !empty($att['hora_salida']);
                $statusClass = $hasEntrada && $hasSalida ? 'badge-complete'
                             : ($hasEntrada              ? 'badge-entry'
                                                        : 'badge-pending');
                $statusText  = $hasEntrada && $hasSalida ? 'Completo'
                             : ($hasEntrada              ? 'Solo Entrada'
                                                        : 'Pendiente');
                ?>
                <tr>
                    <td class="text-muted small"><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($att['cedula']) ?></strong></td>
                    <td>
                        <div class="worker-cell">
                            <div class="worker-avatar-sm">
                                <?= strtoupper(substr($att['nombre'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="worker-name">
                                    <?= htmlspecialchars($att['nombre'] . ' ' . $att['apellido']) ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($att['cargo']) ?></td>
                    <td><?= htmlspecialchars($att['departamento']) ?></td>
                    <td>
                        <?php if ($hasEntrada): ?>
                            <span class="time-badge time-entrada">
                                <i class="bi bi-box-arrow-in-right"></i>
                                <?= date('h:i A', strtotime($att['hora_entrada'])) ?>
                            </span>
                            <?php 
                            $eEst = $att['estado_entrada'] ?? 'A tiempo';
                            if ($eEst === 'Tardanza'): 
                            ?>
                                <div class="mt-1">
                                    <span class="badge bg-danger text-white small" title="Retraso de <?= (int)($att['minutos_retraso'] ?? 0) ?> minutos">
                                        <i class="bi bi-exclamation-circle me-1"></i>Tardanza (+<?= (int)($att['minutos_retraso'] ?? 0) ?>m)
                                    </span>
                                    <?php if (!empty($att['motivo_tardanza'])): ?>
                                        <div class="small text-muted fst-italic mt-1" style="font-size:0.75rem;">
                                            <i class="bi bi-chat-quote me-1"></i><?= htmlspecialchars($att['motivo_tardanza']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($eEst === 'Tolerancia'): ?>
                                <div class="mt-1">
                                    <span class="badge bg-warning text-dark small">
                                        <i class="bi bi-clock-history me-1"></i>Tolerancia
                                    </span>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">–</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($hasSalida): ?>
                            <span class="time-badge time-salida">
                                <i class="bi bi-box-arrow-right"></i>
                                <?= date('h:i A', strtotime($att['hora_salida'])) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">–</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
// Auto-refrescar cada 30 segundos y exportar
document.addEventListener('DOMContentLoaded', () => {
    let countdown = 30;
    const countEl = document.getElementById('refreshCountdown');
    const interval = setInterval(() => {
        countdown--;
        if (countEl) countEl.textContent = countdown;
        if (countdown <= 0) {
            countdown = 30;
            window.location.reload();
        }
    }, 1000);

    document.getElementById('btnRefreshNow')?.addEventListener('click', () => {
        clearInterval(interval);
        window.location.reload();
    });

    // Extraer datos de la tabla para exportación
    function getTodayTableData() {
        const table = document.getElementById('attendanceTable');
        if (!table) return { headers: [], rows: [] };

        const headers = [];
        const ths = table.querySelectorAll('thead th');
        ths.forEach(th => headers.push(th.innerText.trim()));

        const rows = [];
        const trs = table.querySelectorAll('tbody tr');
        trs.forEach(tr => {
            const tds = tr.querySelectorAll('td');
            if (tds.length > 1) {
                const row = [];
                tds.forEach((td, idx) => {
                    if (idx === 2) {
                        const nameEl = td.querySelector('.worker-name');
                        row.push(nameEl ? nameEl.innerText.trim() : td.innerText.trim());
                    } else if (idx === 5 || idx === 6) {
                        const timeEl = td.querySelector('.time-badge');
                        let val = timeEl ? timeEl.innerText.trim() : td.innerText.trim();
                        const noteEl = td.querySelector('.badge');
                        if (noteEl && noteEl !== timeEl) {
                            val += ' (' + noteEl.innerText.trim() + ')';
                        }
                        row.push(val);
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
    document.getElementById('btnExportTodayExcel')?.addEventListener('click', () => {
        const { headers, rows } = getTodayTableData();
        if (rows.length === 0) {
            if (typeof showToast === 'function') showToast('No hay asistencias registradas hoy para exportar', 'warning');
            return;
        }

        const wb = XLSX.utils.book_new();
        const data = window.prepareExcelDataWithHeader 
            ? window.prepareExcelDataWithHeader(headers, rows)
            : [headers, ...rows];

        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Asistencias de Hoy");
        
        const todayStr = '<?= date('Y-m-d') ?>';
        XLSX.writeFile(wb, `asistencias_hoy_${todayStr}.xlsx`);
    });

    // Exportar a PDF
    document.getElementById('btnExportTodayPdf')?.addEventListener('click', async () => {
        const { headers, rows } = getTodayTableData();
        if (rows.length === 0) {
            if (typeof showToast === 'function') showToast('No hay asistencias registradas hoy para exportar', 'warning');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

        const title = 'Reporte de Asistencias de Hoy (<?= date('d/m/Y') ?>)';

        const startY = window.renderPdfHeader 
            ? await window.renderPdfHeader(doc, title)
            : 30;

        doc.autoTable({
            head: [headers],
            body: rows,
            startY: startY,
            styles: { fontSize: 8.5, cellPadding: 2.5 },
            headStyles: { fillColor: [99, 102, 241], textColor: 255 },
            alternateRowStyles: { fillColor: [248, 250, 252] },
        });

        doc.save('asistencias_hoy_<?= date('Y-m-d') ?>.pdf');
    });
});
</script>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
