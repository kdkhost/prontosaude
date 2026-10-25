<?php
ob_start();
session_start();
require_once('config.php');

// Endpoint AJAX para alternar status do Popup rapidamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_popup_status') {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
        exit;
    }
    $status = (int)($_POST['status'] ?? 0);
    $stmt = $pdo->prepare("UPDATE tbl_settings SET popup_status = ? WHERE id = 1");
    $stmt->execute([$status]);
    echo json_encode([
        'success' => true,
        'status' => $status,
        'message' => $status == 1 ? 'Banner de Popup ATIVADO no portal!' : 'Banner de Popup DESATIVADO no portal!'
    ]);
    exit;
}

require_once('header.php');

$success_message = '';
$error_message = '';

// Atualização de configurações completas do Popup
if (isset($_POST['form_popup'])) {
    $popup_status        = isset($_POST['popup_status']) ? 1 : 0;
    $popup_title         = trim($_POST['popup_title'] ?? '');
    $popup_text          = trim($_POST['popup_text'] ?? '');
    $popup_button_text   = trim($_POST['popup_button_text'] ?? '');
    $popup_button_url    = trim($_POST['popup_button_url'] ?? '');
    $popup_delay         = max(500, (int)($_POST['popup_delay'] ?? 2000));
    $popup_expire_hours  = max(1, (int)($_POST['popup_expire_hours'] ?? 24));

    $popup_image = $row['popup_image'] ?? '';
    if (isset($_FILES['popup_image']) && $_FILES['popup_image']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['popup_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
            $newImgName = 'popup-banner-' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['popup_image']['tmp_name'], '../assets/uploads/' . $newImgName)) {
                $popup_image = $newImgName;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE tbl_settings SET 
            popup_status = ?,
            popup_title = ?,
            popup_text = ?,
            popup_image = ?,
            popup_button_text = ?,
            popup_button_url = ?,
            popup_delay = ?,
            popup_expire_hours = ?
            WHERE id = 1");
        $stmt->execute([
            $popup_status,
            $popup_title,
            $popup_text,
            $popup_image,
            $popup_button_text,
            $popup_button_url,
            $popup_delay,
            $popup_expire_hours
        ]);

        $success_message = 'Configurações do Banner de Popup salvas com sucesso!';
        
        $st = $pdo->query("SELECT * FROM tbl_settings WHERE id = 1");
        $row = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error_message = 'Erro ao salvar: ' . $e->getMessage();
    }
}

// Valores atuais
$popup_status       = (int)($row['popup_status'] ?? 0);
$popup_title        = $row['popup_title'] ?? 'Novidades & Promoções Exclusivas';
$popup_text         = $row['popup_text'] ?? 'Conheça nossos planos fidelidade com até 50% de desconto em consultas e exames especializados.';
$popup_image        = $row['popup_image'] ?? '';
$popup_button_text  = $row['popup_button_text'] ?? 'Aproveitar Benefícios';
$popup_button_url   = $row['popup_button_url'] ?? BASE_URL . 'fidelidade.php';
$popup_delay        = (int)($row['popup_delay'] ?? 2000);
$popup_expire_hours = (int)($row['popup_expire_hours'] ?? 24);

