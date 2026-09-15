<?php require VIEWS_PATH . '/layout/header.php'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-gear-fill me-2"></i>Configuración del Sistema</h1>
        <p class="page-subtitle">Gestiona tu perfil, temas visuales, horarios globales y copias de seguridad.</p>
    </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="config-container">
    <!-- Menú Lateral de Pestañas -->
    <div class="config-nav">
        <button class="nav-tab active" onclick="switchTab(event, 'perfil')">
            <i class="bi bi-person-badge"></i> Mi Perfil
        </button>
        <button class="nav-tab" onclick="switchTab(event, 'tema')">
            <i class="bi bi-palette"></i> Tema & Apariciencia
        </button>
        <button class="nav-tab" onclick="switchTab(event, 'encabezado')">
            <i class="bi bi-file-earmark-richtext-fill"></i> Encabezado & Logo
        </button>
        <button class="nav-tab" onclick="switchTab(event, 'backup')">
            <i class="bi bi-database-down"></i> Respaldo de BD
        </button>
    </div>

    <!-- Contenido de Secciones -->
    <div class="config-content">

        <!-- Tab 1: Perfil de Administrador -->
        <div id="tab-perfil" class="tab-pane active">
            <h2>Perfil de Administrador</h2>
            <form action="<?= BASE_URL ?>/configuracion/actualizar-perfil" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= $_token ?>">
                
                <div class="profile-avatar-wrapper mb-4">
                    <?php if (!empty($admin['foto']) && is_file(ROOT_PATH . '/public/uploads/avatars/' . $admin['foto'])): ?>
                        <img id="avatarPreview" src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars($admin['foto']) ?>" alt="Foto Perfil" class="avatar-img">
                    <?php else: ?>
                        <img id="avatarPreview" src="<?= BASE_URL ?>/public/images/logo.jpg" alt="Foto Perfil" class="avatar-img" onerror="this.src='<?= BASE_URL ?>/public/images/azul1.jpg'">
                    <?php endif; ?>
                    
                    <div class="avatar-upload-btn">
                        <label for="fotoPerfil" class="btn btn-outline-primary mb-1"><i class="bi bi-camera-fill me-1"></i> Cambiar Foto de Perfil</label>
                        <input type="file" id="fotoPerfil" name="foto" accept="image/png, image/jpeg" onchange="previewImage(this)">
                        <div class="small text-muted"><i class="bi bi-info-circle me-1"></i>Formatos permitidos: <strong>JPG</strong> y <strong>PNG</strong></div>
                    </div>
                </div>
                
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Nombre Completo</label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($admin['nombre']) ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label fw-bold">Nueva Contraseña <small class="text-muted">(Dejar en blanco para conservar la actual)</small></label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Guardar Perfil</button>
            </form>
        </div>

        <!-- Tab 2: Tema Visual y Fondo Difuminado -->
        <div id="tab-tema" class="tab-pane">
            <h2><i class="bi bi-palette-fill me-2 text-primary"></i>Personalización Visual & Pantallas</h2>
            <p class="text-muted">Personaliza la combinación de colores del menú del sistema y selecciona un fondo difuminado/gradiente para todas las pantallas del sistema.</p>

            <form action="<?= BASE_URL ?>/configuracion/guardar-tema" method="POST">
                <input type="hidden" name="_token" value="<?= $_token ?>">
                <input type="hidden" id="colorFondo" name="color_fondo" value="<?= htmlspecialchars($config['color_fondo'] ?? 'linear-gradient(135deg, #eef2ff 0%, #e0e7ff 50%, #f1f5f9 100%)') ?>">

                <!-- Sección 1: Paleta de Colores para el Menú del Sistema -->
                <div class="card p-3 mb-4 border-0 shadow-sm bg-light">
                    <h5 class="fw-bold mb-2 text-dark"><i class="bi bi-layout-sidebar-inset me-2 text-primary"></i>1. Colores del Menú del Sistema (Sidebar & Topbar)</h5>
                    <p class="small text-muted mb-3">Elige un esquema predefinido o ajusta los colores manualmente.</p>
                    
                    <!-- Presets de Menú -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="applyMenuPreset('#1e293b', '#0f172a')">
                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#0f172a;" class="me-1"></span> Carbón Oscuro
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="applyMenuPreset('#1e1b4b', '#312e81')">
                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#312e81;" class="me-1"></span> Azul Índigo
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="applyMenuPreset('#064e3b', '#047857')">
                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#047857;" class="me-1"></span> Esmeralda
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="applyMenuPreset('#4c1d95', '#6d28d9')">
                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#6d28d9;" class="me-1"></span> Morado Neón
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="applyMenuPreset('#881337', '#be123c')">
                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#be123c;" class="me-1"></span> Vino Carmesí
                        </button>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Barra Superior (Topbar)</label>
                            <input type="color" id="colorHeader" name="color_header" class="form-control form-control-color w-100" value="<?= htmlspecialchars($config['color_header']) ?>" oninput="updateHeaderLive(this.value)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Menú Lateral (Sidebar)</label>
                            <input type="color" id="colorSidebar" name="color_sidebar" class="form-control form-control-color w-100" value="<?= htmlspecialchars($config['color_sidebar']) ?>" oninput="updateSidebarLive(this.value)">
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Paleta de Colores Difuminados para el Fondo de las Pantallas -->
                <div class="card p-3 mb-4 border-0 shadow-sm bg-light">
                    <h5 class="fw-bold mb-2 text-dark"><i class="bi bi-droplet-half me-2 text-primary"></i>2. Fondo Difuminado de Pantallas (Sistema Global)</h5>
                    <p class="small text-muted mb-3">Haz clic en cualquier estilo difuminado/gradiente para aplicarlo al fondo de todas las vistas en tiempo real.</p>
                    
                    <div class="row g-3" id="gradientPalette">
                        <!-- Option 1 -->
                        <div class="col-md-4 col-sm-6">
                            <div class="gradient-card p-3 rounded border text-center cursor-pointer shadow-sm position-relative" 
                                 style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 50%, #f1f5f9 100%); min-height: 85px; cursor: pointer;"
                                 onclick="applyFondoPreset('linear-gradient(135deg, #eef2ff 0%, #e0e7ff 50%, #f1f5f9 100%)', this)">
                                <span class="fw-bold text-dark d-block">Brisa Azul Soft</span>
                                <small class="text-muted">Azul/Violeta Difuminado</small>
                            </div>
                        </div>
                        <!-- Option 2 -->
                        <div class="col-md-4 col-sm-6">
                            <div class="gradient-card p-3 rounded border text-center cursor-pointer shadow-sm position-relative" 
                                 style="background: linear-gradient(135deg, #fce7f3 0%, #f3e8ff 50%, #f8fafc 100%); min-height: 85px; cursor: pointer;"
                                 onclick="applyFondoPreset('linear-gradient(135deg, #fce7f3 0%, #f3e8ff 50%, #f8fafc 100%)', this)">
                                <span class="fw-bold text-dark d-block">Atardecer Suave</span>
                                <small class="text-muted">Rosa/Pastel Difuminado</small>
                            </div>
                        </div>
                        <!-- Option 3 -->
                        <div class="col-md-4 col-sm-6">
                            <div class="gradient-card p-3 rounded border text-center cursor-pointer shadow-sm position-relative" 
                                 style="background: linear-gradient(135deg, #e0f2fe 0%, #f0fdf4 50%, #f8fafc 100%); min-height: 85px; cursor: pointer;"
                                 onclick="applyFondoPreset('linear-gradient(135deg, #e0f2fe 0%, #f0fdf4 50%, #f8fafc 100%)', this)">
                                <span class="fw-bold text-dark d-block">Aurora Menta</span>
                                <small class="text-muted">Cian/Menta Difuminado</small>
                            </div>
                        </div>
                        <!-- Option 4 -->
                        <div class="col-md-4 col-sm-6">
                            <div class="gradient-card p-3 rounded border text-center cursor-pointer shadow-sm position-relative" 
                                 style="background: linear-gradient(135deg, #ffedd5 0%, #fef3c7 50%, #f8fafc 100%); min-height: 85px; cursor: pointer;"
                                 onclick="applyFondoPreset('linear-gradient(135deg, #ffedd5 0%, #fef3c7 50%, #f8fafc 100%)', this)">
                                <span class="fw-bold text-dark d-block">Cálido Sol</span>
                                <small class="text-muted">Ámbar/Dorado Suave</small>
                            </div>
                        </div>
                        <!-- Option 5 -->
                        <div class="col-md-4 col-sm-6">
                            <div class="gradient-card p-3 rounded border text-center cursor-pointer shadow-sm position-relative" 
                                 style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #020617 100%); min-height: 85px; cursor: pointer;"
                                 onclick="applyFondoPreset('linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #020617 100%)', this)">
                                <span class="fw-bold text-white d-block">Noche Estelar</span>
                                <small class="text-light opacity-75">Azul Noche Oscuro</small>
                            </div>
                        </div>
                        <!-- Option 6 -->
                        <div class="col-md-4 col-sm-6">
                            <div class="gradient-card p-3 rounded border text-center cursor-pointer shadow-sm position-relative" 
                                 style="background: #f0f2f7; min-height: 85px; cursor: pointer;"
                                 onclick="applyFondoPreset('#f0f2f7', this)">
                                <span class="fw-bold text-dark d-block">Blanco Perla / Clásico</span>
                                <small class="text-muted">Sobrio Tradicional</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-palette-fill me-1"></i> Aplicar Tema & Fondo</button>
                    <button type="button" class="btn btn-secondary btn-lg" onclick="resetThemeDefaults()">Restablecer Predeterminados</button>
                </div>
            </form>
        </div>

        <!-- Tab 3: Encabezado y Logo Institucional -->
        <div id="tab-encabezado" class="tab-pane">
            <h2><i class="bi bi-file-earmark-richtext me-2 text-primary"></i>Encabezado y Logo Institucional</h2>
            <p class="text-muted">El texto y logo definidos aquí se agregarán automáticamente en la cabecera de cada archivo <strong>PDF</strong> y <strong>Excel</strong> que genere el sistema SCAP.</p>

            <form action="<?= BASE_URL ?>/configuracion/guardar-encabezado" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?= $_token ?>">

                <div class="mb-4">
                    <label class="form-label fw-bold">Encabezado Institucional <span class="text-danger">*</span></label>
                    <textarea name="encabezado" id="inputEncabezado" class="form-control" rows="4" placeholder="Escribe el membrete oficial de la institución..." required><?= htmlspecialchars($config['encabezado'] ?? '') ?></textarea>
                    <small class="text-muted">Puedes incluir varias líneas (ej: República, Ministerio, Unidad Educativa o Departamento).</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Logo de la Institución</label>
                    <input type="file" name="logo" id="inputLogo" class="form-control" accept="image/png, image/jpeg, image/webp" onchange="previewLogoImage(this)">
                    <small class="text-muted">Formatos permitidos: PNG, JPG, WEBP. Se recomienda fondo transparente.</small>
                </div>

                <!-- Previsualización en tiempo real -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Vista Previa de la Cabecera de Reportes</label>
                    <div class="report-header-preview p-3 border rounded bg-light d-flex align-items-center gap-3">
                        <div class="preview-logo-box text-center">
                            <?php if (!empty($config['logo_url'])): ?>
                                <img id="logoPreview" src="<?= BASE_URL ?>/public/uploads/logo/<?= htmlspecialchars($config['logo_url']) ?>" alt="Logo Institución" style="max-height: 70px; max-width: 90px; object-fit: contain;">
                            <?php else: ?>
                                <img id="logoPreview" src="<?= BASE_URL ?>/public/images/logo.jpg" alt="Logo Institución" style="max-height: 70px; max-width: 90px; object-fit: contain;" onerror="this.src='<?= BASE_URL ?>/public/images/azul1.jpg'">
                            <?php endif; ?>
                        </div>
                        <div class="preview-text-box flex-grow-1 border-start ps-3">
                            <pre id="encabezadoTextPreview" style="font-family: inherit; font-size: 0.9rem; font-weight: 600; color: #334155; margin: 0; white-space: pre-wrap;"><?= htmlspecialchars($config['encabezado'] ?? '') ?></pre>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save-fill me-1"></i> Guardar Encabezado y Logo
                </button>
            </form>
        </div>

        <!-- Tab 5: Backup de Base de Datos -->
        <div id="tab-backup" class="tab-pane">
            <h2>Copia de Seguridad de la Base de Datos</h2>
            <p>Genera y descarga un archivo de respaldo <code>.sql</code> estructurado con todas las tablas y registros actuales del sistema.</p>
            <a href="<?= BASE_URL ?>/configuracion/respaldar-bd" class="btn btn-success btn-lg">
                <i class="bi bi-download"></i> Descargar Respaldo MySQL (.sql)
            </a>
        </div>

    </div>
