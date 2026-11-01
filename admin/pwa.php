<?php
ob_start();
session_start();
require_once('config.php');

// Garante colunas de suporte ao Banner PWA na tabela tbl_settings de forma transparente
$pwa_new_cols = [
    'pwa_banner_status'    => 'TINYINT(1) DEFAULT 1',
    'pwa_banner_delay'     => 'INT DEFAULT 3',
    'pwa_banner_cooldown'  => 'INT DEFAULT 7',
    'pwa_banner_title'     => 'VARCHAR(255) NULL',
    'pwa_banner_desc'      => 'TEXT NULL',
    'pwa_banner_btn_text'  => 'VARCHAR(100) NULL',
    'pwa_banner_position'  => 'VARCHAR(50) NULL',
    'pwa_banner_ios_guide' => 'TINYINT(1) DEFAULT 1'
];
foreach ($pwa_new_cols as $c => $d) {
    try {
        $pdo->query("SELECT {$c} FROM tbl_settings LIMIT 1");
    } catch (Exception $e) {
        try {
            $pdo->exec("ALTER TABLE tbl_settings ADD COLUMN {$c} {$d}");
        } catch (Exception $ex) {}
    }
}

// Endpoint AJAX para alternar status do PWA rapidamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_pwa_status') {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
        exit;
    }
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

// Endpoint AJAX para alternar status do Banner de Instalação PWA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_pwa_banner_status') {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
        exit;
    }
    $status = (int)($_POST['status'] ?? 0);
    $stmt = $pdo->prepare("UPDATE tbl_settings SET pwa_banner_status = ? WHERE id = 1");
    $stmt->execute([$status]);
    echo json_encode([
        'success' => true,
        'status' => $status,
        'message' => $status == 1 ? 'Banner de Instalação PWA ATIVADO no site!' : 'Banner de Instalação PWA DESATIVADO no site!'
    ]);
    exit;
}

require_once('header.php');

$success_message = '';
$error_message = '';

// Atualização de configurações completas do PWA e do Banner
if (isset($_POST['form_pwa'])) {
    $pwa_status          = isset($_POST['pwa_status']) ? 1 : 0;
    $pwa_app_name        = trim($_POST['pwa_app_name'] ?? 'Pronto Saúde');
    $pwa_short_name      = trim($_POST['pwa_short_name'] ?? 'ProntoSaúde');
    $pwa_description     = trim($_POST['pwa_description'] ?? '');
    $pwa_theme_color     = trim($_POST['pwa_theme_color'] ?? '#0a6b74');
    $pwa_bg_color        = trim($_POST['pwa_bg_color'] ?? '#ffffff');

    // Configurações do Banner de Instalação
    $pwa_banner_status   = isset($_POST['pwa_banner_status']) ? 1 : 0;
    $pwa_banner_delay    = max(0, min(60, (int)($_POST['pwa_banner_delay'] ?? 3)));
    $pwa_banner_cooldown = max(0, min(90, (int)($_POST['pwa_banner_cooldown'] ?? 7)));
    $pwa_banner_title    = trim($_POST['pwa_banner_title'] ?? 'Instale o App Pronto Saúde');
    $pwa_banner_desc     = trim($_POST['pwa_banner_desc'] ?? '');
    $pwa_banner_btn_text = trim($_POST['pwa_banner_btn_text'] ?? 'Instalar Aplicativo Grátis');
    $pwa_banner_position = in_array($_POST['pwa_banner_position'] ?? '', ['bottom-right', 'bottom-left', 'bottom-center', 'bottom-bar']) ? $_POST['pwa_banner_position'] : 'bottom-right';
    $pwa_banner_ios_guide= isset($_POST['pwa_banner_ios_guide']) ? 1 : 0;

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
                pwa_icon_512 = ?,
                pwa_banner_status = ?,
                pwa_banner_delay = ?,
                pwa_banner_cooldown = ?,
                pwa_banner_title = ?,
                pwa_banner_desc = ?,
                pwa_banner_btn_text = ?,
                pwa_banner_position = ?,
                pwa_banner_ios_guide = ?
                WHERE id = 1");
            $stmt->execute([
                $pwa_status,
                $pwa_app_name,
                $pwa_short_name,
                $pwa_description,
                $pwa_theme_color,
                $pwa_bg_color,
                $pwa_icon_192,
                $pwa_icon_512,
                $pwa_banner_status,
                $pwa_banner_delay,
                $pwa_banner_cooldown,
                $pwa_banner_title,
                $pwa_banner_desc,
                $pwa_banner_btn_text,
                $pwa_banner_position,
                $pwa_banner_ios_guide
            ]);

            $success_message = 'Configurações do PWA e Banner de Instalação salvas com sucesso!';
            
            // Recarrega dados atualizados
            $st = $pdo->query("SELECT * FROM tbl_settings WHERE id = 1");
            $row = $st->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $error_message = 'Erro ao salvar no banco: ' . $e->getMessage();
        }
    }
}

