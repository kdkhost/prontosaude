<?php
// tools/update_index.php
// Gera index.php com arquitetura robusta, funcional, compacta (sem vazios excessivos)
// e 100% alinhada à Identidade Visual da Pronto Saúde (Teal #0a6b74, Dark Teal #07474d, Verde #10b981)

$index_file = __DIR__ . '/../index.php';

$index_code = '<?php require_once(\'header.php\'); ?>

<!-- ==============================================================================
     1. HERO SECTION ROBUSTA & DINÂMICA (CROSS-FADE CAROUSEL + AGENDAMENTO RÁPIDO)
     ============================================================================== -->
<section class="hero-section position-relative" id="inicio">
    <?php
    $slides = [];
    try {
        $st_slide = $pdo->prepare("SELECT * FROM tbl_slider WHERE status = \'Active\' ORDER BY id DESC");
        $st_slide->execute();
        $slides = $st_slide->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    if (empty($slides)) {
        $slides = [
            [
                \'id\' => 1,
                \'heading\' => \'Clínica Médica & Odontologia Integrada\',
                \'subheading\' => \'Consultas especializadas, exames laboratoriais e diagnósticos de alta precisão para cuidar de quem você ama.\',
                \'button_text\' => \'Agendar Atendimento\',
                \'button_url\' => \'https://wa.me/\' . $whats_clean . \'?text=\' . urlencode(\'Olá! Gostaria de agendar uma consulta na Pronto Saúde\'),
                \'photo\' => \'slider-1.png\'
            ]
        ];
    }
    ?>

    <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel" data-bs-interval="6500">
        <?php if(count($slides) > 1): ?>
        <div class="carousel-indicators">
            <?php foreach ($slides as $idx => $s): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $idx; ?>" class="<?= $idx === 0 ? \'active\' : \'\'; ?>" aria-label="Slide <?= $idx + 1; ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="carousel-inner h-100">
            <?php foreach ($slides as $idx => $slide): 
                $img_name = $slide[\'photo\'] ?? \'\';
                $slide_img = (!empty($img_name) && file_exists(__DIR__ . \'/assets/uploads/\' . $img_name))
                    ? BASE_URL . \'assets/uploads/\' . $img_name
                    : BASE_URL . \'assets/uploads/department-1.png\';
                $btn_text = !empty($slide[\'button_text\']) ? $slide[\'button_text\'] : \'AGENDAR CONSULTA AGORA\';
                $btn_url = !empty($slide[\'button_url\']) && $slide[\'button_url\'] !== \'#\'
                    ? $slide[\'button_url\']
                    : \'https://wa.me/\' . $whats_clean . \'?text=\' . urlencode(\'Olá! Gostaria de agendar um atendimento na Pronto Saúde\');
            ?>
            <div class="carousel-item h-100 <?= $idx === 0 ? \'active\' : \'\'; ?>">
                <div class="hero-slide-bg" style="background-image: url(\'<?= $slide_img; ?>\');"></div>
                <div class="hero-slide-overlay"></div>

                <div class="hero-slide-content">
                    <div class="container position-relative">
                        <div class="row align-items-center">
                            <!-- Coluna Esquerda: Apresentação & Ação Principal -->
                            <div class="col-lg-7 text-start py-3">
                                <div class="hero-badge">
                                    <i class="fas fa-heartbeat me-2"></i> Pronto Saúde • Cuidado Integral
                                </div>
                                <h1 class="hero-title">
                                    <?= htmlspecialchars($slide[\'heading\'] ?? \'Excelência Médica e Odontológica\'); ?>
                                </h1>
                                <div class="hero-desc">
                                    <?= nl2br(htmlspecialchars($slide[\'subheading\'] ?? \'Consultas e exames completos com tecnologia avançada, corpo clínico qualificado e atendimento humanizado no Rio de Janeiro.\')); ?>
                                </div>
                                <div class="hero-btn-group d-flex flex-wrap gap-2">
                                    <a href="<?= htmlspecialchars($btn_url); ?>" target="_blank" rel="noopener noreferrer" class="btn-foco-primary">
                                        <i class="fas fa-calendar-check"></i> <?= htmlspecialchars($btn_text); ?>
                                    </a>
                                    <a href="#servicos" class="btn-foco-outline">
                                        <i class="fas fa-stethoscope"></i> Ver Especialidades
                                    </a>
                                </div>
                            </div>

                            <!-- Coluna Direita: Card de Agendamento Rápido em 1 Minuto -->
                            <div class="col-lg-5 text-center mt-3 mt-lg-0 py-2">
                                <div class="card-simulacao-hero">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="badge rounded-pill px-3 py-1 text-white fw-bold" style="background: var(--ps-primary); font-size: 0.76rem;">
                                            ATENDIMENTO ÁGIL
                                        </span>
                                        <span class="small fw-semibold text-success"><i class="fas fa-bolt me-1"></i> Retorno Rápido</span>
                                    </div>
                                    <h5 class="fw-bold mb-1 text-start" style="color: var(--ps-title);">Agendamento em 1 Minuto</h5>
                                    <p class="small text-muted mb-3 text-start">Escolha o serviço desejado e fale diretamente com a nossa recepção:</p>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <a href="#servicos" class="btn-tipo-veiculo">
                                                <i class="fas fa-user-md fs-4 d-block mb-1"></i>
                                                <span class="fw-bold d-block small">Consultas</span>
                                                <span class="text-muted" style="font-size: 0.72rem;">Diversas Áreas</span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#servicos" class="btn-tipo-veiculo">
                                                <i class="fas fa-flask fs-4 d-block mb-1"></i>
                                                <span class="fw-bold d-block small">Exames</span>
                                                <span class="text-muted" style="font-size: 0.72rem;">Laudos Precisos</span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#servicos" class="btn-tipo-veiculo">
                                                <i class="fas fa-tooth fs-4 d-block mb-1"></i>
                                                <span class="fw-bold d-block small">Odontologia</span>
                                                <span class="text-muted" style="font-size: 0.72rem;">Tratamento Dental</span>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="#planos" class="btn-tipo-veiculo">
                                                <i class="fas fa-id-card fs-4 d-block mb-1"></i>
                                                <span class="fw-bold d-block small">Fidelidade</span>
                                                <span class="text-muted" style="font-size: 0.72rem;">Até 50% Desconto</span>
                                            </a>
                                        </div>
                                    </div>
                                    <a href="#planos" class="btn-foco-primary w-100 justify-content-center py-2" style="font-size: 0.88rem;">
                                        <i class="fas fa-shield-alt"></i> CONHEÇA O CARTÃO FIDELIDADE
                                    </a>
                                </div>
                            </div>
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
<section id="riscos" class="section-risco">
    <div class="container">
        <div class="text-center max-w-700 mx-auto section-header-compact">
            <span class="fw-bold text-uppercase small letter-spacing-1" style="color: var(--ps-primary);">
                Saúde Preventiva &amp; Cuidado Integrado
            </span>
            <h2 class="h3 fw-bold mt-1" style="color: var(--ps-title);">O cuidado com sua saúde não pode esperar</h2>
            <p class="text-muted small mb-0">Prevenção e diagnóstico precoce garantem longevidade e bem-estar para você e sua família.</p>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card-risco">
                    <div class="icon-risco"><i class="fas fa-stethoscope"></i></div>
                    <h5 class="fw-bold mb-2" style="color: var(--ps-title);">Diagnóstico Ágil e Preciso</h5>
                    <p class="text-muted small mb-3">Consultas especializadas e exames no mesmo endereço, sem perda de tempo e com tecnologia digital integrada.</p>
                    <div class="d-flex align-items-center fw-semibold small" style="color: var(--ps-primary);">
                        <i class="fas fa-check-circle me-2 text-success"></i> Laudos rápidos e confiáveis.
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-risco">
                    <div class="icon-risco"><i class="fas fa-user-doctor"></i></div>
                    <h5 class="fw-bold mb-2" style="color: var(--ps-title);">Corpo Clínico Humanizado</h5>
                    <p class="text-muted small mb-3">Médicos especialistas dedicados a ouvir com empatia e construir um plano terapêutico individualizado.</p>
                    <div class="d-flex align-items-center fw-semibold small" style="color: var(--ps-primary);">
                        <i class="fas fa-check-circle me-2 text-success"></i> Atendimento com hora marcada.
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-risco">
                    <div class="icon-risco"><i class="fas fa-wallet"></i></div>
                    <h5 class="fw-bold mb-2" style="color: var(--ps-title);">Economia Sem Carências</h5>
                    <p class="text-muted small mb-3">Acesso a saúde de alta qualidade sem as mensalidades exorbitantes e sem as carências dos planos tradicionais.</p>
                    <div class="d-flex align-items-center fw-semibold small" style="color: var(--ps-primary);">
                        <i class="fas fa-check-circle me-2 text-success"></i> Descontos reais de até 50%.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================================
     3. QUEM SOMOS E AUTORIDADE MÉDICA (ESTRUTURA INTEGRADA)
     ============================================================================== -->
<section id="sobre" class="section-sobre">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="fw-bold text-uppercase small" style="color: var(--ps-primary);">Centro Médico Moderno</span>
                <h2 class="h3 fw-bold mt-1 mb-3" style="color: var(--ps-title);">Referência em Cuidados Médicos e Odontológicos</h2>
                <p class="text-muted small leading-relaxed mb-4">
                    A <strong class="text-dark">Pronto Saúde</strong> nasceu com o propósito de aproximar a excelência médica da comunidade. Contamos com consultórios modernos, equipamentos de alta tecnologia e laboratório próprio de análises clínicas para oferecer atendimento completo e humanizado em um único local.
                </p>
                <div class="row g-2">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border">
                            <div class="text-white rounded-3 p-2 fs-4 d-flex align-items-center justify-content-center" style="background: var(--ps-primary); width: 44px; height: 44px;">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" style="color: var(--ps-title);">+15 Áreas</h5>
                                <span class="text-muted" style="font-size: 0.76rem;">Especialidades Médicas</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border">
                            <div class="text-white rounded-3 p-2 fs-4 d-flex align-items-center justify-content-center" style="background: var(--ps-primary); width: 44px; height: 44px;">
                                <i class="fas fa-hospital-user"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" style="color: var(--ps-title);">+10.000</h5>
                                <span class="text-muted" style="font-size: 0.76rem;">Pacientes Atendidos</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border">
                            <div class="text-white rounded-3 p-2 fs-4 d-flex align-items-center justify-content-center" style="background: var(--ps-primary); width: 44px; height: 44px;">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" style="color: var(--ps-title);">98.6%</h5>
                                <span class="text-muted" style="font-size: 0.76rem;">Satisfação Comprovada</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border">
                            <div class="text-white rounded-3 p-2 fs-4 d-flex align-items-center justify-content-center" style="background: var(--ps-primary); width: 44px; height: 44px;">
                                <i class="fas fa-file-medical"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" style="color: var(--ps-title);">100%</h5>
                                <span class="text-muted" style="font-size: 0.76rem;">Laudos Digitais Ágeis</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-4 rounded-4 shadow-sm border bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="text-white p-3 rounded-circle d-flex align-items-center justify-content-center" style="background: var(--ps-primary); width: 48px; height: 48px;">
                            <i class="fas fa-clinic-medical fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" style="color: var(--ps-title);">Centro Integrado Pronto Saúde</h6>
                            <span class="text-muted" style="font-size: 0.78rem;">Gardênia Azul • Jacarepaguá • RJ</span>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-3 small">
                        <li class="py-2 border-bottom d-flex align-items-center text-muted">
                            <i class="fas fa-check-circle text-success me-2 fs-6"></i>
                            <span>Consultórios médicos climatizados, acessíveis e confortáveis</span>
                        </li>
                        <li class="py-2 border-bottom d-flex align-items-center text-muted">
                            <i class="fas fa-check-circle text-success me-2 fs-6"></i>
                            <span>Laboratório de análises clínicas com coleta humanizada</span>
                        </li>
                        <li class="py-2 border-bottom d-flex align-items-center text-muted">
                            <i class="fas fa-check-circle text-success me-2 fs-6"></i>
                            <span>Odontologia moderna com raio-x e procedimentos integrados</span>
                        </li>
                        <li class="py-2 d-flex align-items-center text-muted">
                            <i class="fas fa-check-circle text-success me-2 fs-6"></i>
                            <span>Agendamento ágil pelo WhatsApp em menos de 1 minuto</span>
                        </li>
                    </ul>
                    <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode(\'Olá! Gostaria de agendar uma visita ou atendimento na Pronto Saúde.\'); ?>" target="_blank" rel="noopener noreferrer" class="btn-foco-primary w-100 justify-content-center py-2" style="font-size: 0.88rem;">
                        <i class="fab fa-whatsapp me-1"></i> Falar com um Atendente
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================================================
     4. PLANOS E CARTÃO FIDELIDADE (COMPACTO & SEM ESPAÇOS OCIOSOS)
     ============================================================================== -->
<section id="planos" class="section-planos">
    <div class="container" style="max-width: 1240px;">
        <div class="text-center max-w-700 mx-auto section-header-compact">
            <span class="fw-bold text-uppercase small letter-spacing-1" style="color: var(--ps-primary);">Economia &amp; Benefícios</span>
            <h2 class="h3 fw-bold mt-1" style="color: var(--ps-title);">Cartão Fidelidade &amp; Planos</h2>
            <p class="text-muted small mb-0">Sem carência, sem consulta ao SPC/Serasa e com descontos de até 50% para toda a sua família.</p>
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
                    \'pricing_plan_id\' => 1,
                    \'pricing_plan_name\' => \'Top Saúde Individual\',
                    \'pricing_plan_badge\' => \'Mais Escolhido\',
                    \'pricing_plan_popular\' => 1,
                    \'pricing_plan_price\' => \'R$ 69,90\',
                    \'pricing_plan_period\' => \'/mês\',
                    \'pricing_plan_button_text\' => \'ADERIR AO PLANO\',
                    \'pricing_plan_button_url\' => \'https://wa.me/\' . $whats_clean . \'?text=\' . urlencode(\'Olá! Gostaria de saber mais sobre o plano Top Saúde.\')
                ],
                [
                    \'pricing_plan_id\' => 2,
                    \'pricing_plan_name\' => \'Top Saúde Med Odonto\',
                    \'pricing_plan_badge\' => \'\',
                    \'pricing_plan_popular\' => 0,
                    \'pricing_plan_price\' => \'R$ 105,00\',
                    \'pricing_plan_period\' => \'/mês\',
                    \'pricing_plan_button_text\' => \'ADERIR AO PLANO\',
                    \'pricing_plan_button_url\' => \'https://wa.me/\' . $whats_clean . \'?text=\' . urlencode(\'Olá! Gostaria de saber mais sobre o plano Med Odonto.\')
                ],
                [
                    \'pricing_plan_id\' => 3,
                    \'pricing_plan_name\' => \'Plano Empresa\',
                    \'pricing_plan_badge\' => \'\',
                    \'pricing_plan_popular\' => 0,
                    \'pricing_plan_price\' => \'Sob Consulta\',
                    \'pricing_plan_period\' => \'/mês\',
                    \'pricing_plan_button_text\' => \'CONSULTAR VALORES\',
                    \'pricing_plan_button_url\' => \'https://wa.me/\' . $whats_clean . \'?text=\' . urlencode(\'Olá! Gostaria de saber sobre o Plano Empresa.\')
                ]
            ];
        }

        $col_class = count($planos) <= 3 ? \'col-lg-4 col-md-6\' : \'col-xl-3 col-lg-6 col-md-6\';
        ?>

        <div class="row g-3 justify-content-center align-items-stretch">
            <?php foreach ($planos as $plano): 
                $p_id = $plano[\'pricing_plan_id\'];
                $is_destaque = !empty($plano[\'pricing_plan_popular\']) && $plano[\'pricing_plan_popular\'] == 1;
                $p_badge = $plano[\'pricing_plan_badge\'] ?? ($is_destaque ? \'MAIS ESCOLHIDO\' : \'\');

                $itens = [];
                try {
                    $st_itens = $pdo->prepare("SELECT pricing_item_name FROM tbl_pricing_item WHERE pricing_plan_id = ?");
                    $st_itens->execute([$p_id]);
                    $itens = $st_itens->fetchAll(PDO::FETCH_COLUMN);
                } catch (Exception $e) {}

                if (empty($itens)) {
                    $itens = [
                        \'Consultas liberadas imediatamente\',
                        \'Desconto de até 50% em especialidades\',
                        \'Cobertura em exames laboratoriais\',
                        \'Atendimento prioritário no WhatsApp\'
                    ];
                }

                $p_url = !empty($plano[\'pricing_plan_button_url\']) 
                    ? $plano[\'pricing_plan_button_url\'] 
                    : \'https://wa.me/\' . $whats_clean . \'?text=\' . urlencode(\'Olá! Gostaria de contratar o \' . $plano[\'pricing_plan_name\']);
            ?>
            <div class="<?= $col_class; ?> d-flex">
                <div class="card-plano w-100 <?= $is_destaque ? \'destaque\' : \'\'; ?>">
                    <?php if(!empty($p_badge)): ?>
                        <div class="tag-plano-destaque">
                            <i class="fas fa-fire me-1"></i> <?= htmlspecialchars($p_badge); ?>
                        </div>
                    <?php endif; ?>

                    <div class="text-center">
                        <div class="icone-plano-box">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h5 class="plano-titulo"><?= htmlspecialchars($plano[\'pricing_plan_name\']); ?></h5>
                        <p class="plano-subtitulo">Acesso imediato sem burocracia</p>
                    </div>

                    <div class="bloco-preco">
                        <div>
                            <span class="preco-valor"><?= htmlspecialchars($plano[\'pricing_plan_price\']); ?></span>
                            <span class="preco-periodo"><?= htmlspecialchars($plano[\'pricing_plan_period\'] ?? \'/mês\'); ?></span>
                        </div>
                        <span class="badge-adesao">
                            <i class="fas fa-check-circle me-1"></i> SEM CARÊNCIA
                        </span>
                    </div>

                    <ul class="recursos-list">
                        <?php foreach ($itens as $item_txt): ?>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><?= htmlspecialchars($item_txt); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <a href="<?= htmlspecialchars($p_url); ?>" target="_blank" rel="noopener noreferrer" class="btn <?= $is_destaque ? \'btn-foco-primary\' : \'btn-outline-danger\'; ?> w-100 py-2 mt-auto fw-bold text-nowrap justify-content-center" style="font-size: 0.86rem;">
                        <i class="fas fa-shield-alt me-1"></i> <?= htmlspecialchars($plano[\'pricing_plan_button_text\'] ?: \'ADERIR AO PLANO\'); ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==============================================================================
     5. ESPECIALIDADES MÉDICAS & EXAMES (ROBUSTO & ACESSÍVEL)
     ============================================================================== -->
<section id="servicos" class="section-estetica">
    <div class="container" style="max-width: 1240px;">
        <div class="text-center max-w-700 mx-auto section-header-compact">
            <span class="badge-estetica-topo text-uppercase small fw-bold px-3 py-1 rounded-pill">
                <i class="fas fa-stethoscope me-1" style="color: var(--ps-primary);"></i> Cuidado Especializado
            </span>
            <h2 class="h3 fw-bold mt-2" style="color: var(--ps-title);">Especialidades Médicas &amp; Exames</h2>
            <p class="text-muted small mb-0">Atendimento ambulatorial com hora marcada, corpo clínico experiente e laudos confiáveis.</p>
        </div>

        <?php
        $servicos = [];
        try {
            $st_serv = $pdo->query("SELECT * FROM tbl_service WHERE status = \'Active\' ORDER BY id ASC LIMIT 6");
            $servicos = $st_serv->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
        ?>

        <div class="row g-3 justify-content-center">
            <?php if(!empty($servicos)): ?>
                <?php foreach ($servicos as $servico): 
                    $s_photo = (!empty($servico[\'photo\']) && file_exists(__DIR__ . \'/assets/uploads/\' . $servico[\'photo\']))
                        ? BASE_URL . \'assets/uploads/\' . $servico[\'photo\']
                        : \'\';
                    $s_desc = !empty($servico[\'short_description\']) ? strip_tags($servico[\'short_description\']) : \'Atendimento com suporte integral e diagnóstico precoce seguro.\';
                ?>
                <div class="col-xl-4 col-md-6">
                    <div class="card-estetica">
                        <div>
                            <?php if(!empty($s_photo)): ?>
                                <div style="height: 160px; overflow: hidden; border-radius: 10px; margin-bottom: 12px;">
                                    <img src="<?= $s_photo; ?>" alt="<?= htmlspecialchars($servico[\'name\']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            <?php else: ?>
                                <div class="icon-estetica-box mb-3">
                                    <i class="fas fa-stethoscope"></i>
                                </div>
                            <?php endif; ?>

                            <h5 class="fw-bold mb-1" style="color: var(--ps-title);"><?= htmlspecialchars($servico[\'name\']); ?></h5>
                            <div class="text-muted small mb-3">
                                <?= htmlspecialchars(mb_strimwidth($s_desc, 0, 130, \'...\')); ?>
                            </div>
                        </div>

                        <div class="pt-2 border-top border-light-subtle mt-auto">
                            <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode(\'Olá! Gostaria de agendar atendimento na especialidade: \' . $servico[\'name\']); ?>" target="_blank" rel="noopener noreferrer" class="btn-agendar-estetica">
                                <i class="fas fa-calendar-check me-2"></i> Agendar Especialidade
                            </a>
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
            <a href="<?= BASE_URL; ?>servicos.php" class="btn btn-outline-danger fw-bold px-4 py-2 rounded-pill small">
                <i class="fas fa-th-large me-2"></i> Ver Todas as Especialidades &amp; Exames
            </a>
        </div>
    </div>
</section>

<!-- ==============================================================================
     6. CORPO CLÍNICO / MÉDICOS (ENQUADRAMENTO EQUILIBRADO)
     ============================================================================== -->
<section id="medicos" class="section-medicos">
    <div class="container" style="max-width: 1240px;">
        <div class="text-center max-w-700 mx-auto section-header-compact">
            <span class="fw-bold text-uppercase small letter-spacing-1" style="color: var(--ps-primary);">Profissionais Dedicados</span>
            <h2 class="h3 fw-bold mt-1" style="color: var(--ps-title);">Nosso Corpo Clínico</h2>
            <p class="text-muted small mb-0">Médicos capacitados prontos para acolher você e sua família com respeito e cuidado.</p>
        </div>

        <?php
        $medicos = [];
        try {
            $st_doc = $pdo->query("SELECT d.*, dg.designation_name FROM tbl_doctor d LEFT JOIN tbl_designation dg ON d.designation_id = dg.designation_id ORDER BY d.id ASC LIMIT 4");
            $medicos = $st_doc->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
        ?>

        <div class="row g-3 justify-content-center">
            <?php foreach ($medicos as $doc): 
                $doc_photo = (!empty($doc[\'photo\']) && file_exists(__DIR__ . \'/assets/uploads/\' . $doc[\'photo\']))
                    ? BASE_URL . \'assets/uploads/\' . $doc[\'photo\']
                    : BASE_URL . \'assets/uploads/department-1.png\';
            ?>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card-medico">
                    <img src="<?= $doc_photo; ?>" alt="<?= htmlspecialchars($doc[\'name\']); ?>" class="card-medico-thumb">
                    <div class="card-medico-body">
                        <h6 class="card-medico-name"><?= htmlspecialchars($doc[\'name\']); ?></h6>
                        <span class="card-medico-spec"><?= htmlspecialchars($doc[\'designation_name\'] ?? \'Médico Especialista\'); ?></span>
                        <div class="card-medico-footer">
                            <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode(\'Olá! Gostaria de agendar uma consulta com \' . $doc[\'name\']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger w-100 fw-bold">
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

<!-- ==============================================================================
     7. DEPOIMENTOS DE PACIENTES
     ============================================================================== -->
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
            <h2 class="h3 fw-bold mt-1" style="color: var(--ps-title);">Quem Cuida da Saúde Conosco, Recomenda</h2>
            <p class="text-muted small mb-0">Depoimentos reais de quem confia na dedicação e no acolhimento da Pronto Saúde.</p>
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
                        "<?= strip_tags($dep[\'comment\']); ?>"
                    </p>
                    <div class="d-flex align-items-center gap-3 pt-3 border-top">
                        <div class="text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="background: var(--ps-primary); width: 38px; height: 38px; font-size: 0.95rem;">
                            <?= strtoupper(substr($dep[\'name\'], 0, 1)); ?>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark small"><?= htmlspecialchars($dep[\'name\']); ?></h6>
                            <span class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($dep[\'designation\'] ?: \'Paciente Pronto Saúde\'); ?></span>
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
     8. CONVÊNIOS E PARCEIROS
     ============================================================================== -->
<?php
$parceiros = [];
try {
    $st_parc = $pdo->query("SELECT * FROM tbl_partner ORDER BY id ASC LIMIT 8");
    $parceiros = $st_parc->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<?php if(!empty($parceiros)): ?>
<section id="parceiros" class="section-parceiros border-top border-bottom">
    <div class="container text-center">
        <span class="fw-bold text-uppercase small d-block mb-1" style="color: var(--ps-primary);">Convênios &amp; Parcerias</span>
        <h5 class="fw-bold mb-3" style="color: var(--ps-title);">Facilidades &amp; Sistema Livre Escolha</h5>
        <div class="row align-items-center justify-content-center g-3">
            <?php foreach ($parceiros as $parc): 
                $p_logo = (!empty($parc[\'photo\']) && file_exists(__DIR__ . \'/assets/uploads/\' . $parc[\'photo\']))
                    ? BASE_URL . \'assets/uploads/\' . $parc[\'photo\']
                    : \'\';
            ?>
                <?php if(!empty($p_logo)): ?>
                <div class="col-lg-2 col-md-3 col-4">
                    <img src="<?= $p_logo; ?>" alt="<?= htmlspecialchars($parc[\'name\']); ?>" class="img-fluid" style="max-height: 42px; opacity: 0.85;">
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==============================================================================
     9. PERGUNTAS FREQUENTES (FAQ CLÍNICO)
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
        </div>
        <div class="max-w-800 mx-auto">
            <div class="accordion accordion-foco" id="faqAccordion">
                <?php if(!empty($faqs)): ?>
                    <?php foreach ($faqs as $idx => $faq): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFaq<?= $faq[\'faq_id\']; ?>">
                            <button class="accordion-button <?= $idx !== 0 ? \'collapsed\' : \'\'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaq<?= $faq[\'faq_id\']; ?>" aria-expanded="<?= $idx === 0 ? \'true\' : \'false\'; ?>" aria-controls="collapseFaq<?= $faq[\'faq_id\']; ?>">
                                <i class="fas fa-question-circle me-2" style="color: var(--ps-primary);"></i> <?= htmlspecialchars($faq[\'faq_title\']); ?>
                            </button>
                        </h2>
                        <div id="collapseFaq<?= $faq[\'faq_id\']; ?>" class="accordion-collapse collapse <?= $idx === 0 ? \'show\' : \'\'; ?>" aria-labelledby="headingFaq<?= $faq[\'faq_id\']; ?>" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?= $faq[\'faq_content\']; ?>
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
     10. FALE CONOSCO & CONTATO DIRETO
     ============================================================================== -->
<section id="contato" class="section-contato">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <span class="fw-bold text-uppercase small" style="color: var(--ps-primary);">Atendimento Direto</span>
                <h2 class="h3 fw-bold mt-1 mb-3" style="color: var(--ps-title);">Agende Sua Consulta ou Tire Dúvidas</h2>
                <p class="text-muted small mb-4">Nossa equipe de recepção está pronta para atender com agilidade e cordialidade.</p>
                <div class="d-flex flex-column gap-2">
                    <?php if(!empty($contact_phone)): ?>
                    <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border">
                        <div class="card-contato-icon text-white" style="background: var(--ps-primary);"><i class="fas fa-phone-alt"></i></div>
                        <div>
                            <span class="text-muted" style="font-size: 0.75rem;">Telefone da Recepção</span>
                            <h6 class="fw-bold mb-0 text-dark small"><?= htmlspecialchars($contact_phone); ?></h6>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border">
                        <div class="card-contato-icon text-white" style="background: var(--ps-secondary);"><i class="fab fa-whatsapp"></i></div>
                        <div>
                            <span class="text-muted" style="font-size: 0.75rem;">WhatsApp Oficial</span>
                            <h6 class="fw-bold mb-0 text-dark small">Agendamento em 1 Minuto</h6>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm border">
                        <div class="card-contato-icon text-white" style="background: var(--ps-primary-dark);"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <span class="text-muted" style="font-size: 0.75rem;">Nossa Unidade</span>
                            <h6 class="fw-bold mb-0 text-dark small"><?= htmlspecialchars($contact_address ?: \'Gardênia Azul, Jacarepaguá, Rio de Janeiro\'); ?></h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card-contato-form">
                    <h5 class="fw-bold mb-3" style="color: var(--ps-title);">Envie Sua Mensagem</h5>
                    <form action="<?= BASE_URL; ?>send_email.php" method="POST" id="formContatoSite">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-semibold mb-1">Seu Nome *</label>
                                <input type="text" name="visitor_name" class="form-control" required placeholder="Nome completo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-semibold mb-1">Telefone / WhatsApp *</label>
                                <input type="text" name="visitor_phone" class="form-control" required placeholder="(21) 99999-9999">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted fw-semibold mb-1">Seu E-mail *</label>
                                <input type="email" name="visitor_email" class="form-control" required placeholder="seu@email.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted fw-semibold mb-1">Como podemos ajudar? *</label>
                                <textarea name="visitor_comment" class="form-control" rows="3" required placeholder="Informe a consulta, exame ou especialidade desejada..."></textarea>
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn-foco-primary w-100 justify-content-center py-2">
                                    <i class="fas fa-paper-plane me-1"></i> ENVIAR MENSAGEM
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once(\'footer.php\'); ?>
';

// Remove BOM se houver
if (substr($index_code, 0, 3) === "\xEF\xBB\xBF") {
    $index_code = substr($index_code, 3);
}

file_put_contents($index_file, $index_code);
echo "Sucesso: index.php atualizado com arquitetura densa e identidade Pronto Saúde (" . strlen($index_code) . " bytes).\n";
