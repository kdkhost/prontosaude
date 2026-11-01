<?php
ob_start();
session_start();
require_once('config.php');

// Garante a existência da coluna sob_consulta de forma transparente
try {
    $pdo->query("SELECT sob_consulta FROM tbl_pricing_plan LIMIT 1");
} catch (Exception $e) {
    try {
        $pdo->exec("ALTER TABLE tbl_pricing_plan ADD COLUMN sob_consulta TINYINT(1) DEFAULT 0");
    } catch (Exception $ex) {}
}

if (!isset($_REQUEST['id'])) {
    header('location: pricing-plan.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan WHERE pricing_plan_id = ?");
    $statement->execute([$_REQUEST['id']]);
    $current_plan = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$current_plan) {
        header('location: pricing-plan.php');
        exit;
    }
}

require_once('header.php');

$success_message = '';
$error_message = '';

if (isset($_POST['form1'])) {
    $valid = 1;

    $name          = trim($_POST['pricing_plan_name'] ?? '');
    $price         = trim($_POST['pricing_plan_price'] ?? '');
    $period        = trim($_POST['pricing_plan_period'] ?? '/mês');
    $badge         = trim($_POST['pricing_plan_badge'] ?? '');
    $popular       = isset($_POST['pricing_plan_popular']) ? 1 : 0;
    $status        = isset($_POST['status']) ? 1 : 0;
    $sob_consulta  = isset($_POST['sob_consulta']) ? 1 : 0;
    $button_text   = trim($_POST['pricing_plan_button_text'] ?? '');
    $button_url    = trim($_POST['pricing_plan_button_url'] ?? '');
    $benefits_text = trim($_POST['pricing_plan_benefits'] ?? '');

    // Preserva o valor real no banco: se não digitou nada ou digitou zero, ativa Sob Consulta
    $clean_num = trim(str_replace(['R$', ' ', ','], ['', '', '.'], $price));
    if (empty($price) || $clean_num === '0' || $clean_num === '0.00' || stripos($price, 'sob') !== false || stripos($price, 'consultar') !== false) {
        $sob_consulta = 1;
    }

    if (empty($name)) {
        $valid = 0;
        $error_message .= "O nome do plano é obrigatório.<br>";
    } else {
        $statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan WHERE pricing_plan_name = ? AND pricing_plan_id != ?");
        $statement->execute([$name, $_REQUEST['id']]);
        if ($statement->rowCount() > 0) {
            $valid = 0;
            $error_message .= 'Já existe outro plano cadastrado com este nome.<br>';
        }
    }

    // Se não for Sob Consulta, o preço é obrigatório
    if ($sob_consulta == 0 && (empty($price) || $clean_num === '0' || $clean_num === '0.00')) {
        $valid = 0;
        $error_message .= "O valor do plano é obrigatório quando não estiver sob consulta.<br>";
    }

    // Processamento de Upload da Nova Imagem (se enviada)
    $final_photo = $current_plan['pricing_plan_photo'] ?? '';
    if (isset($_FILES['pricing_plan_photo']) && !empty($_FILES['pricing_plan_photo']['name'])) {
        $path = $_FILES['pricing_plan_photo']['name'];
        $path_tmp = $_FILES['pricing_plan_photo']['tmp_name'];
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed_exts)) {
            $valid = 0;
            $error_message .= "Formato de imagem inválido. Envie arquivos JPG, PNG, WEBP ou GIF.<br>";
        } else {
            $final_photo = 'plano-' . $_REQUEST['id'] . '-' . time() . '.' . $ext;
            if (move_uploaded_file($path_tmp, '../assets/uploads/' . $final_photo)) {
                $old_file = $current_plan['pricing_plan_photo'];
                if (!empty($old_file) && !in_array($old_file, ['top-saude.png', 'odonto.png', 'empresarial.png'])) {
                    if (file_exists('../assets/uploads/' . $old_file)) {
                        @unlink('../assets/uploads/' . $old_file);
                    }
                }
            } else {
                $valid = 0;
                $error_message .= "Falha ao enviar a nova imagem do plano.<br>";
            }
        }
    }

    if ($valid == 1) {
        $statement = $pdo->prepare("UPDATE tbl_pricing_plan SET 
            pricing_plan_name = ?,
            pricing_plan_badge = ?,
            pricing_plan_popular = ?,
            pricing_plan_photo = ?,
            pricing_plan_price = ?,
            pricing_plan_period = ?,
            sob_consulta = ?,
            pricing_plan_button_text = ?,
            pricing_plan_button_url = ?,
            status = ?
            WHERE pricing_plan_id = ?");

        $statement->execute([
            $name,
            $badge,
            $popular,
            $final_photo,
            $price,
            $period,
            $sob_consulta,
            $button_text,
            $button_url,
            $status,
            $_REQUEST['id']
        ]);

        // Sincronizando Benefícios do Plano
        $stmtDel = $pdo->prepare("DELETE FROM tbl_pricing_item WHERE pricing_plan_id = ?");
        $stmtDel->execute([$_REQUEST['id']]);

        if (!empty($benefits_text)) {
            $lines = explode("\n", str_replace("\r", "", $benefits_text));
            $stmtItem = $pdo->prepare("INSERT INTO tbl_pricing_item (pricing_item_name, pricing_plan_id) VALUES (?, ?)");
            foreach ($lines as $line) {
                $benefit = trim($line);
                if (!empty($benefit)) {
                    $stmtItem->execute([$benefit, $_REQUEST['id']]);
                }
            }
        }

        $success_message = 'Plano de saúde e seus benefícios foram atualizados com sucesso!';

        // Recarrega dados atualizados
        $statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan WHERE pricing_plan_id = ?");
        $statement->execute([$_REQUEST['id']]);
        $current_plan = $statement->fetch(PDO::FETCH_ASSOC);
    }
}

