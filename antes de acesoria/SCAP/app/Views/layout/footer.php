<?php
/* =========================================================
 *  layout/footer.php  –  Footer profesional de SCAP
 *  Estructura: cierre de .page-content / .main-area / .app-wrapper
 *              → Footer de 3 columnas + barra de copyright
 *              → Toast, Scripts y Loading Overlay (sin cambios)
 * ========================================================= */

// ── Datos dinámicos para el footer ──────────────────────────
$footerYear    = date('Y');                                        // Año dinámico
$footerUser    = htmlspecialchars($_SESSION['admin_name']     ?? 'Sin sesión');
$footerRole    = htmlspecialchars($_SESSION['admin_username'] ?? '—');
$serverStatus  = (function_exists('mysqli_connect')) ? 'Operativo' : 'Sin BD';
$serverColor   = ($serverStatus === 'Operativo') ? 'success' : 'danger';
$serverIcon    = ($serverStatus === 'Operativo') ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
?>

        </main><!-- /.page-content -->

        <!-- ╔══════════════════════════════════════════════════╗
             ║           FOOTER PROFESIONAL — SCAP             ║
             ╚══════════════════════════════════════════════════╝ -->
        <footer class="app-footer" role="contentinfo" aria-label="Pie de página">

            <!-- ── Línea decorativa superior con degradado ── -->
            <div class="footer-divider"></div>

            <!-- ── Fila 1: Marca + Estado del sistema ── -->
            <div class="footer-main">

                <!-- Izquierda: Nombre del sistema -->
                <div class="footer-brand">
                    <span class="footer-brand-name"><?= APP_NAME ?></span>
                    <span class="footer-brand-sep">&mdash;</span>
                    <span class="footer-brand-full"><?= APP_FULL_NAME ?></span>
                </div>

                <!-- Derecha: Pills de estado -->
                <div class="footer-status-row">

                    <!-- Versión -->
                    <div class="footer-pill">
                        <i class="bi bi-layers-fill"></i>
                        <span class="footer-pill-label">Versión</span>
                        <span class="footer-pill-value"><?= APP_VERSION ?></span>
                    </div>

                    <!-- Servidor -->
                    <div class="footer-pill footer-pill--<?= $serverColor ?>">
                        <i class="bi <?= $serverIcon ?>"></i>
                        <span class="footer-pill-label">Servidor</span>
                        <span class="footer-pill-value"><?= $serverStatus ?></span>
                    </div>

                    <!-- Zona horaria -->
                    <div class="footer-pill">
                        <i class="bi bi-clock-fill"></i>
                        <span class="footer-pill-label">Zona</span>
                        <span class="footer-pill-value"><?= TIMEZONE ?></span>
                    </div>

                    <!-- Usuario -->
                    <div class="footer-pill footer-pill--user">
                        <i class="bi bi-person-fill"></i>
                        <span class="footer-pill-label">Usuario</span>
                        <span class="footer-pill-value"><?= $footerUser ?></span>
                    </div>

                </div><!-- /footer-status-row -->
            </div><!-- /footer-main -->

            <!-- ── Separador interno ── -->
            <div class="footer-inner-sep"></div>

            <!-- ── Fila 2: Copyright + meta ── -->
            <div class="footer-bar">
                <span class="footer-copy">
                    <i class="bi bi-c-circle me-1 opacity-50"></i>
                    <?= $footerYear ?> &nbsp;<strong><?= APP_NAME ?></strong>
                    &nbsp;&mdash;&nbsp; Todos los derechos reservados.
                </span>
                <span class="footer-meta">
                    <span class="footer-meta-item">
                        <i class="bi bi-shield-lock-fill"></i>Sistema Interno
                    </span>
                    <span class="footer-meta-dot"></span>
                    <span class="footer-meta-item">
                        <i class="bi bi-code-slash"></i>SCAP v<?= APP_VERSION ?>
                    </span>
                </span>
            </div><!-- /footer-bar -->

        </footer><!-- /app-footer -->


    </div><!-- /.main-area -->
