<?php require_once('header.php'); ?>
<?php
// Sincroniza seções da Home com o status de cada página individual
if (function_exists('pronto_is_page_active')) {
    if (!pronto_is_page_active('doctor')) $home_status_doctor = 0;
    if (!pronto_is_page_active('servicos')) $home_status_service = 0;
    if (!pronto_is_page_active('fidelidade')) $home_status_pricing = 0;
    if (!pronto_is_page_active('news')) $home_status_news = 0;
}
?>

<!-- ========================================================
     1. HERO SECTION NOVO ESTILO FOCO7 (IDENTIDADE PRONTO SAÚDE)
     ======================================================== -->
<section class="ps-foco-hero" id="home">
    <div class="container">
        <div class="row align-items-center">
            <!-- Coluna Esquerda: Chamada Principal e CTA -->
            <div class="col-lg-7 col-md-12">
                <div class="ps-fade-up-element">
                    <span class="ps-hero-badge">
                        <i class="fa fa-hospital-o"></i> Centro Médico & Odontológico
                    </span>
                    <h1 class="ps-hero-title">
                        Cuidar hoje para que você viva o amanhã com saúde e tranquilidade
                    </h1>
                    <p class="ps-hero-subtitle">
                        A Pronto Saúde combina corpo clínico altamente qualificado, infraestrutura moderna de diagnóstico e atendimento humanizado para acolher você e toda a sua família.
                    </p>
                    <div class="ps-hero-actions">
                        <a href="https://wa.me/+5521985831464?text=Olá,%20gostaria%20de%20agendar%20uma%20consulta" target="_blank" rel="noopener" class="btn-hero-cta">
                            <i class="fa fa-calendar-check-o"></i> AGENDAR MINHA CONSULTA
                        </a>
                        <a href="https://wa.me/+5521985831464?text=Olá,%20gostaria%20de%20mais%20informações%20sobre%20os%20serviços" target="_blank" rel="noopener" class="btn-hero-whatsapp">
                            <i class="fa fa-whatsapp text-success"></i> FALAR NO WHATSAPP
                        </a>
                    </div>
                    <div class="ps-hero-stats">
                        <div class="ps-hero-stat-item">
                            <h4>+15 Anos</h4>
                            <p>De Tradição e Cuidado</p>
                        </div>
                        <div class="ps-hero-stat-item">
                            <h4>+50 Mil</h4>
                            <p>Pacientes Atendidos</p>
                        </div>
                        <div class="ps-hero-stat-item">
                            <h4>+25 Especialidades</h4>
                            <p>Médicas Integradas</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita: Card Flutuante de Agendamento Rápido em 1 Minuto -->
            <div class="col-lg-5 col-md-12">
                <div class="ps-quick-card ps-fade-up-element">
                    <span class="ps-quick-badge">
                        <i class="fa fa-heartbeat"></i> Atendimento Rápido
                    </span>
                    <h2 class="ps-quick-title">Agendamento em 1 Minuto</h2>
                    <p class="ps-quick-sub">
                        Escolha seu tipo de atendimento e receba retorno imediato da nossa central especializada.
                    </p>

                    <div class="ps-quick-grid">
                        <a href="#servicos" class="ps-quick-option">
                            <i class="fa fa-user-md ps-quick-icon"></i>
                            <span class="ps-quick-name">Consultas</span>
                            <span class="ps-quick-price">A partir de R$ 90,00</span>
                        </a>
                        <a href="#servicos" class="ps-quick-option">
                            <i class="fa fa-flask ps-quick-icon"></i>
                            <span class="ps-quick-name">Exames</span>
                            <span class="ps-quick-price">Resultados rápidos</span>
                        </a>
                        <a href="#servicos" class="ps-quick-option">
                            <i class="fa fa-smile-o ps-quick-icon"></i>
                            <span class="ps-quick-name">Odontologia</span>
                            <span class="ps-quick-price">Tratamentos completos</span>
                        </a>
                        <a href="#fidelidade" class="ps-quick-option">
                            <i class="fa fa-id-card-o ps-quick-icon"></i>
                            <span class="ps-quick-name">Fidelidade</span>
                            <span class="ps-quick-price">Descontos de até 50%</span>
                        </a>
                    </div>

                    <a href="#servicos" class="btn-quick-cta">
                        VER TODOS OS SERVIÇOS E ESPECIALIDADES
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================
     2. SEÇÃO DE BENEFÍCIOS / CUIDADO PREVENTIVO (ESTILO FOCO7)
     ======================================================== -->
