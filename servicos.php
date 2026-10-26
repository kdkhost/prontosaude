<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
foreach ($result as $row) {
	$total_recent_news_home_page = $row['total_recent_news_home_page'];
	$home_title_service          = $row['home_title_service'];
	$home_subtitle_service       = $row['home_subtitle_service'];
	$home_status_service         = $row['home_status_service'];
	$home_title_department       = $row['home_title_department'];
	$home_subtitle_department    = $row['home_subtitle_department'];
	$home_status_department      = $row['home_status_department'];
	$home_title_doctor           = $row['home_title_doctor'];
	$home_subtitle_doctor        = $row['home_subtitle_doctor'];
	$home_status_doctor          = $row['home_status_doctor'];
	$home_title_pricing          = $row['home_title_pricing'];
	$home_subtitle_pricing       = $row['home_subtitle_pricing'];
	$home_status_pricing         = $row['home_status_pricing'];
	$home_title_testimonial      = $row['home_title_testimonial'];
	$home_subtitle_testimonial   = $row['home_subtitle_testimonial'];
	$home_status_testimonial     = $row['home_status_testimonial'];
	$home_title_news             = $row['home_title_news'];
	$home_subtitle_news          = $row['home_subtitle_news'];
	$home_status_news            = $row['home_status_news'];
	$home_title_partner          = $row['home_title_partner'];
	$home_subtitle_partner       = $row['home_subtitle_partner'];
	$home_status_partner         = $row['home_status_partner'];
}
?>

<!-- Banner da Página de Serviços -->
<div class="page-banner" style="background-image: url(<?= BASE_URL; ?>assets/uploads/department-1.png);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center">
				<div class="banner-text">
					<span class="ps-section-badge mb-2"><i class="fa fa-stethoscope"></i> Cuidado Integral</span>
					<h1 class="mt-2 mb-2"><?php echo !empty($home_title_service) ? htmlspecialchars($home_title_service) : 'Nossas Especialidades & Serviços'; ?></h1>
					<p><?php echo !empty($home_subtitle_service) ? htmlspecialchars($home_subtitle_service) : 'Atendimento humanizado, equipamentos de ponta e ampla gama de especialidades médicas para cuidar de você e sua família.'; ?></p>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if($home_status_service == 1): ?>
<!-- Service Start -->
<section class="ps-foco-services section-servicos" id="servicos" style="padding: 70px 0;">
	<div class="container">
		<div class="row g-4">
			<?php
			$statement = $pdo->prepare("SELECT * FROM tbl_service WHERE status = 'Active' ORDER BY FIELD(slug, 'toxicologico', 'oftalmologia', 'teste-de-alergia', 'dna', 'exames-laboratoriais', 'usg', 'mapa', 'holter', 'eletro-cardiograma', 'psiquiatria', 'fonoaudiologia', 'psicologia', 'ginecologia', 'gastroenterologia', 'pediatria', 'contraprova') ASC, id ASC");
			$statement->execute();
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($result as $row) {
				$link_btn = !empty($row['button_url']) ? $row['button_url'] : BASE_URL . 'service/' . $row['slug'];
				$btn_target = (!empty($row['button_url']) && strpos($row['button_url'], 'http') === 0) ? 'target="_blank" rel="noopener"' : '';
				$photo_rel = !empty($row['photo']) ? 'assets/uploads/' . $row['photo'] : '';
				$photo_src = (!empty($photo_rel) && file_exists($photo_rel)) ? BASE_URL . $photo_rel . '?v=' . filemtime($photo_rel) : BASE_URL . 'assets/images/sem-foto.gif';
				$benefits_items = !empty($row['benefits']) ? explode("\n", str_replace("\r", "", $row['benefits'])) : [];
				?>
				<div class="col-lg-4 col-md-6 mb-4">
					<div class="ps-service-card-modern">
						<div class="ps-service-thumb">
							<img src="<?php echo $photo_src; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" decoding="async">
							<?php if(!empty($row['price'])): 
								$price_raw = trim($row['price']);
								if (stripos($price_raw, 'consul') !== false) {
									$price_label = 'Sob Consulta';
								} elseif (stripos($price_raw, 'a partir') !== false) {
									$price_label = htmlspecialchars($price_raw);
								} else {
									$price_label = 'A partir de R$ ' . htmlspecialchars($price_raw);
								}
							?>
								<span class="ps-service-price-tag"><?php echo $price_label; ?></span>
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
	</div>
</section>
<!-- Service End -->
<?php endif; ?>

<?php require_once('footer.php'); ?>