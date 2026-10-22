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
     1. SLIDER / CARROSSEL PRINCIPAL (IMAGENS ORIGINAIS & FOCO7)
     ======================================================== -->
<section class="main-slider" id="home">
	<div class="slider">
		<ul class="bxslider">
			<?php
			$statement = $pdo->prepare("SELECT * FROM tbl_slider WHERE status=? ORDER BY id DESC");
			$statement->execute(array('Active'));
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($result as $row) 
			{
				$align = 'tal';
				if($row['position'] == 'Left') { $align = 'tal'; }
				if($row['position'] == 'Center') { $align = 'tac'; }
				if($row['position'] == 'Right') { $align = 'tar'; }
				$slider_photo = (!empty($row['photo']) && file_exists('assets/uploads/' . $row['photo'])) 
								? BASE_URL . 'assets/uploads/' . $row['photo'] 
								: BASE_URL . 'assets/uploads/department-1.png';
				?>
				<li style="background-image:url(<?php echo $slider_photo; ?>);">
					<div class="overlay"></div>
					<div class="content">
						<div class="inner <?php echo $align; ?>">
							<div class="text">
								<?php if(!empty($row['heading'])): ?>
								<h2>
									<?php echo htmlspecialchars($row['heading']); ?>
								</h2>
								<?php endif; ?>

								<?php if(!empty($row['subheading'])): ?>
								<h3>
									<?php echo htmlspecialchars($row['subheading']); ?>
								</h3>
								<?php endif; ?>
								
								<?php if(!empty($row['content'])): ?>
								<p>
									<?php echo nl2br(htmlspecialchars($row['content'])); ?>
								</p>
								<?php endif; ?>
								
								<p class="button" style="margin-top: 24px;">
									<?php if(!empty($row['button_text'])): ?>
										<a href="<?php echo htmlspecialchars($row['button_url'] ?: 'https://wa.me/+5521985831464?text=' . urlencode('Olá! Gostaria de agendar uma consulta')); ?>" class="btn btn-flat">
											<i class="fa fa-calendar-check-o me-1"></i> <?php echo htmlspecialchars($row['button_text']); ?>
										</a>
									<?php else: ?>
										<a href="https://wa.me/+5521985831464?text=<?php echo urlencode('Olá! Gostaria de agendar um atendimento na Pronto Saúde'); ?>" target="_blank" rel="noopener" class="btn btn-flat">
											<i class="fa fa-whatsapp me-1"></i> Agendamento Rápido
										</a>
									<?php endif; ?>
								</p>
							</div>
						</div>
					</div>
				</li>
				<?php
			}
			?>			
		</ul>
	</div>
</section>
<!-- Slider End -->

<!-- ========================================================
     2. FAIXA FLUTUANTE DE AGENDAMENTO RÁPIDO EM 1 MINUTO (FOCO7)
     ======================================================== -->
<section class="ps-quick-strip-section">
    <div class="container">
        <div class="ps-quick-strip-wrapper ps-fade-up-element">
            <div class="row align-items-center">
                <div class="col-lg-4 col-md-12 mb-4 mb-lg-0">
                    <span class="ps-quick-badge">
                        <i class="fa fa-bolt"></i> Atendimento Rápido
                    </span>
                    <h3 class="ps-quick-strip-title">Agendamento em 1 Minuto</h3>
                    <p class="ps-quick-strip-sub">
                        Escolha seu tipo de cuidado médico e receba retorno imediato da nossa central especializada.
                    </p>
                </div>
                <div class="col-lg-8 col-md-12">
                    <div class="ps-quick-grid" style="margin-bottom: 0;">
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
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================
     3. SEÇÃO DE DIFERENCIAIS / SAÚDE PREVENTIVA (ESTILO FOCO7)
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
                        <span>Solução Pronto Saúde: Laudos e exames laboratoriais com rapidez e precisão.</span>
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
     4. SEÇÃO SOBRE A CLÍNICA & ESTRUTURA (COM FOTO REAL)
     ======================================================== -->