</div>

<!-- Estilos Específicos CSS -->
<style>
.config-container {
    display: flex;
    gap: 1.5rem;
    background: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}
.config-nav {
    display: flex;
    flex-direction: column;
    width: 250px;
    border-right: 1px solid #e2e8f0;
    gap: 0.5rem;
}
.nav-tab {
    background: none;
    border: none;
    text-align: left;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    color: #64748b;
    transition: all 0.2s;
}
.nav-tab:hover, .nav-tab.active {
    background: #e0e7ff;
    color: #4f46e5;
}
.config-content {
    flex: 1;
}
.tab-pane {
    display: none;
}
.tab-pane.active {
    display: block;
}
.avatar-img {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #4f46e5;
}
.profile-avatar-wrapper {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}
.avatar-upload-btn input[type="file"] {
    display: none;
}
.avatar-upload-btn label {
    background: #f1f5f9;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}
</style>

<!-- Scripts de Interacción JavaScript -->
<script>
// Cambiar pestañas
function switchTab(evt, tabId) {
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.nav-tab').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');
    evt.currentTarget.classList.add('active');
}

// Previsualización dinámica de foto de perfil
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function applyMenuPreset(headerColor, sidebarColor) {
    const inputHeader = document.getElementById('colorHeader');
    const inputSidebar = document.getElementById('colorSidebar');
    if (inputHeader) inputHeader.value = headerColor;
    if (inputSidebar) inputSidebar.value = sidebarColor;
    updateHeaderLive(headerColor);
    updateSidebarLive(sidebarColor);
}