// Valores atuais para a interface
$pwa_status          = (int)($row['pwa_status'] ?? 1);
$pwa_app_name        = $row['pwa_app_name'] ?? 'Pronto Saúde | Centro Médico & Odontológico';
$pwa_short_name      = $row['pwa_short_name'] ?? 'Pronto Saúde';
$pwa_description     = $row['pwa_description'] ?? 'Aplicativo oficial Pronto Saúde: consultas, exames, corpo clínico e cartão fidelidade.';
$pwa_theme_color     = $row['pwa_theme_color'] ?? '#0a6b74';
$pwa_bg_color        = $row['pwa_bg_color'] ?? '#ffffff';
$pwa_icon_192        = $row['pwa_icon_192'] ?? '';
$pwa_icon_512        = $row['pwa_icon_512'] ?? '';

// Configurações do Banner
$pwa_banner_status   = (int)($row['pwa_banner_status'] ?? 1);
$pwa_banner_delay    = (int)($row['pwa_banner_delay'] ?? 3);
$pwa_banner_cooldown = (int)($row['pwa_banner_cooldown'] ?? 7);
$pwa_banner_title    = !empty($row['pwa_banner_title']) ? $row['pwa_banner_title'] : 'Instale o App Pronto Saúde';
$pwa_banner_desc     = !empty($row['pwa_banner_desc']) ? $row['pwa_banner_desc'] : 'Agendamento rápido em 1 minuto, carteirinha digital e histórico de consultas sem ocupar memória do aparelho.';
$pwa_banner_btn_text = !empty($row['pwa_banner_btn_text']) ? $row['pwa_banner_btn_text'] : 'Instalar Aplicativo Grátis';
$pwa_banner_position = !empty($row['pwa_banner_position']) ? $row['pwa_banner_position'] : 'bottom-right';
$pwa_banner_ios_guide= (int)($row['pwa_banner_ios_guide'] ?? 1);

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
                    <i class="bi bi-phone-fill text-primary"></i> Aplicativo PWA & Banner de Instalação
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

        <!-- Cards Superiores de Status com Switches Rápidos -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Status Global do PWA -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-3 bg-primary-subtle text-primary rounded-circle fs-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                <i class="bi bi-phone"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Status Global do Aplicativo PWA</h6>
                                <p class="text-body-secondary mb-0 small">Habilita manifesto, offline e suporte a instalação no Android, iOS e PC.</p>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge <?= $pwa_status == 1 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'; ?> rounded-pill px-3 py-1 mb-2 d-inline-block small" id="badgePwaGlobal">
                                <?= $pwa_status == 1 ? 'PWA Ativado' : 'PWA Desativado'; ?>
                            </span>
                            <div class="form-check form-switch fs-4 mb-0 d-flex justify-content-end">
                                <input class="form-check-input pronto-status-switch" type="checkbox" role="switch" data-entity="pwa" data-id="1" data-name="Aplicativo PWA" data-badge-target="#badgePwaGlobal" data-status-type="int" data-active-text="PWA Ativado" data-inactive-text="PWA Desativado" <?= $pwa_status == 1 ? 'checked' : ''; ?> title="Ligar ou desligar PWA">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Status do Banner Inteligente de Instalação -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-3 bg-success-subtle text-success rounded-circle fs-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                <i class="bi bi-badge-ad"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Banner Moderno de Instalação</h6>
                                <p class="text-body-secondary mb-0 small">Substitui o popup feio padrão do navegador por um banner bonito e intuitivo.</p>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge <?= $pwa_banner_status == 1 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle'; ?> rounded-pill px-3 py-1 mb-2 d-inline-block small" id="badgePwaBanner">
                                <?= $pwa_banner_status == 1 ? 'Banner Visível' : 'Banner Oculto'; ?>
                            </span>
                            <div class="form-check form-switch fs-4 mb-0 d-flex justify-content-end">
                                <input class="form-check-input switch-pwa-banner" type="checkbox" role="switch" id="switchBannerQuick" <?= $pwa_banner_status == 1 ? 'checked' : ''; ?> title="Ligar ou desligar Banner no site">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="" method="post" enctype="multipart/form-data" id="formPwaConfig">
            <div class="row">
                <!-- Coluna Principal: Formulário Completo -->
                <div class="col-lg-7">

                    <!-- SEÇÃO 1: Banner Inteligente de Instalação (FOCO PRINCIPAL) -->
                    <div class="card card-outline card-success shadow-sm rounded-4 mb-4 border-top border-4 border-success">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 text-success d-flex align-items-center gap-2">
                                <i class="bi bi-stars"></i> Banner Personalizado de Instalação
                            </h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Design Hospitalar</span>
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- Switch de Ativação do Banner -->
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-body-tertiary mb-3 border">
                                <div>
                                    <h6 class="fw-bold mb-1">Exibir Banner de Instalação no Site</h6>
                                    <span class="text-muted small">Quando ativo, o banner elegante é disparado automaticamente respeitando o tempo configurado.</span>
                                </div>
                                <div class="form-check form-switch fs-4 mb-0">
                                    <input class="form-check-input" type="checkbox" name="pwa_banner_status" value="1" id="pwaBannerStatusForm" <?= $pwa_banner_status == 1 ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <!-- Configuração de Tempo (Delay) e Frequência (Cooldown) -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold d-flex align-items-center gap-1">
                                        <i class="bi bi-stopwatch text-primary"></i> Tempo de Espera (Delay em Segundos)
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="pwa_banner_delay" id="inputBannerDelay" min="0" max="60" value="<?= $pwa_banner_delay; ?>" required>
                                        <span class="input-group-text">segundos</span>
                                    </div>
                                    <div class="form-text">Tempo após o carregamento da página para exibir o banner (ex: 3s).</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold d-flex align-items-center gap-1">
                                        <i class="bi bi-arrow-repeat text-primary"></i> Frequência de Reexibição (Cooldown)
                                    </label>
                                    <select class="form-select" name="pwa_banner_cooldown" id="selectBannerCooldown">
                                        <option value="0" <?= $pwa_banner_cooldown == 0 ? 'selected' : ''; ?>>A cada nova sessão do visitante (0 dias)</option>
                                        <option value="1" <?= $pwa_banner_cooldown == 1 ? 'selected' : ''; ?>>1 dia após fechar</option>
                                        <option value="3" <?= $pwa_banner_cooldown == 3 ? 'selected' : ''; ?>>3 dias após fechar</option>
                                        <option value="7" <?= $pwa_banner_cooldown == 7 ? 'selected' : ''; ?>>7 dias após fechar (Recomendado)</option>
                                        <option value="14" <?= $pwa_banner_cooldown == 14 ? 'selected' : ''; ?>>14 dias após fechar</option>
                                        <option value="30" <?= $pwa_banner_cooldown == 30 ? 'selected' : ''; ?>>30 dias após fechar</option>
                                    </select>
                                    <div class="form-text">Evita incomodar quem já dispensou o banner.</div>
                                </div>
                            </div>

                            <!-- Posição do Banner -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold d-flex align-items-center gap-1">
                                    <i class="bi bi-layout-sidebar-inset-reverse text-primary"></i> Posição na Tela do Usuário
                                </label>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <label class="p-2 border rounded-3 w-100 d-flex align-items-center gap-2 cursor-pointer <?= $pwa_banner_position === 'bottom-right' ? 'border-primary bg-primary-subtle' : 'bg-body'; ?>">
                                            <input type="radio" name="pwa_banner_position" value="bottom-right" <?= $pwa_banner_position === 'bottom-right' ? 'checked' : ''; ?> class="form-check-input mt-0 position-radio">
                                            <div class="small">
                                                <strong>Canto Inferior Direito</strong>
                                                <div class="text-muted" style="font-size: 0.75rem;">Flutuante padrão desktop</div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="p-2 border rounded-3 w-100 d-flex align-items-center gap-2 cursor-pointer <?= $pwa_banner_position === 'bottom-left' ? 'border-primary bg-primary-subtle' : 'bg-body'; ?>">
                                            <input type="radio" name="pwa_banner_position" value="bottom-left" <?= $pwa_banner_position === 'bottom-left' ? 'checked' : ''; ?> class="form-check-input mt-0 position-radio">
                                            <div class="small">
                                                <strong>Canto Inferior Esquerdo</strong>
                                                <div class="text-muted" style="font-size: 0.75rem;">Flutuante lado esquerdo</div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="p-2 border rounded-3 w-100 d-flex align-items-center gap-2 cursor-pointer <?= $pwa_banner_position === 'bottom-center' ? 'border-primary bg-primary-subtle' : 'bg-body'; ?>">
                                            <input type="radio" name="pwa_banner_position" value="bottom-center" <?= $pwa_banner_position === 'bottom-center' ? 'checked' : ''; ?> class="form-check-input mt-0 position-radio">
                                            <div class="small">
                                                <strong>Centro Inferior</strong>
                                                <div class="text-muted" style="font-size: 0.75rem;">Centralizado na base</div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="p-2 border rounded-3 w-100 d-flex align-items-center gap-2 cursor-pointer <?= $pwa_banner_position === 'bottom-bar' ? 'border-primary bg-primary-subtle' : 'bg-body'; ?>">
                                            <input type="radio" name="pwa_banner_position" value="bottom-bar" <?= $pwa_banner_position === 'bottom-bar' ? 'checked' : ''; ?> class="form-check-input mt-0 position-radio">
                                            <div class="small">
                                                <strong>Barra Inferior Fixa</strong>
                                                <div class="text-muted" style="font-size: 0.75rem;">Estilo rodapé moderno</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Textos do Banner -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Título Principal do Banner</label>
                                <input type="text" class="form-control" name="pwa_banner_title" id="inputBannerTitle" value="<?= htmlspecialchars($pwa_banner_title); ?>" required placeholder="Ex: Instale o App Pronto Saúde">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Descrição / Benefícios do Aplicativo</label>
                                <textarea class="form-control" name="pwa_banner_desc" id="inputBannerDesc" rows="3" placeholder="Destaque as principais vantagens para o paciente..."><?= htmlspecialchars($pwa_banner_desc); ?></textarea>
                                <div class="form-text">Destaque agilidade, carteirinha digital, exames e economia de memória.</div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-7">
                                    <label class="form-label fw-semibold">Texto do Botão de Instalação</label>
                                    <input type="text" class="form-control" name="pwa_banner_btn_text" id="inputBannerBtnText" value="<?= htmlspecialchars($pwa_banner_btn_text); ?>" required placeholder="Ex: Instalar Aplicativo Grátis">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Guia Visual iPhone (iOS)</label>
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input" type="checkbox" name="pwa_banner_ios_guide" value="1" id="pwaBannerIosGuide" <?= $pwa_banner_ios_guide == 1 ? 'checked' : ''; ?>>
                                        <label class="form-check-label small" for="pwaBannerIosGuide">Mostrar guia passo a passo no Safari</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- SEÇÃO 2: Identidade do Aplicativo (PWA Manifest) -->
                    <div class="card card-outline card-primary shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-body py-3 border-bottom">
                            <h5 class="fw-bold mb-0"><i class="bi bi-sliders me-1 text-primary"></i> Identidade & Parâmetros do Manifesto</h5>
                        </div>
                        <div class="card-body p-4">
                            
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Nome Completo do Aplicativo</label>
                                    <input type="text" class="form-control" name="pwa_app_name" id="inputAppName" value="<?= htmlspecialchars($pwa_app_name); ?>" required placeholder="Ex: Pronto Saúde | Centro Médico">
                                    <div class="form-text">Exibido na barra de título e no diálogo do sistema.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Nome Curto (Short Name)</label>
                                    <input type="text" class="form-control" name="pwa_short_name" id="inputAppShortName" value="<?= htmlspecialchars($pwa_short_name); ?>" required placeholder="Ex: Pronto Saúde">
                                    <div class="form-text">Exibido abaixo do ícone no celular.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Descrição do Manifesto</label>
                                <textarea class="form-control" name="pwa_description" rows="2"><?= htmlspecialchars($pwa_description); ?></textarea>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Cor do Tema (Barra do Navegador)</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="themeColorPicker" value="<?= htmlspecialchars($pwa_theme_color); ?>" title="Escolha a cor">
                                        <input type="text" class="form-control font-monospace" name="pwa_theme_color" id="themeColorText" value="<?= htmlspecialchars($pwa_theme_color); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Cor de Fundo da Abertura (Splash)</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="bgColorPicker" value="<?= htmlspecialchars($pwa_bg_color); ?>" title="Escolha a cor">
                                        <input type="text" class="form-control font-monospace" name="pwa_bg_color" id="bgColorText" value="<?= htmlspecialchars($pwa_bg_color); ?>">
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-images me-1 text-primary"></i> Ícones do Aplicativo</h6>

                            <div class="row mb-4">
                                <!-- Ícone 192x192 -->
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-body-tertiary">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <img src="<?= $pwa_icon_192_url; ?>" id="preview192" alt="Ícone 192" class="rounded-3 shadow-sm border" style="width: 58px; height: 58px; object-fit: cover;">
                                            <div>
                                                <h6 class="fw-bold mb-1 small">Ícone Médio (192x192)</h6>
                                                <span class="badge bg-secondary-subtle text-secondary border">Launcher / Banner</span>
                                            </div>
                                        </div>
                                        <input type="file" class="form-control form-control-sm" name="pwa_icon_192" accept="image/png,image/webp,image/jpeg" onchange="previewImg(this, '#preview192')">
                                    </div>
                                </div>

                                <!-- Ícone 512x512 -->
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-body-tertiary">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <img src="<?= $pwa_icon_512_url; ?>" id="preview512" alt="Ícone 512" class="rounded-3 shadow-sm border" style="width: 58px; height: 58px; object-fit: cover;">
                                            <div>
                                                <h6 class="fw-bold mb-1 small">Ícone Grande (512x512)</h6>
                                                <span class="badge bg-primary-subtle text-primary border">Splash Screen</span>
                                            </div>
                                        </div>
                                        <input type="file" class="form-control form-control-sm" name="pwa_icon_512" accept="image/png,image/webp,image/jpeg" onchange="previewImg(this, '#preview512')">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                                <div class="form-check form-switch fs-5 mb-0">
                                    <input class="form-check-input" type="checkbox" name="pwa_status" value="1" id="pwaStatusForm" <?= $pwa_status == 1 ? 'checked' : ''; ?>>
                                    <label class="form-check-label fs-6 fw-semibold ms-2" for="pwaStatusForm">Habilitar PWA no site</label>
                                </div>
                                <button type="submit" name="form_pwa" class="btn btn-primary px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm">
                                    <i class="bi bi-save2"></i> Salvar Todas as Configurações
                                </button>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- Coluna Lateral: Live Preview em Tempo Real -->
                <div class="col-lg-5">

                    <!-- Live Preview do Banner em Tempo Real -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4 position-sticky" style="top: 20px;">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0 text-primary d-flex align-items-center gap-2">
                                <i class="bi bi-eye"></i> Pré-visualização ao Vivo do Banner
                            </h6>
                            <span class="badge bg-success-subtle text-success small border border-success-subtle">
                                <i class="bi bi-lightning-charge"></i> Tempo Real
                            </span>
                        </div>
                        <div class="card-body p-4 bg-body-tertiary">
                            <p class="text-muted small mb-3">Veja exatamente como o banner aparecerá para os pacientes no site:</p>
                            
                            <!-- Caixa Simuladora do Banner -->
                            <div class="p-3 bg-white dark-preview rounded-4 shadow border position-relative" id="liveBannerBox" style="font-family: system-ui, -apple-system, sans-serif;">
                                <!-- Botão Fechar Simulado -->
                                <span class="position-absolute text-muted" style="top: 12px; right: 14px; font-size: 14px; cursor: default;">
                                    <i class="bi bi-x-lg"></i>
                                </span>

                                <!-- Badge Simulado -->
                                <div class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill mb-2 fw-bold" style="font-size: 0.68rem; background: rgba(10, 107, 116, 0.1); color: #0a6b74;">
                                    <i class="bi bi-patch-check-fill text-success"></i> APP OFICIAL PRONTO SAÚDE
                                </div>

                                <!-- Header do Banner -->
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <img src="<?= $pwa_icon_192_url; ?>" id="liveBannerIcon" alt="App" class="rounded-3 shadow-sm border" style="width: 52px; height: 52px; object-fit: cover;">
                                    <div class="flex-grow-1 min-width-0">
                                        <h6 class="fw-bold mb-0 text-dark" id="liveBannerTitle" style="font-size: 0.98rem;"><?= htmlspecialchars($pwa_banner_title); ?></h6>
                                        <small class="text-teal fw-semibold" id="liveBannerSub" style="color: #0a6b74; font-size: 0.76rem;">Pronto Saúde na palma da sua mão</small>
                                    </div>
                                </div>

                                <!-- Descrição -->
                                <p class="text-secondary small mb-3" id="liveBannerDesc" style="font-size: 0.8rem; line-height: 1.4;">
                                    <?= nl2br(htmlspecialchars($pwa_banner_desc)); ?>
                                </p>

                                <!-- Chips -->
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span class="badge bg-light text-dark border small fw-normal py-1 px-2" style="font-size: 0.7rem;"><i class="bi bi-lightning-fill text-success me-1"></i> 1 clique</span>
                                    <span class="badge bg-light text-dark border small fw-normal py-1 px-2" style="font-size: 0.7rem;"><i class="bi bi-card-heading text-success me-1"></i> Carteirinha Digital</span>
                                    <span class="badge bg-light text-dark border small fw-normal py-1 px-2" style="font-size: 0.7rem;"><i class="bi bi-feather text-success me-1"></i> 0 MB</span>
                                </div>

                                <!-- Botões -->
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn text-white fw-bold py-2 px-3 flex-grow-1 shadow-sm d-flex align-items-center justify-content-center gap-2" id="liveBannerBtn" style="background: linear-gradient(135deg, #0a6b74 0%, #10b981 100%); border-radius: 10px; font-size: 0.85rem;">
                                        <i class="bi bi-download"></i> <span id="liveBannerBtnText"><?= htmlspecialchars($pwa_banner_btn_text); ?></span>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary py-2 px-3" style="border-radius: 10px; font-size: 0.8rem;">
                                        Agora não
                                    </button>
                                </div>
                            </div>

                            <!-- Botões de Teste e Demonstração -->
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="btnTestIosDemo">
                                    <i class="bi bi-apple"></i> Ver Guia iOS iPhone
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnPulseLive">
                                    <i class="bi bi-magic"></i> Simular Entrada
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Mockup do Celular com App Instalado -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-body py-3 border-bottom">
                            <h6 class="fw-bold mb-0"><i class="bi bi-phone me-1 text-primary"></i> Ícone no Smartphone do Paciente</h6>
                        </div>
                        <div class="card-body p-4 text-center">
                            <div class="p-4 rounded-4 shadow mx-auto position-relative" style="max-width: 270px; background: #0f172a; color: #fff; border: 4px solid #334155;">
                                <div class="d-flex justify-content-between align-items-center text-white-50 mb-4 px-1" style="font-size: 0.75rem;">
                                    <span>09:41</span>
                                    <div class="d-flex gap-1">
                                        <i class="bi bi-wifi"></i>
                                        <i class="bi bi-battery-full"></i>
                                    </div>
                                </div>
                                <div class="d-inline-flex flex-column align-items-center p-3 rounded-4" style="background: rgba(255,255,255,0.06);">
                                    <img src="<?= $pwa_icon_192_url; ?>" id="mockupIcon" alt="App" class="rounded-3 shadow mb-2" style="width: 58px; height: 58px; object-fit: cover;">
                                    <span class="fw-bold text-white text-truncate small" style="max-width: 100px;" id="mockupTitle"><?= htmlspecialchars($pwa_short_name); ?></span>
                                </div>
                                <div class="mt-4 pt-3 border-top border-secondary">
                                    <span class="badge bg-success-subtle text-success small border border-success-subtle">
                                        <i class="bi bi-shield-check"></i> Pronto para Instalar
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>

    </div>
</div>

<!-- Modal de Demonstração do Guia iOS Safari -->
<div class="modal fade" id="modalIosDemo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-0 text-center">
                <div class="d-inline-flex p-3 rounded-circle bg-primary-subtle text-primary mb-2 fs-3">
                    <i class="bi bi-apple"></i>
                </div>
                <h5 class="fw-bold mb-1">Guia Visual para Usuários de iPhone (iOS)</h5>
                <p class="text-muted small mb-3">Demonstração de como o paciente é orientado no navegador Safari:</p>

                <div class="p-3 bg-light rounded-3 text-start mb-2 border d-flex align-items-center gap-3">
                    <span class="badge bg-primary rounded-circle p-2 fs-6" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">1</span>
                    <div class="small">Toque no ícone de <strong>Compartilhar</strong> <i class="bi bi-box-arrow-up text-primary mx-1"></i> na barra inferior do Safari.</div>
                </div>

                <div class="p-3 bg-light rounded-3 text-start mb-2 border d-flex align-items-center gap-3">
                    <span class="badge bg-primary rounded-circle p-2 fs-6" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">2</span>
                    <div class="small">Role o menu e toque em <strong>"Adicionar à Tela de Início"</strong> <i class="bi bi-plus-square text-primary mx-1"></i>.</div>
                </div>

                <div class="p-3 bg-light rounded-3 text-start mb-3 border d-flex align-items-center gap-3">
                    <span class="badge bg-primary rounded-circle p-2 fs-6" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">3</span>
                    <div class="small">No canto superior direito, confirme em <strong>"Adicionar"</strong>. Pronto!</div>
                </div>

                <button type="button" class="btn btn-primary w-100 py-2 fw-semibold rounded-3" data-bs-dismiss="modal">
                    Fechar Demonstração
                </button>
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
                const liveIcon = document.querySelector('#liveBannerIcon');
                if (liveIcon) liveIcon.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Sincronização em tempo real do Live Preview
document.addEventListener('DOMContentLoaded', function() {
    const inputTitle = document.getElementById('inputBannerTitle');
    const inputDesc = document.getElementById('inputBannerDesc');
    const inputBtnText = document.getElementById('inputBannerBtnText');
    const inputShortName = document.getElementById('inputAppShortName');

    const liveTitle = document.getElementById('liveBannerTitle');
    const liveDesc = document.getElementById('liveBannerDesc');
    const liveBtnText = document.getElementById('liveBannerBtnText');
    const mockupTitle = document.getElementById('mockupTitle');

    if (inputTitle && liveTitle) {
        inputTitle.addEventListener('input', function() {
            liveTitle.textContent = this.value || 'Instale o App Pronto Saúde';
        });
    }

    if (inputDesc && liveDesc) {
        inputDesc.addEventListener('input', function() {
            liveDesc.textContent = this.value || 'Agendamento rápido em 1 minuto, carteirinha digital e exames sem ocupar memória.';
        });
    }

    if (inputBtnText && liveBtnText) {
        inputBtnText.addEventListener('input', function() {
            liveBtnText.textContent = this.value || 'Instalar Aplicativo Grátis';
        });
    }

    if (inputShortName && mockupTitle) {
        inputShortName.addEventListener('input', function() {
            mockupTitle.textContent = this.value || 'Pronto Saúde';
        });
    }

    // Cores sincronizadas
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

    // Switch Rápido do Banner via AJAX
    const switchBannerQuick = document.getElementById('switchBannerQuick');
    const badgePwaBanner = document.getElementById('badgePwaBanner');
    const pwaBannerStatusForm = document.getElementById('pwaBannerStatusForm');

    if (switchBannerQuick) {
        switchBannerQuick.addEventListener('change', function() {
            const isChecked = this.checked ? 1 : 0;
            if (pwaBannerStatusForm) pwaBannerStatusForm.checked = this.checked;

            const formData = new FormData();
            formData.append('action', 'toggle_pwa_banner_status');
            formData.append('status', isChecked);

            fetch('pwa.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (badgePwaBanner) {
                        badgePwaBanner.className = isChecked 
                            ? 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 mb-2 d-inline-block small' 
                            : 'badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1 mb-2 d-inline-block small';
                        badgePwaBanner.textContent = isChecked ? 'Banner Visível' : 'Banner Oculto';
                    }
                    if (window.toastr) {
                        toastr.success(data.message, 'Banner PWA');
                    } else if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Status Atualizado',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    if (window.toastr) toastr.error(data.message || 'Erro ao alterar status.');
                }
            })
            .catch(() => {
                if (window.toastr) toastr.error('Falha na comunicação com o servidor.');
            });
        });
    }

    // Botão de Testar Modal iOS
    const btnTestIos = document.getElementById('btnTestIosDemo');
    if (btnTestIos) {
        btnTestIos.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalIosDemo'));
            modal.show();
        });
    }

    // Simular Animação de Entrada
    const btnPulse = document.getElementById('btnPulseLive');
    const liveBox = document.getElementById('liveBannerBox');
    if (btnPulse && liveBox) {
        btnPulse.addEventListener('click', function() {
            liveBox.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
            liveBox.style.transform = 'scale(0.92)';
            liveBox.style.opacity = '0.5';
            setTimeout(() => {
                liveBox.style.transform = 'scale(1)';
                liveBox.style.opacity = '1';
            }, 300);
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
