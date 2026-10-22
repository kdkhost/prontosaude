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

<?php if($home_status_pricing == 1): ?>
<!-- Pricing Start -->
<section class="pricing-v1" id="fidelidade">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2><?php echo $home_title_pricing; ?></h2>
					<p><?php echo $home_subtitle_pricing; ?></p>
				</div>
			</div>
		</div>
		<div class="row">
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

				$plan_btn_text = !empty($row['pricing_plan_button_text']) ? $row['pricing_plan_button_text'] : 'Contratar Este';
				$plan_btn_url = !empty($row['pricing_plan_button_url']) ? $row['pricing_plan_button_url'] : '#';
				$btn_target = (strpos($plan_btn_url, 'http') === 0) ? 'target="_blank" rel="noopener"' : '';
				$is_popular = !empty($row['pricing_plan_popular']);
				$badge = $row['pricing_plan_badge'] ?? '';
				$price_val = trim($row['pricing_plan_price'] ?? '');
				$period_val = trim($row['pricing_plan_period'] ?? '/mês');
				?>
				<div class="col-md-4 mb-4">
					<div class="pricing-item <?php echo $is_popular ? 'pricing-popular' : ''; ?>" style="position: relative; transition: all 0.3s ease; margin-bottom: 30px;">
						<?php if(!empty($badge)): ?>
							<div class="pricing-badge-flag" style="position: absolute; top: 12px; right: 12px; background: #ffc107; color: #1e293b; font-weight: bold; font-size: 11px; padding: 4px 10px; border-radius: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); z-index: 2;">
								<i class="fa fa-star" style="margin-right: 3px;"></i> <?php echo htmlspecialchars($badge); ?>
							</div>
						<?php endif; ?>

						<div class="title" style="font-size:19px; font-weight: bold;">
							<?php echo htmlspecialchars($row['pricing_plan_name']); ?>
						</div>

						<div class="price">
							<?php if(!empty($photo_src)): ?>
								<div class="plan-img-container" style="margin: 12px auto; max-width: 220px; height: 95px; display: flex; align-items: center; justify-content: center;">
									<img class="img img-responsive center-block" src="<?php echo $photo_src; ?>" alt="<?php echo htmlspecialchars($row['pricing_plan_name']); ?>" style="max-height: 90px; max-width: 100%; object-fit: contain; margin: 0 auto;" />
								</div>
							<?php endif; ?>

							<div class="plan-pricing-amount" style="margin: 12px 0;">
								<?php if(strtolower($price_val) === 'consultar' || stripos($price_val, 'sob') !== false): ?>
									<span class="text-uppercase h4 d-block fw-bold" style="color: #0a6b74;">Sob Consulta</span>
								<?php else: ?>
									<div style="color: #0a6b74; line-height: 1.2;">
										<small style="font-size: 12px; color: #64748b; font-weight: 500; display: block; text-transform: uppercase;">A partir de</small>
										<span style="font-size: 16px; font-weight: bold;">R$</span> 
										<span style="font-size: 26px; font-weight: 800;"><?php echo htmlspecialchars($price_val); ?></span>
										<?php if(!empty($period_val)): ?>
											<span style="font-size: 13px; color: #64748b; font-weight: normal;"><?php echo htmlspecialchars($period_val); ?></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>

						<div class="offer">
							<ul>
								<?php
								$statement1 = $pdo->prepare("SELECT * FROM tbl_pricing_item WHERE pricing_plan_id=? ORDER BY pricing_item_id ASC");
								$statement1->execute(array($row['pricing_plan_id']));
								$result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);							
								foreach ($result1 as $row1) {
									?>
									<li>
										<i class="fa fa-check text-success" style="margin-right: 6px;"></i>
										<?php echo htmlspecialchars($row1['pricing_item_name']); ?>
									</li>
									<?php
								}
								?>
							</ul>
						</div>

						<div class="button" style="margin-top: 15px;">
							<a href="<?php echo htmlspecialchars($plan_btn_url); ?>" <?php echo $btn_target; ?> style="display: block; border-radius: 6px; font-weight: 600; padding: 10px 15px;">
								<?php echo htmlspecialchars($plan_btn_text); ?>
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