$popup_image_url = !empty($popup_image) ? BASE_URL . 'assets/uploads/' . $popup_image : BASE_URL . 'assets/images/about.jpg';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-window-stack text-primary"></i> Banner de Popup Promocional & Avisos
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Banner de Popup</li>
                </ol>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASE_URL; ?>" target="_blank" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-up-right"></i> Visualizar no Site
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

        <!-- Card de Status Rápido do Popup -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-primary-subtle text-primary rounded-circle fs-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Status do Popup no Site</h5>
                        <p class="text-body-secondary mb-0 small">
                            Ative ou desative campanhas, avisos de feriados e promoções instantaneamente na Home do portal.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge <?= $popup_status == 1 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle'; ?> rounded-pill px-3 py-2 fs-6 status-badge" id="badgePopupGlobal">
                        <?= $popup_status == 1 ? 'Popup Ativo no Site' : 'Popup Desativado'; ?>
                    </span>
                    <div class="form-check form-switch fs-3 mb-0">
                        <input class="form-check-input pronto-status-switch" type="checkbox" role="switch" data-entity="popup" data-id="1" data-name="Banner Popup" data-badge-target="#badgePopupGlobal" data-status-type="int" data-active-text="Popup Ativo no Site" data-inactive-text="Popup Desativado" <?= $popup_status == 1 ? 'checked' : ''; ?> title="Ativar ou desativar o popup na home">
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Formulário -->
            <div class="col-lg-7">
                <div class="card card-outline card-primary shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-body py-3 border-bottom">
                        <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square me-1 text-primary"></i> Conteúdo & Comportamento do Popup</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="" method="post" enctype="multipart/form-data">
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Título Principal</label>
                                <input type="text" class="form-control" name="popup_title" id="inputPopupTitle" value="<?= htmlspecialchars($popup_title); ?>" placeholder="Ex: Campanha de Vacinação / Desconto Especial">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mensagem / Descrição</label>
                                <textarea class="form-control" name="popup_text" id="inputPopupText" rows="3" placeholder="Texto explicativo para engajar o visitante..."><?= htmlspecialchars($popup_text); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Imagem / Banner Ilustrativo</label>
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <img src="<?= $popup_image_url; ?>" id="previewPopupImg" alt="Popup" class="rounded-3 shadow-sm border" style="width: 120px; height: 80px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <input type="file" class="form-control form-control-sm" name="popup_image" accept="image/*" onchange="previewImg(this, '#previewPopupImg')">
                                        <div class="form-text small">Recomendado: 600x400 pixels em JPG, PNG ou WEBP.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Texto do Botão (CTA)</label>
                                    <input type="text" class="form-control" name="popup_button_text" id="inputPopupBtnText" value="<?= htmlspecialchars($popup_button_text); ?>" placeholder="Ex: Agendar Agora">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Link de Destino do Botão</label>
                                    <input type="text" class="form-control" name="popup_button_url" id="inputPopupBtnUrl" value="<?= htmlspecialchars($popup_button_url); ?>" placeholder="Ex: https://wa.me/... ou fidelidade.php">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tempo de Abertura (Delay)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="popup_delay" value="<?= $popup_delay; ?>" min="500" step="500">
                                        <span class="input-group-text">ms</span>
                                    </div>
                                    <div class="form-text">2000 ms = 2 segundos após a página carregar.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Frequência de Exibição (Cookies)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="popup_expire_hours" value="<?= $popup_expire_hours; ?>" min="1">
                                        <span class="input-group-text">horas</span>
                                    </div>
                                    <div class="form-text">Tempo para exibir novamente após o usuário fechar.</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                                <div class="form-check form-switch fs-5 mb-0">
                                    <input class="form-check-input" type="checkbox" name="popup_status" value="1" id="popupStatusForm" <?= $popup_status == 1 ? 'checked' : ''; ?>>
                                    <label class="form-check-label fs-6 fw-semibold ms-2" for="popupStatusForm">Ativar Popup no site</label>
                                </div>
                                <button type="submit" name="form_popup" class="btn btn-primary px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-save2"></i> Salvar Configurações
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral: Preview em Tempo Real do Modal -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-body py-3 border-bottom">
                        <h6 class="fw-bold mb-0"><i class="bi bi-eye me-1 text-primary"></i> Simulação Ao Vivo do Popup</h6>
                    </div>
                    <div class="card-body p-4 bg-body-tertiary">
                        <!-- Mockup Modal Visual -->
                        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mx-auto" style="max-width: 380px;">
                            <div class="position-relative">
                                <img src="<?= $popup_image_url; ?>" id="mockupPopupImg" alt="Banner" class="w-100" style="height: 180px; object-fit: cover;">
                                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 bg-white p-2 shadow-sm rounded-circle" style="opacity: 0.9;" disabled></button>
                            </div>
                            <div class="card-body p-4 text-center">
                                <h5 class="fw-bold mb-2 text-dark" id="mockupPopupTitle"><?= htmlspecialchars($popup_title); ?></h5>
                                <p class="text-body-secondary small mb-4" id="mockupPopupText"><?= htmlspecialchars($popup_text); ?></p>
                                <a href="javascript:void(0)" class="btn btn-primary w-100 py-2 fw-semibold rounded-pill shadow-sm" id="mockupPopupBtn">
                                    <?= htmlspecialchars($popup_button_text); ?> <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
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
            const mockup = document.querySelector('#mockupPopupImg');
            if (mockup) mockup.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Atualização de inputs no mockup
document.addEventListener('DOMContentLoaded', function() {
    const inTitle = document.getElementById('inputPopupTitle');
    const inText = document.getElementById('inputPopupText');
    const inBtn = document.getElementById('inputPopupBtnText');

    const mkTitle = document.getElementById('mockupPopupTitle');
    const mkText = document.getElementById('mockupPopupText');
    const mkBtn = document.getElementById('mockupPopupBtn');

    if (inTitle && mkTitle) inTitle.addEventListener('input', () => mkTitle.textContent = inTitle.value || 'Título do Popup');
    if (inText && mkText) inText.addEventListener('input', () => mkText.textContent = inText.value || 'Mensagem do popup...');
    if (inBtn && mkBtn) inBtn.addEventListener('input', () => mkBtn.innerHTML = (inBtn.value || 'Acessar') + ' <i class="bi bi-arrow-right ms-1"></i>');
});
</script>

<?php require_once('footer.php'); ?>
