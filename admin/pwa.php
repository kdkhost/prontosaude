<?php
require_once('header.php');

// Endpoint AJAX para alternar status do PWA rapidamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_pwa_status') {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    $status = (int)($_POST['status'] ?? 0);
    $stmt = $pdo->prepare("UPDATE tbl_settings SET pwa_status = ? WHERE id = 1");
    $stmt->execute([$status]);
    echo json_encode([
        'success' => true,
        'status' => $status,
        'message' => $status == 1 ? 'Aplicativo PWA ATIVADO no portal!' : 'Aplicativo PWA DESATIVADO no portal!'
    ]);
    exit;
}

$success_message = '';
$error_message = '';

// Atualização de configurações completas do PWA
if (isset($_POST['form_pwa'])) {
    $pwa_status       = isset($_POST['pwa_status']) ? 1 : 0;
    $pwa_app_name     = trim($_POST['pwa_app_name'] ?? 'Pronto Saúde');
    $pwa_short_name   = trim($_POST['pwa_short_name'] ?? 'ProntoSaúde');
    $pwa_description  = trim($_POST['pwa_description'] ?? '');
    $pwa_theme_color  = trim($_POST['pwa_theme_color'] ?? '#0a6b74');
    $pwa_bg_color     = trim($_POST['pwa_bg_color'] ?? '#ffffff');

    // Validações
    if (empty($pwa_app_name)) {
        $error_message = 'O nome do aplicativo é obrigatório.';
    } else {
        // Upload do Ícone 192x192
        $pwa_icon_192 = $row['pwa_icon_192'] ?? '';
        if (isset($_FILES['pwa_icon_192']) && $_FILES['pwa_icon_192']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['pwa_icon_192']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'webp', 'jpg', 'jpeg'])) {
                $newName192 = 'pwa-icon-192-' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['pwa_icon_192']['tmp_name'], '../assets/uploads/' . $newName192)) {
                    $pwa_icon_192 = $newName192;
                }
            }
        }

        // Upload do Ícone 512x512
        $pwa_icon_512 = $row['pwa_icon_512'] ?? '';
        if (isset($_FILES['pwa_icon_512']) && $_FILES['pwa_icon_512']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['pwa_icon_512']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'webp', 'jpg', 'jpeg'])) {
                $newName512 = 'pwa-icon-512-' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['pwa_icon_512']['tmp_name'], '../assets/uploads/' . $newName512)) {
                    $pwa_icon_512 = $newName512;
                }
            }
        }

        try {
            $stmt = $pdo->prepare("UPDATE tbl_settings SET 
                pwa_status = ?,
                pwa_app_name = ?,
                pwa_short_name = ?,
                pwa_description = ?,
                pwa_theme_color = ?,
                pwa_bg_color = ?,
                pwa_icon_192 = ?,
                pwa_icon_512 = ?
                WHERE id = 1");
            $stmt->execute([
                $pwa_status,
                $pwa_app_name,
                $pwa_short_name,
                $pwa_description,
                $pwa_theme_color,
                $pwa_bg_color,
                $pwa_icon_192,
                $pwa_icon_512
            ]);

            $success_message = 'Configurações do Aplicativo PWA salvas com sucesso!';
            
            // Recarrega dados atualizados
            $st = $pdo->query("SELECT * FROM tbl_settings WHERE id = 1");
            $row = $st->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $error_message = 'Erro ao salvar no banco: ' . $e->getMessage();
        }
    }
}

// Valores atuais
$pwa_status      = (int)($row['pwa_status'] ?? 1);
$pwa_app_name    = $row['pwa_app_name'] ?? 'Pronto Saúde | Centro Médico & Odontológico';
$pwa_short_name  = $row['pwa_short_name'] ?? 'Pronto Saúde';
$pwa_description = $row['pwa_description'] ?? 'Aplicativo oficial Pronto Saúde: consultas, exames, corpo clínico e cartão fidelidade.';
$pwa_theme_color = $row['pwa_theme_color'] ?? '#0a6b74';
$pwa_bg_color    = $row['pwa_bg_color'] ?? '#ffffff';
$pwa_icon_192    = $row['pwa_icon_192'] ?? '';
$pwa_icon_512    = $row['pwa_icon_512'] ?? '';

