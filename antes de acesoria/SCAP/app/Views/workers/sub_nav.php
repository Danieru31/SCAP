<?php
///Submenú de Navegación de Personal Obrero

$currentSubTab = $currentSubTab ?? 'caracteristicas';
?>
<div class="worker-submenu-bar mb-4">
    <div class="nav nav-pills custom-subnav-pills" role="tablist">
        <a href="<?= BASE_URL ?>/obreros/caracteristicas" 
           class="nav-link custom-subnav-link <?= $currentSubTab === 'caracteristicas' ? 'active' : '' ?>">
            <i class="bi bi-person-vcard-fill me-2"></i>Características del Personal
        </a>
        <a href="<?= BASE_URL ?>/obreros/horas-trabajo" 
           class="nav-link custom-subnav-link <?= $currentSubTab === 'horas' ? 'active' : '' ?>">
            <i class="bi bi-clock-history me-2"></i>Horas de Trabajo
        </a>
    </div>
</div>

<style>
.worker-submenu-bar {
    background: #ffffff;
    border-radius: 12px;
    padding: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    border: 1px solid #e2e8f0;
}
.custom-subnav-pills {
    display: flex;
    gap: 8px;
}
.custom-subnav-link {
    font-weight: 600;
    font-size: 0.95rem;
    color: #64748b;
    padding: 10px 20px;
    border-radius: 8px !important;
    transition: all 0.25s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}
.custom-subnav-link:hover {
    color: #4f46e5;
    background-color: #f1f5f9;
}
.custom-subnav-link.active {
    color: #ffffff !important;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
}
</style>
