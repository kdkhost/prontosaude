		<?php
		// As variáveis de settings já foram carregadas no header.php
		// Reutilizando: $footer_about, $footer_copyright, $contact_address,
		// $contact_email, $contact_phone, $contact_fax,
		// $total_recent_news_footer, $total_popular_news_footer,
		// $total_recent_news_sidebar, $total_popular_news_sidebar,
		// $total_recent_news_home_page
		?>
		
		<script>
        window.cookieconsent.initialise({
          "palette": {
            "popup": {
              "background": "#f5f5f5",
              "text": "#000000"
            },
            "button": {
              "background": "#f00",
              "text": "#ffffff"
            }
          },
          "content": {
            "message": "Este site usa cookies para garantir que você obtenha a melhor experiência de navegação. Desativar os cookies do site pode prejudicar a funcionalidade de alguns recursos.",
            "dismiss": "Concordar e Fechar",
            "link": "Ler mais",
            "href": "/privacidade.php"
          }
        });
        </script>
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

</body>
</html>