<section class="ps-benefits-section" id="diferenciais">
    <div class="container">
        <div class="ps-section-header ps-fade-up-element">
            <span class="ps-section-badge">Saúde Preventiva & Cuidado</span>
            <h2 class="ps-section-title">O cuidado com sua saúde não pode esperar o imprevisto</h2>
            <p class="ps-section-subtitle">
                A prevenção e o diagnóstico precoce salvam vidas. Cuidar de você antes do agravamento dos sintomas garante mais longevidade e economia para sua família.
            </p>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="ps-benefit-card ps-fade-up-element">
                    <div>
                        <div class="ps-benefit-icon-box">
                            <i class="fa fa-stethoscope"></i>
                        </div>
                        <h3>Diagnóstico Rápido e Seguro</h3>
                        <p>
                            Exames laboratoriais e consultas médicas integradas no mesmo espaço físico. Agilidade para você não perder tempo entre a solicitação e o início do tratamento.
                        </p>
                    </div>
                    <div class="ps-benefit-footer">
                        <i class="fa fa-shield"></i>
                        <span>Solução Pronto Saúde: Laudos e exames laboratoriais no mesmo dia.</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="ps-benefit-card ps-fade-up-element">
                    <div>
                        <div class="ps-benefit-icon-box">
                            <i class="fa fa-user-md"></i>
                        </div>
                        <h3>Corpo Clínico Integrado</h3>
                        <p>
                            Mais de 25 especialidades médicas conectadas, garantindo que seu histórico seja avaliado de forma multidisciplinar com total atenção aos detalhes clínicos.
                        </p>
                    </div>
                    <div class="ps-benefit-footer">
                        <i class="fa fa-check-circle"></i>
                        <span>Solução Pronto Saúde: Médicos experientes com agendamento simplificado.</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="ps-benefit-card ps-fade-up-element">
                    <div>
                        <div class="ps-benefit-icon-box">
                            <i class="fa fa-credit-card"></i>
                        </div>
                        <h3>Acesso Fácil e Preços Justos</h3>
                        <p>
                            Eliminamos burocracias e mensalidades abusivas de planos tradicionais. Consultas a preços acessíveis e vantagens exclusivas com nosso Cartão Fidelidade.
                        </p>
                    </div>
                    <div class="ps-benefit-footer">
                        <i class="fa fa-tags"></i>
                        <span>Solução Pronto Saúde: Consultas acessíveis a partir de R$ 90,00.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================
     3. SEÇÃO SOBRE A CLÍNICA & ESTATÍSTICAS (ESTILO FOCO7)
     ======================================================== -->
