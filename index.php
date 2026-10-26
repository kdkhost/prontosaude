<?php require_once('header.php'); ?>
<?php
// Sincroniza seções da Home com o status de cada página individual conforme o banco de dados
if (function_exists('pronto_is_page_active')) {
    if (!pronto_is_page_active('doctor')) $home_status_doctor = 0;
    if (!pronto_is_page_active('servicos')) $home_status_service = 0;
    if (!pronto_is_page_active('fidelidade')) $home_status_pricing = 0;
    if (!pronto_is_page_active('news')) $home_status_news = 0;
}
?>

<!-- ==============================================================================
     1. HERO SECTION ROBUSTA & DINÂMICA (CROSS-FADE CAROUSEL + AGENDAMENTO RÁPIDO)
     ============================================================================== -->
<section class="hero-section position-relative" id="inicio">
    <?php
    $slides = [];
    try {
        $st_slide = $pdo->prepare("SELECT * FROM tbl_slider WHERE status = 'Active' ORDER BY id DESC");
        $st_slide->execute();
        $slides = $st_slide->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    if (empty($slides)) {
        $slides = [
            [
                'id' => 1,
                'heading' => 'Clínica Médica & Odontologia Integrada',
                'subheading' => 'Consultas especializadas, exames laboratoriais e diagnósticos de alta precisão para cuidar de quem você ama.',
                'button_text' => 'Agendar Atendimento',
                'button_url' => 'https://wa.me/' . $whats_clean . '?text=' . urlencode('Olá! Gostaria de agendar uma consulta na Pronto Saúde'),
                'photo' => 'slider-1.png'
            ]
        ];
    }
    ?>

    <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel" data-bs-interval="6500">
        <?php if(count($slides) > 1): ?>
        <div class="carousel-indicators">
            <?php foreach ($slides as $idx => $s): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $idx; ?>" class="<?= $idx === 0 ? 'active' : ''; ?>" aria-label="Slide <?= $idx + 1; ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="carousel-inner h-100">
            <?php foreach ($slides as $idx => $slide): 
                $img_name = $slide['photo'] ?? '';
                $slide_img = (!empty($img_name) && file_exists(__DIR__ . '/assets/uploads/' . $img_name))
                    ? BASE_URL . 'assets/uploads/' . $img_name
                    : BASE_URL . 'assets/uploads/department-1.png';
                $btn_text = !empty($slide['button_text']) ? $slide['button_text'] : 'AGENDAR CONSULTA AGORA';
                $btn_url = !empty($slide['button_url']) && $slide['button_url'] !== '#'
                    ? $slide['button_url']
                    : 'https://wa.me/' . $whats_clean . '?text=' . urlencode('Olá! Gostaria de agendar um atendimento na Pronto Saúde');
            ?>
            <div class="carousel-item h-100 <?= $idx === 0 ? 'active' : ''; ?>">
                <div class="hero-slide-bg" style="background-image: url('<?= $slide_img; ?>');"></div>
                <div class="hero-slide-overlay"></div>

                <div class="hero-slide-content">
                    <div class="container position-relative">
                        <div class="row align-items-center">
                            <!-- Coluna Esquerda: Apresentação & Ação Principal -->
                            <div class="<?= ($hero_box_status == 1) ? 'col-lg-7' : 'col-lg-10 col-xl-9'; ?> text-start py-3">
                                <div class="hero-badge">
                                    <i class="fas fa-heartbeat me-2"></i> Pronto Saúde • Cuidado Integral
                                </div>
                                <h1 class="hero-title">
                                    <?= htmlspecialchars($slide['heading'] ?? 'Excelência Médica e Odontológica'); ?>
                                </h1>
                                <div class="hero-desc">
                                    <?= nl2br(htmlspecialchars($slide['subheading'] ?? 'Consultas e exames completos com tecnologia avançada, corpo clínico qualificado e atendimento humanizado no Rio de Janeiro.')); ?>
                                </div>
                                <?php if(!empty($slide['content'])):
                                    $clean_slide_content = trim($slide['content']);
                                    if(!empty($clean_slide_content)): ?>
                                    <div class="hero-content-extra mb-3">
                                        <?= nl2br(htmlspecialchars($clean_slide_content)); ?>
                                    </div>
                                <?php endif; endif; ?>
                                <div class="hero-btn-group d-flex flex-wrap gap-2">
                                    <a href="<?= htmlspecialchars($btn_url); ?>" target="_blank" rel="noopener noreferrer" class="btn-foco-primary">
                                        <i class="fas fa-calendar-check"></i> <?= htmlspecialchars($btn_text); ?>
                                    </a>
                                    <a href="<?= BASE_URL; ?>servicos.php" class="btn-foco-outline">
                                        <i class="fas fa-stethoscope"></i> Ver Especialidades
                                    </a>
                                </div>
                            </div>

                            <?php if($hero_box_status == 1): 
                                $btn1_url = !empty($hero_box_item1_url) ? (strpos($hero_box_item1_url, 'http') === 0 ? $hero_box_item1_url : BASE_URL . ltrim($hero_box_item1_url, '/')) : BASE_URL . 'servicos.php';
                                $btn2_url = !empty($hero_box_item2_url) ? (strpos($hero_box_item2_url, 'http') === 0 ? $hero_box_item2_url : BASE_URL . ltrim($hero_box_item2_url, '/')) : BASE_URL . 'servicos.php';
                                $btn3_url = !empty($hero_box_item3_url) ? (strpos($hero_box_item3_url, 'http') === 0 ? $hero_box_item3_url : BASE_URL . ltrim($hero_box_item3_url, '/')) : BASE_URL . 'odontologia.php';
                                $btn4_url = !empty($hero_box_item4_url) ? (strpos($hero_box_item4_url, 'http') === 0 ? $hero_box_item4_url : BASE_URL . ltrim($hero_box_item4_url, '/')) : BASE_URL . 'fidelidade.php';
                                $cta_url  = !empty($hero_box_btn_url) ? (strpos($hero_box_btn_url, 'http') === 0 ? $hero_box_btn_url : BASE_URL . ltrim($hero_box_btn_url, '/')) : BASE_URL . 'fidelidade.php';
                            ?>
                            <!-- Coluna Direita: Card de Agendamento Rápido em 1 Minuto (Configurável no Admin) -->
                            <div class="col-lg-5 text-center mt-3 mt-lg-0 py-2">
                                <div class="card-simulacao-hero">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="badge rounded-pill px-3 py-1 text-white fw-bold" style="background: var(--ps-primary); font-size: 0.76rem;">
                                            <?= htmlspecialchars($hero_box_badge_left ?: 'ATENDIMENTO ÁGIL'); ?>
                                        </span>
                                        <span class="small fw-semibold text-success"><i class="fas fa-bolt me-1"></i> <?= htmlspecialchars($hero_box_badge_right ?: 'Retorno Rápido'); ?></span>
                                    </div>
                                    <h5 class="fw-bold mb-1 text-start" style="color: var(--ps-title);"><?= htmlspecialchars($hero_box_title ?: 'Agendamento em 1 Minuto'); ?></h5>
                                    <p class="small text-muted mb-3 text-start"><?= htmlspecialchars($hero_box_desc ?: 'Escolha o serviço desejado e fale diretamente com a nossa recepção:'); ?></p>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <a href="<?= htmlspecialchars($btn1_url); ?>" class="btn-tipo-veiculo">
                                                <i class="<?= htmlspecialchars($hero_box_item1_icon ?: 'fas fa-user-md'); ?> fs-4 d-block mb-1"></i>
                                                <span class="fw-bold d-block small"><?= htmlspecialchars($hero_box_item1_title ?: 'Consultas'); ?></span>
                                                <span class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($hero_box_item1_subtitle ?: 'Diversas Áreas'); ?></span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="<?= htmlspecialchars($btn2_url); ?>" class="btn-tipo-veiculo">
                                                <i class="<?= htmlspecialchars($hero_box_item2_icon ?: 'fas fa-flask'); ?> fs-4 d-block mb-1"></i>
                                                <span class="fw-bold d-block small"><?= htmlspecialchars($hero_box_item2_title ?: 'Exames'); ?></span>
                                                <span class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($hero_box_item2_subtitle ?: 'Laudos Precisos'); ?></span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="<?= htmlspecialchars($btn3_url); ?>" class="btn-tipo-veiculo">
                                                <i class="<?= htmlspecialchars($hero_box_item3_icon ?: 'fas fa-tooth'); ?> fs-4 d-block mb-1"></i>
                                                <span class="fw-bold d-block small"><?= htmlspecialchars($hero_box_item3_title ?: 'Odontologia'); ?></span>
                                                <span class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($hero_box_item3_subtitle ?: 'Tratamento Dental'); ?></span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="<?= htmlspecialchars($btn4_url); ?>" class="btn-tipo-veiculo">
                                                <i class="<?= htmlspecialchars($hero_box_item4_icon ?: 'fas fa-id-card'); ?> fs-4 d-block mb-1"></i>
                                                <span class="fw-bold d-block small"><?= htmlspecialchars($hero_box_item4_title ?: 'Fidelidade'); ?></span>
                                                <span class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($hero_box_item4_subtitle ?: 'Até 50% Desconto'); ?></span>
                                            </a>
                                        </div>
                                    </div>
                                    <a href="<?= htmlspecialchars($cta_url); ?>" class="btn-foco-primary w-100 justify-content-center py-2" style="font-size: 0.88rem;">
                                        <i class="fas fa-shield-alt"></i> <?= htmlspecialchars($hero_box_btn_text ?: 'CONHEÇA O CARTÃO FIDELIDADE'); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if(count($slides) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Slide Anterior">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Próximo Slide">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
        <?php endif; ?>
    </div>
</section>

<!-- ==============================================================================
     2. DIFERENCIAIS CLÍNICOS & SAÚDE PREVENTIVA (DENSE & ROBUST)
     ============================================================================== -->
<?php if (!empty($home_risco_status) && $home_risco_status == 1): ?>
<section id="riscos" class="section-risco">
    <div class="container">
        <div class="text-center max-w-700 mx-auto section-header-compact">
            <span class="fw-bold text-uppercase small letter-spacing-1" style="color: var(--ps-primary);">
                <?= htmlspecialchars($home_risco_badge ?: 'Saúde Preventiva & Cuidado Integrado'); ?>
            </span>
            <h2 class="h3 fw-bold mt-1" style="color: var(--ps-title);"><?= htmlspecialchars($home_risco_title ?: 'O cuidado com sua saúde não pode esperar'); ?></h2>
            <p class="text-muted small mb-0"><?= nl2br(htmlspecialchars($home_risco_subtitle ?: 'Prevenção e diagnóstico precoce garantem longevidade e bem-estar para você e sua família.')); ?></p>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card-risco">
                    <div class="icon-risco"><i class="<?= htmlspecialchars($home_risco_item1_icon ?: 'fas fa-stethoscope'); ?>"></i></div>
                    <h5 class="fw-bold mb-2" style="color: var(--ps-title);"><?= htmlspecialchars($home_risco_item1_title ?: 'Diagnóstico Ágil e Preciso'); ?></h5>
                    <p class="text-muted small mb-3"><?= nl2br(htmlspecialchars($home_risco_item1_desc ?: 'Consultas especializadas e exames no mesmo endereço, sem perda de tempo e com tecnologia digital integrada.')); ?></p>
                    <div class="d-flex align-items-center fw-semibold small" style="color: var(--ps-primary);">
                        <i class="fas fa-check-circle me-2 text-success"></i> <?= htmlspecialchars($home_risco_item1_highlight ?: 'Laudos rápidos e confiáveis.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-risco">
                    <div class="icon-risco"><i class="<?= htmlspecialchars($home_risco_item2_icon ?: 'fas fa-user-doctor'); ?>"></i></div>
                    <h5 class="fw-bold mb-2" style="color: var(--ps-title);"><?= htmlspecialchars($home_risco_item2_title ?: 'Corpo Clínico Humanizado'); ?></h5>
                    <p class="text-muted small mb-3"><?= nl2br(htmlspecialchars($home_risco_item2_desc ?: 'Médicos especialistas dedicados a ouvir com empatia e construir um plano terapêutico individualizado.')); ?></p>
                    <div class="d-flex align-items-center fw-semibold small" style="color: var(--ps-primary);">
                        <i class="fas fa-check-circle me-2 text-success"></i> <?= htmlspecialchars($home_risco_item2_highlight ?: 'Atendimento com hora marcada.'); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-risco">
                    <div class="icon-risco"><i class="<?= htmlspecialchars($home_risco_item3_icon ?: 'fas fa-wallet'); ?>"></i></div>
                    <h5 class="fw-bold mb-2" style="color: var(--ps-title);"><?= htmlspecialchars($home_risco_item3_title ?: 'Economia Sem Carências'); ?></h5>
                    <p class="text-muted small mb-3"><?= nl2br(htmlspecialchars($home_risco_item3_desc ?: 'Acesso a saúde de alta qualidade sem as mensalidades exorbitantes e sem as carências dos planos tradicionais.')); ?></p>
                    <div class="d-flex align-items-center fw-semibold small" style="color: var(--ps-primary);">
                        <i class="fas fa-check-circle me-2 text-success"></i> <?= htmlspecialchars($home_risco_item3_highlight ?: 'Descontos reais de até 50%.'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==============================================================================
     3. QUEM SOMOS E AUTORIDADE MÉDICA (ESTRUTURA INTEGRADA)
     ============================================================================== -->
<?php if (!empty($home_about_status) && $home_about_status == 1): 
    $about_btn1_href = !empty($home_about_btn1_url) ? (strpos($home_about_btn1_url, 'http') === 0 ? $home_about_btn1_url : BASE_URL . ltrim($home_about_btn1_url, '/')) : BASE_URL . 'page/sobre';
    $about_btn2_href = !empty($home_about_btn2_url) ? (strpos($home_about_btn2_url, 'http') === 0 ? $home_about_btn2_url : BASE_URL . ltrim($home_about_btn2_url, '/')) : 'https://wa.me/' . $whats_clean . '?text=' . urlencode('Olá! Gostaria de agendar uma visita ou atendimento na Pronto Saúde.');
?>
<section id="sobre" class="section-sobre">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="fw-bold text-uppercase small" style="color: var(--ps-primary);"><?= htmlspecialchars($home_about_badge ?: 'Centro Médico Moderno'); ?></span>
                <h2 class="h3 fw-bold mt-1 mb-3" style="color: var(--ps-title);"><?= htmlspecialchars($home_about_title ?: 'Referência em Cuidados Médicos e Odontológicos'); ?></h2>
                <div class="text-muted small leading-relaxed mb-4">
                    <?= nl2br(htmlspecialchars($home_about_desc ?: 'A Pronto Saúde nasceu com o propósito de aproximar a excelência médica da comunidade. Contamos com consultórios modernos, equipamentos de alta tecnologia e laboratório próprio de análises clínicas para oferecer atendimento completo e humanizado em um único local.')); ?>
                </div>
                <div class="row g-2">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border">
                            <div class="text-white rounded-3 p-2 fs-4 d-flex align-items-center justify-content-center" style="background: var(--ps-primary); width: 44px; height: 44px;">
                                <i class="<?= htmlspecialchars($home_about_stat1_icon ?: 'fas fa-user-md'); ?>"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" style="color: var(--ps-title);"><?= htmlspecialchars($home_about_stat1_val ?: '+15 Áreas'); ?></h5>
                                <span class="text-muted" style="font-size: 0.76rem;"><?= htmlspecialchars($home_about_stat1_lbl ?: 'Especialidades Médicas'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border">
                            <div class="text-white rounded-3 p-2 fs-4 d-flex align-items-center justify-content-center" style="background: var(--ps-primary); width: 44px; height: 44px;">
                                <i class="<?= htmlspecialchars($home_about_stat2_icon ?: 'fas fa-hospital-user'); ?>"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" style="color: var(--ps-title);"><?= htmlspecialchars($home_about_stat2_val ?: '+10.000'); ?></h5>
                                <span class="text-muted" style="font-size: 0.76rem;"><?= htmlspecialchars($home_about_stat2_lbl ?: 'Pacientes Atendidos'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border">
                            <div class="text-white rounded-3 p-2 fs-4 d-flex align-items-center justify-content-center" style="background: var(--ps-primary); width: 44px; height: 44px;">
                                <i class="<?= htmlspecialchars($home_about_stat3_icon ?: 'fas fa-star'); ?>"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" style="color: var(--ps-title);"><?= htmlspecialchars($home_about_stat3_val ?: '98.6%'); ?></h5>
                                <span class="text-muted" style="font-size: 0.76rem;"><?= htmlspecialchars($home_about_stat3_lbl ?: 'Satisfação Comprovada'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border">
                            <div class="text-white rounded-3 p-2 fs-4 d-flex align-items-center justify-content-center" style="background: var(--ps-primary); width: 44px; height: 44px;">
                                <i class="<?= htmlspecialchars($home_about_stat4_icon ?: 'fas fa-file-medical'); ?>"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" style="color: var(--ps-title);"><?= htmlspecialchars($home_about_stat4_val ?: '100%'); ?></h5>
                                <span class="text-muted" style="font-size: 0.76rem;"><?= htmlspecialchars($home_about_stat4_lbl ?: 'Laudos Digitais Ágeis'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-4 rounded-4 shadow-sm border bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="text-white p-3 rounded-circle d-flex align-items-center justify-content-center" style="background: var(--ps-primary); width: 48px; height: 48px;">
                            <i class="<?= htmlspecialchars($home_about_card_icon ?: 'fas fa-clinic-medical'); ?> fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" style="color: var(--ps-title);"><?= htmlspecialchars($home_about_card_title ?: 'Centro Integrado Pronto Saúde'); ?></h6>
                            <span class="text-muted" style="font-size: 0.78rem;"><?= htmlspecialchars($home_about_card_subtitle ?: 'Gardênia Azul • Jacarepaguá • RJ'); ?></span>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-3 small">
                        <?php if(!empty($home_about_card_item1)): ?>
                        <li class="py-2 border-bottom d-flex align-items-center text-muted">
                            <i class="fas fa-check-circle text-success me-2 fs-6"></i>
                            <span><?= htmlspecialchars($home_about_card_item1); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if(!empty($home_about_card_item2)): ?>
                        <li class="py-2 border-bottom d-flex align-items-center text-muted">
                            <i class="fas fa-check-circle text-success me-2 fs-6"></i>
                            <span><?= htmlspecialchars($home_about_card_item2); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if(!empty($home_about_card_item3)): ?>
                        <li class="py-2 border-bottom d-flex align-items-center text-muted">
                            <i class="fas fa-check-circle text-success me-2 fs-6"></i>
                            <span><?= htmlspecialchars($home_about_card_item3); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if(!empty($home_about_card_item4)): ?>
                        <li class="py-2 d-flex align-items-center text-muted">
                            <i class="fas fa-check-circle text-success me-2 fs-6"></i>
                            <span><?= htmlspecialchars($home_about_card_item4); ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <div class="d-flex gap-2">
                        <a href="<?= htmlspecialchars($about_btn1_href); ?>" class="btn btn-outline-secondary w-50 py-2 small fw-bold">
                            <i class="fas fa-info-circle me-1"></i> <?= htmlspecialchars($home_about_btn1_text ?: 'Conheça Nossa História'); ?>
                        </a>
                        <a href="<?= htmlspecialchars($about_btn2_href); ?>" target="_blank" rel="noopener noreferrer" class="btn-foco-primary w-50 justify-content-center py-2" style="font-size: 0.88rem;">
                            <i class="fab fa-whatsapp me-1"></i> <?= htmlspecialchars($home_about_btn2_text ?: 'Falar com Atendente'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==============================================================================
     4. PLANOS E CARTÃO FIDELIDADE (RESPEITA BANCO DE DADOS: $home_status_pricing)
     ============================================================================== -->
<?php if(!empty($home_status_pricing) && $home_status_pricing == 1): ?>
<section id="planos" class="section-planos">
    <div class="container" style="max-width: 1240px;">
        <div class="text-center max-w-700 mx-auto section-header-compact">
            <span class="fw-bold text-uppercase small letter-spacing-1" style="color: var(--ps-primary);">Economia &amp; Benefícios</span>
            <h2 class="h3 fw-bold mt-1" style="color: var(--ps-title);"><?= !empty($home_title_pricing) ? htmlspecialchars($home_title_pricing) : 'Cartão Fidelidade &amp; Planos'; ?></h2>
            <p class="text-muted small mb-0"><?= !empty($home_subtitle_pricing) ? htmlspecialchars($home_subtitle_pricing) : 'Sem carência, sem consulta ao SPC/Serasa e com descontos de até 50% para toda a sua família.'; ?></p>
        </div>

        <?php
        $planos = [];
        try {
            $st_plan = $pdo->query("SELECT * FROM tbl_pricing_plan WHERE status = 1 ORDER BY pricing_plan_id ASC");
            $planos = $st_plan->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}

        if (empty($planos)) {
            $planos = [
                [
                    'pricing_plan_id' => 1,
                    'pricing_plan_name' => 'Top Saúde Individual',
                    'pricing_plan_badge' => 'Mais Escolhido',
                    'pricing_plan_popular' => 1,
                    'pricing_plan_price' => '69,90',
                    'pricing_plan_period' => '/mês',
                    'pricing_plan_button_text' => 'ADERIR AO PLANO',
                    'pricing_plan_button_url' => 'https://wa.me/' . $whats_clean . '?text=' . urlencode('Olá! Gostaria de saber mais sobre o plano Top Saúde.')
                ],
                [
                    'pricing_plan_id' => 2,
                    'pricing_plan_name' => 'Top Saúde Med Odonto',
                    'pricing_plan_badge' => '',
                    'pricing_plan_popular' => 0,
                    'pricing_plan_price' => '105,00',
                    'pricing_plan_period' => '/mês',
                    'pricing_plan_button_text' => 'ADERIR AO PLANO',
                    'pricing_plan_button_url' => 'https://wa.me/' . $whats_clean . '?text=' . urlencode('Olá! Gostaria de saber mais sobre o plano Med Odonto.')
                ]
            ];
        }

        $col_class = (count($planos) >= 4) ? 'col-xl-3 col-lg-6 col-md-6' : 'col-lg-4 col-md-6';
        ?>

        <div class="row g-4 justify-content-center">
            <?php foreach ($planos as $plano): 
                $p_id = $plano['pricing_plan_id'];
                $is_destaque = !empty($plano['pricing_plan_popular']) && $plano['pricing_plan_popular'] == 1;
                $p_badge = $plano['pricing_plan_badge'] ?? ($is_destaque ? 'Mais Escolhido' : '');

                $plan_photo = $plano['pricing_plan_photo'] ?? '';
                $photo_src = '';
                if(!empty($plan_photo) && file_exists('assets/uploads/' . $plan_photo)) {
                    $photo_src = BASE_URL . 'assets/uploads/' . $plan_photo;
                } elseif(!empty($plan_photo) && file_exists('assets/images/planos/' . $plan_photo)) {
                    $photo_src = BASE_URL . 'assets/images/planos/' . $plan_photo;
                }

                // Fallback de imagem consistente e realista por plano
                if(empty($photo_src)) {
                    if (stripos($plano['pricing_plan_name'], 'empresa') !== false) {
                        $photo_src = BASE_URL . 'assets/images/planos/empresarial.png';
                    } elseif (stripos($plano['pricing_plan_name'], 'odonto') !== false) {
                        $photo_src = BASE_URL . 'assets/images/planos/odonto.png';
                    } else {
                        $photo_src = BASE_URL . 'assets/images/planos/top-saude.png';
                    }
                }

                $price_val = trim($plano['pricing_plan_price'] ?? '');
                $clean_price = preg_replace('/^R\$\s*/i', '', $price_val);
                $period_val = trim($plano['pricing_plan_period'] ?? '/mês');

                $itens = [];
                try {
                    $st_itens = $pdo->prepare("SELECT pricing_item_name FROM tbl_pricing_item WHERE pricing_plan_id = ? ORDER BY pricing_item_id ASC");
                    $st_itens->execute([$p_id]);
                    $itens = $st_itens->fetchAll(PDO::FETCH_COLUMN);
                } catch (Exception $e) {}

                if (empty($itens)) {
                    $itens = [
                        'Consultas liberadas imediatamente',
                        'Desconto de até 50% em especialidades',
                        'Cobertura em exames laboratoriais',
                        'Atendimento prioritário no WhatsApp'
                    ];
                }

                $plan_btn_text = !empty($plano['pricing_plan_button_text']) ? $plano['pricing_plan_button_text'] : 'Quero Este Plano';
                $plan_btn_url = !empty($plano['pricing_plan_button_url']) 
                    ? $plano['pricing_plan_button_url'] 
                    : 'https://wa.me/' . $whats_clean . '?text=' . urlencode('Olá! Gostaria de contratar o ' . $plano['pricing_plan_name']);
                $btn_target = (strpos($plan_btn_url, 'http') === 0) ? 'target="_blank" rel="noopener"' : '';
            ?>
            <div class="<?= $col_class; ?> d-flex mb-4">
                <div class="ps-plan-card w-100 <?= $is_destaque ? 'ps-plan-popular' : ''; ?>">
                    <?php if($is_destaque || !empty($p_badge)): ?>
                        <span class="ps-plan-badge-tag"><i class="fas fa-fire me-1"></i><?= htmlspecialchars(!empty($p_badge) ? $p_badge : 'Mais Escolhido'); ?></span>
                    <?php endif; ?>
                    <div class="ps-plan-thumb">
                        <img src="<?= $photo_src; ?>" alt="<?= htmlspecialchars($plano['pricing_plan_name']); ?>" loading="lazy" decoding="async">
                    </div>
                    <div class="ps-plan-body">
                        <h3 class="ps-plan-name"><?= htmlspecialchars($plano['pricing_plan_name']); ?></h3>
                        <div class="ps-plan-price-wrap">
                            <?php if(strtolower($clean_price) === 'consultar' || stripos($price_val, 'sob') !== false): ?>
                                <span class="ps-plan-price-val fw-bold" style="font-size: 1.15rem; color: var(--ps-primary);">Sob Consulta</span>
                            <?php else: ?>
                                <span class="ps-plan-from">A partir de</span>
                                <div class="ps-plan-amount">
                                    <span class="ps-plan-currency">R$</span>
                                    <span class="ps-plan-number"><?= htmlspecialchars($clean_price); ?></span>
                                    <?php if(!empty($period_val)): ?>
                                        <span class="ps-plan-period"><?= htmlspecialchars($period_val); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <ul class="ps-plan-features">
                            <?php foreach ($itens as $item_txt): ?>
                                <li><i class="fa fa-check-circle"></i> <?= htmlspecialchars($item_txt); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= htmlspecialchars($plan_btn_url); ?>" <?= $btn_target; ?> class="ps-plan-btn">
                            <?= htmlspecialchars($plan_btn_text); ?> <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL; ?>fidelidade.php" class="btn btn-outline-secondary fw-bold px-4 py-2 rounded-pill small">
                <i class="fas fa-id-card me-2"></i> Ver Todos os Detalhes do Cartão Fidelidade
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==============================================================================
     5. ESPECIALIDADES MÉDICAS & EXAMES (RESPEITA BANCO DE DADOS: $home_status_service)
     ============================================================================== -->
<?php if(!empty($home_status_service) && $home_status_service == 1): ?>
<section id="servicos" class="section-estetica">
    <div class="container" style="max-width: 1240px;">
        <div class="text-center max-w-700 mx-auto section-header-compact">
            <span class="badge-estetica-topo text-uppercase small fw-bold px-3 py-1 rounded-pill">
                <i class="fas fa-stethoscope me-1" style="color: var(--ps-primary);"></i> Cuidado Especializado
            </span>
            <h2 class="h3 fw-bold mt-2" style="color: var(--ps-title);"><?= !empty($home_title_service) ? htmlspecialchars($home_title_service) : 'Especialidades Médicas &amp; Exames'; ?></h2>
            <p class="text-muted small mb-0"><?= !empty($home_subtitle_service) ? htmlspecialchars($home_subtitle_service) : 'Atendimento ambulatorial com hora marcada, corpo clínico experiente e laudos confiáveis.'; ?></p>
        </div>

        <?php
        $servicos = [];
        try {
            $st_serv = $pdo->query("SELECT * FROM tbl_service WHERE status = 'Active' ORDER BY id ASC LIMIT 6");
            $servicos = $st_serv->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
        ?>

        <div class="row g-3 justify-content-center">
            <?php if(!empty($servicos)): ?>
                <?php foreach ($servicos as $servico): 
                    $s_photo = (!empty($servico['photo']) && file_exists(__DIR__ . '/assets/uploads/' . $servico['photo']))
                        ? BASE_URL . 'assets/uploads/' . $servico['photo']
                        : '';
                    $s_desc = !empty($servico['short_description']) ? strip_tags($servico['short_description']) : 'Atendimento com suporte integral e diagnóstico precoce seguro.';
                    $s_link = !empty($servico['slug']) ? BASE_URL . 'service/' . $servico['slug'] : BASE_URL . 'servicos.php';
                ?>
                <div class="col-xl-4 col-md-6 mb-3">
                    <div class="card-estetica">
                        <div class="card-estetica-thumb">
                            <?php if(!empty($s_photo)): ?>
                                <img src="<?= $s_photo; ?>" alt="<?= htmlspecialchars($servico['name']); ?>" loading="lazy" decoding="async">
                            <?php else: ?>
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white" style="background: linear-gradient(135deg, var(--ps-primary) 0%, var(--ps-primary-dark) 100%); font-size: 2.5rem;">
                                    <i class="fas fa-stethoscope"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-estetica-body">
                            <div>
                                <h5 class="fw-bold mb-2" style="color: var(--ps-title); font-size: 1.15rem;">
                                    <a href="<?= $s_link; ?>" class="text-decoration-none" style="color: inherit;">
                                        <?= htmlspecialchars($servico['name']); ?>
                                    </a>
                                </h5>
                                <p class="text-muted small mb-3" style="line-height: 1.6;">
                                    <?= htmlspecialchars(mb_strimwidth($s_desc, 0, 130, '...')); ?>
                                </p>
                            </div>

                            <div class="pt-3 border-top border-light-subtle mt-auto d-flex gap-2">
                                <a href="<?= $s_link; ?>" class="btn btn-sm btn-outline-secondary w-50 py-2 small fw-semibold d-inline-flex align-items-center justify-content-center">
                                    <i class="fas fa-info-circle me-1"></i> Saiba Mais
                                </a>
                                <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar atendimento na especialidade: ' . $servico['name']); ?>" target="_blank" rel="noopener noreferrer" class="btn-agendar-estetica w-50 text-center d-inline-flex align-items-center justify-content-center">
                                    <i class="fas fa-calendar-check me-1"></i> Agendar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-3">
                    <p class="text-muted small">Consulte nossa recepção para a lista completa de especialidades.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL; ?>servicos.php" class="btn btn-outline-secondary fw-bold px-4 py-2 rounded-pill small">
                <i class="fas fa-th-large me-2"></i> Ver Todas as Especialidades &amp; Exames
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==============================================================================
     6. CORPO CLÍNICO / MÉDICOS (RESPEITA BANCO DE DADOS: $home_status_doctor)
     ============================================================================== -->
<?php if(!empty($home_status_doctor) && $home_status_doctor == 1): ?>
<section id="medicos" class="section-medicos">
    <div class="container" style="max-width: 1240px;">
        <div class="text-center max-w-700 mx-auto section-header-compact">
            <span class="fw-bold text-uppercase small letter-spacing-1" style="color: var(--ps-primary);">Profissionais Dedicados</span>
            <h2 class="h3 fw-bold mt-1" style="color: var(--ps-title);"><?= !empty($home_title_doctor) ? htmlspecialchars($home_title_doctor) : 'Nosso Corpo Clínico'; ?></h2>
            <p class="text-muted small mb-0"><?= !empty($home_subtitle_doctor) ? htmlspecialchars($home_subtitle_doctor) : 'Médicos capacitados prontos para acolher você e sua família com respeito e cuidado.'; ?></p>
        </div>

        <?php
        $medicos = [];
        try {
            $st_doc = $pdo->query("SELECT d.*, dg.designation_name FROM tbl_doctor d LEFT JOIN tbl_designation dg ON d.designation_id = dg.designation_id WHERE d.status = 'Active' ORDER BY d.id ASC LIMIT 4");
            $medicos = $st_doc->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
        ?>

        <div class="row g-3 justify-content-center">
            <?php foreach ($medicos as $doc): 
                $doc_photo = (!empty($doc['photo']) && file_exists(__DIR__ . '/assets/uploads/' . $doc['photo']))
                    ? BASE_URL . 'assets/uploads/' . $doc['photo']
                    : BASE_URL . 'assets/uploads/department-1.png';
                $doc_link = !empty($doc['slug']) ? BASE_URL . 'doctor/' . $doc['slug'] : '#';
            ?>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card-medico">
                    <img src="<?= $doc_photo; ?>" alt="<?= htmlspecialchars($doc['name']); ?>" class="card-medico-thumb">
                    <div class="card-medico-body">
                        <h6 class="card-medico-name">
                            <a href="<?= $doc_link; ?>" class="text-decoration-none" style="color: inherit;">
                                <?= htmlspecialchars($doc['name']); ?>
                            </a>
                        </h6>
                        <span class="card-medico-spec"><?= htmlspecialchars($doc['designation_name'] ?? 'Médico Especialista'); ?></span>
                        <div class="card-medico-footer">
                            <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar uma consulta com ' . $doc['name']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-foco-primary w-100 fw-bold">
                                <i class="fas fa-calendar-check me-1"></i> Agendar Consulta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==============================================================================
     7. DEPOIMENTOS DE PACIENTES (RESPEITA BANCO DE DADOS: $home_status_testimonial)
     ============================================================================== -->
<?php if(!empty($home_status_testimonial) && $home_status_testimonial == 1): ?>
<?php
$depoimentos = [];
try {
    $st_dep = $pdo->query("SELECT * FROM tbl_testimonial ORDER BY id DESC LIMIT 3");
    $depoimentos = $st_dep->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<?php if(!empty($depoimentos)): ?>
<section id="depoimentos" class="section-depoimentos">
    <div class="container position-relative">
        <div class="text-center max-w-700 mx-auto section-header-compact">
            <span class="fw-bold text-uppercase small" style="color: var(--ps-primary);">A Voz dos Pacientes</span>
            <h2 class="h3 fw-bold mt-1" style="color: var(--ps-title);"><?= !empty($home_title_testimonial) ? htmlspecialchars($home_title_testimonial) : 'Quem Cuida da Saúde Conosco, Recomenda'; ?></h2>
            <p class="text-muted small mb-0"><?= !empty($home_subtitle_testimonial) ? htmlspecialchars($home_subtitle_testimonial) : 'Depoimentos reais de quem confia na dedicação e no acolhimento da Pronto Saúde.'; ?></p>
        </div>

        <div class="row g-3 justify-content-center">
            <?php foreach ($depoimentos as $dep): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card-risco bg-white h-100 d-flex flex-column p-4">
                    <div class="text-warning mb-2 small">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="text-muted small mb-3 flex-grow-1" style="font-style: italic;">
                        "<?= strip_tags($dep['comment']); ?>"
                    </p>
                    <div class="d-flex align-items-center gap-3 pt-3 border-top">
                        <div class="text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="background: var(--ps-primary); width: 38px; height: 38px; font-size: 0.95rem;">
                            <?= strtoupper(substr($dep['name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark small"><?= htmlspecialchars($dep['name']); ?></h6>
                            <span class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($dep['designation'] ?: 'Paciente Pronto Saúde'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php endif; ?>

<!-- ==============================================================================
     8. NOTÍCIAS & DICAS DE SAÚDE (RESPEITA BANCO DE DADOS: $home_status_news)
     ============================================================================== -->
<?php if(!empty($home_status_news) && $home_status_news == 1): ?>
<?php
$noticias = [];
try {
    // Busca até 12 postagens mais recentes e ativas do blog
    $st_news = $pdo->prepare("SELECT n.*, c.category_name, c.category_slug 
                              FROM tbl_news n 
                              LEFT JOIN tbl_category c ON n.category_id = c.category_id 
                              ORDER BY n.news_id DESC LIMIT 12");
    $st_news->execute();
    $noticias = $st_news->fetchAll(PDO::FETCH_ASSOC);

    // Ordem randômica entre todas as ativas mais recentes do blog a cada requisição
    if (!empty($noticias)) {
        shuffle($noticias);
    }
} catch (Exception $e) {}
?>
<?php if(!empty($noticias)): ?>
<section id="noticias" class="section-sobre py-5 bg-light">
    <div class="container" style="max-width: 1240px;">
        <div class="text-center max-w-700 mx-auto section-header-compact mb-4">
            <span class="fw-bold text-uppercase small letter-spacing-1" style="color: var(--ps-primary);">Informação &amp; Prevenção</span>
            <h2 class="h3 fw-bold mt-1" style="color: var(--ps-title);"><?= !empty($home_title_news) ? htmlspecialchars($home_title_news) : 'Notícias &amp; Dicas de Saúde'; ?></h2>
            <p class="text-muted small mb-0"><?= !empty($home_subtitle_news) ? htmlspecialchars($home_subtitle_news) : 'Artigos e orientações médicas preparados por nossa equipe para o seu bem-estar.'; ?></p>
        </div>

        <!-- Carrossel Swiper Responsivo: 3 Notícias por vez no desktop em ordem randômica -->
        <div class="position-relative px-2 px-md-4">
            <div class="swiper ps-home-news-carousel py-2">
                <div class="swiper-wrapper align-items-stretch">
                    <?php foreach ($noticias as $item): 
                        $n_photo = (!empty($item['photo']) && file_exists(__DIR__ . '/assets/uploads/' . $item['photo']))
                            ? BASE_URL . 'assets/uploads/' . $item['photo']
                            : BASE_URL . 'assets/uploads/department-1.png';
                        $n_link = BASE_URL . 'news/' . $item['news_slug'];
                    ?>
                    <div class="swiper-slide h-auto">
                        <div class="card h-100 border rounded-4 overflow-hidden shadow-sm bg-white d-flex flex-column" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
                            <div style="height: 190px; overflow: hidden; position: relative;">
                                <a href="<?= $n_link; ?>">
                                    <img src="<?= $n_photo; ?>" alt="<?= htmlspecialchars($item['news_title']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                </a>
                                <?php if(!empty($item['category_name'])): ?>
                                    <span class="badge position-absolute top-0 start-0 m-3 px-3 py-1 rounded-pill" style="background: var(--ps-primary); font-size: 0.72rem; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                        <?= htmlspecialchars($item['category_name']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <span class="text-muted small mb-1" style="font-size: 0.75rem;">
                                    <i class="far fa-calendar-alt me-1 text-primary"></i> <?= pronto_format_date($item['news_date']); ?>
                                </span>
                                <h6 class="fw-bold mb-2" style="color: var(--ps-title); line-height: 1.4; min-height: 44px;">
                                    <a href="<?= $n_link; ?>" class="text-decoration-none" style="color: inherit;">
                                        <?= htmlspecialchars(mb_strimwidth($item['news_title'], 0, 75, '...')); ?>
                                    </a>
                                </h6>
                                <p class="text-muted small flex-grow-1 mb-3" style="font-size: 0.82rem; line-height: 1.6;">
                                    <?= htmlspecialchars(mb_strimwidth(strip_tags($item['news_content']), 0, 110, '...')); ?>
                                </p>
                                <a href="<?= $n_link; ?>" class="btn btn-sm btn-outline-secondary w-100 fw-bold mt-auto py-2">
                                    Ler Artigo Completo <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Paginação por Bullets / Dots do Carrossel -->
                <div class="swiper-pagination ps-news-pagination position-relative mt-4"></div>
            </div>

            <!-- Setas de Navegação Personalizadas -->
            <button type="button" class="swiper-button-prev ps-news-nav-btn ps-news-prev d-none d-md-flex" aria-label="Notícia Anterior">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button type="button" class="swiper-button-next ps-news-nav-btn ps-news-next d-none d-md-flex" aria-label="Próxima Notícia">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <!-- Botão Ver Todas as Notícias & Artigos sem tocar abaixo dos cards -->
        <div class="text-center mt-5 pt-2">
            <a href="<?= BASE_URL; ?>page/blog" class="btn btn-outline-secondary fw-bold px-4 py-2 rounded-pill small shadow-sm">
                <i class="fas fa-newspaper me-2"></i> Ver Todas as Notícias &amp; Artigos
            </a>
        </div>
    </div>
</section>
<?php endif; ?>
<?php endif; ?>

<!-- ==============================================================================
     9. CONVÊNIOS E PARCEIROS (RESPEITA BANCO DE DADOS: $home_status_partner)
     ============================================================================== -->
<?php if(!empty($home_status_partner) && $home_status_partner == 1): ?>
<?php
$parceiros = [];
try {
    $st_parc = $pdo->query("SELECT * FROM tbl_partner ORDER BY id ASC LIMIT 8");
    $parceiros = $st_parc->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<?php if(!empty($parceiros)): ?>
<section id="parceiros" class="section-parceiros border-top border-bottom py-4 bg-white">
    <div class="container text-center">
        <span class="fw-bold text-uppercase small d-block mb-1" style="color: var(--ps-primary);">Rede Credenciada</span>
        <h5 class="fw-bold mb-3" style="color: var(--ps-title);"><?= !empty($home_title_partner) ? htmlspecialchars($home_title_partner) : 'Convênios Aceitos &amp; Parceiros Institucionais'; ?></h5>
        <div class="row align-items-center justify-content-center g-3">
            <?php foreach ($parceiros as $parc): 
                $p_logo = (!empty($parc['photo']) && file_exists(__DIR__ . '/assets/uploads/' . $parc['photo']))
                    ? BASE_URL . 'assets/uploads/' . $parc['photo']
                    : '';
            ?>
                <?php if(!empty($p_logo)): ?>
                <div class="col-lg-2 col-md-3 col-4">
                    <img src="<?= $p_logo; ?>" alt="<?= htmlspecialchars($parc['name']); ?>" class="img-fluid" style="max-height: 42px; opacity: 0.85;">
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php endif; ?>

<!-- ==============================================================================
     10. PERGUNTAS FREQUENTES (FAQ CLÍNICO)
     ============================================================================== -->
<?php
$faqs = [];
try {
    $st_faq = $pdo->query("SELECT * FROM tbl_faq ORDER BY faq_id ASC LIMIT 6");
    $faqs = $st_faq->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<section id="faq" class="section-faq">
    <div class="container">
        <div class="text-center max-w-700 mx-auto section-header-compact">
            <span class="fw-bold text-uppercase small" style="color: var(--ps-primary);">Tire Suas Dúvidas</span>
            <h2 class="h3 fw-bold mt-1" style="color: var(--ps-title);">Perguntas Frequentes</h2>
            <p class="text-muted small mb-0">Confira respostas para as principais dúvidas sobre consultas, exames e agendamentos.</p>
        </div>
        <div class="max-w-800 mx-auto">
            <div class="accordion accordion-foco" id="faqAccordion">
                <?php if(!empty($faqs)): ?>
                    <?php foreach ($faqs as $idx => $faq): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFaq<?= $faq['faq_id']; ?>">
                            <button class="accordion-button <?= $idx !== 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaq<?= $faq['faq_id']; ?>" aria-expanded="<?= $idx === 0 ? 'true' : 'false'; ?>" aria-controls="collapseFaq<?= $faq['faq_id']; ?>">
                                <i class="fas fa-question-circle me-2" style="color: var(--ps-primary);"></i> <?= htmlspecialchars($faq['faq_title']); ?>
                            </button>
                        </h2>
                        <div id="collapseFaq<?= $faq['faq_id']; ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : ''; ?>" aria-labelledby="headingFaq<?= $faq['faq_id']; ?>" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?= $faq['faq_content']; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqDef1">
                                <i class="fas fa-question-circle me-2" style="color: var(--ps-primary);"></i> Como agendar uma consulta ou exame?
                            </button>
                        </h2>
                        <div id="faqDef1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                O agendamento é imediato pelo nosso WhatsApp oficial ou telefone fixo da clínica.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================================
     11. BANNER INSTITUCIONAL DE AGENDAMENTO (SEM FORMULÁRIO NA HOME - MULTIPAGE)
     ============================================================================== -->
<section class="section-cta-atendimento py-4" style="background: linear-gradient(135deg, var(--ps-primary-dark) 0%, var(--ps-primary) 100%); color: #fff;">
    <div class="container">
        <div class="row align-items-center justify-content-between g-3">
            <div class="col-lg-8 text-center text-lg-start">
                <span class="badge rounded-pill px-3 py-1 text-white fw-bold mb-2" style="background: rgba(255,255,255,0.18); font-size: 0.76rem;">
                    <i class="fas fa-headset me-1"></i> CANAL DIRETO COM A RECEPÇÃO
                </span>
                <h3 class="fw-bold mb-1 text-white">Precisa de atendimento ou deseja tirar dúvidas?</h3>
                <p class="mb-0 text-white-50 small">Nossa equipe está disponível de segunda a sábado para cuidar de você e sua família.</p>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-end gap-2">
                    <a href="<?= BASE_URL; ?>page/contato" class="btn btn-light fw-bold px-3 py-2 rounded-pill small" style="color: var(--ps-primary);">
                        <i class="fas fa-envelope me-1"></i> Página de Contato
                    </a>
                    <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar um atendimento na Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success fw-bold px-3 py-2 rounded-pill small" style="background: #10b981; border: none;">
                        <i class="fab fa-whatsapp me-1"></i> Chamar no WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
