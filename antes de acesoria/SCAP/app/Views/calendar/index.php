<?php require VIEWS_PATH . '/layout/header.php'; ?>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- CALENDARIO ANUAL DE CONTROL LABORAL (TEMA AZUL Y BLANCO)  -->
<!-- ═══════════════════════════════════════════════════════ -->

<style>
/* Estilos específicos para el Calendario Anual – Tema Azul y Blanco SCAP */
:root {
  --c-work:         #10b981;   /* Verde Esmeralda */
  --c-work-bg:      #dcfce7;
  --c-work-border:  #86efac;

  --c-vacation:     #0284c7;   /* Azul Océano / Turquesa */
  --c-vacation-bg:  #e0f2fe;
  --c-vacation-border: #93c5fd;

  --c-holiday:      #d97706;   /* Ámbar / Dorado */
  --c-holiday-bg:   #fef3c7;
  --c-holiday-border: #fde68a;

  --c-birthday:     #1d4ed8;   /* Azul Rey Marca SCAP */
  --c-birthday-bg:  #dbeafe;
  --c-birthday-border: #93c5fd;

  --c-emergency:    #dc2626;   /* Rojo Carmesí */
  --c-emergency-bg: #fee2e2;
  --c-emergency-border: #fca5a5;

  --c-weekend:      #f8fafc;
}

/* Tarjeta Principal */
.calendar-main-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
  margin-bottom: 30px;
}

/* Header Superior (Azul Rey y Blanco) */
.calendar-header-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  padding: 20px 24px;
  background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
  border-bottom: 1px solid #1e40af;
  color: #ffffff;
}

.calendar-year-nav {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 10px;
  padding: 4px 14px;
  backdrop-filter: blur(4px);
}

.calendar-year-nav button {
  background: none;
  border: none;
  color: #ffffff;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 2px 8px;
  border-radius: 6px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
}

.calendar-year-nav button:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: scale(1.1);
}

#yearDisplay {
  font-weight: 800;
  font-size: 1.3rem;
  min-width: 60px;
  text-align: center;
  color: #ffffff;
  letter-spacing: 0.02em;
}

.search-calendar-wrap {
  position: relative;
  min-width: 260px;
  max-width: 380px;
  flex: 1;
}

.search-calendar-wrap input {
  width: 100%;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  color: #0f172a;
  padding: 8px 12px 8px 36px;
  font-size: 0.88rem;
  outline: none;
  transition: all 0.2s;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.search-calendar-wrap input::placeholder {
  color: #94a3b8;
}

.search-calendar-wrap input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
}

.search-calendar-wrap i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  pointer-events: none;
}

.search-suggestions-box {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  right: 0;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  z-index: 100;
  overflow: hidden;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
  display: none;
}

.suggestion-item {
  padding: 10px 14px;
  cursor: pointer;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: background 0.15s;
  color: #1e293b;
}

.suggestion-item:hover {
  background: #eff6ff;
  color: #1d4ed8;
}

/* Toolbar de Administrador (Fondo Blanco / Gris Suave) */
.admin-toolbar-panel {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  padding: 14px 24px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.toolbar-label {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #475569;
  margin-right: 4px;
}

.btn-tool {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 600;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #334155;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}

.btn-tool:hover {
  background: #f1f5f9;
  color: #1d4ed8;
  border-color: #93c5fd;
  transform: translateY(-1px);
}

.btn-tool.active-mode {
  border-color: #2563eb;
  background: #dbeafe;
  color: #1e40af;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.3);
}

.btn-bulk-workdays {
  background: linear-gradient(135deg, #1e40af, #2563eb);
  border: 1px solid #1e3a8a;
  color: #ffffff;
  font-weight: 600;
  box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
}
.btn-bulk-workdays:hover {
  background: linear-gradient(135deg, #1d4ed8, #3b82f6);
  color: #ffffff;
}

/* Grilla de Meses */
.months-wrapper-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
  padding: 24px;
  background: #ffffff;
}

@media (max-width: 1200px) { .months-wrapper-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 900px)  { .months-wrapper-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 576px)  { .months-wrapper-grid { grid-template-columns: 1fr; } }

.month-block {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.month-block:hover {
  border-color: #93c5fd;
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.08);
}

.month-block.month-highlight {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3);
}

.month-title-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  border-bottom: 1px solid #dbeafe;
}

.month-name {
  font-size: 0.84rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #1e3a8a;
}

.month-marked-badge {
  font-size: 0.68rem;
  font-weight: 600;
  background: #ffffff;
  color: #2563eb;
  padding: 2px 8px;
  border-radius: 12px;
  border: 1px solid #bfdbfe;
}

.days-header-row {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  padding: 6px 6px 2px;
  text-align: center;
  background: #ffffff;
}

.day-header-item {
  font-size: 0.65rem;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  padding: 2px 0;
}

.day-header-item.weekend {
  color: #94a3b8;
}

.days-cells-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 3px;
  padding: 6px 6px 8px;
  background: #ffffff;
}