// Fallbacks de ícone caso vazio
if (empty($pwa_icon_192)) {
    $pwa_icon_192_url = BASE_URL . 'assets/uploads/' . ($favicon ?: 'favicon.png');
} else {
    $pwa_icon_192_url = BASE_URL . 'assets/uploads/' . $pwa_icon_192;
}
if (empty($pwa_icon_512)) {
    $pwa_icon_512_url = $pwa_icon_192_url;
} else {
    $pwa_icon_512_url = BASE_URL . 'assets/uploads/' . $pwa_icon_512;
}
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-phone-fill text-primary"></i> Aplicativo PWA (Progressive Web App)
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Aplicativo PWA</li>
                </ol>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASE_URL; ?>manifest.webmanifest.php" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-file-code"></i> Ver Manifesto JSON
                </a>
                <a href="<?= BASE_URL; ?>" target="_blank" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-up-right"></i> Testar no Site
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success bg-success-subtle border-success-subtle text-success-emphasis alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div><?= htmlspecialchars($success_message); ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger bg-danger-subtle border-danger-subtle text-danger-emphasis alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div><?= htmlspecialchars($error_message); ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Card de Status do PWA com Switch Rápido -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-primary-subtle text-primary rounded-circle fs-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-app-indicator"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Status do Aplicativo PWA no Site</h5>
                        <p class="text-body-secondary mb-0 small">
                            Quando ativado, os visitantes podem instalar o site como aplicativo no Android, iOS e Computador.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge <?= $pwa_status == 1 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'; ?> rounded-pill px-3 py-2 fs-6 status-badge" id="badgePwaGlobal">
                        <?= $pwa_status == 1 ? 'PWA Ativado & Operante' : 'PWA Desativado'; ?>
                    </span>
                    <div class="form-check form-switch fs-3 mb-0">
                        <input class="form-check-input pronto-status-switch" type="checkbox" role="switch" data-entity="pwa" data-id="1" data-name="Aplicativo PWA" data-badge-target="#badgePwaGlobal" data-status-type="int" data-active-text="PWA Ativado & Operante" data-inactive-text="PWA Desativado" <?= $pwa_status == 1 ? 'checked' : ''; ?> title="Ligar ou desligar PWA instantaneamente">
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Coluna Principal: Formulário de Configuração -->
            <div class="col-lg-8">
                <div class="card card-outline card-primary shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-body py-3 border-bottom">
                        <h5 class="fw-bold mb-0"><i class="bi bi-sliders me-1 text-primary"></i> Identidade & Parâmetros do App</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="" method="post" enctype="multipart/form-data">
                            
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Nome Completo do Aplicativo (App Name)</label>
                                    <input type="text" class="form-control" name="pwa_app_name" value="<?= htmlspecialchars($pwa_app_name); ?>" required placeholder="Ex: Pronto Saúde | Centro Médico">
                                    <div class="form-text">Exibido na tela de instalação e barra de título.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Nome Curto (Short Name)</label>
                                    <input type="text" class="form-control" name="pwa_short_name" value="<?= htmlspecialchars($pwa_short_name); ?>" required placeholder="Ex: Pronto Saúde">
                                    <div class="form-text">Exibido abaixo do ícone na tela inicial.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Descrição do Aplicativo</label>
                                <textarea class="form-control" name="pwa_description" rows="2" placeholder="Breve resumo das funcionalidades do aplicativo..."><?= htmlspecialchars($pwa_description); ?></textarea>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Cor do Tema (Theme Color)</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="themeColorPicker" value="<?= htmlspecialchars($pwa_theme_color); ?>" title="Escolha a cor">
                                        <input type="text" class="form-control font-monospace" name="pwa_theme_color" id="themeColorText" value="<?= htmlspecialchars($pwa_theme_color); ?>">
                                    </div>
                                    <div class="form-text">Colore a barra de status e topo do navegador móvel.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Cor de Fundo da Abertura (Splash Background)</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="bgColorPicker" value="<?= htmlspecialchars($pwa_bg_color); ?>" title="Escolha a cor">
                                        <input type="text" class="form-control font-monospace" name="pwa_bg_color" id="bgColorText" value="<?= htmlspecialchars($pwa_bg_color); ?>">
                                    </div>
                                    <div class="form-text">Cor exibida durante o carregamento inicial (Splash).</div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h5 class="fw-bold mb-3"><i class="bi bi-images me-1 text-primary"></i> Ícones do Aplicativo</h5>

                            <div class="row mb-4">
                                <!-- Ícone 192x192 -->
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-body-tertiary">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <img src="<?= $pwa_icon_192_url; ?>" id="preview192" alt="Ícone 192" class="rounded-3 shadow-sm border" style="width: 64px; height: 64px; object-fit: cover;">
                                            <div>
                                                <h6 class="fw-bold mb-1">Ícone Médio (192x192)</h6>
                                                <span class="badge bg-secondary-subtle text-secondary border">Telas HD / Launcher</span>
                                            </div>
                                        </div>
                                        <label class="form-label small fw-semibold">Substituir Ícone (PNG / WEBP):</label>
                                        <input type="file" class="form-control form-control-sm" name="pwa_icon_192" accept="image/png,image/webp,image/jpeg" onchange="previewImg(this, '#preview192')">
                                    </div>
                                </div>

                                <!-- Ícone 512x512 -->
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-body-tertiary">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <img src="<?= $pwa_icon_512_url; ?>" id="preview512" alt="Ícone 512" class="rounded-3 shadow-sm border" style="width: 64px; height: 64px; object-fit: cover;">
                                            <div>
                                                <h6 class="fw-bold mb-1">Ícone Grande (512x512)</h6>
                                                <span class="badge bg-primary-subtle text-primary border">Splash Screen / Splash</span>
                                            </div>
                                        </div>
                                        <label class="form-label small fw-semibold">Substituir Ícone (PNG / WEBP):</label>
                                        <input type="file" class="form-control form-control-sm" name="pwa_icon_512" accept="image/png,image/webp,image/jpeg" onchange="previewImg(this, '#preview512')">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                                <div class="form-check form-switch fs-5 mb-0">
                                    <input class="form-check-input" type="checkbox" name="pwa_status" value="1" id="pwaStatusForm" <?= $pwa_status == 1 ? 'checked' : ''; ?>>
                                    <label class="form-check-label fs-6 fw-semibold ms-2" for="pwaStatusForm">Habilitar PWA no site</label>
                                </div>
                                <button type="submit" name="form_pwa" class="btn btn-primary px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-save2"></i> Salvar Configurações
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral: Mockup & Auditoria do PWA -->
            <div class="col-lg-4">
                
                <!-- Mockup de Smartphone com App Instalado -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-body py-3 border-bottom">
                        <h6 class="fw-bold mb-0"><i class="bi bi-phone me-1 text-primary"></i> Pré-visualização na Tela Inicial</h6>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div class="p-4 rounded-4 shadow-sm mx-auto position-relative" style="max-width: 280px; background: #0f172a; color: #fff; border: 4px solid #334155;">
                            <!-- Barra de Notificações do Smartphone -->
                            <div class="d-flex justify-content-between align-items-center small text-white-50 mb-4 px-1" style="font-size: 0.75rem;">
                                <span>09:41</span>
                                <div class="d-flex gap-1">
                                    <i class="bi bi-wifi"></i>
                                    <i class="bi bi-battery-full"></i>
                                </div>
                            </div>

                            <!-- Ícone do App na Tela Inicial -->
                            <div class="d-inline-flex flex-column align-items-center p-3 rounded-4" style="background: rgba(255,255,255,0.06);">
                                <img src="<?= $pwa_icon_192_url; ?>" id="mockupIcon" alt="App" class="rounded-3 shadow mb-2" style="width: 60px; height: 60px; object-fit: cover;">
                                <span class="fw-bold text-white text-truncate small" style="max-width: 100px;" id="mockupTitle"><?= htmlspecialchars($pwa_short_name); ?></span>
                            </div>

                            <!-- Botão Simulado de Ação -->
                            <div class="mt-4 pt-3 border-top border-secondary">
                                <span class="badge bg-success-subtle text-success small border border-success-subtle">
                                    <i class="bi bi-shield-check"></i> Pronto para Instalar
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card de Vantagens do PWA -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-body py-3 border-bottom">
                        <h6 class="fw-bold mb-0"><i class="bi bi-stars me-1 text-warning"></i> Benefícios do PWA</h6>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small">
                            <li class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success fs-6 mt-1"></i>
                                <div><strong>Instalação sem Lojas:</strong> O usuário adiciona à tela inicial direto do Chrome, Safari ou Edge sem precisar de Google Play ou App Store.</div>
                            </li>
                            <li class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success fs-6 mt-1"></i>
                                <div><strong>Abertura Ultrarrápida:</strong> Carregamento instantâneo via Service Worker inteligente com cache em disco.</div>
                            </li>
                            <li class="d-flex gap-2">
                                <i class="bi bi-check-circle-fill text-success fs-6 mt-1"></i>
                                <div><strong>Tela Cheia (Standalone):</strong> Remove as barras de endereço do navegador, comportando-se exatamente como um aplicativo nativo.</div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
function previewImg(input, target) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector(target).src = e.target.result;
            if (target === '#preview192') {
                const mockup = document.querySelector('#mockupIcon');
                if (mockup) mockup.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Sincronização em tempo real de seletores de cor
document.addEventListener('DOMContentLoaded', function() {
    const themePicker = document.getElementById('themeColorPicker');
    const themeText = document.getElementById('themeColorText');
    if (themePicker && themeText) {
        themePicker.addEventListener('input', () => themeText.value = themePicker.value);
        themeText.addEventListener('input', () => themePicker.value = themeText.value);
    }

    const bgPicker = document.getElementById('bgColorPicker');
    const bgText = document.getElementById('bgColorText');
    if (bgPicker && bgText) {
        bgPicker.addEventListener('input', () => bgText.value = bgPicker.value);
        bgText.addEventListener('input', () => bgPicker.value = bgText.value);
    }
});
</script>

<?php require_once('footer.php'); ?>