// Carrega os benefícios existentes para exibição no textarea
$statement_items = $pdo->prepare("SELECT pricing_item_name FROM tbl_pricing_item WHERE pricing_plan_id = ? ORDER BY pricing_item_id ASC");
$statement_items->execute([$_REQUEST['id']]);
$current_items = $statement_items->fetchAll(PDO::FETCH_COLUMN);
$current_benefits_str = implode("\n", $current_items);

// Determina foto do plano para exibição
$photo = $current_plan['pricing_plan_photo'] ?? '';
$photo_src = '';
if (!empty($photo) && file_exists('../assets/uploads/' . $photo)) {
    $photo_src = BASE_URL . 'assets/uploads/' . $photo;
} elseif (!empty($photo) && file_exists('../assets/images/planos/' . $photo)) {
    $photo_src = BASE_URL . 'assets/images/planos/' . $photo;
} elseif (stripos($current_plan['pricing_plan_name'], 'empresa') !== false) {
    $photo_src = BASE_URL . 'assets/images/planos/empresarial.png';
} elseif (stripos($current_plan['pricing_plan_name'], 'odonto') !== false) {
    $photo_src = BASE_URL . 'assets/images/planos/odonto.png';
} else {
    $photo_src = BASE_URL . 'assets/images/planos/top-saude.png';
}

