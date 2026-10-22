		<?php
		// As variáveis de settings já foram carregadas no header.php
		// Reutilizando: $footer_about, $footer_copyright, $contact_address,
		// $contact_email, $contact_phone, $contact_fax,
		// $total_recent_news_footer, $total_popular_news_footer,
		// $total_recent_news_sidebar, $total_popular_news_sidebar,
		// $total_recent_news_home_page
		?>
		

		<!-- Footer Social Start -->
		<section class="footer-social">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
					    <div>
					        
					    </div>
						<div class="item">
							<ul>
								<?php
								// Reutilizando dados de redes sociais já carregados no header
								if(isset($social_data)) {
									foreach ($social_data as $social_row) {
										if($social_row['social_url']!='')
										{
											echo '<li><a href="'.$social_row['social_url'].'"><i class="'.$social_row['social_icon'].'"></i></a></li>';
										}
									}
								}
								?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Footer Social End -->

		
		<!-- Footer Main Start -->
		<section class="footer-main">
			<div class="container">
				<div class="row">
					<div class="col-sm-6 col-md-3 col-lg-3 footer-col">
						<h3>Sobre Nós</h3>
						<p>
							<?php echo nl2br($footer_about); ?>
						</p>
					</div>
					<div class="col-sm-6 col-md-3 col-lg-3 footer-col">
						<h3>Últimas Notícias</h3>
						<?php
						$statement = $pdo->prepare("SELECT * FROM tbl_news ORDER BY news_id DESC LIMIT " . (int)$total_recent_news_footer);
						$statement->execute();
						$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
						foreach ($result as $row) {
							?>
							<div class="news-item">
								<div class="news-title">
								    <a href="<?php echo BASE_URL; ?>news/<?php echo $row['news_slug']; ?>">
								        <?php echo $row['news_title']; ?>
								    </a>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col-sm-6 col-md-3 col-lg-3 footer-col">
						<h3>Notícias Populares</h3>
						<?php
						$statement = $pdo->prepare("SELECT * FROM tbl_news ORDER BY total_view DESC LIMIT " . (int)$total_popular_news_footer);
						$statement->execute();
						$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
						foreach ($result as $row) {
							?>
							<div class="news-item">
								<div class="news-title">
								    <a href="<?php echo BASE_URL; ?>news/<?php echo $row['news_slug']; ?>">
								        <?php echo $row['news_title']; ?>
								    </a>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col-sm-6 col-md-3 col-lg-3 footer-col">
						<h3>Contato</h3>
						<div class="contact-item">
							<div class="text">
							    <i class="fa fa-map mr-2"></i>
							    <?php echo $contact_address; ?>
							</div>
						</div>
						<div class="contact-item">
							<div class="text">
							    <i class="fa fa-phone mr-2"></i>
							    <?php echo $contact_phone; ?>
							</div>
						</div>
						<?php if(function_exists('pronto_is_page_active') && pronto_is_page_active('trabalhe-conosco')): ?>
						<div class="contact-item">
							<div class="text">
							    <a href="../trabalhe-conosco.php" id="link">
							        <i class="fa fa-briefcase mr-2"></i>
							        Trabalhe Conosco
							    </a>
							</div>
						</div>
						<?php endif; ?>
						<div class="contact-item">
							<div class="text">
							    <i class="fa fa-envelope-o mr-2"></i>
							    <?php echo $contact_email; ?>
							</div>
						</div>
						<?php if(function_exists('pronto_is_page_active') && pronto_is_page_active('privacidade')): ?>
						<div class="contact-item">
							<div class="text">
							    <a href="privacidade.php" id="link">
							        <i class="fa fa-hand-o-right mr-2"></i>
							        Política de Privacidade
							    </a>
							</div>
						</div>
						<?php endif; ?>
						<?php if(function_exists('pronto_is_page_active') && pronto_is_page_active('rsocial')): ?>
						<div class="contact-item">
							<div class="text">
							   <a href="rsocial.php" id="link">
							        <i class="fa fa-hand-o-right mr-2"></i>
							       Responsabilidade Social
							   </a>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<!-- Footer Main End -->



		<!-- Footer Bottom Start -->
		<section class="footer-bottom">
		    
			<div class="container">
				<div class="row">
				    <div class="col-md-6">
                        <a href="https://ethestrategias.com.br" target="_blank" style="color:#ffffff;">
                            <img src="<?php echo BASE_URL; ?>/assets/img/yellow.png" 
                            alt="Eth! Estratégias" 
                            data-toggle="tooltip" 
                            data-placement="top" 
                            title="Eth! Estratégias" style="max-width:100%;height:48px; margin-bottom:0px;">
                        </a>
				    </div>
					<div class="col-md-6 copyright" style="text-align: right;">
						<?php echo $footer_copyright; ?>
					</div>
				</div>
			</div>
		</section>
		<!-- Footer Bottom End -->

		<a href="#" class="scrollup">
			<i class="fa fa-angle-up"></i>
		</a>

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

	<!-- Botão Flutuante de Atendimento WhatsApp Oficial -->
	<a href="https://wa.me/+5521985831464?text=Olá,%20gostaria%20de%20agendar%20uma%20consulta%20através%20do%20site%20Pronto%20Saúde" 
	   target="_blank" 
	   rel="noopener noreferrer" 
	   class="ps-floating-whatsapp" 
	   title="Fale Conosco pelo WhatsApp"
	   aria-label="Fale Conosco pelo WhatsApp">
		<i class="fa fa-whatsapp"></i>
		<span class="ps-floating-whatsapp-tooltip">Agende sua Consulta</span>
	</a>

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