<?php require_once('header.php'); ?>



<!-- Slider Start -->
<section class="main-slider">
	<div class="slider">
		<ul class="bxslider">
				
			<?php
			$statement = $pdo->prepare("SELECT * FROM tbl_slider WHERE status=? ORDER BY id DESC");
			$statement->execute(array('Active'));
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($result as $row) 
			{
				if($row['position']=='Left') {$align='tal';}
				if($row['position']=='Center') {$align='tac';}
				if($row['position']=='Right') {$align='tar';}
				?>
				<li style="background-image:url(<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>);">
					<div class="overlay"></div>
					<div class="content">
						<div class="inner <?php echo $align; ?>">
							<div class="text">
							
								<?php if($row['heading']!=''): ?>
								<h2>
									<?php echo $row['heading']; ?>
								</h2>
								<?php endif; ?>

								<?php if($row['subheading']!=''): ?>
								<h3>
									<?php echo $row['subheading']; ?>
								</h3>
								<?php endif; ?>
								
								<?php if($row['content']!=''): ?>
								<p>
									<?php echo nl2br($row['content']); ?>
								</p>
								<?php endif; ?>
								
								<?php if($row['button_text']!=''): ?>
								<p class="button">
									<a href="<?php echo $row['button_url']; ?>" class="btn btn-flat"><?php echo $row['button_text']; ?></a>
								</p>
								<?php endif; ?>

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


<?php if($home_status_service == 1): ?>
<!-- Service Start -->
<section class="service-v1">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2><?php echo $home_title_service; ?></h2>
					<p><?php echo $home_subtitle_service; ?></p>
				</div>
			</div>
		</div>
		<div class="row">
			<?php
			$statement = $pdo->prepare("SELECT * FROM tbl_service ORDER BY id ASC LIMIT 3");
			$statement->execute();
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($result as $row) {
				$link_btn = !empty($row['button_url']) ? $row['button_url'] : BASE_URL . 'service/' . $row['slug'];
				$btn_target = (!empty($row['button_url']) && strpos($row['button_url'], 'http') === 0) ? 'target="_blank" rel="noopener"' : '';
				$btn_label = !empty($row['button_text']) ? $row['button_text'] : 'Agendar Consulta';
				$photo_src = (!empty($row['photo']) && file_exists('assets/uploads/' . $row['photo'])) ? BASE_URL . 'assets/uploads/' . $row['photo'] : BASE_URL . 'assets/images/sem-foto.gif';
				$benefits_items = !empty($row['benefits']) ? explode("\n", str_replace("\r", "", $row['benefits'])) : [];
				?>
				<div class="col-sm-6 col-md-4 col-lg-4">
					<div class="item service-card-box">
						<div class="photo" style="background-image:url(<?php echo $photo_src; ?>); position: relative;">
							<?php if(!empty($row['price'])): ?>
								<div class="service-card-price-badge">
									<small>A partir de</small> R$ <?php echo htmlspecialchars($row['price']); ?>
								</div>
							<?php endif; ?>
						</div>
						<div class="text">
							<div class="inner">
								<h3><a href="<?php echo htmlspecialchars($link_btn); ?>" <?php echo $btn_target; ?>><?php echo htmlspecialchars($row['name']); ?></a></h3>
								<p><?php echo substr(strip_tags($row['short_description']), 0, 140) . '...'; ?></p>
								<?php if(!empty($benefits_items)): ?>
									<ul class="service-card-benefits">
										<?php foreach(array_slice($benefits_items, 0, 3) as $b_item): if(!empty(trim($b_item))): ?>
											<li><i class="fa fa-check text-success" style="margin-right: 5px;"></i> <?php echo htmlspecialchars(trim($b_item)); ?></li>
										<?php endif; endforeach; ?>
									</ul>
								<?php endif; ?>
								<p class="button">
									<a href="<?php echo htmlspecialchars($link_btn); ?>" <?php echo $btn_target; ?>><?php echo htmlspecialchars($btn_label); ?></a>
								</p>
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
<!-- Service End -->
<?php endif; ?>




<?php if($home_status_department == 1): ?>
<!-- Department Start -->
<section class="department-v2">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2><?php echo $home_title_department; ?></h2>
					<p><?php echo $home_subtitle_department; ?></p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				
				<!-- Department Tab Start -->
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
								<li class="<?php if($i==1) {echo 'active';} ?>"><a href="#tab<?php echo $i; ?>" data-toggle="tab" aria-expanded="<?php if($i==1) {echo 'true';}else{echo 'false';} ?>"><?php echo $row['dep_name']; ?></a></li>
							<?php
						}
						?>
					</ul>
					
					<!-- Tab Content Start -->
					<div class="tab-content col-md-8">
						<?php
						$i=0;
						if(isset($departments)) {
							foreach ($departments as $row) {
								$i++;
								?>
								<div class="tab-pane fade <?php if($i==1) {echo 'active in';} ?>" id="tab<?php echo $i; ?>">
									<div class="row">										
										<div class="col-md-7">
											<div class="department-content">
												<h2><?php echo $row['dep_name']; ?></h2>
												<p>
													<?php echo $row['dep_detail']; ?>
												</p>
												<p class="button">
													<a href="<?php echo BASE_URL; ?>department/<?php echo $row['dep_slug']; ?>">Detalhes</a>
												</p>											
											</div>
										</div>
										<div class="col-md-5">
											<div class="thumb">
												<img class="img-fullwidth" src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['dep_photo']; ?>"  alt="<?php echo $row['dep_name']; ?>">
											</div>
										</div>
									</div>
								</div>
								<?php
							}
						}
						?>
						

					</div>
					<!-- Tab Content End -->
				</div>
				<!-- Department Tab End -->


			</div>
		</div>
	</div>
</section>
<!-- Department End -->
<?php endif; ?>



<?php if($home_status_doctor == 1): ?>
<!-- Doctors Start -->
<section class="doctor-v1">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2><?php echo $home_title_doctor; ?></h2>
					<p><?php echo $home_subtitle_doctor; ?></p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				
				<!-- Doctor Carousel Start -->
				<div class="doctor-carousel">


					<?php
					$statement = $pdo->prepare("SELECT 
												
												t1.id,
												t1.name,
												t1.slug,
												t1.designation_id,
												t1.photo,
												t1.facebook,
												t1.twitter,
												t1.linkedin,
												t1.youtube,
												t1.google_plus,
												t1.instagram,
												t1.flickr,

												t2.designation_id,
												t2.designation_name

					                           FROM tbl_doctor t1
					                           JOIN tbl_designation t2
					                           ON t1.designation_id = t2.designation_id
					                           WHERE  t1.status = ?
					                           ");
					$statement->execute(array('Active'));
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						?>
						<div class="item">
							<div class="thumb">
								<div class="photo" style='background-image:url(<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>);'></div>
								<div class="overlay"></div>
								<div class="social-icons">
									<ul>
										<?php if($row['facebook']!=''): ?>
											<li><a href="<?php echo $row['facebook']; ?>" target="_blank"><i class="fa fa-facebook"></i></a></li>
										<?php endif; ?>

										<?php if($row['twitter']!=''): ?>
											<li><a href="<?php echo $row['twitter']; ?>" target="_blank"><i class="fa fa-twitter"></i></a></li>
										<?php endif; ?>

										<?php if($row['linkedin']!=''): ?>
											<li><a href="<?php echo $row['linkedin']; ?>" target="_blank"><i class="fa fa-linkedin"></i></a></li>
										<?php endif; ?>

										<?php if($row['youtube']!=''): ?>
											<li><a href="<?php echo $row['youtube']; ?>" target="_blank"><i class="fa fa-youtube"></i></a></li>
										<?php endif; ?>

										<?php if($row['google_plus']!=''): ?>
											<li><a href="<?php echo $row['google_plus']; ?>" target="_blank"><i class="fa fa-google-plus"></i></a></li>
										<?php endif; ?>

										<?php if($row['instagram']!=''): ?>
											<li><a href="<?php echo $row['instagram']; ?>" target="_blank"><i class="fa fa-instagram"></i></a></li>
										<?php endif; ?>

										<?php if($row['flickr']!=''): ?>
											<li><a href="<?php echo $row['flickr']; ?>" target="_blank"><i class="fa fa-flickr"></i></a></li>
										<?php endif; ?>
									</ul>
								</div>
							</div>
							<div class="text">
								<h3><a href="<?php echo BASE_URL; ?>doctor/<?php echo $row['slug']; ?>"><?php echo $row['name']; ?></a></h3>
								<p><?php echo $row['designation_name']; ?></p>
							</div>
						</div>
						<?php
					}
					?>

					
					
				</div>
				<!-- Doctor Carousel End -->

			</div>
		</div>
	</div>
</section>
<!-- Doctors End -->
<?php endif; ?>



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
			$statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan WHERE status = 1 ORDER BY pricing_plan_id ASC LIMIT 3");
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

				$plan_btn_text = !empty($row['pricing_plan_button_text']) ? $row['pricing_plan_button_text'] : 'Contratar Agora';
				$plan_btn_url = !empty($row['pricing_plan_button_url']) ? $row['pricing_plan_button_url'] : BASE_URL . 'fidelidade.php';
				$btn_target = (strpos($plan_btn_url, 'http') === 0) ? 'target="_blank" rel="noopener"' : '';
				$is_popular = !empty($row['pricing_plan_popular']);
				$badge = $row['pricing_plan_badge'] ?? '';
				$price_val = trim($row['pricing_plan_price'] ?? '');
				$period_val = trim($row['pricing_plan_period'] ?? '/mês');
				?>
				<div class="col-md-4">
					<div class="pricing-item <?php echo $is_popular ? 'pricing-popular' : ''; ?>" style="position: relative; transition: all 0.3s ease;">
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




<?php if($home_status_testimonial == 1): ?>
<!-- Testimonial Start -->
<section class="testimonial-v1" style="background-image:url(<?= BASE_URL; ?>assets/uploads/department-1.png);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2><?php echo $home_title_testimonial; ?></h2>
					<p><?php echo $home_subtitle_testimonial; ?></p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				
				<!-- Testimonial Carousel Start -->
				<div class="testimonial-carousel">
					<?php
					$statement = $pdo->prepare("SELECT * FROM tbl_testimonial ORDER BY id ASC");
					$statement->execute();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						?>
						<div class="item">
							<div class="testimonial-wrapper">								
								<div class="content">
									<div class="stars" style="color: #f59e0b; font-size: 15px; margin-bottom: 12px;">
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
									</div>
									<div class="comment">
										<p>
											"<?php echo nl2br($row['comment']); ?>"
										</p>
										<div class="icon"></div>
									</div>
									<div class="author">
										<div class="photo">
											<img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>" alt="<?php echo $row['name']; ?>">
										</div>
										<div class="text">
											<h3><?php echo $row['name']; ?></h3>
											<h4><?php echo $row['designation']; ?> 
											<?php if($row['company']!=''): ?>
											<span>(<?php echo $row['company']; ?>)</span>
											<?php endif; ?>
											</h4>
										</div>
									</div>										
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<!-- Testimonial Carousel End -->

			</div>
		</div>
	</div>
</section>
<!-- Testimonial End -->
<?php endif; ?>




<?php if($home_status_news == 1): ?>
<!-- News Start -->
<section class="news-v1">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2><?php echo $home_title_news; ?></h2>
					<p><?php echo $home_subtitle_news; ?></p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				
				<!-- News Carousel Start -->
				<div class="news-carousel">

					<?php
					$i=0;
					$statement = $pdo->prepare("SELECT * FROM tbl_news ORDER BY news_id DESC");
					$statement->execute();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						$i++;
						if($i>$total_recent_news_home_page) {break;}
						?>
						<div class="item">
							<div class="thumb">
								<div class="photo" style="background-image:url(<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>);"></div>
							</div>
							<div class="text">
								<h3><a href="<?php echo BASE_URL; ?>news/<?php echo $row['news_slug']; ?>"><?php echo $row['news_title']; ?></a></h3>
								<?php echo substr($row['news_content'],0,200).' ...'; ?>
							</div>
						</div>
						<?php
					}
					?>
					
				</div>
				<!-- News Carousel End -->

			</div>
		</div>
	</div>
</section>
<!-- News End -->
<?php endif; ?>
	


<?php if($home_status_partner == 1): ?>
<!-- Partner Start -->
<section class="partner-v1">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2><?php echo $home_title_partner; ?></h2>
					<p><?php echo $home_subtitle_partner; ?></p>
				</div>
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
						?>
						<div class="item">
							<div class="inner">
								<?php if($row['url']==''): ?>
									<img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>"  alt="<?php echo $row['name']; ?>">
								<?php else: ?>
									<a href="<?php echo $row['url']; ?>" target="_blank">
									    <img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>"  alt="<?php echo $row['name']; ?>">
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
<!-- Partner End -->
<?php endif; ?>

<?php require_once('footer.php'); ?>