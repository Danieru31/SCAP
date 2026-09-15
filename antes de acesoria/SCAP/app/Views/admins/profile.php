<?php require VIEWS_PATH . '/layout/header.php'; ?>

<!--Editar Perfil-->

<div class="page-header">
    <div>
        <h1 class="page-title">Editar Perfil</h1>
        <p class="page-subtitle">Configure sus credenciales de acceso y preguntas de seguridad</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-xl-6">
        <div class="card-panel p-4">
            
            <form id="profileForm" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_token) ?>">

                <!-- Foto de Perfil -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="text-primary small fw-bold text-uppercase mb-3">Foto de Perfil</h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="profile-avatar-box">
                            <?php if (!empty($admin['foto']) && is_file(ROOT_PATH . '/public/uploads/avatars/' . $admin['foto'])): ?>
                                <img id="avatarPreview" src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars($admin['foto']) ?>" alt="Foto Perfil" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #6366f1;">
                            <?php else: ?>
                                <img id="avatarPreview" src="<?= BASE_URL ?>/public/images/logo.jpg" alt="Foto Perfil" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #6366f1;" onerror="this.src='<?= BASE_URL ?>/public/images/azul1.jpg'">
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <label class="form-label-custom mb-1" for="fotoPerfil">Nueva Foto de Perfil</label>
                            <input type="file" name="foto" id="fotoPerfil" class="form-control" accept="image/png, image/jpeg" onchange="previewProfileAvatar(this)">
                            <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>Formatos permitidos: <strong>JPG</strong> y <strong>PNG</strong>.</small>
                        </div>
                    </div>
                </div>

                <!-- Contraseñas -->
                <div class="mb-4">
                    <h5 class="text-primary small fw-bold text-uppercase mb-3">Actualizar Contraseña</h5>

                    
                    <div class="mb-3">
                        <label class="form-label-custom" for="pCurrentPassword">Contraseña Actual <span class="text-danger">*</span></label>
                        <div class="password-wrap">
                            <input type="password" name="current_password" id="pCurrentPassword" 
                                   class="form-control-custom" placeholder="Contraseña actual por seguridad" required>
                            <button type="button" class="toggle-pass-modal" id="toggleCurrentPass" tabindex="-1">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom" for="pNewPassword">Nueva Contraseña <span class="form-hint">(dejar en blanco para no cambiar)</span></label>
                            <div class="password-wrap">
                                <input type="password" name="new_password" id="pNewPassword" 
                                       class="form-control-custom" placeholder="Mínimo 6 caracteres">
                                <button type="button" class="toggle-pass-modal" id="toggleNewPass" tabindex="-1">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom" for="pConfirmPassword">Confirmar Nueva Contraseña</label>
                            <div class="password-wrap">
                                <input type="password" name="confirm_password" id="pConfirmPassword" 
                                       class="form-control-custom" placeholder="Repita la nueva contraseña">
                                <button type="button" class="toggle-pass-modal" id="toggleConfirmPass" tabindex="-1">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preguntas de seguridad -->
                <div class="mb-4 pt-3 border-top">
                    <h5 class="text-primary small fw-bold text-uppercase mb-3">Preguntas de Seguridad</h5>
                    
                    <!-- Pregunta #1 -->
                    <div class="mb-3">
                        <label class="form-label-custom" for="pPregunta1">Pregunta de Seguridad 1 <span class="text-danger">*</span></label>
                        <input type="text" name="pregunta_1" id="pPregunta1" 
                               class="form-control-custom" placeholder="Ej: ¿Nombre de tu primera mascota?" 
                               value="<?= htmlspecialchars($admin['pregunta_1'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom" for="pRespuesta1">
                            Respuesta de Seguridad 1 <span class="form-hint">(dejar en blanco para mantener la actual)</span>
                        </label>
                        <input type="text" name="respuesta_1" id="pRespuesta1" 
                               class="form-control-custom" placeholder="Respuesta a la pregunta 1">
                    </div>

                    <!-- Pregunta #2 -->
                    <div class="mb-3 pt-2 border-top-dashed">
                        <label class="form-label-custom" for="pPregunta2">Pregunta de Seguridad 2 <span class="text-danger">*</span></label>
                        <input type="text" name="pregunta_2" id="pPregunta2" 
                               class="form-control-custom" placeholder="Ej: ¿Ciudad de tu primer empleo?" 
                               value="<?= htmlspecialchars($admin['pregunta_2'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom" for="pRespuesta2">
                            Respuesta de Seguridad 2 <span class="form-hint">(dejar en blanco para mantener la actual)</span>
                        </label>
                        <input type="text" name="respuesta_2" id="pRespuesta2" 
                               class="form-control-custom" placeholder="Respuesta a la pregunta 2">
                    </div>
                </div>

                <div class="alert alert-danger d-none" id="profileError"></div>

                <div class="pt-2 border-top d-flex justify-content-end">
                    <button type="button" class="btn-action btn-primary-action" id="btnSaveProfile">
                        <span class="btn-text"><i class="bi bi-save2-fill me-1"></i> Guardar Cambios</span>
                        <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm"></span> Guardando...</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Togle de visibilidad de contraseñas
    const bindToggle = (inputName, btnId) => {
        const passInput  = document.getElementById(inputName);
        const toggleBtn  = document.getElementById(btnId);
        if (!passInput || !toggleBtn) return;
        
        toggleBtn.addEventListener('click', () => {
            const isPass = passInput.type === 'password';
            passInput.type = isPass ? 'text' : 'password';
            toggleBtn.querySelector('i').className = isPass ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
        });
    };

    bindToggle('pCurrentPassword', 'toggleCurrentPass');
    bindToggle('pNewPassword', 'toggleNewPass');
    bindToggle('pConfirmPassword', 'toggleConfirmPass');

    // Guardar cambios vía ajax
    const saveBtn = document.getElementById('btnSaveProfile');
    const form    = document.getElementById('profileForm');
    const errorEl = document.getElementById('profileError');

    saveBtn?.addEventListener('click', async () => {
        errorEl.classList.add('d-none');
        errorEl.textContent = '';

        const fd = buildForm(form);
        btnLoading(saveBtn, true);

        const data = await apiFetch(`${window.SCAP?.baseUrl}/perfil/actualizar`, {
            method: 'POST',
            body: fd
        });

        btnLoading(saveBtn, false);

        if (data.success) {
            showToast(data.message, 'success');
            // Limpiar claves
            document.getElementById('pCurrentPassword').value = '';
            document.getElementById('pNewPassword').value = '';
            document.getElementById('pConfirmPassword').value = '';
            document.getElementById('pRespuesta1').value = '';
            document.getElementById('pRespuesta2').value = '';
        } else {
            errorEl.textContent = data.message;
            errorEl.classList.remove('d-none');
            showToast(data.message, 'error');
        }
    });
});
function previewProfileAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            if (preview) preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<style>
.border-top-dashed {
    border-top: 1px dashed var(--border) !important;
}
</style>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>

