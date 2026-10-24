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

<!-- Banner Start -->
<div class="page-banner" style="background-image: url(<?= BASE_URL; ?>assets/uploads/department-1.png);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center">
				<div class="banner-text">
					<span class="ps-section-badge mb-2"><i class="fa fa-id-card"></i> Benefícios Exclusivos</span>
					<h1 class="mt-2 mb-2"><?php echo !empty($home_title_pricing) ? htmlspecialchars($home_title_pricing) : 'Planos de Fidelidade'; ?></h1>
					<p><?php echo !empty($home_subtitle_pricing) ? htmlspecialchars($home_subtitle_pricing) : 'Planos pensados para oferecer tranquilidade, consultas com descontos especiais e facilidade para sua família.'; ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->

<?php if($home_status_pricing == 1): ?>
<!-- Pricing Start -->
<section class="ps-foco-plans section-planos" id="fidelidade" style="padding: 70px 0;">
	<div class="container">
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

				// Fallback de imagem consistente por plano
				if(empty($photo_src)) {
					if (stripos($row['pricing_plan_name'], 'empresa') !== false) {
						$photo_src = BASE_URL . 'assets/images/planos/empresarial.png';
					} elseif (stripos($row['pricing_plan_name'], 'odonto') !== false) {
						$photo_src = BASE_URL . 'assets/images/planos/odonto.png';
					} else {
						$photo_src = BASE_URL . 'assets/images/planos/top-saude.png';
					}
				}

				$plan_btn_text = !empty($row['pricing_plan_button_text']) ? $row['pricing_plan_button_text'] : 'Quero Este Plano';
				$plan_btn_url = !empty($row['pricing_plan_button_url']) ? $row['pricing_plan_button_url'] : '#';
				$btn_target = (strpos($plan_btn_url, 'http') === 0) ? 'target="_blank" rel="noopener"' : '';
				$is_popular = !empty($row['pricing_plan_popular']);
				$badge = $row['pricing_plan_badge'] ?? '';
				$price_val = trim($row['pricing_plan_price'] ?? '');
				$clean_price = preg_replace('/^R\$\s*/i', '', $price_val);
				$period_val = trim($row['pricing_plan_period'] ?? '/mês');
				?>
				<div class="col-lg-4 col-md-6 mb-4">
					<div class="ps-plan-card <?php echo $is_popular ? 'ps-plan-popular' : ''; ?>">
						<?php if($is_popular || !empty($badge)): ?>
							<span class="ps-plan-badge-tag"><i class="fas fa-fire me-1"></i><?php echo htmlspecialchars(!empty($badge) ? $badge : 'Mais Escolhido'); ?></span>
						<?php endif; ?>
						<div class="ps-plan-thumb">
							<img src="<?php echo $photo_src; ?>" alt="<?php echo htmlspecialchars($row['pricing_plan_name']); ?>" loading="lazy" decoding="async">
						</div>
						<div class="ps-plan-body">
							<h3 class="ps-plan-name"><?php echo htmlspecialchars($row['pricing_plan_name']); ?></h3>
							<div class="ps-plan-price-wrap">
								<?php if(strtolower($clean_price) === 'consultar' || stripos($price_val, 'sob') !== false): ?>
									<span class="ps-plan-price-val fw-bold" style="font-size: 1.15rem; color: var(--ps-primary);">Sob Consulta</span>
								<?php else: ?>
									<span class="ps-plan-from">A partir de</span>
									<div class="ps-plan-amount">
										<span class="ps-plan-currency">R$</span>
										<span class="ps-plan-number"><?php echo htmlspecialchars($clean_price); ?></span>
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
				</div>
				<?php
			}
			?>
		</div>
	</div>
</section>
<!-- Pricing End -->
<?php endif; ?>

<?php require_once('footer.php'); ?>