<section class="ps-about-section" id="sobre">
    <div class="container">
        <div class="row align-items-center">
            <!-- Esquerda: Imagem Institucional Real da Clínica -->
            <div class="col-lg-6 col-md-12 mb-4 mb-lg-0">
                <div class="ps-fade-up-element" style="position: relative;">
                    <div style="border-radius: 20px; overflow: hidden; box-shadow: 0 16px 40px rgba(10, 107, 116, 0.18); border: 4px solid #ffffff;">
                        <img src="<?= BASE_URL; ?>assets/uploads/department-1.png" alt="Instalações Pronto Saúde" loading="lazy" decoding="async" style="width: 100%; height: 380px; object-fit: cover;">
                    </div>
                    <div style="position: absolute; bottom: 20px; left: 20px; background: rgba(7, 24, 34, 0.9); backdrop-filter: blur(8px); padding: 14px 22px; border-radius: 14px; color: #ffffff; border: 1px solid rgba(255,255,255,0.15);">
                        <strong style="display: block; font-size: 1.1rem; color: #10b981;"><i class="fa fa-check-circle me-1"></i> Ambiente Moderno & Acolhedor</strong>
                        <small style="color: #cbd5e1;">Consultórios climatizados e infraestrutura de ponta</small>
                    </div>
                </div>
            </div>

            <!-- Direita: Apresentação e Estatísticas -->
            <div class="col-lg-6 col-md-12">
                <div class="ps-fade-up-element" style="padding-left: 15px;">
                    <span class="ps-section-badge">Compromisso com a Vida</span>
                    <h2 class="ps-section-title" style="text-align: left; font-size: 2.15rem;">
                        Excelência Médica e Diagnóstica ao Seu Alcance
                    </h2>
                    <p style="font-size: 1rem; color: #475569; line-height: 1.7; margin-bottom: 24px;">
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
        </div>
    </div>
</section>

<?php if($home_status_service == 1): ?>
<!-- ========================================================
     5. SEÇÃO DE SERVIÇOS & ESPECIALIDADES (COM FOTOS REAIS)
     ======================================================== -->
