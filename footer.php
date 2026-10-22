		<?php
		// As variáveis de settings já foram carregadas no header.php
		// Reutilizando: $footer_about, $footer_copyright, $contact_address,
		// $contact_email, $contact_phone, $contact_fax,
		// $total_recent_news_footer, $total_popular_news_footer,
		// $total_recent_news_sidebar, $total_popular_news_sidebar,
		// $total_recent_news_home_page
		?>
		

		<!-- Rodapé Premium Escuro Estilo Foco7 -->
		<footer class="ps-foco-footer">
			<div class="container">
				<div class="row">
					<!-- Coluna 1: Marca e Redes Sociais -->
					<div class="col-lg-4 col-md-6 mb-4 ps-footer-col ps-footer-about">
						<?php if(!empty($logo)): ?>
							<img src="<?= BASE_URL; ?>assets/uploads/<?= $logo; ?>" alt="Pronto Saúde" style="max-height: 48px; width: auto; margin-bottom: 18px; filter: brightness(0) invert(1);">
						<?php else: ?>
							<h3 style="margin-bottom: 14px;"><i class="fa fa-hospital-o text-success me-2"></i> Pronto Saúde</h3>
						<?php endif; ?>
						<p>
							<?php echo !empty($footer_about) ? nl2br($footer_about) : 'Centro médico e odontológico completo para cuidar de você e da sua família com excelência, rapidez e acolhimento humanizado.'; ?>
						</p>
						<div class="ps-topbar-socials" style="margin-top: 18px;">
							<?php
							if(isset($social_data)) {
								foreach ($social_data as $social_row) {
									if(!empty($social_row['social_url'])) {
										echo '<a href="'.$social_row['social_url'].'" target="_blank" rel="noopener noreferrer" class="ps-topbar-social-btn" style="background: rgba(255,255,255,0.1); color: #ffffff;"><i class="'.$social_row['social_icon'].'"></i></a>';
									}
								}
							}
							?>
						</div>
					</div>

					<!-- Coluna 2: Especialidades Médicas -->
					<div class="col-lg-2 col-md-6 mb-4 ps-footer-col">
						<h3>Especialidades</h3>
						<ul class="ps-footer-links">
							<?php
							$st_serv = $pdo->query("SELECT name, slug FROM tbl_service WHERE status = 'Active' ORDER BY id ASC LIMIT 5");
							while($srv = $st_serv->fetch(PDO::FETCH_ASSOC)) {
								echo '<li><a href="'.BASE_URL.'servicos.php"><i class="fa fa-angle-right"></i> '.htmlspecialchars($srv['name']).'</a></li>';
							}
							?>
							<li><a href="<?= BASE_URL; ?>servicos.php"><i class="fa fa-th-large text-success"></i> Ver Todas</a></li>
						</ul>
					</div>

					<!-- Coluna 3: Links Institucionais -->
					<div class="col-lg-3 col-md-6 mb-4 ps-footer-col">
						<h3>Links Rápidos</h3>
						<ul class="ps-footer-links">
							<li><a href="<?= BASE_URL; ?>#home"><i class="fa fa-angle-right"></i> Início</a></li>
							<li><a href="<?= BASE_URL; ?>#sobre"><i class="fa fa-angle-right"></i> Sobre a Clínica</a></li>
							<li><a href="<?= BASE_URL; ?>doctor.php"><i class="fa fa-angle-right"></i> Corpo Clínico</a></li>
							<li><a href="<?= BASE_URL; ?>#fidelidade"><i class="fa fa-angle-right"></i> Cartão Fidelidade</a></li>
							<li><a href="<?= BASE_URL; ?>#faq"><i class="fa fa-angle-right"></i> Perguntas Frequentes</a></li>
							<?php if(function_exists('pronto_is_page_active') && pronto_is_page_active('privacidade')): ?>
								<li><a href="<?= BASE_URL; ?>privacidade.php"><i class="fa fa-angle-right"></i> Política de Privacidade</a></li>
							<?php endif; ?>
						</ul>
					</div>

					<!-- Coluna 4: Onde Estamos e Contato -->
					<div class="col-lg-3 col-md-6 mb-4 ps-footer-col">
						<h3>Onde Estamos</h3>
						<?php if(!empty($contact_address)): ?>
						<div class="ps-footer-contact-item">
							<i class="fa fa-map-marker"></i>
							<span><?= htmlspecialchars($contact_address); ?></span>
						</div>
						<?php endif; ?>
						<?php if(!empty($contact_phone)): ?>
						<div class="ps-footer-contact-item">
							<i class="fa fa-phone"></i>
							<a href="tel:+55<?= preg_replace('/[^0-9]/', '', $contact_phone); ?>" style="color: #cbd5e1; text-decoration: none;"><?= htmlspecialchars($contact_phone); ?></a>
						</div>
						<?php endif; ?>
						<div class="ps-footer-contact-item">
							<i class="fa fa-clock-o"></i>
							<span>Seg a Sex: 07h às 19h | Sáb: 07h às 13h</span>
						</div>
						<?php if(!empty($contact_map_iframe)): ?>
						<div style="border-radius: 10px; overflow: hidden; margin-top: 14px; max-height: 140px; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
							<?= str_replace('height="450"', 'height="140"', $contact_map_iframe); ?>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Rodapé Inferior -->
			<div class="ps-footer-bottom">
				<div class="container">
					<div class="row align-items-center">
						<div class="col-md-7 text-left">
							<?= $footer_copyright ?: '© ' . date('Y') . ' Pronto Saúde - Centro Médico & Odontológico. Todos os direitos reservados.'; ?>
						</div>
						<div class="col-md-5 text-right">
							<span style="font-size: 0.8rem; color: #64748b;">Tecnologia & Inovação em Saúde</span>
						</div>
					</div>
				</div>
			</div>
		</footer>
		<!-- Fim do Rodapé Foco7 -->

		<!-- BOTÃO VOLTAR AO TOPO FLUTUANTE (ESTILO FOCO 7) -->
		<button type="button" id="btnVoltarAoTopo" class="foco-btn-topo scrollup" aria-label="Voltar ao início da página" title="Voltar ao Topo">
			<i class="fa fa-arrow-up"></i>
		</button>

	</div>

	<!-- Scripts -->
	<script src="<?php echo BASE_URL; ?>assets/js/jquery-2.2.4.min.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/jquery.slicknav.min.js"></script>	
	<script src="<?php echo BASE_URL; ?>assets/js/hoverIntent.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/superfish.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/owl.carousel.min.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/owl.animate.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/wow.min.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/jquery.bxslider.min.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/jquery.mixitup.min.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/jquery.magnific-popup.min.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/custom.js?v=<?= time(); ?>"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/sweetalert2.all.min.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/toastr.min.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/pronto-global.js?v=<?= time(); ?>"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/pronto-drawer.js?v=<?= time(); ?>"></script>

	<script>
	$(function () {
		$('#accordion').on('show.bs.collapse', function () {
			$('#accordion .in').collapse('hide');
		});

		// ==========================================================================
		// BOTÃO WHATSAPP FLUTUANTE & BOX MULTICANAIS (ESTILO FOCO 7)
		// ==========================================================================
		var $btnWhatsappFloat = $('#btnFocoWhatsappFloat');
		var $popupSupport = $('#popupSupportWhatsapp');
		var $btnCloseSupport = $('#btnCloseSupportPopup');
		var $btnTopo = $('#btnVoltarAoTopo');

		if ($btnWhatsappFloat.length && $popupSupport.length) {
			$btnWhatsappFloat.on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				$popupSupport.toggleClass('show');
			});

			if ($btnCloseSupport.length) {
				$btnCloseSupport.on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					$popupSupport.removeClass('show');
				});
			}

			$(document).on('click', function(e) {
				if (!$(e.target).closest('#popupSupportWhatsapp, #btnFocoWhatsappFloat').length) {
					$popupSupport.removeClass('show');
				}
			});

			$(document).on('keydown', function(e) {
				if (e.key === 'Escape' && $popupSupport.hasClass('show')) {
					$popupSupport.removeClass('show');
				}
			});
		}

		// Botão Voltar ao Topo Flutuante
		if ($btnTopo.length) {
			$(window).on('scroll', function() {
				if ($(this).scrollTop() > 300) {
					$btnTopo.addClass('show');
				} else {
					$btnTopo.removeClass('show');
				}
			});

			$btnTopo.on('click', function(e) {
				e.preventDefault();
				window.scrollTo({
					top: 0,
					behavior: 'smooth'
				});
			});
		}
	});
	</script>

	<?php if(!empty($_GET['pagina_indisponivel'])): ?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		setTimeout(function() {
			if (typeof toastr !== 'undefined') {
				toastr.info("A página solicitada está temporariamente desativada pelo administrador.", "Página Indisponível", { timeOut: 6000 });
			}
		}, 600);
	});
	</script>
	<?php endif; ?>

	<!-- ==========================================================================
	     BOTÃO FLUTUANTE DO WHATSAPP & BOX MULTICANAIS DE ATENDIMENTO (ESTILO FOCO 7)
	     ========================================================================== -->
	<?php
	$whats_raw = !empty($contact_phone) ? $contact_phone : '21985831464';
	$whats_clean = preg_replace('/\D/', '', $whats_raw);
	if (strlen($whats_clean) <= 11) {
		$whats_clean = '55' . $whats_clean;
	}
	?>
	<!-- BOTÃO FLUTUANTE DO WHATSAPP OFICIAL NA COR DA PALETA -->
	<button type="button" id="btnFocoWhatsappFloat" class="foco-whatsapp-float" aria-label="Abrir canais de atendimento Pronto Saúde pelo WhatsApp" title="Central de Atendimento pelo WhatsApp">
		<span class="whatsapp-badge-pulse"></span>
		<i class="fa fa-whatsapp"></i>
		<span class="whatsapp-tooltip">Central WhatsApp</span>
	</button>

	<!-- BOX POP-UP MULTICANAIS DE SUPORTE -->
	<div class="foco-support-popup shadow-lg" id="popupSupportWhatsapp" role="dialog" aria-modal="true" aria-labelledby="txtSupportTitle">
		<div class="foco-support-header">
			<button type="button" class="btn-close-support" id="btnCloseSupportPopup" aria-label="Fechar box">
				<i class="fa fa-times"></i>
			</button>
			<div class="foco-support-online-badge">
				<span class="support-pulse-dot"></span>
				<span class="support-online-text">Online</span>
			</div>
			<h6 class="foco-support-title" id="txtSupportTitle">Central de Atendimento Pronto Saúde</h6>
			<small class="foco-support-subtitle">Atendimento Humanizado • Selecione o canal desejado:</small>
		</div>
		<div class="foco-support-body">
			<!-- Canal 1: Agendamentos & Consultas -->
			<a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar uma consulta médica na Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="foco-channel-card">
				<div class="foco-channel-icon ch-consulta"><i class="fa fa-calendar-check-o"></i></div>
				<div class="foco-channel-info">
					<p class="foco-channel-title">Consultas & Especialidades</p>
					<p class="foco-channel-desc">Agende com cardiologia, pediatria, ginecologia e mais</p>
				</div>
				<i class="fa fa-chevron-right foco-channel-arrow"></i>
			</a>

			<!-- Canal 2: Exames & Laboratório -->
			<a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de informações e agendamento de exames laboratoriais / imagem.'); ?>" target="_blank" rel="noopener noreferrer" class="foco-channel-card">
				<div class="foco-channel-icon ch-exame"><i class="fa fa-flask"></i></div>
				<div class="foco-channel-info">
					<p class="foco-channel-title">Exames Laboratoriais & Imagem</p>
					<p class="foco-channel-desc">Preparo, agendamento e entrega de resultados rápidos</p>
				</div>
				<i class="fa fa-chevron-right foco-channel-arrow"></i>
			</a>

			<!-- Canal 3: Odontologia -->
			<a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar uma avaliação odontológica na Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="foco-channel-card">
				<div class="foco-channel-icon ch-odonto"><i class="fa fa-medkit"></i></div>
				<div class="foco-channel-info">
					<p class="foco-channel-title">Centro Odontológico</p>
					<p class="foco-channel-desc">Limpeza, ortodontia, próteses, clareamento e implantes</p>
				</div>
				<i class="fa fa-chevron-right foco-channel-arrow"></i>
			</a>

			<!-- Canal 4: Cartão Fidelidade -->
			<a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de conhecer e solicitar o Cartão Fidelidade Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="foco-channel-card">
				<div class="foco-channel-icon ch-plano"><i class="fa fa-credit-card"></i></div>
				<div class="foco-channel-info">
					<p class="foco-channel-title">Cartão Fidelidade & Benefícios</p>
					<p class="foco-channel-desc">Descontos exclusivos em consultas e procedimentos</p>
				</div>
				<i class="fa fa-chevron-right foco-channel-arrow"></i>
			</a>

			<!-- Canal 5: Recepção & Informações Gerais -->
			<a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Preciso de auxílio ou informações gerais com a recepção da Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="foco-channel-card">
				<div class="foco-channel-icon ch-duvidas"><i class="fa fa-comments-o"></i></div>
				<div class="foco-channel-info">
					<p class="foco-channel-title">Recepção & Informações Gerais</p>
					<p class="foco-channel-desc">Horários de funcionamento, convênios atendidos e dúvidas</p>
				</div>
				<i class="fa fa-chevron-right foco-channel-arrow"></i>
			</a>
		</div>
	</div>

	<!-- Banner LGPD Moderno (Glassmorphism & Conformidade com a Lei Geral de Proteção de Dados) -->
	<div class="ps-lgpd-banner" id="psLgpdBanner">
		<div class="ps-lgpd-content">
			<div class="ps-lgpd-icon">
				<i class="fa fa-shield"></i>
			</div>
			<div class="ps-lgpd-text">
				<h6>Privacidade e Cookies</h6>
				<p>Utilizamos cookies para aprimorar sua experiência e oferecer serviços médicos personalizados. Ao continuar, você concorda com nossa <a href="<?= BASE_URL; ?>privacidade.php">Política de Privacidade</a>.</p>
			</div>
			<div class="ps-lgpd-actions">
				<button type="button" class="btn-ps-lgpd-prefs" id="btnLgpdPreferences">Preferências</button>
				<button type="button" class="btn-ps-lgpd-accept" id="btnAcceptLgpd">Aceitar Todos</button>
			</div>
		</div>
	</div>

	<!-- Modal de Preferências LGPD -->
	<div class="ps-lgpd-modal-overlay" id="psLgpdModal">
		<div class="ps-lgpd-modal-card">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<h5 class="fw-bold mb-0 text-dark"><i class="fa fa-sliders text-primary mr-2"></i> Preferências de Privacidade</h5>
				<button type="button" class="btn-close" id="btnCloseLgpdModal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
			</div>
			<p class="text-muted small mb-3">Escolha quais categorias de cookies você deseja permitir em sua navegação:</p>
			<div class="mb-3 p-3 bg-light rounded" style="background: #f8fafc; border-radius: 12px; padding: 14px; margin-bottom: 12px;">
				<div class="d-flex justify-content-between align-items-center mb-1">
					<strong class="text-dark small">Cookies Essenciais</strong>
					<span class="badge bg-secondary small" style="background: #64748b; color: #fff; padding: 3px 8px; border-radius: 6px;">Sempre Ativos</span>
				</div>
				<p class="text-muted small mb-0" style="font-size: 0.8rem; color: #64748b; margin-top: 4px;">Necessários para o funcionamento básico, segurança e sessões do portal.</p>
			</div>
			<div class="mb-4 p-3 bg-light rounded" style="background: #f8fafc; border-radius: 12px; padding: 14px; margin-bottom: 16px;">
				<div class="d-flex justify-content-between align-items-center mb-1">
					<strong class="text-dark small">Cookies de Desempenho & Análise</strong>
					<input type="checkbox" id="lgpdOptAnalytics" checked style="width: 18px; height: 18px; cursor: pointer;">
				</div>
				<p class="text-muted small mb-0" style="font-size: 0.8rem; color: #64748b; margin-top: 4px;">Ajudam-nos a mensurar a audiência e melhorar os serviços de saúde oferecidos.</p>
			</div>
			<button type="button" class="btn-ps-lgpd-accept w-100 py-2" id="btnSaveLgpdPrefs" style="width: 100%;">Salvar Preferências</button>
		</div>
	</div>

	<?php if (!empty($pwa_status) && $pwa_status == 1): ?>
	<!-- Toast de Instalação do Aplicativo PWA -->
	<div class="ps-pwa-install-toast" id="psPwaInstallToast">
		<img src="<?= htmlspecialchars($pwa_icon_192, ENT_QUOTES, 'UTF-8'); ?>" alt="App" class="ps-pwa-app-icon">
		<div class="ps-pwa-toast-info">
			<strong>Instale o Aplicativo</strong>
			<span>Acesso rápido e consultas na palma da mão</span>
		</div>
		<button type="button" class="btn-ps-pwa-install" id="btnInstallPwaApp">Instalar</button>
		<button type="button" class="btn-ps-pwa-close" id="btnDismissPwaApp" title="Dispensar">&times;</button>
	</div>
	<?php endif; ?>

	<?php 
	$cur_page_name = basename($_SERVER['SCRIPT_NAME'] ?? '');
	if (!empty($popup_status) && $popup_status == 1 && ($cur_page_name === 'index.php' || $cur_page_name === '')): 
	?>
	<!-- Banner de Popup Promocional & Avisos da Home -->
	<div class="ps-promo-popup-overlay" id="psPromoPopup" data-active="1" data-delay="<?= $popup_delay; ?>" data-expire-hours="<?= $popup_expire_hours; ?>">
		<div class="ps-promo-popup-card">
			<button type="button" class="ps-popup-close-btn ps-close-popup" aria-label="Fechar">&times;</button>
			<?php if (!empty($popup_image)): ?>
				<img src="<?= htmlspecialchars($popup_image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($popup_title, ENT_QUOTES, 'UTF-8'); ?>" class="ps-popup-banner-img">
			<?php endif; ?>
			<div class="ps-popup-body">
				<h4><?= htmlspecialchars($popup_title, ENT_QUOTES, 'UTF-8'); ?></h4>
				<p><?= nl2br(htmlspecialchars($popup_text, ENT_QUOTES, 'UTF-8')); ?></p>
				<?php if (!empty($popup_button_text)): ?>
					<a href="<?= !empty($popup_button_url) ? htmlspecialchars($popup_button_url, ENT_QUOTES, 'UTF-8') : '#'; ?>" class="btn-ps-popup-action">
						<?= htmlspecialchars($popup_button_text, ENT_QUOTES, 'UTF-8'); ?> <i class="fa fa-arrow-right ml-1"></i>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- Recursos Modernos: PWA, LGPD, Popup & Lazy Loading Assíncrono -->
	<script src="<?php echo BASE_URL; ?>assets/js/pronto-frontend-features.js?v=<?= time(); ?>"></script>

</body>
</html>