function updateHeaderLive(color) {
    document.documentElement.style.setProperty('--topbar-bg', color);
    const headerElem = document.querySelector('.topbar, header');
    if (headerElem) {
        headerElem.style.backgroundColor = color;
        let hex = color.replace('#', '');
        if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
        if (hex.length === 6) {
            const r = parseInt(hex.substring(0, 2), 16);
            const g = parseInt(hex.substring(2, 4), 16);
            const b = parseInt(hex.substring(4, 6), 16);
            const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
            const isDark = yiq < 128;
            headerElem.classList.toggle('topbar--dark', isDark);
            headerElem.classList.toggle('topbar--light', !isDark);
        }
    }
}

function updateSidebarLive(color) {
    document.documentElement.style.setProperty('--sidebar-bg-from', color);
    document.documentElement.style.setProperty('--sidebar-bg-to', color);
    const sidebarElem = document.querySelector('.sidebar');
    if (sidebarElem) sidebarElem.style.background = color;
}

function applyFondoPreset(gradientVal, elem) {
    const inputFondo = document.getElementById('colorFondo');
    if (inputFondo) inputFondo.value = gradientVal;
    
    document.documentElement.style.setProperty('--bg', gradientVal);
    document.body.style.background = gradientVal;
    document.body.style.backgroundAttachment = 'fixed';
    
    const mainArea = document.querySelector('.main-area');
    if (mainArea) mainArea.style.background = gradientVal;
    
    const pageContent = document.querySelector('.page-content');
    if (pageContent) pageContent.style.background = gradientVal;

    document.querySelectorAll('#gradientPalette .gradient-card').forEach(el => {
        el.classList.remove('border-primary', 'border-3', 'shadow');
    });
    if (elem) {
        elem.classList.add('border-primary', 'border-3', 'shadow');
    }
}

function previewLogoImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const logoPreview = document.getElementById('logoPreview');
            if (logoPreview) logoPreview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('inputEncabezado')?.addEventListener('input', function(e) {
    const previewEl = document.getElementById('encabezadoTextPreview');
    if (previewEl) {
        previewEl.textContent = e.target.value;
    }
});

function resetThemeDefaults() {
    applyMenuPreset('#1e293b', '#0f172a');
    applyFondoPreset('linear-gradient(135deg, #eef2ff 0%, #e0e7ff 50%, #f1f5f9 100%)', null);
}
</script>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