$is_sob = !empty($current_plan['sob_consulta']) || empty($current_plan['pricing_plan_price']) || stripos($current_plan['pricing_plan_price'], 'sob') !== false || stripos($current_plan['pricing_plan_price'], 'consultar') !== false;
$raw_price = trim($current_plan['pricing_plan_price'] ?? '');
$clean_display_price = preg_replace('/^R\$\s*/i', '', $raw_price);
if (strtolower($clean_display_price) === 'sob consulta' || strtolower($clean_display_price) === 'consultar') {
    $clean_display_price = '';
}
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card-2-front-fill text-primary"></i> Editar Plano: <?= htmlspecialchars($current_plan['pricing_plan_name']); ?>
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="pricing-plan.php">Planos & Fidelidade</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Plano</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="pricing-plan.php" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Voltar à Listagem
                </a>
                <a href="<?= BASE_URL; ?>fidelidade.php" target="_blank" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-up-right"></i> Ver no Site
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
                <div><?= $error_message; ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" id="formPlanEdit">
            <div class="row">
                
                <!-- Coluna Principal: Formulário Moderno -->
                <div class="col-lg-8">

                    <!-- BLOCO 1: Identidade do Plano -->
                    <div class="card card-outline card-primary shadow-sm rounded-4 mb-4 border-top border-4 border-primary">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 text-primary d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle"></i> Identidade & Destaques do Plano
                            </h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">ID #<?= $current_plan['pricing_plan_id']; ?></span>
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- Nome do Plano -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nome do Plano / Cartão <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" name="pricing_plan_name" id="planNameInput" value="<?= htmlspecialchars($current_plan['pricing_plan_name']); ?>" required placeholder="Ex: Top Saúde, Plano Empresa, Odonto Total">
                            </div>

                            <!-- Selo / Badge de Destaque -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-7">
                                    <label class="form-label fw-semibold">Selo de Destaque (Badge Flutuante)</label>
                                    <input type="text" class="form-control" name="pricing_plan_badge" id="planBadgeInput" placeholder="Ex: Mais Escolhido, Popular, Recomendado" value="<?= htmlspecialchars($current_plan['pricing_plan_badge'] ?? ''); ?>">
                                    <div class="form-text">Exibido no topo do card (ex: "🔥 Mais Escolhido").</div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Status de Exibição no Site</label>
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1" <?= (($current_plan['status'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-semibold" for="status">Plano Ativo (Visível)</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Switch de Destaque / Moldura Popular -->
                            <div class="p-3 rounded-3 bg-body-tertiary border mb-0 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="fw-bold mb-1"><i class="bi bi-star-fill text-warning me-1"></i> Destacar como Plano Popular</h6>
                                    <span class="text-muted small">Adiciona borda esmeralda iluminada e elevação diferenciada na grade de planos.</span>
                                </div>
                                <div class="form-check form-switch fs-4 mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="pricing_plan_popular" name="pricing_plan_popular" value="1" <?= (!empty($current_plan['pricing_plan_popular'])) ? 'checked' : ''; ?>>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- BLOCO 2: Precificação & Opção Sob Consulta -->
                    <div class="card card-outline card-success shadow-sm rounded-4 mb-4 border-top border-4 border-success">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 text-success d-flex align-items-center gap-2">
                                <i class="bi bi-cash-coin"></i> Precificação & Modalidade de Cobrança
                            </h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Sob Consulta / Valor Fixo</span>
                        </div>
                        <div class="card-body p-4">

                            <!-- SWITCH PRINCIPAL: Preço Sob Consulta -->
                            <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle mb-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-success mb-1 d-flex align-items-center gap-2">
                                        <i class="bi bi-chat-dots-fill"></i> Preço Sob Consulta
                                    </h6>
                                    <p class="text-secondary mb-0 small">
                                        Quando ativado, <strong>oculta o valor monetário numérico</strong> e exibe o badge elegante <strong>"Sob Consulta"</strong> no site (ideal para planos corporativos, personalizados ou empresariais).
                                    </p>
                                </div>
                                <div class="form-check form-switch fs-3 mb-0 ms-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switchSobConsulta" name="sob_consulta" value="1" <?= $is_sob ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <!-- Campos de Preço e Período -->
                            <div class="row g-3" id="wrapPrecoNumerico">
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label fw-semibold mb-0">
                                            Valor / Preço do Plano <span class="text-danger" id="reqStar" style="<?= $is_sob ? 'display: none;' : ''; ?>">*</span>
                                        </label>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small" id="badgeValorStatus" style="<?= $is_sob ? '' : 'display: none;'; ?>">
                                            <i class="bi bi-shield-check me-1"></i>Salvo internamente
                                        </span>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text fw-bold">R$</span>
                                        <input type="text" class="form-control mascara-moeda" name="pricing_plan_price" id="planPriceInput" value="<?= htmlspecialchars($clean_display_price); ?>" placeholder="0,00" <?= $is_sob ? '' : 'required'; ?>>
                                    </div>
                                    <div class="form-text" id="helpPriceText">
                                        <?= $is_sob ? '<span class="text-primary"><i class="bi bi-info-circle-fill me-1"></i>O valor gravado permanece seguro no sistema, mas no site é sobrescrito para "Sob Consulta".</span>' : 'Digite o valor mensal ou de adesão do plano.'; ?>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Período de Cobrança</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                        <input type="text" class="form-control" name="pricing_plan_period" id="planPeriodInput" placeholder="Ex: /mês, /ano" value="<?= htmlspecialchars($current_plan['pricing_plan_period'] ?? '/mês'); ?>">
                                    </div>
                                    <div class="form-text">Sufixo de tempo exibido ao lado do valor (ex: /mês).</div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- BLOCO 3: Imagem do Cartão / Card -->
                    <div class="card card-outline card-info shadow-sm rounded-4 mb-4 border-top border-4 border-info">
                        <div class="card-header bg-body py-3 border-bottom">
                            <h5 class="fw-bold mb-0 text-info-emphasis d-flex align-items-center gap-2">
                                <i class="bi bi-image"></i> Imagem do Cartão de Fidelidade
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-wrap align-items-center gap-4">
                                <div class="p-2 border rounded-4 bg-body-tertiary shadow-sm text-center" style="width: 140px;">
                                    <img src="<?= $photo_src; ?>" id="previewCardImg" alt="Foto do Plano" class="img-fluid rounded-3" style="max-height: 100px; object-fit: contain;">
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label fw-semibold">Substituir Imagem do Card (PNG / JPG / WEBP)</label>
                                    <input type="file" name="pricing_plan_photo" id="planPhotoInput" class="form-control" accept="image/*" onchange="previewCard(this)">
                                    <div class="form-text">Recomendado formato retangular proporcional (ex: 400x260px) com o layout do cartão ou mockup institucional.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BLOCO 4: Benefícios e Botão de Ação -->
                    <div class="card card-outline card-secondary shadow-sm rounded-4 mb-4 border-top border-4 border-secondary">
                        <div class="card-header bg-body py-3 border-bottom">
                            <h5 class="fw-bold mb-0 text-secondary-emphasis d-flex align-items-center gap-2">
                                <i class="bi bi-check2-square"></i> Benefícios Cobertos & Chamada para Ação
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- Lista de Benefícios (1 por linha) -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold d-flex align-items-center justify-content-between">
                                    <span>Benefícios Inclusos (1 por linha)</span>
                                    <span class="badge bg-secondary-subtle text-secondary small" id="badgeTotalBeneficios"><?= count($current_items); ?> benefício(s)</span>
                                </label>
                                <textarea class="form-control font-monospace" name="pricing_plan_benefits" id="planBenefitsTextarea" rows="6" placeholder="Consultas liberadas imediatamente&#10;Cobertura em mais de 130 exames&#10;Desconto a partir de 30% nos demais exames"><?= htmlspecialchars($current_benefits_str); ?></textarea>
                                <div class="form-text">Pressione Enter para adicionar cada benefício. Cada linha se torna um item com check verde <i class="bi bi-check-circle-fill text-success"></i> no card.</div>
                            </div>

                            <!-- Botão de Ação (Texto + Link) -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Texto do Botão de Ação</label>
                                    <input type="text" class="form-control" name="pricing_plan_button_text" id="planBtnTextInput" value="<?= htmlspecialchars($current_plan['pricing_plan_button_text'] ?? ($is_sob ? 'Confira Valores' : 'Quero Este Plano')); ?>" placeholder="Ex: Quero Este Plano, Confira Valores">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Link de Destino do Botão</label>
                                    <input type="text" class="form-control" name="pricing_plan_button_url" id="planBtnUrlInput" placeholder="Ex: https://wa.me/5521990882462 ou fidelidade.php" value="<?= htmlspecialchars($current_plan['pricing_plan_button_url'] ?? ''); ?>">
                                    <div class="form-text">Deixe em branco para direcionar automaticamente ao WhatsApp oficial.</div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Botões de Envio -->
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-4 bg-body border shadow-sm mb-4">
                        <a href="pricing-plan.php" class="btn btn-outline-secondary px-3">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar / Voltar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm" name="form1">
                            <i class="bi bi-save2"></i> Salvar Alterações do Plano
                        </button>
                    </div>

                </div>

                <!-- Coluna Lateral: Live Preview em Tempo Real do Card -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4 position-sticky" style="top: 20px;">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0 text-primary d-flex align-items-center gap-2">
                                <i class="bi bi-eye"></i> Pré-visualização do Card
                            </h6>
                            <span class="badge bg-success-subtle text-success small border border-success-subtle">
                                <i class="bi bi-lightning-charge"></i> Ao Vivo
                            </span>
                        </div>
                        <div class="card-body p-4 bg-body-tertiary">
                            <p class="text-muted small mb-3">Veja como este plano aparecerá para os visitantes no site público:</p>

                            <!-- MOCKUP DO CARD IDÊNTICO AO SITE -->
                            <div class="p-3 bg-white rounded-4 shadow border position-relative" id="mockupCardBox" style="font-family: system-ui, -apple-system, sans-serif;">
                                
                                <!-- Selo de Destaque -->
                                <div id="mockupBadgeTag" class="position-absolute px-3 py-1 rounded-pill text-white fw-bold shadow-sm" style="top: -12px; left: 50%; transform: translateX(-50%); font-size: 0.75rem; background: #0a6b74; <?= empty($current_plan['pricing_plan_badge']) ? 'display: none;' : ''; ?>">
                                    <i class="bi bi-fire me-1 text-warning"></i><span id="mockupBadgeText"><?= htmlspecialchars($current_plan['pricing_plan_badge'] ?: 'Mais Escolhido'); ?></span>
                                </div>

                                <!-- Imagem do Cartão -->
                                <div class="text-center my-2 p-1">
                                    <img src="<?= $photo_src; ?>" id="mockupImg" alt="Plano" class="img-fluid rounded-3 shadow-sm border" style="max-height: 140px; width: 100%; object-fit: contain; background: #fafafa;">
                                </div>

                                <!-- Título do Plano -->
                                <h5 class="fw-bold text-dark mt-3 mb-2" id="mockupTitle"><?= htmlspecialchars($current_plan['pricing_plan_name']); ?></h5>

                                <!-- Bloco de Preço -->
                                <div class="p-3 rounded-3 text-center mb-3" id="mockupPriceWrap" style="background: rgba(10, 107, 116, 0.08);">
                                    <!-- Exibição Sob Consulta -->
                                    <div id="mockupSobConsulta" style="<?= $is_sob ? '' : 'display: none;'; ?>">
                                        <span class="fw-bold fs-5 text-teal" style="color: #0a6b74;">Sob Consulta</span>
                                    </div>

                                    <!-- Exibição Valor Normal -->
                                    <div id="mockupPrecoNormal" style="<?= $is_sob ? 'display: none;' : ''; ?>">
                                        <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">A PARTIR DE</div>
                                        <div class="d-flex align-items-baseline justify-content-center gap-1">
                                            <span class="text-muted fw-bold" style="font-size: 0.9rem;">R$</span>
                                            <span class="fw-bold fs-3 text-dark" id="mockupPriceVal"><?= htmlspecialchars($clean_display_price ?: '0,00'); ?></span>
                                            <span class="text-muted small" id="mockupPeriodVal"><?= htmlspecialchars($current_plan['pricing_plan_period'] ?? '/mês'); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lista de Benefícios -->
                                <ul class="list-unstyled small mb-4 d-flex flex-column gap-2 text-start" id="mockupBenefitsList">
                                    <?php if (!empty($current_items)): ?>
                                        <?php foreach ($current_items as $b): ?>
                                            <li class="d-flex align-items-center gap-2">
                                                <i class="bi bi-check-circle-fill text-success"></i> <span><?= htmlspecialchars($b); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> <span>Consultas liberadas imediatamente</span></li>
                                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> <span>Cobertura em exames laboratoriais</span></li>
                                    <?php endif; ?>
                                </ul>

                                <!-- Botão Simulado -->
                                <button type="button" class="btn text-white fw-bold w-100 py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm" id="mockupBtn" style="background: #0a6b74; border-radius: 10px; font-size: 0.9rem;">
                                    <span id="mockupBtnText"><?= htmlspecialchars($current_plan['pricing_plan_button_text'] ?: ($is_sob ? 'Confira Valores' : 'Quero Este Plano')); ?></span> <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</div>