</div><!-- /.app-wrapper -->

<!-- ╔══════════════════════════════════════════════════╗
     ║         Toast Global de Notificaciones          ║
     ╚══════════════════════════════════════════════════╝ -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="globalToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="globalToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
</div>

<!-- ╔══════════════════════════════════════════════════╗
     ║                   Scripts                       ║
     ╚══════════════════════════════════════════════════╝ -->
<!-- Bootstrap JS Bundle (Popper incluido) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- jsPDF + autoTable para exportación PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<!-- SheetJS para exportación Excel -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    // Pasar configuración global de PHP a JS
    window.SCAP = {
        baseUrl: '<?= BASE_URL ?>',
        token:   '<?= htmlspecialchars($_token ?? '') ?>',
        adminId: <?= (int)($_SESSION['admin_id'] ?? 0) ?>
    };

    // Auxiliar global para renderizar la cabecera institucional y logo en PDFs
    window.renderPdfHeader = async function(doc, titleText) {
        const config = window.APP_CONFIG || {};
        const encabezado = config.encabezado || '';
        const logoUrl = config.logoUrl || '';

        let currentY = 14;

        if (logoUrl) {
            try {
                const img = await new Promise((resolve) => {
                    const image = new Image();
                    image.crossOrigin = 'Anonymous';
                    image.onload = () => resolve(image);
                    image.onerror = () => resolve(null);
                    image.src = logoUrl;
                });

                if (img && (img.naturalWidth > 0 || img.width > 0)) {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.naturalWidth || img.width || 100;
                    canvas.height = img.naturalHeight || img.height || 100;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    const imgData = canvas.toDataURL('image/png');
                    doc.addImage(imgData, 'PNG', 14, 10, 22, 22);
                }
            } catch (e) {
                console.warn("Logo no disponible para PDF:", e);
            }
        }

        if (encabezado) {
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(8.5);
            doc.setTextColor(51, 65, 85);
            const lines = encabezado.split('\n');
            const startX = logoUrl ? 40 : 14;
            lines.forEach(line => {
                if (line.trim()) {
                    doc.text(line.trim(), startX, currentY);
                    currentY += 4.5;
                }
            });
            currentY = Math.max(currentY + 4, 34);
        } else {
            currentY = 32;
        }

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(13);
        doc.setTextColor(15, 23, 42);
        doc.text(titleText, 14, currentY);

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(8.5);
        doc.setTextColor(100, 116, 139);
        doc.text('Generado: ' + new Date().toLocaleString('es-VE'), 14, currentY + 5);

        return currentY + 10;
    };

    // Auxiliar global para estructurar filas de Excel con el encabezado institucional
    window.prepareExcelDataWithHeader = function(headers, rows) {
        const config = window.APP_CONFIG || {};
        const encabezado = config.encabezado || '';
        const data = [];

        if (encabezado) {
            const lines = encabezado.split('\n');
            lines.forEach(line => {
                if (line.trim()) {
                    data.push([line.trim()]);
                }
            });
            data.push([]);
        }

        if (headers && headers.length > 0) {
            data.push(headers);
        }

        if (rows && rows.length > 0) {
            rows.forEach(r => data.push(r));
        }

        return data;
    };
</script>

<!-- App JS personalizado -->
<script src="<?= BASE_URL ?>/public/js/app.js"></script>

<!-- ╔══════════════════════════════════════════════════╗
     ║          Fullscreen Loading Overlay             ║
     ╚══════════════════════════════════════════════════╝ -->
<div id="loadingOverlay" class="loading-overlay d-none" role="status" aria-live="polite">
    <div class="loading-content">
        <div class="loading-logo-wrap">
            <img src="<?= BASE_URL ?>/public/images/logo.jpg" alt="Logo" class="loading-logo-img">
            <div class="loading-spinner-circle"></div>
        </div>
        <p class="loading-text mt-3">Procesando, por favor espere...</p>
    </div>
</div>

</body>
</html>