<section class="ps-foco-services" id="servicos" style="padding: 75px 0; background: #f8fafc;">
    <div class="container">
        <div class="ps-section-header ps-fade-up-element">
            <span class="ps-section-badge"><i class="fa fa-stethoscope"></i> Nossas Especialidades</span>
            <h2 class="ps-section-title"><?php echo htmlspecialchars($home_title_service ?: 'Serviços Médicos e Exames Especializados'); ?></h2>
            <p class="ps-section-subtitle">
                <?php echo htmlspecialchars($home_subtitle_service ?: 'Consulte nossos procedimentos e agende seu horário com os melhores médicos e especialistas da região.'); ?>
            </p>
        </div>

        <div class="row g-4">
            <?php
            $statement = $pdo->prepare("SELECT * FROM tbl_service WHERE status = 'Active' ORDER BY id ASC LIMIT 6");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);							
            foreach ($result as $row) {
                $link_btn = !empty($row['button_url']) ? $row['button_url'] : BASE_URL . 'service/' . $row['slug'];
                $btn_target = (!empty($row['button_url']) && strpos($row['button_url'], 'http') === 0) ? 'target="_blank" rel="noopener"' : '';
                $btn_label = !empty($row['button_text']) ? $row['button_text'] : 'Agendar Consulta';
                $photo_src = (!empty($row['photo']) && file_exists('assets/uploads/' . $row['photo'])) ? BASE_URL . 'assets/uploads/' . $row['photo'] : BASE_URL . 'assets/images/sem-foto.gif';
                $benefits_items = !empty($row['benefits']) ? explode("\n", str_replace("\r", "", $row['benefits'])) : [];
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="ps-service-card-modern ps-fade-up-element">
                        <div class="ps-service-thumb">
                            <img src="<?php echo $photo_src; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" loading="lazy" decoding="async">
                            <?php if(!empty($row['price'])): ?>
                                <span class="ps-service-price-tag">A partir de R$ <?php echo htmlspecialchars($row['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="ps-service-body">
                            <h3 class="ps-service-title">
                                <a href="<?php echo htmlspecialchars($link_btn); ?>" <?php echo $btn_target; ?>><?php echo htmlspecialchars($row['name']); ?></a>
                            </h3>
                            <p class="ps-service-desc">
                                <?php echo mb_substr(strip_tags($row['short_description']), 0, 140, 'UTF-8') . '...'; ?>
                            </p>
                            <?php if(!empty($benefits_items)): ?>
                                <ul class="ps-service-benefits">
                                    <?php foreach(array_slice($benefits_items, 0, 3) as $b_item): if(!empty(trim($b_item))): ?>
                                        <li><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars(trim($b_item)); ?></li>
                                    <?php endif; endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <a href="<?php echo htmlspecialchars($link_btn); ?>" <?php echo $btn_target; ?> class="ps-service-action-btn">
                                <?php echo htmlspecialchars($btn_label); ?> <i class="fa fa-arrow-right"></i>
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

<?php if($home_status_department == 1): ?>
<!-- ========================================================
     6. SEÇÃO DE DEPARTAMENTOS & CLÍNICAS (COM FOTOS REAIS)
     ======================================================== -->
<section class="department-v2" id="departamentos" style="padding: 75px 0; background: #ffffff;">
	<div class="container">
		<div class="ps-section-header ps-fade-up-element">
			<span class="ps-section-badge"><i class="fa fa-hospital-o"></i> Nossa Estrutura</span>
			<h2 class="ps-section-title"><?php echo htmlspecialchars($home_title_department ?: 'Departamentos & Clínicas Integradas'); ?></h2>
			<p class="ps-section-subtitle"><?php echo htmlspecialchars($home_subtitle_department ?: 'Instalações confortáveis, seguras e equipadas para oferecer diagnósticos precisos e atendimento ágil.'); ?></p>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="department-tab">
					<ul class="nav nav-tabs col-md-4">
						<?php
						$i=0;
						$statement = $pdo->prepare("SELECT * FROM tbl_department ORDER BY dep_id ASC");
						$statement->execute();
						$departments = $statement->fetchAll(PDO::FETCH_ASSOC);							
						foreach ($departments as $row) {
							$i++;
							?>
							<li class="<?php if($i==1) {echo 'active';} ?>"><a href="#tab<?php echo $i; ?>" data-toggle="tab" aria-expanded="<?php if($i==1) {echo 'true';}else{echo 'false';} ?>"><?php echo htmlspecialchars($row['dep_name']); ?></a></li>
							<?php
						}
						?>
					</ul>
					
					<div class="tab-content col-md-8">
						<?php
						$i=0;
						if(isset($departments)) {
							foreach ($departments as $row) {
								$i++;
								$dep_photo = (!empty($row['dep_photo']) && file_exists('assets/uploads/' . $row['dep_photo'])) ? BASE_URL . 'assets/uploads/' . $row['dep_photo'] : BASE_URL . 'assets/uploads/department-1.png';
								?>
								<div class="tab-pane fade <?php if($i==1) {echo 'active in';} ?>" id="tab<?php echo $i; ?>">
									<div class="row align-items-center">										
										<div class="col-md-7">
											<div class="department-content">
												<h2><?php echo htmlspecialchars($row['dep_name']); ?></h2>
												<p><?php echo nl2br(strip_tags($row['dep_content'] ?? '')); ?></p>
												<p class="button" style="margin-top: 20px;">
													<a href="https://wa.me/+5521985831464?text=<?php echo urlencode('Olá, gostaria de agendar uma consulta no departamento de ' . $row['dep_name']); ?>" target="_blank" rel="noopener" class="btn-card-action" style="display: inline-flex; width: auto; padding: 10px 22px;">
														<i class="fa fa-calendar-check-o me-1"></i> Agendar Neste Departamento
													</a>
												</p>
											</div>
										</div>
										<div class="col-md-5">
											<div class="department-photo" style="border-radius: 18px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
												<img class="img-fullwidth" src="<?php echo $dep_photo; ?>" alt="<?php echo htmlspecialchars($row['dep_name']); ?>" loading="lazy" decoding="async" style="width: 100%; height: auto; object-fit: cover;">
											</div>
										</div>
									</div>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if($home_status_pricing == 1): ?>
<!-- ========================================================
     7. SEÇÃO DE PLANOS & CARTÃO FIDELIDADE (COM FOTOS REAIS)
     ======================================================== -->
<section class="ps-foco-plans" id="fidelidade" style="padding: 75px 0; background: #f8fafc;">
    <div class="container">
        <div class="ps-section-header ps-fade-up-element">
            <span class="ps-section-badge"><i class="fa fa-id-card"></i> Cartão Fidelidade</span>
            <h2 class="ps-section-title"><?php echo htmlspecialchars($home_title_pricing ?: 'Planos de Acesso à Saúde'); ?></h2>
            <p class="ps-section-subtitle"><?php echo htmlspecialchars($home_subtitle_pricing ?: 'Planos pensados para oferecer tranquilidade, consultas com descontos especiais e facilidade para sua família.'); ?></p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan WHERE status = 1 ORDER BY pricing_plan_id ASC");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                $plan_photo = $row['pricing_plan_photo'] ?? '';
                $photo_src = '';
                if(!empty($plan_photo) && file_exists('assets/uploads/' . $plan_photo)) {
                    $photo_src = BASE_URL . 'assets/uploads/' . $plan_photo;
                } elseif(!empty($plan_photo) && file_exists('assets/images/planos/' . $plan_photo)) {
                    $photo_src = BASE_URL . 'assets/images/planos/' . $plan_photo;
                }
                $plan_btn_text = !empty($row['pricing_plan_button_text']) ? $row['pricing_plan_button_text'] : 'Quero Este Plano';
                $plan_btn_url = !empty($row['pricing_plan_button_url']) ? $row['pricing_plan_button_url'] : '#';
                $btn_target = (strpos($plan_btn_url, 'http') === 0) ? 'target="_blank" rel="noopener"' : '';
                $is_popular = !empty($row['pricing_plan_popular']);
                $badge = $row['pricing_plan_badge'] ?? '';
                $price_val = trim($row['pricing_plan_price'] ?? '');
                $period_val = trim($row['pricing_plan_period'] ?? '/mês');
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="ps-plan-card <?php echo $is_popular ? 'ps-plan-popular' : ''; ?> ps-fade-up-element">
                        <?php if($is_popular || !empty($badge)): ?>
                            <span class="ps-plan-badge-tag"><?php echo htmlspecialchars(!empty($badge) ? $badge : 'Mais Popular'); ?></span>
                        <?php endif; ?>
                        <h3 class="ps-plan-name"><?php echo htmlspecialchars($row['pricing_plan_name']); ?></h3>
                        <?php if(!empty($photo_src)): ?>
                            <div class="ps-plan-img">
                                <img src="<?php echo $photo_src; ?>" alt="<?php echo htmlspecialchars($row['pricing_plan_name']); ?>" loading="lazy" decoding="async">
                            </div>
                        <?php endif; ?>
                        <div class="ps-plan-price-wrap">
                            <?php if(strtolower($price_val) === 'consultar' || stripos($price_val, 'sob') !== false): ?>
                                <span class="ps-plan-price-val">Sob Consulta</span>
                            <?php else: ?>
                                <span class="ps-plan-from">A partir de</span>
                                <div class="ps-plan-amount">
                                    <span class="ps-plan-currency">R$</span>
                                    <span class="ps-plan-number"><?php echo htmlspecialchars($price_val); ?></span>
                                    <?php if(!empty($period_val)): ?>
                                        <span class="ps-plan-period"><?php echo htmlspecialchars($period_val); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <ul class="ps-plan-features">
                            <?php
                            $statement1 = $pdo->prepare("SELECT * FROM tbl_pricing_item WHERE pricing_plan_id=? ORDER BY pricing_item_id ASC");
                            $statement1->execute(array($row['pricing_plan_id']));
                            $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result1 as $row1) {
                                ?>
                                <li><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($row1['pricing_item_name']); ?></li>
                                <?php
                            }
                            ?>
                        </ul>
                        <a href="<?php echo htmlspecialchars($plan_btn_url); ?>" <?php echo $btn_target; ?> class="ps-plan-btn">
                            <?php echo htmlspecialchars($plan_btn_text); ?> <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if($home_status_doctor == 1): ?>
<!-- ========================================================
     8. SEÇÃO CORPO CLÍNICO & MÉDICOS (COM FOTOS REAIS)
     ======================================================== -->
<section class="doctor-v1" id="medicos" style="padding: 75px 0; background: #ffffff;">
	<div class="container">
		<div class="ps-section-header ps-fade-up-element">
			<span class="ps-section-badge"><i class="fa fa-user-md"></i> Especialistas</span>
			<h2 class="ps-section-title"><?php echo htmlspecialchars($home_title_doctor ?: 'Corpo Clínico Especializado'); ?></h2>
			<p class="ps-section-subtitle"><?php echo htmlspecialchars($home_subtitle_doctor ?: 'Médicos capacitados com ampla experiência e dedicação no atendimento humanizado.'); ?></p>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="doctor-carousel">
					<?php
					$statement = $pdo->prepare("SELECT 
													t1.id, t1.name, t1.slug, t1.designation_id, t1.photo,
													t1.facebook, t1.twitter, t1.linkedin,
													t2.designation_id, t2.designation_name
					                           FROM tbl_doctor t1
					                           JOIN tbl_designation t2 ON t1.designation_id = t2.designation_id
					                           WHERE t1.status = 'Active' ORDER BY t1.id ASC");
					$statement->execute();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						$photo_src = (!empty($row['photo']) && file_exists('assets/uploads/' . $row['photo'])) ? BASE_URL . 'assets/uploads/' . $row['photo'] : BASE_URL . 'assets/images/sem-foto.gif';
						?>
						<div class="item">
							<div class="thumb" style="border-radius: 18px 18px 0 0; overflow: hidden; position: relative;">
								<div class="photo" style="background-image:url(<?php echo $photo_src; ?>); height: 260px; background-size: cover; background-position: center;"></div>
								<div class="overlay"></div>
								<div class="social-icons">
									<ul>
										<?php if(!empty($row['facebook'])): ?>
											<li><a href="<?php echo $row['facebook']; ?>" target="_blank" rel="noopener"><i class="fa fa-facebook"></i></a></li>
										<?php endif; ?>
										<?php if(!empty($row['twitter'])): ?>
											<li><a href="<?php echo $row['twitter']; ?>" target="_blank" rel="noopener"><i class="fa fa-twitter"></i></a></li>
										<?php endif; ?>
										<?php if(!empty($row['linkedin'])): ?>
											<li><a href="<?php echo $row['linkedin']; ?>" target="_blank" rel="noopener"><i class="fa fa-linkedin"></i></a></li>
										<?php endif; ?>
									</ul>
								</div>
							</div>
							<div class="text" style="background: #f8fafc; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 18px 18px; padding: 20px 16px; text-align: center;">
								<h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 4px;">
									<a href="<?php echo BASE_URL; ?>doctor/<?php echo $row['slug']; ?>" style="color: #0f172a; text-decoration: none;">
										<?php echo htmlspecialchars($row['name']); ?>
									</a>
								</h3>
								<p style="color: #0a6b74; font-weight: 700; font-size: 0.85rem; margin-bottom: 14px;">
									<?php echo htmlspecialchars($row['designation_name']); ?>
								</p>
								<a href="https://wa.me/+5521985831464?text=<?php echo urlencode('Olá, gostaria de agendar uma consulta com ' . $row['name']); ?>" target="_blank" rel="noopener" class="btn-card-action" style="padding: 8px 16px; font-size: 0.825rem; width: 100%; justify-content: center;">
									<i class="fa fa-calendar-check-o"></i> Agendar Consulta
								</a>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if($home_status_testimonial == 1): ?>
<!-- ========================================================
     9. SEÇÃO DE DEPOIMENTOS (COM FOTOS REAIS DOS PACIENTES)
     ======================================================== -->
<section class="testimonial-v1" id="depoimentos" style="background-image:url(<?= BASE_URL; ?>assets/uploads/department-1.png); background-size: cover; background-position: center; position: relative; padding: 80px 0;">
	<div class="overlay" style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(7, 24, 34, 0.92) 0%, rgba(10, 107, 116, 0.88) 100%);"></div>
	<div class="container" style="position: relative; z-index: 2;">
		<div class="ps-section-header ps-fade-up-element" style="color: #ffffff;">
			<span class="ps-section-badge" style="background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.3); color: #ffffff;"><i class="fa fa-star text-warning"></i> Avaliações Reais</span>
			<h2 class="ps-section-title" style="color: #ffffff;"><?php echo htmlspecialchars($home_title_testimonial ?: 'O Que Nossos Pacientes Dizem'); ?></h2>
			<p class="ps-section-subtitle" style="color: rgba(255,255,255,0.85);"><?php echo htmlspecialchars($home_subtitle_testimonial ?: 'A satisfação de quem conta com o cuidado, acolhimento e profissionalismo da Pronto Saúde.'); ?></p>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="testimonial-carousel">
					<?php
					$statement = $pdo->prepare("SELECT * FROM tbl_testimonial WHERE status = 'Active' ORDER BY id ASC");
					$statement->execute();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						$photo_src = (!empty($row['photo']) && file_exists('assets/uploads/' . $row['photo'])) ? BASE_URL . 'assets/uploads/' . $row['photo'] : BASE_URL . 'assets/images/sem-foto.gif';
						?>
						<div class="item">
							<div class="testimonial-wrapper" style="background: rgba(255, 255, 255, 0.98); border-radius: 20px; padding: 36px 30px; box-shadow: 0 15px 40px rgba(0,0,0,0.2); max-width: 780px; margin: 0 auto; text-align: center;">								
								<div class="content">
									<div class="stars" style="color: #f59e0b; font-size: 18px; margin-bottom: 14px;">
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
									</div>
									<div class="comment">
										<p style="font-size: 1.05rem; color: #334155; line-height: 1.7; font-style: italic; margin-bottom: 24px;">
											"<?php echo nl2br(htmlspecialchars($row['comment'])); ?>"
										</p>
									</div>
									<div class="author" style="display: flex; align-items: center; justify-content: center; gap: 14px;">
										<div class="photo" style="width: 58px; height: 58px; border-radius: 50%; overflow: hidden; border: 3px solid #0a6b74; flex-shrink: 0;">
											<img src="<?php echo $photo_src; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
										</div>
										<div class="text" style="text-align: left;">
											<h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0;"><?php echo htmlspecialchars($row['name']); ?></h4>
											<p style="color: #0a6b74; font-size: 0.85rem; font-weight: 600; margin: 2px 0 0 0;"><?php echo htmlspecialchars($row['designation']); ?></p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if($home_status_news == 1): ?>
<!-- ========================================================
     10. SEÇÃO DE NOTÍCIAS & ARTIGOS (COM FOTOS REAIS)
     ======================================================== -->
<section class="news-v1" id="noticias" style="padding: 75px 0; background: #ffffff;">
	<div class="container">
		<div class="ps-section-header ps-fade-up-element">
			<span class="ps-section-badge"><i class="fa fa-newspaper-o"></i> Saúde & Prevenção</span>
			<h2 class="ps-section-title"><?php echo htmlspecialchars($home_title_news ?: 'Últimas Notícias e Artigos Médicos'); ?></h2>
			<p class="ps-section-subtitle"><?php echo htmlspecialchars($home_subtitle_news ?: 'Fique informado com conteúdos relevantes, orientações preventivas e novidades dos nossos especialistas.'); ?></p>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="news-carousel">
					<?php
					$i=0;
					$statement = $pdo->prepare("SELECT * FROM tbl_news WHERE status = 'Active' ORDER BY news_id DESC");
					$statement->execute();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						$i++;
						if($i > $total_recent_news_home_page) { break; }
						$photo_src = (!empty($row['photo']) && file_exists('assets/uploads/' . $row['photo'])) ? BASE_URL . 'assets/uploads/' . $row['photo'] : BASE_URL . 'assets/images/sem-foto.gif';
						?>
						<div class="item" style="border-radius: 18px; overflow: hidden; background: #f8fafc; border: 1px solid #e2e8f0; margin-bottom: 20px;">
							<div class="thumb" style="height: 200px; overflow: hidden; position: relative;">
								<div class="photo" style="background-image:url(<?php echo $photo_src; ?>); height: 100%; background-size: cover; background-position: center; transition: transform 0.4s ease;"></div>
							</div>
							<div class="text" style="padding: 22px 20px;">
								<h3 style="font-size: 1.15rem; font-weight: 800; line-height: 1.4; margin-bottom: 10px;">
									<a href="<?php echo BASE_URL; ?>news/<?php echo $row['news_slug']; ?>" style="color: #0f172a; text-decoration: none;">
										<?php echo htmlspecialchars($row['news_title']); ?>
									</a>
								</h3>
								<p style="color: #64748b; font-size: 0.9rem; line-height: 1.6; margin-bottom: 16px;">
									<?php echo mb_substr(strip_tags($row['news_content']), 0, 120, 'UTF-8') . '...'; ?>
								</p>
								<a href="<?php echo BASE_URL; ?>news/<?php echo $row['news_slug']; ?>" class="btn-card-action" style="padding: 8px 16px; font-size: 0.825rem; display: inline-flex;">
									Ler Artigo Completo <i class="fa fa-arrow-right ms-1"></i>
								</a>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if($home_status_partner == 1): ?>
<!-- ========================================================
     11. SEÇÃO DE CONVÊNIOS & PARCEIROS (COM LOGOS REAIS)
     ======================================================== -->
<section class="partner-v1" id="convenios" style="padding: 50px 0; background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center mb-4">
				<span class="ps-section-badge mb-2"><i class="fa fa-handshake-o"></i> Rede Credenciada</span>
				<h3 style="font-size: 1.55rem; font-weight: 800; color: #0f172a; margin: 6px 0;"><?php echo htmlspecialchars($home_title_partner ?: 'Convênios Aceitos & Parceiros Institucionais'); ?></h3>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="partner-carousel">
					<?php
					$statement = $pdo->prepare("SELECT * FROM tbl_partner ORDER BY id ASC");
					$statement->execute();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						$photo_src = (!empty($row['photo']) && file_exists('assets/uploads/' . $row['photo'])) ? BASE_URL . 'assets/uploads/' . $row['photo'] : '';
						if(empty($photo_src)) continue;
						?>
						<div class="item" style="display: flex; align-items: center; justify-content: center; height: 90px; padding: 10px;">
							<div class="inner" style="background: #ffffff; padding: 12px 20px; border-radius: 12px; border: 1px solid #e2e8f0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
								<?php if(empty($row['url'])): ?>
									<img src="<?php echo $photo_src; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="max-height: 55px; max-width: 100%; object-fit: contain; filter: grayscale(20%); transition: all 0.3s ease;">
								<?php else: ?>
									<a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank" rel="noopener">
									    <img src="<?php echo $photo_src; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="max-height: 55px; max-width: 100%; object-fit: contain; filter: grayscale(20%); transition: all 0.3s ease;">
									</a>
								<?php endif; ?>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ========================================================
     12. SEÇÃO FAQ - PERGUNTAS FREQUENTES (ESTILO FOCO7)
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