<script>
function previewCard(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewCardImg').src = e.target.result;
            document.getElementById('mockupImg').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const switchSob = document.getElementById('switchSobConsulta');
    const wrapPreco = document.getElementById('wrapPrecoNumerico');
    const inputPrice = document.getElementById('planPriceInput');
    const reqStar = document.getElementById('reqStar');
    const helpPrice = document.getElementById('helpPriceText');

    const mockupSob = document.getElementById('mockupSobConsulta');
    const mockupPreco = document.getElementById('mockupPrecoNormal');
    const mockupBtnText = document.getElementById('mockupBtnText');
    const inputBtnText = document.getElementById('planBtnTextInput');

    function updateSobConsultaState(isSob) {
        const badgeStatus = document.getElementById('badgeValorStatus');
        if (isSob) {
            reqStar.style.display = 'none';
            inputPrice.removeAttribute('required');
            helpPrice.innerHTML = '<span class="text-primary"><i class="bi bi-info-circle-fill me-1"></i>O valor gravado permanece seguro no sistema, mas no site é sobrescrito para "Sob Consulta".</span>';
            if (badgeStatus) badgeStatus.style.display = 'inline-block';
            mockupSob.style.display = 'block';
            mockupPreco.style.display = 'none';
            if (!inputBtnText.value || inputBtnText.value === 'Quero Este Plano') {
                inputBtnText.value = 'Confira Valores';
                mockupBtnText.textContent = 'Confira Valores';
            }
        } else {
            reqStar.style.display = 'inline';
            inputPrice.setAttribute('required', 'required');
            helpPrice.textContent = 'Digite o valor mensal ou de adesão do plano.';
            if (badgeStatus) badgeStatus.style.display = 'none';
            mockupSob.style.display = 'none';
            mockupPreco.style.display = 'block';
            if (inputBtnText.value === 'Confira Valores') {
                inputBtnText.value = 'Quero Este Plano';
                mockupBtnText.textContent = 'Quero Este Plano';
            }
        }
    }

    if (switchSob) {
        switchSob.addEventListener('change', function() {
            updateSobConsultaState(this.checked);
        });
    }

    // Sincronização em tempo real do Mockup
    const planName = document.getElementById('planNameInput');
    const mockupTitle = document.getElementById('mockupTitle');
    if (planName && mockupTitle) {
        planName.addEventListener('input', function() {
            mockupTitle.textContent = this.value || 'Nome do Plano';
        });
    }

    const planBadge = document.getElementById('planBadgeInput');
    const mockupBadgeTag = document.getElementById('mockupBadgeTag');
    const mockupBadgeText = document.getElementById('mockupBadgeText');
    if (planBadge && mockupBadgeTag && mockupBadgeText) {
        planBadge.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                mockupBadgeTag.style.display = 'block';
                mockupBadgeText.textContent = this.value;
            } else {
                mockupBadgeTag.style.display = 'none';
            }
        });
    }

    const planPrice = document.getElementById('planPriceInput');
    const mockupPriceVal = document.getElementById('mockupPriceVal');
    if (planPrice && mockupPriceVal) {
        planPrice.addEventListener('input', function() {
            mockupPriceVal.textContent = this.value || '0,00';
            // Se o usuário digitar '0,00' ou '0', sugere Sob Consulta
            if (this.value === '0,00' || this.value === '0' || this.value === '') {
                // Mantém comportamento fluido
            }
        });
    }

    const planPeriod = document.getElementById('planPeriodInput');
    const mockupPeriodVal = document.getElementById('mockupPeriodVal');
    if (planPeriod && mockupPeriodVal) {
        planPeriod.addEventListener('input', function() {
            mockupPeriodVal.textContent = this.value || '/mês';
        });
    }

    if (inputBtnText && mockupBtnText) {
        inputBtnText.addEventListener('input', function() {
            mockupBtnText.textContent = this.value || 'Contratar';
        });
    }

    // Sincronização dos benefícios
    const planBenefits = document.getElementById('planBenefitsTextarea');
    const mockupList = document.getElementById('mockupBenefitsList');
    const badgeTotal = document.getElementById('badgeTotalBeneficios');
    if (planBenefits && mockupList) {
        planBenefits.addEventListener('input', function() {
            const lines = this.value.split('\n').map(l => l.trim()).filter(l => l.length > 0);
            if (badgeTotal) badgeTotal.textContent = lines.length + ' benefício(s)';
            mockupList.innerHTML = '';
            if (lines.length === 0) {
                mockupList.innerHTML = '<li class="d-flex align-items-center gap-2 text-muted"><span>Nenhum benefício informado</span></li>';
            } else {
                lines.forEach(b => {
                    const li = document.createElement('li');
                    li.className = 'd-flex align-items-center gap-2';
                    li.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> <span>' + b + '</span>';
                    mockupList.appendChild(li);
                });
            }
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