.day-box {
  position: relative;
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.74rem;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  user-select: none;
  transition: all 0.15s ease;
  border: 1px solid transparent;
  color: #334155;
}

.day-box.empty {
  cursor: default;
}

.day-box:not(.empty):hover {
  transform: scale(1.15);
  z-index: 5;
  border-color: #2563eb;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.day-box.is-today {
  box-shadow: 0 0 0 2px #2563eb;
  font-weight: 800;
  color: #1e3a8a !important;
}

/* Celdas con estado */
.day-box.state-laborable {
  background: var(--c-work-bg);
  color: #15803d;
  border-color: var(--c-work-border);
}

.day-box.state-vacaciones {
  background: var(--c-vacation-bg);
  color: #0369a1;
  border-color: var(--c-vacation-border);
}

.day-box.state-feriado {
  background: var(--c-holiday-bg);
  color: #b45309;
  border-color: var(--c-holiday-border);
}

.day-box.state-cumpleanios {
  background: var(--c-birthday-bg);
  color: #1d4ed8;
  border-color: var(--c-birthday-border);
  font-weight: 700;
}

.day-box.state-emergencia {
  background: var(--c-emergency-bg);
  color: #b91c1c;
  border-color: var(--c-emergency-border);
  animation: pulse-emergencia 2s infinite ease-in-out;
}

@keyframes pulse-emergencia {
  0%, 100% { box-shadow: none; }
  50% { box-shadow: 0 0 8px rgba(220, 38, 38, 0.4); }
}

.day-box .mini-badge {
  position: absolute;
  top: 1px;
  right: 2px;
  font-size: 8px;
  line-height: 1;
}

/* Leyenda y Estadísticas (Fondo Blanco) */
.calendar-footer-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  padding: 14px 24px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.legend-items-list {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  font-size: 0.8rem;
  color: #475569;
}

.legend-chip {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 3px;
  flex-shrink: 0;
  border: 1px solid rgba(0,0,0,0.1);
}

.stats-counter-list {
  display: flex;
  align-items: center;
  gap: 18px;
  font-size: 0.8rem;
  color: #334155;
  font-weight: 500;
}

.stat-badge {
  font-weight: 800;
  font-size: 0.9rem;
}

/* Tooltip Flotante (Blanco y Azul) */
#calendarTooltip {
  position: fixed;
  pointer-events: none;
  z-index: 9999;
  background: #ffffff;
  border: 1px solid #93c5fd;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 0.8rem;
  color: #0f172a;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
  opacity: 0;
  transform: translateY(6px);
  transition: opacity 0.15s, transform 0.15s;
  max-width: 250px;
}

#calendarTooltip.visible {
  opacity: 1;
  transform: translateY(0);
}