<section class="ps-about-section" id="sobre">
    <div class="container">
        <div class="row align-items-center">
            <!-- Esquerda: História e Números -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="ps-fade-up-element">
                    <span class="ps-section-badge">Compromisso com a Vida</span>
                    <h2 class="ps-section-title" style="text-align: left;">
                        Excelência Médica e Diagnóstica ao Seu Alcance
                    </h2>
                    <p style="font-size: 1.05rem; color: #475569; line-height: 1.7; margin-bottom: 24px;">
                        A <strong>Pronto Saúde</strong> nasceu com a missão de democratizar o acesso à medicina de excelência. Combinamos atendimento humanizado, tecnologia moderna em diagnósticos laboratoriais e uma estrutura acolhedora para proporcionar a melhor experiência a cada paciente.
                    </p>

                    <div class="ps-stat-card-row">
                        <div class="ps-stat-box">
                            <div class="ps-stat-icon">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <div class="ps-stat-info">
                                <h3>+15 Anos</h3>
                                <p>De Tradição Sólida</p>
                            </div>
                        </div>
                        <div class="ps-stat-box">
                            <div class="ps-stat-icon">
                                <i class="fa fa-smile-o"></i>
                            </div>
                            <div class="ps-stat-info">
                                <h3>99.2%</h3>
                                <p>Índice de Satisfação</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Direita: Card de Diferenciais -->
            <div class="col-lg-6 col-md-12">
                <div class="ps-about-features-card ps-fade-up-element">
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-bottom: 24px;">
                        Por que escolher a Pronto Saúde?
                    </h3>

                    <div class="ps-feature-list-item">
                        <div class="ps-feature-check-icon"><i class="fa fa-check"></i></div>
                        <div class="ps-feature-text">
                            <h4>Central de Agendamento Ágil</h4>
                            <p>Atendimento humanizado e sem filas pelo WhatsApp oficial ou canais digitais.</p>
                        </div>
                    </div>

                    <div class="ps-feature-list-item">
                        <div class="ps-feature-check-icon"><i class="fa fa-check"></i></div>
                        <div class="ps-feature-text">
                            <h4>Equipamentos de Diagnóstico Modernos</h4>
                            <p>Exames laboratoriais e de imagem com precisão certificada e rapidez na entrega.</p>
                        </div>
                    </div>

                    <div class="ps-feature-list-item">
                        <div class="ps-feature-check-icon"><i class="fa fa-check"></i></div>
                        <div class="ps-feature-text">
                            <h4>Atendimento Respeitoso e Pontual</h4>
                            <p>Consultórios climatizados, instalações acessíveis e médicos que ouvem você.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if($home_status_service == 1): ?>
<!-- ========================================================
     4. SEÇÃO DE SERVIÇOS & ESPECIALIDADES (CARDS ESTILO FOCO7)
     ======================================================== -->
