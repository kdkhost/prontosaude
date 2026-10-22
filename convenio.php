<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_convenio WHERE id_conv = 1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $conv) {
    $titulo     = $conv["titulo"];
    $subtitulo  = $conv["subtitulo"];
    $descricao  = $conv["descricao"];
    $banner     = $conv["banner"];
    $nome       = $conv["titulo_banner"];
}

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
<div class="page-banner" style="background-image: url(<?= BASE_URL; ?>assets/uploads/<?= $banner; ?>)">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1>
					    <?= $nome; ?>
					</h1>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->

<?php
if($home_status_service == 1): ?>

<!-- Service Start -->
<section class="service-v1">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2>
					    <?= $titulo; ?>
					</h2>
					<p>
					    <?= $subtitulo; ?>
					</p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="p-4 p-md-5" style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(10, 107, 116, 0.08); font-size: 1.05rem; line-height: 1.8;">
					<?= $descricao; ?>
					<div class="text-center mt-4 pt-3" style="border-top: 1px solid #f1f5f9;">
						<a href="https://wa.me/+5521985831464?text=Olá,%20gostaria%20de%20saber%20mais%20sobre%20o%20Convênio%20Livre%20Escolha" target="_blank" rel="noopener noreferrer" class="btn btn-success" style="padding: 12px 28px; border-radius: 50px; font-weight: 700; background: #25d366; border: none; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.35);">
							<i class="fa fa-whatsapp me-1"></i> Tirar Dúvidas via WhatsApp
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- Service End -->
<?php endif; ?>

<?php if($home_status_partner == 1): ?>
<!-- Partner Start -->
<section class="partner-v1">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="partner-carousel">
					<?php
					$statement = $pdo->prepare("SELECT * FROM tbl_partner ORDER BY id ASC");
					$statement->execute();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {?>
						<div class="item">
							<div class="inner">
								<?php if($row['url'] == ''): ?>
									<img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>"  alt="<?php echo $row['name']; ?>">
								<?php else: ?>
									<a href="<?php echo $row['url']; ?>" target="_blank">
									    <img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>"  alt="<?php echo $row['name']; ?>">
									</a>
								<?php endif; ?>
								
							</div>
						</div>
					<?php }?>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- Partner End -->
<?php endif; ?>

<?php require_once('footer.php'); ?>