/* Modales SCAP Tema Claro */
.modal-content.scap-modal {
  background: #ffffff;
  color: #0f172a;
  border-radius: 16px;
  border: 1px solid #cbd5e1;
  box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.modal-header.scap-modal-header {
  background: linear-gradient(135deg, #1e3a8a, #2563eb);
  color: #ffffff;
  border-top-left-radius: 15px;
  border-top-right-radius: 15px;
}

.modal-header.scap-modal-header .modal-title {
  color: #ffffff;
  font-weight: 700;
}
</style>

<div class="container-fluid px-0">

  <!-- Header de página -->
  <div class="page-header mb-3">
    <div>
      <h1 class="page-title"><i class="bi bi-calendar3-range-fill text-primary me-2"></i>Calendario Anual de Control Laboral</h1>
      <p class="page-subtitle">Gestión administrativa de días laborables, vacaciones, feriados, cumpleaños y decretos de emergencia</p>
    </div>
  </div>

  <!-- Contenedor Principal del Calendario -->
  <div class="calendar-main-card">

    <!-- Superior: Buscador y Navegación de Año -->
    <div class="calendar-header-bar">
      <div class="d-flex align-items-center gap-3">
        <div class="calendar-year-nav">
          <button id="btnPrevYear" title="Año Anterior"><i class="bi bi-chevron-left"></i></button>
          <span id="yearDisplay"><?= $year ?></span>
          <button id="btnNextYear" title="Año Siguiente"><i class="bi bi-chevron-right"></i></button>
        </div>
        <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill shadow-sm">SCAP <?= date('Y') ?></span>
      </div>

      <div class="search-calendar-wrap">
        <i class="bi bi-search"></i>
        <input type="text" id="calendarSearchInput" placeholder="Buscar mes o fecha (ej: Julio, 15/07)..." autocomplete="off">
        <div class="search-suggestions-box" id="searchSuggestionsBox"></div>
      </div>
    </div>

    <!-- Panel de Herramientas del Administrador -->
    <div class="admin-toolbar-panel">
      <span class="toolbar-label"><i class="bi bi-tools me-1"></i>Herramientas Admin:</span>

      <button type="button" class="btn-tool" id="toolWork" onclick="openModalWork()">
        <i class="bi bi-check-circle-fill text-success"></i> Laborable
      </button>

      <button type="button" class="btn-tool" onclick="openModalVacations()">
        <i class="bi bi-sun-fill text-info"></i> Vacaciones
      </button>

      <button type="button" class="btn-tool" onclick="openModalHoliday()">
        <i class="bi bi-balloon-fill text-warning"></i> Feriado
      </button>

      <button type="button" class="btn-tool" onclick="openModalBirthday()">
        <i class="bi bi-cake2-fill text-primary"></i> Cumpleaños
      </button>

      <button type="button" class="btn-tool" onclick="openModalEmergency()">
        <i class="bi bi-exclamation-triangle-fill text-danger"></i> Emergencia / Decreto
      </button>

      <div class="vr mx-1 opacity-25"></div>

      <button type="button" class="btn-tool" id="toolEraser" onclick="setActiveMode('borrador')">
        <i class="bi bi-eraser-fill text-secondary"></i> Borrador
      </button>

      <button type="button" class="btn-tool text-danger ms-auto" onclick="confirmClearYear()">
        <i class="bi bi-trash3-fill"></i> Limpiar Año
      </button>
    </div>

    <!-- Grilla Anual de 12 Meses -->
    <div class="months-wrapper-grid" id="monthsGrid">
      <!-- Se genera dinámicamente vía JS -->
    </div>

    <!-- Barra Inferior: Contador de Estadísticas -->
    <div class="calendar-footer-bar">
      <div class="legend-items-list">
        <span class="fw-bold me-2 text-dark">Leyenda:</span>
        <div class="legend-chip"><div class="legend-dot" style="background:var(--c-work-bg);border-color:var(--c-work-border)"></div> Laborable</div>
        <div class="legend-chip"><div class="legend-dot" style="background:var(--c-vacation-bg);border-color:var(--c-vacation-border)"></div> Vacaciones</div>
        <div class="legend-chip"><div class="legend-dot" style="background:var(--c-holiday-bg);border-color:var(--c-holiday-border)"></div> Feriado</div>
        <div class="legend-chip"><div class="legend-dot" style="background:var(--c-birthday-bg);border-color:var(--c-birthday-border)"></div> Cumpleaños</div>
        <div class="legend-chip"><div class="legend-dot" style="background:var(--c-emergency-bg);border-color:var(--c-emergency-border)"></div> Emergencia / Decreto</div>
      </div>

      <div class="stats-counter-list">
        <div>Laborables: <span class="stat-badge text-success" id="countWork">0</span></div>
        <div>Vacaciones: <span class="stat-badge text-info" id="countVacation">0</span></div>
        <div>Feriados: <span class="stat-badge text-warning" id="countHoliday">0</span></div>
        <div>Cumpleaños: <span class="stat-badge text-primary" id="countBirthday">0</span></div>
        <div>Emergencias: <span class="stat-badge text-danger" id="countEmergency">0</span></div>
      </div>
    </div>

  </div>

</div>

<!-- ===== TOOLTIP FLOTANTE ===== -->
<div id="calendarTooltip">
  <div id="ttDate" class="fw-bold text-secondary mb-1"></div>
  <div id="ttState" class="fw-bold mb-1"></div>
  <div id="ttNote" class="small text-muted"></div>
</div>

<!-- ===== MODALES DE CONFIGURACIÓN ===== -->

<!-- Modal Laborable -->
<div class="modal fade" id="modalWork" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content scap-modal">
      <div class="modal-header scap-modal-header">
        <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Asignar Periodo Laborable</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="formWork">
          <input type="hidden" name="_token" value="<?= $_token ?>">

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label text-secondary small text-uppercase fw-semibold">Fecha Inicio</label>
              <input type="date" name="from" id="workFrom" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label text-secondary small text-uppercase fw-semibold">Fecha Fin</label>
              <input type="date" name="to" id="workTo" class="form-control" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-secondary small text-uppercase fw-semibold">Notas u Observaciones</label>
            <textarea name="nota" class="form-control" rows="2" placeholder="Ej: Semana laborable regular"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer border-top-0 pt-0 pe-4 pb-3">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success btn-sm fw-bold px-3" onclick="submitWork()">Guardar Periodo Laborable</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Vacaciones -->
<div class="modal fade" id="modalVacations" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content scap-modal">
      <div class="modal-header scap-modal-header">
        <h5 class="modal-title"><i class="bi bi-sun-fill me-2"></i>Asignar Periodo Vacacional</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="formVacations">
          <input type="hidden" name="_token" value="<?= $_token ?>">
          
          <div class="mb-3">
            <label class="form-label text-secondary small text-uppercase fw-semibold">Trabajador (Opcional - Dejar en blanco para colectivo)</label>
            <select name="worker_id" class="form-select">
              <option value="">-- Vacaciones Colectivas / Todo el Personal --</option>
              <?php foreach ($workers as $w): ?>
                <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['nombre'] . ' ' . $w['apellido']) ?> (<?= htmlspecialchars($w['cargo']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label text-secondary small text-uppercase fw-semibold">Fecha Inicio</label>
              <input type="date" name="from" id="vacFrom" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label text-secondary small text-uppercase fw-semibold">Fecha Fin</label>
              <input type="date" name="to" id="vacTo" class="form-control" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-secondary small text-uppercase fw-semibold">Notas u Observaciones</label>
            <textarea name="nota" class="form-control" rows="2" placeholder="Ej: Periodo vacacional de verano 2026"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer border-top-0 pt-0 pe-4 pb-3">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary btn-sm fw-bold px-3" onclick="submitVacations()">Guardar Vacaciones</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Feriado -->
<div class="modal fade" id="modalHoliday" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content scap-modal">
      <div class="modal-header scap-modal-header">
        <h5 class="modal-title"><i class="bi bi-balloon-fill me-2"></i>Registrar Día Feriado</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="formHoliday">
          <input type="hidden" name="_token" value="<?= $_token ?>">
          
          <div class="mb-3">
            <label class="form-label text-secondary small text-uppercase fw-semibold">Nombre del Feriado</label>
            <input type="text" name="nombre" class="form-control" placeholder="Ej: Día de la Independencia" required>
          </div>

          <div class="mb-3">
            <label class="form-label text-secondary small text-uppercase fw-semibold">Fecha</label>
            <input type="date" name="fecha" id="holDate" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label text-secondary small text-uppercase fw-semibold">Tipo de Feriado</label>
            <select name="tipo" class="form-select">
              <option value="Feriado Nacional">Feriado Nacional</option>
              <option value="Feriado Regional / Local">Feriado Regional / Local</option>
              <option value="Feriado Institucional">Feriado Institucional</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer border-top-0 pt-0 pe-4 pb-3">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary btn-sm fw-bold px-3" onclick="submitHoliday()">Registrar Feriado</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Cumpleaños -->
<div class="modal fade" id="modalBirthday" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content scap-modal">
      <div class="modal-header scap-modal-header">
        <h5 class="modal-title"><i class="bi bi-cake2-fill me-2"></i>Registrar Cumpleaños del Personal</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="formBirthday">
          <input type="hidden" name="_token" value="<?= $_token ?>">

          <div class="mb-3">
            <label class="form-label text-secondary small text-uppercase fw-semibold">Trabajador</label>
            <select name="worker_id" class="form-select" required>
              <option value="">-- Seleccionar Trabajador --</option>
              <?php foreach ($workers as $w): ?>
                <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['nombre'] . ' ' . $w['apellido']) ?> (C.I. <?= htmlspecialchars($w['cedula']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label text-secondary small text-uppercase fw-semibold">Fecha de Cumpleaños</label>
            <input type="date" name="fecha" id="bdayDate" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label text-secondary small text-uppercase fw-semibold">Nota / Detalle</label>
            <input type="text" name="nota" class="form-control" placeholder="Ej: Cumpleaños institucional">
          </div>
        </form>
      </div>
      <div class="modal-footer border-top-0 pt-0 pe-4 pb-3">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary btn-sm fw-bold px-3" onclick="submitBirthday()">Registrar Cumpleaños</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Emergencia / Decreto -->
<div class="modal fade" id="modalEmergency" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content scap-modal">
      <div class="modal-header scap-modal-header bg-danger text-white" style="background:linear-gradient(135deg, #b91c1c, #dc2626);">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Días de Emergencia / Suspensión de Labores</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="formEmergency">
          <input type="hidden" name="_token" value="<?= $_token ?>">

          <div class="mb-3">
            <label class="form-label text-secondary small text-uppercase fw-semibold">Motivo de Suspensión</label>
            <select name="motivo" class="form-select" required>
              <option value="Decreto Nacional">🏛️ Decreto Nacional</option>
              <option value="Decisión Administrativa">🏢 Decisión / Administración del Instituto</option>
              <option value="Emergencia Sanitaria">🏥 Emergencia Sanitaria</option>
              <option value="Desastre Natural">⛈️ Desastre Natural</option>
              <option value="Otro">⚠️ Otro Motivo</option>
            </select>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label text-secondary small text-uppercase fw-semibold">Fecha Inicio</label>
              <input type="date" name="from" id="emgFrom" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label text-secondary small text-uppercase fw-semibold">Fecha Fin</label>
              <input type="date" name="to" id="emgTo" class="form-control">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-secondary small text-uppercase fw-semibold">Descripción / Detalles del Decreto</label>
            <textarea name="descripcion" class="form-control" rows="3" placeholder="Ej: Suspensión de actividades docentes y administrativas conforme a Decreto Presidencial N°..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer border-top-0 pt-0 pe-4 pb-3">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger btn-sm fw-bold px-3" onclick="submitEmergency()">Declarar Emergencia</button>
      </div>
    </div>
  </div>
</div>

<!-- SCRIPTS DE INTERACCIÓN VÍA AJAX -->
<script>
let currentYear = <?= $year ?>;
let activeMode  = 'laborable'; // 'laborable' | 'borrador'
let calendarData = {};
const csrfToken = '<?= $_token ?>';
const baseUrl   = '<?= BASE_URL ?>';

const MONTHS_NAMES = [
  'Enero','Febrero','Marzo','Abril','Mayo','Junio',
  'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
];

const DAY_LABELS = ['Lu','Ma','Mi','Ju','Vi','Sá','Do'];

document.addEventListener('DOMContentLoaded', () => {
  loadCalendarData(currentYear);

  document.getElementById('btnPrevYear').addEventListener('click', () => changeYear(-1));
  document.getElementById('btnNextYear').addEventListener('click', () => changeYear(1));
  document.getElementById('calendarSearchInput').addEventListener('input', handleCalendarSearch);
});

// Cambiar de año
function changeYear(delta) {
  currentYear += delta;
  document.getElementById('yearDisplay').textContent = currentYear;
  loadCalendarData(currentYear);
}

// Cargar datos AJAX desde PHP
function loadCalendarData(year) {
  fetch(`${baseUrl}/calendario/datos?year=${year}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        calendarData = data.calendar;
        renderCalendar();
      }
    })
    .catch(err => console.error('Error cargando calendario:', err));
}

// Renderizar la retícula de 12 meses
function renderCalendar() {
  const grid = document.getElementById('monthsGrid');
  grid.innerHTML = '';

  const today = new Date();
  const counts = { laborable: 0, vacaciones: 0, feriado: 0, cumpleanios: 0, emergencia: 0 };

  for (let m = 0; m < 12; m++) {
    const firstDay  = new Date(currentYear, m, 1);
    const totalDays = new Date(currentYear, m + 1, 0).getDate();
    let startDow    = (firstDay.getDay() + 6) % 7; // Monday-first

    let markedCount = 0;
    for (let d = 1; d <= totalDays; d++) {
      const key = `${currentYear}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      if (calendarData[key]) {
        markedCount++;
        const st = calendarData[key].tipo_estado;
        if (counts[st] !== undefined) counts[st]++;
      }
    }

    const monthEl = document.createElement('div');
    monthEl.className = 'month-block';
    monthEl.id = `monthCard-${m}`;

    monthEl.innerHTML = `
      <div class="month-title-bar">
        <span class="month-name">${MONTHS_NAMES[m]}</span>
        <span class="month-marked-badge">${markedCount > 0 ? markedCount + ' marcados' : ''}</span>
      </div>
      <div class="days-header-row">
        ${DAY_LABELS.map((d, i) => `<div class="day-header-item ${i >= 5 ? 'weekend' : ''}">${d}</div>`).join('')}
      </div>
      <div class="days-cells-grid" id="daysGrid-${m}"></div>
    `;

    grid.appendChild(monthEl);
    const cellsGrid = monthEl.querySelector(`#daysGrid-${m}`);

    // Celdas vacías de alineación
    for (let i = 0; i < startDow; i++) {
      const emptyCell = document.createElement('div');
      emptyCell.className = 'day-box empty';
      cellsGrid.appendChild(emptyCell);
    }

    // Días del mes
    for (let d = 1; d <= totalDays; d++) {
      const key = `${currentYear}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      const dow = (new Date(currentYear, m, d).getDay() + 6) % 7;
      const isWeekend = dow >= 5;
      const isToday   = (today.getFullYear() === currentYear && today.getMonth() === m && today.getDate() === d);

      const cell = document.createElement('div');
      cell.className = 'day-box' + (isToday ? ' is-today' : '');
      cell.textContent = d;
      cell.dataset.key = key;

      const info = calendarData[key];
      if (info) {
        cell.classList.add(`state-${info.tipo_estado}`);
        if (info.tipo_estado === 'cumpleanios') {
          cell.innerHTML = `${d}<span class="mini-badge">🎂</span>`;
        } else if (info.tipo_estado === 'emergencia') {
          cell.innerHTML = `${d}<span class="mini-badge">🚨</span>`;
        }
      }

      cell.addEventListener('click', () => onDayClick(key));
      cell.addEventListener('mouseenter', (e) => showTooltip(e, key, d, m));
      cell.addEventListener('mouseleave', hideTooltip);

      cellsGrid.appendChild(cell);
    }
  }

  // Actualizar contadores
  document.getElementById('countWork').textContent      = counts.laborable;
  document.getElementById('countVacation').textContent  = counts.vacaciones;
  document.getElementById('countHoliday').textContent   = counts.feriado;
  document.getElementById('countBirthday').textContent  = counts.cumpleanios;
  document.getElementById('countEmergency').textContent = counts.emergencia;
}

// Clic en un día
function onDayClick(fecha) {
  if (activeMode === 'borrador') {
    deleteDate(fecha);
  }
}

// Cambiar modo pincel
function setActiveMode(mode) {
  activeMode = mode;
  document.querySelectorAll('.btn-tool').forEach(b => b.classList.remove('active-mode'));
  if (mode === 'borrador') document.getElementById('toolEraser').classList.add('active-mode');
}

// Modal Laborable
function openModalWork() {
  document.getElementById('workFrom').value = `${currentYear}-01-01`;
  document.getElementById('workTo').value   = `${currentYear}-01-05`;
  new bootstrap.Modal(document.getElementById('modalWork')).show();
}

function submitWork() {
  const form = document.getElementById('formWork');
  const formData = new FormData(form);

  fetch(`${baseUrl}/calendario/laborables-masivo`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(formData)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalWork')).hide();
      loadCalendarData(currentYear);
    } else {
      alert(data.message);
    }
  });
}

// Eliminar fecha
function deleteDate(fecha) {
  fetch(`${baseUrl}/calendario/eliminar-fecha`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ _token: csrfToken, fecha })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      delete calendarData[fecha];
      renderCalendar();
    }
  });
}

// Limpiar año completo
function confirmClearYear() {
  if (confirm(`¿Estás seguro de limpiar todos los registros marcados para el año ${currentYear}?`)) {
    fetch(`${baseUrl}/calendario/limpiar-anio`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ _token: csrfToken, year: currentYear })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        calendarData = {};
        renderCalendar();
      }
    });
  }
}

// Modales abrir
function openModalVacations() {
  document.getElementById('vacFrom').value = `${currentYear}-01-01`;
  document.getElementById('vacTo').value   = `${currentYear}-01-15`;
  new bootstrap.Modal(document.getElementById('modalVacations')).show();
}

function submitVacations() {
  const form = document.getElementById('formVacations');
  const formData = new FormData(form);

  fetch(`${baseUrl}/calendario/vacaciones`, {
    method: 'POST',
    body: new URLSearchParams(formData)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalVacations')).hide();
      loadCalendarData(currentYear);
    } else {
      alert(data.message);
    }
  });
}

function openModalHoliday() {
  document.getElementById('holDate').value = `${currentYear}-01-01`;
  new bootstrap.Modal(document.getElementById('modalHoliday')).show();
}

function submitHoliday() {
  const form = document.getElementById('formHoliday');
  const formData = new FormData(form);

  fetch(`${baseUrl}/calendario/feriado`, {
    method: 'POST',
    body: new URLSearchParams(formData)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalHoliday')).hide();
      loadCalendarData(currentYear);
    } else {
      alert(data.message);
    }
  });
}

function openModalBirthday() {
  document.getElementById('bdayDate').value = `${currentYear}-01-01`;
  new bootstrap.Modal(document.getElementById('modalBirthday')).show();
}

function submitBirthday() {
  const form = document.getElementById('formBirthday');
  const formData = new FormData(form);

  fetch(`${baseUrl}/calendario/cumpleanios`, {
    method: 'POST',
    body: new URLSearchParams(formData)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalBirthday')).hide();
      loadCalendarData(currentYear);
    } else {
      alert(data.message);
    }
  });
}

function openModalEmergency() {
  document.getElementById('emgFrom').value = `${currentYear}-01-01`;
  document.getElementById('emgTo').value   = `${currentYear}-01-01`;
  new bootstrap.Modal(document.getElementById('modalEmergency')).show();
}

function submitEmergency() {
  const form = document.getElementById('formEmergency');
  const formData = new FormData(form);

  fetch(`${baseUrl}/calendario/emergencia`, {
    method: 'POST',
    body: new URLSearchParams(formData)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalEmergency')).hide();
      loadCalendarData(currentYear);
    } else {
      alert(data.message);
    }
  });
}

// Tooltip flotante
const tooltip = document.getElementById('calendarTooltip');

function showTooltip(e, key, day, monthIndex) {
  const info = calendarData[key];
  const dow  = (new Date(currentYear, monthIndex, day).getDay() + 6) % 7;
  const dowNames = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];

  document.getElementById('ttDate').textContent = `${dowNames[dow]}, ${day} de ${MONTHS_NAMES[monthIndex]} ${currentYear}`;

  if (info) {
    let stateLabel = info.tipo_estado.toUpperCase();
    let color = '#10b981';
    if (info.tipo_estado === 'vacaciones')  { stateLabel = '🏖️ VACACIONES'; color = '#0284c7'; }
    if (info.tipo_estado === 'feriado')     { stateLabel = '🎉 FERIADO'; color = '#d97706'; }
    if (info.tipo_estado === 'cumpleanios')  { stateLabel = '🎂 CUMPLEAÑOS'; color = '#1d4ed8'; }
    if (info.tipo_estado === 'emergencia')  { stateLabel = `🚨 ${info.motivo_emergencia || 'EMERGENCIA'}`; color = '#dc2626'; }
    if (info.tipo_estado === 'laborable')   { stateLabel = '✅ DÍA LABORABLE'; color = '#10b981'; }

    document.getElementById('ttState').innerHTML = `<span style="color:${color}">${stateLabel}</span>`;
    document.getElementById('ttNote').textContent  = (info.worker_nombre ? `${info.worker_nombre} - ` : '') + (info.nota || '');
  } else {
    document.getElementById('ttState').innerHTML = `<span class="text-muted">${dow >= 5 ? 'Fin de Semana' : 'Sin marcar'}</span>`;
    document.getElementById('ttNote').textContent = '';
  }

  tooltip.classList.add('visible');
  moveTooltip(e);
}

function moveTooltip(e) {
  const x = e.clientX + 12;
  const y = e.clientY + 12;
  tooltip.style.left = `${Math.min(x, window.innerWidth - 250)}px`;
  tooltip.style.top  = `${Math.min(y, window.innerHeight - 90)}px`;
}

document.addEventListener('mousemove', (e) => {
  if (tooltip.classList.contains('visible')) moveTooltip(e);
});

function hideTooltip() { tooltip.classList.remove('visible'); }

// Búsqueda de meses y fechas
function handleCalendarSearch() {
  const q = document.getElementById('calendarSearchInput').value.trim().toLowerCase();
  const box = document.getElementById('searchSuggestionsBox');
  if (!q) { box.style.display = 'none'; return; }

  const results = [];
  MONTHS_NAMES.forEach((name, idx) => {
    if (name.toLowerCase().includes(q)) {
      results.push({ label: name, index: idx, icon: '📅', sub: 'Ir al mes' });
    }
  });

  const m = q.match(/^(\d{1,2})[\/\-](\d{1,2})$/);
  if (m) {
    const day = parseInt(m[1]);
    const mon = parseInt(m[2]) - 1;
    if (mon >= 0 && mon < 12) {
      results.push({ label: `${day} de ${MONTHS_NAMES[mon]}`, index: mon, day, icon: '🗓️', sub: 'Ir a la fecha' });
    }
  }

  if (!results.length) { box.style.display = 'none'; return; }

  box.innerHTML = results.map(r =>
    `<div class="suggestion-item" onclick="jumpToMonth(${r.index}, ${r.day || 'null'})">
      <span>${r.icon}</span>
      <div>
        <div class="fw-bold">${r.label}</div>
        <div class="small text-muted">${r.sub}</div>
      </div>
    </div>`
  ).join('');
  box.style.display = 'block';
}

function jumpToMonth(mIdx, day) {
  document.getElementById('searchSuggestionsBox').style.display = 'none';
  document.getElementById('calendarSearchInput').value = '';

  const card = document.getElementById(`monthCard-${mIdx}`);
  if (card) {
    card.classList.add('month-highlight');
    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => card.classList.remove('month-highlight'), 2500);
  }
}
</script>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