<section class="ps-services-section" id="servicos">
    <div class="container">
        <div class="ps-section-header ps-fade-up-element">
            <span class="ps-section-badge">Nossas Especialidades</span>
            <h2 class="ps-section-title"><?php echo htmlspecialchars($home_title_service ?: 'Serviços Médicos e Exames Especializados'); ?></h2>
            <p class="ps-section-subtitle">
                <?php echo htmlspecialchars($home_subtitle_service ?: 'Consulte nossos procedimentos e agende seu horário com os melhores médicos e especialistas da região.'); ?>
            </p>
        </div>

        <div class="row">
            <?php
            $statement = $pdo->prepare("SELECT * FROM tbl_service WHERE status = 'Active' ORDER BY id ASC LIMIT 6");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);							
            foreach ($result as $row) {
                $link_btn = !empty($row['button_url']) ? $row['button_url'] : 'https://wa.me/+5521985831464?text=' . urlencode('Olá, gostaria de agendar uma consulta em ' . $row['name']);
                $btn_label = !empty($row['button_text']) ? $row['button_text'] : 'Agendar Este Serviço';
                $price_display = !empty($row['price']) ? 'R$ ' . htmlspecialchars($row['price']) : 'Sob Consulta';
                ?>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="ps-service-card-modern ps-fade-up-element">
                        <div>
                            <div class="ps-card-topbar">
                                <div class="ps-card-icon-pill">
                                    <i class="fa fa-stethoscope"></i>
                                </div>
                                <span class="ps-card-time-badge">
                                    <i class="fa fa-clock-o"></i> Atendimento Imediato
                                </span>
                            </div>
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p class="desc">
                                <?php echo htmlspecialchars(substr(strip_tags($row['short_description']), 0, 140)) . '...'; ?>
                            </p>
                        </div>

                        <div>
                            <div class="ps-card-price-row">
                                <span class="ps-price-label">Condição Especial:</span>
                                <span class="ps-price-value"><?php echo $price_display; ?></span>
                            </div>
                            <a href="<?php echo htmlspecialchars($link_btn); ?>" target="_blank" rel="noopener" class="btn-card-action">
                                <i class="fa fa-calendar-check-o me-1"></i> <?php echo htmlspecialchars($btn_label); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL; ?>servicos.php" class="btn-foco-outline" style="padding: 12px 28px; font-size: 1rem;">
                <i class="fa fa-th-large"></i> Ver Todas as Especialidades Médicas
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if($home_status_pricing == 1): ?>
<!-- ========================================================
     5. SEÇÃO DE PLANOS & CARTÃO FIDELIDADE (ESTILO FOCO7)
     ======================================================== -->
<section class="ps-plans-section" id="fidelidade">
    <div class="container">
        <div class="ps-section-header ps-fade-up-element">
            <span class="ps-section-badge">Investimento Inteligente</span>
            <h2 class="ps-section-title"><?php echo htmlspecialchars($home_title_pricing ?: 'Escolha o Plano Ideal para Sua Saúde'); ?></h2>
            <p class="ps-section-subtitle">
                <?php echo htmlspecialchars($home_subtitle_pricing ?: 'Sem carência abusiva, sem limites de utilização e com descontos reais em consultas e exames laboratoriais.'); ?>
            </p>
        </div>

        <div class="row">
            <?php
            $statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan WHERE status = 1 ORDER BY pricing_plan_id ASC LIMIT 3");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                $plan_btn_text = !empty($row['pricing_plan_button_text']) ? $row['pricing_plan_button_text'] : 'Contratar Agora';
                $plan_btn_url = !empty($row['pricing_plan_button_url']) ? $row['pricing_plan_button_url'] : 'https://wa.me/+5521985831464?text=' . urlencode('Olá, quero contratar o ' . $row['pricing_plan_name']);
                $is_popular = !empty($row['pricing_plan_popular']);
                $price_val = trim($row['pricing_plan_price'] ?? '');
                $period_val = trim($row['pricing_plan_period'] ?? '/mês');
                ?>
                <div class="col-md-4 mb-4">
                    <div class="ps-plan-card <?= $is_popular ? 'popular' : ''; ?> ps-fade-up-element">
                        <?php if($is_popular): ?>
                            <span class="ps-plan-popular-badge">MAIS POPULAR ⭐</span>
                        <?php endif; ?>

                        <div>
                            <div class="ps-plan-icon-top">
                                <i class="fa fa-id-card-o"></i>
                            </div>
                            <h3 class="ps-plan-name"><?php echo htmlspecialchars($row['pricing_plan_name']); ?></h3>

                            <div class="ps-plan-price-wrap">
                                <?php if(strtolower($price_val) === 'consultar' || stripos($price_val, 'sob') !== false): ?>
                                    <span class="ps-plan-price-amount" style="font-size: 1.6rem;">Sob Consulta</span>
                                <?php else: ?>
                                    <span class="ps-plan-price-amount">R$ <?php echo htmlspecialchars($price_val); ?></span>
                                    <span class="ps-plan-period"><?php echo htmlspecialchars($period_val); ?></span>
                                <?php endif; ?>
                            </div>

                            <ul class="ps-plan-features-list">
                                <?php
                                $statement1 = $pdo->prepare("SELECT * FROM tbl_pricing_item WHERE pricing_plan_id=? ORDER BY pricing_item_id ASC");
                                $statement1->execute(array($row['pricing_plan_id']));
                                $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);							
                                foreach ($result1 as $row1) {
                                    ?>
                                    <li>
                                        <i class="fa fa-check-circle"></i>
                                        <span><?php echo htmlspecialchars($row1['pricing_item_name']); ?></span>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>

                        <a href="<?php echo htmlspecialchars($plan_btn_url); ?>" target="_blank" rel="noopener" class="<?= $is_popular ? 'btn-foco-primary' : 'btn-card-action'; ?>" style="width: 100%; justify-content: center;">
                            <?php echo htmlspecialchars($plan_btn_text); ?>
                        </a>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if($home_status_doctor == 1): ?>
<!-- ========================================================
     6. SEÇÃO CORPO CLÍNICO & MÉDICOS
     ======================================================== -->
<section class="ps-services-section" id="medicos" style="background: #ffffff;">
    <div class="container">
        <div class="ps-section-header ps-fade-up-element">
            <span class="ps-section-badge">Corpo Clínico Especializado</span>
            <h2 class="ps-section-title"><?php echo htmlspecialchars($home_title_doctor ?: 'Médicos e Especialistas Renomados'); ?></h2>
            <p class="ps-section-subtitle">
                <?php echo htmlspecialchars($home_subtitle_doctor ?: 'Profissionais com sólida experiência médica, prontos para cuidar da sua saúde com dedicação.'); ?>
            </p>
        </div>

        <div class="row">
            <?php
            $statement = $pdo->prepare("SELECT 
                                            t1.id, t1.name, t1.slug, t1.designation_id, t1.photo,
                                            t2.designation_name
                                        FROM tbl_doctor t1
                                        JOIN tbl_designation t2 ON t1.designation_id = t2.designation_id
                                        WHERE t1.status = 'Active'
                                        LIMIT 4");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);							
            foreach ($result as $row) {
                $photo_src = (!empty($row['photo']) && file_exists('assets/uploads/' . $row['photo'])) ? BASE_URL . 'assets/uploads/' . $row['photo'] : BASE_URL . 'assets/images/sem-foto.gif';
                ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="ps-service-card-modern ps-fade-up-element" style="text-align: center; padding: 24px 20px;">
                        <div style="width: 110px; height: 110px; border-radius: 50%; overflow: hidden; margin: 0 auto 16px auto; border: 3px solid #0a6b74; box-shadow: 0 6px 16px rgba(10, 107, 116, 0.2);">
                            <img src="<?php echo $photo_src; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <h4 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </h4>
                        <span style="font-size: 0.85rem; color: #0a6b74; font-weight: 700; display: block; margin-bottom: 16px;">
                            <?php echo htmlspecialchars($row['designation_name']); ?>
                        </span>
                        <a href="https://wa.me/+5521985831464?text=<?php echo urlencode('Olá, gostaria de agendar uma consulta com ' . $row['name']); ?>" target="_blank" rel="noopener" class="btn-card-action" style="padding: 9px 14px; font-size: 0.825rem;">
                            <i class="fa fa-calendar-check-o"></i> Agendar Consulta
                        </a>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if($home_status_testimonial == 1): ?>
<!-- ========================================================
     7. SEÇÃO DE DEPOIMENTOS DE PACIENTES
     ======================================================== -->
<section class="ps-about-section" id="depoimentos">
    <div class="container">
        <div class="ps-section-header ps-fade-up-element">
            <span class="ps-section-badge">Prova Social</span>
            <h2 class="ps-section-title"><?php echo htmlspecialchars($home_title_testimonial ?: 'O Que Nossos Pacientes Dizem'); ?></h2>
            <p class="ps-section-subtitle">
                <?php echo htmlspecialchars($home_subtitle_testimonial ?: 'A opinião de quem confia na dedicação e no atendimento humanizado da Pronto Saúde diariamente.'); ?>
            </p>
        </div>

        <div class="row">
            <?php
            $statement = $pdo->prepare("SELECT * FROM tbl_testimonial WHERE status = 'Active' ORDER BY id DESC LIMIT 3");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);							
            foreach ($result as $row) {
                ?>
                <div class="col-md-4 mb-4">
                    <div class="ps-benefit-card ps-fade-up-element" style="background: #ffffff; padding: 32px 26px;">
                        <div>
                            <div style="color: #f59e0b; font-size: 1.15rem; margin-bottom: 14px;">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                            </div>
                            <p style="font-size: 0.95rem; color: #334155; line-height: 1.65; font-style: italic; margin-bottom: 22px;">
                                "<?php echo nl2br(htmlspecialchars($row['comment'])); ?>"
                            </p>
                        </div>
                        <div class="ps-benefit-footer" style="border-top: 1px solid #f1f5f9; padding-top: 14px;">
                            <i class="fa fa-user-circle" style="font-size: 1.25rem;"></i>
                            <div>
                                <strong style="color: #0f172a; display: block; font-size: 0.9rem;"><?php echo htmlspecialchars($row['name']); ?></strong>
                                <small style="color: #64748b;"><?php echo htmlspecialchars($row['designation']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========================================================
     8. SEÇÃO FAQ - PERGUNTAS FREQUENTES (ESTILO FOCO7)
     ======================================================== -->
<section class="ps-faq-section" id="faq">
    <div class="container">
        <div class="ps-section-header ps-fade-up-element">
            <span class="ps-section-badge">Tire Suas Dúvidas</span>
            <h2 class="ps-section-title">Perguntas Frequentes</h2>
            <p class="ps-section-subtitle">
                Confira as respostas para as principais dúvidas sobre consultas, exames, prazos de entrega e convênios.
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-12">
                <div class="ps-faq-item ps-fade-up-element active">
                    <div class="ps-faq-header" onclick="this.parentElement.classList.toggle('active')">
                        <div class="ps-faq-header-left">
                            <span class="ps-faq-q-icon">?</span>
                            <h4>Como funciona o agendamento de consultas na Pronto Saúde?</h4>
                        </div>
                        <i class="fa fa-chevron-down ps-faq-toggle-icon"></i>
                    </div>
                    <div class="ps-faq-body">
                        O agendamento é imediato e simplificado. Você pode clicar em qualquer botão de agendamento do site ou chamar nossa central pelo WhatsApp oficial. Nossa equipe localiza o melhor horário para você no mesmo dia.
                    </div>
                </div>

                <div class="ps-faq-item ps-fade-up-element">
                    <div class="ps-faq-header" onclick="this.parentElement.classList.toggle('active')">
                        <div class="ps-faq-header-left">
                            <span class="ps-faq-q-icon">?</span>
                            <h4>Em quanto tempo os resultados dos exames laboratoriais ficam prontos?</h4>
                        </div>
                        <i class="fa fa-chevron-down ps-faq-toggle-icon"></i>
                    </div>
                    <div class="ps-faq-body">
                        A grande maioria dos exames de rotina e laboratoriais têm laudos emitidos no mesmo dia ou em até 24 horas úteis, com consulta online para sua comodidade.
                    </div>
                </div>

                <div class="ps-faq-item ps-fade-up-element">
                    <div class="ps-faq-header" onclick="this.parentElement.classList.toggle('active')">
                        <div class="ps-faq-header-left">
                            <span class="ps-faq-q-icon">?</span>
                            <h4>A Pronto Saúde atende convênios ou apenas particular?</h4>
                        </div>
                        <i class="fa fa-chevron-down ps-faq-toggle-icon"></i>
                    </div>
                    <div class="ps-faq-body">
                        Atendemos os principais convênios e planos de saúde da região, além de oferecermos a modalidade particular a preços populares e acessíveis, com condições especiais pelo Cartão Fidelidade Pronto Saúde.
                    </div>
                </div>

                <div class="ps-faq-item ps-fade-up-element">
                    <div class="ps-faq-header" onclick="this.parentElement.classList.toggle('active')">
                        <div class="ps-faq-header-left">
                            <span class="ps-faq-q-icon">?</span>
                            <h4>Como funciona o Cartão Fidelidade Pronto Saúde?</h4>
                        </div>
                        <i class="fa fa-chevron-down ps-faq-toggle-icon"></i>
                    </div>
                    <div class="ps-faq-body">
                        O Cartão Fidelidade é um programa de benefícios que oferece até 50% de desconto em consultas e exames para você e seus dependentes, sem carência e sem limite de utilizações no ano.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>