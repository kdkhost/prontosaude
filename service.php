<?php require_once('header.php'); ?>

<?php
// Preventing the direct access of this page.
if(!isset($_REQUEST['slug']))
{
	header('location: '.BASE_URL);
	exit;
}
else
{
	// Check the page slug is valid or not.
	$statement = $pdo->prepare("SELECT * FROM tbl_service WHERE slug=?");
	$statement->execute(array($_REQUEST['slug']));
	$total = $statement->rowCount();
	if( $total == 0 )
	{
		header('location: '.BASE_URL);
		exit;
	}
}

// Getting the detailed data of a service from slug
$statement = $pdo->prepare("SELECT * FROM tbl_service WHERE slug=?");
$statement->execute(array($_REQUEST['slug']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);				
foreach ($result as $row)
{
	$name              = $row['name'];
	$slug              = $row['slug'];
	$short_description = $row['short_description'];
	$description       = $row['description'];
	$photo             = $row['photo'];
	$banner            = $row['banner'];
	
}
?>
				
<?php
$banner_src = (!empty($banner) && file_exists('assets/uploads/' . $banner)) ? BASE_URL . 'assets/uploads/' . $banner : BASE_URL . 'assets/uploads/department-1.png';
$photo_src = (!empty($photo) && file_exists('assets/uploads/' . $photo)) ? BASE_URL . 'assets/uploads/' . $photo : BASE_URL . 'assets/images/sem-foto.gif';
$zap_num = !empty($contact_phone) ? preg_replace('/[^0-9]/', '', $contact_phone) : '5521985831464';
if (strlen($zap_num) <= 11 && substr($zap_num, 0, 2) !== '55') {
    $zap_num = '55' . $zap_num;
}
$zap_msg = urlencode("*Olá! Gostaria de mais informações e agendamento para o serviço:* " . $name);
?>

<!-- Banner Start -->
<div class="page-banner" style="background-image:url(<?= $banner_src; ?>);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1><?= htmlspecialchars($name); ?></h1>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->

<!-- Service Detail Start -->
<section class="blog">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				
				<div class="blog-grid">
					<div class="row">
						<div class="col-md-12">
							
							<div class="post-item" style="background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 30px;">
								<div class="image-holder" style="border-radius: 12px; overflow: hidden; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
									<img class="img-responsive" src="<?= $photo_src; ?>" alt="<?= htmlspecialchars($name); ?>" style="width: 100%; max-height: 440px; object-fit: cover;">
								</div>
								<div class="text">
									<h2 style="color: #0f172a; font-weight: 800; font-size: 26px; margin-bottom: 16px;"><?= htmlspecialchars($name); ?></h2>
									<div class="service-content" style="color: #334155; font-size: 15.5px; line-height: 1.8;">
										<?= $description; ?>
									</div>
									<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
										<a href="<?= BASE_URL; ?>servicos.php" class="btn btn-outline-secondary fw-bold px-3 py-2 rounded-3 text-decoration-none d-inline-flex align-items-center">
											<i class="fas fa-arrow-left me-2"></i> Ver Todos os Serviços
										</a>
										<a href="https://wa.me/<?= $zap_num; ?>?text=<?= $zap_msg; ?>" target="_blank" rel="noopener" class="btn btn-success" style="background: #10b981; border: none; border-radius: 8px; font-weight: 700; padding: 11px 24px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(16,185,129,0.35);">
											<i class="fa fa-whatsapp" style="font-size: 18px;"></i> Agendar Este Serviço
										</a>
									</div>
								</div>
							</div>

						</div>
					</div>
				</div>

			</div>
			<div class="col-md-4">
				
				<!-- Sidebar Container Start -->
				<div class="sidebar">
					<div class="widget" style="background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
						<h4 style="font-size: 18px; font-weight: 800; color: #0a6b74; border-bottom: 2px solid #e6f3f4; padding-bottom: 10px; margin-bottom: 16px;">
							<i class="fa fa-stethoscope" style="margin-right: 8px;"></i> Outros Serviços
						</h4>
						<ul style="list-style: none; padding-left: 0; margin-bottom: 0;">
							<?php
							$statement = $pdo->prepare("SELECT * FROM tbl_service ORDER BY name ASC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row_s) { 
								$is_curr = ($row_s['slug'] == $slug);
							?>
								<li style="margin-bottom: 8px;">
								    <a href="<?= BASE_URL; ?>service/<?= $row_s['slug']; ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 8px; font-weight: <?= $is_curr ? '700' : '500'; ?>; color: <?= $is_curr ? '#0a6b74' : '#334155'; ?>; background: <?= $is_curr ? '#e6f3f4' : 'transparent'; ?>; text-decoration: none; transition: all 0.2s ease;">
								        <span><?= htmlspecialchars($row_s['name']); ?></span>
								        <i class="fa fa-chevron-right" style="font-size: 11px; opacity: <?= $is_curr ? '1' : '0.4'; ?>;"></i>
								    </a>
								</li>
							<?php } ?>							
						</ul>
					</div>							
				</div>
				<!-- Sidebar Container End -->
			
			</div>
		</div>

			


		</div>
	</div>
</section>
<!-- Service End -->
			
<?php require_once('footer.php'); ?>