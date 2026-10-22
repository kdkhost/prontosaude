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
	$statement = $pdo->prepare("SELECT * FROM tbl_department WHERE dep_slug=?");
	$statement->execute(array($_REQUEST['slug']));
	$total = $statement->rowCount();
	if( $total == 0 )
	{
		header('location: '.BASE_URL);
		exit;
	}
}

// Getting the detailed data of a department from slug
$statement = $pdo->prepare("SELECT * FROM tbl_department WHERE dep_slug=?");
$statement->execute(array($_REQUEST['slug']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);				
foreach ($result as $row)
{
	$dep_id      = $row['dep_id'];
	$dep_name    = $row['dep_name'];
	$dep_slug    = $row['dep_slug'];
	$dep_detail  = $row['dep_detail'];
	$dep_address = $row['dep_address'];
	$dep_phone   = $row['dep_phone'];
	$dep_fax     = $row['dep_fax'];
	$dep_email   = $row['dep_email'];
	$dep_photo   = $row['dep_photo'];
	$dep_banner  = $row['dep_banner'];
}

$statement = $pdo->prepare("SELECT * FROM tbl_department_openning_hour WHERE dep_id=?");
$statement->execute(array($dep_id));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);				
foreach ($result as $row)
{
	$arr_oh_day[]  = $row['oh_day'];
	$arr_oh_time[] = $row['oh_time'];
}

$statement = $pdo->prepare("SELECT * FROM tbl_department_faq WHERE dep_id=?");
$statement->execute(array($dep_id));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);				
foreach ($result as $row)
{
	$arr_fq_title[]   = $row['fq_title'];
	$arr_fq_content[] = $row['fq_content'];
}
?>
				
<?php
$dep_banner_src = (!empty($dep_banner) && file_exists('assets/uploads/' . $dep_banner)) ? BASE_URL . 'assets/uploads/' . $dep_banner : BASE_URL . 'assets/uploads/department-1.png';
$dep_photo_src = (!empty($dep_photo) && file_exists('assets/uploads/' . $dep_photo)) ? BASE_URL . 'assets/uploads/' . $dep_photo : BASE_URL . 'assets/images/sem-foto.gif';
$zap_dep = !empty($dep_fax) ? preg_replace('/[^0-9]/', '', $dep_fax) : (!empty($dep_phone) ? preg_replace('/[^0-9]/', '', $dep_phone) : (!empty($contact_phone) ? preg_replace('/[^0-9]/', '', $contact_phone) : '5521985831464'));
if (strlen($zap_dep) <= 11 && substr($zap_dep, 0, 2) !== '55') {
    $zap_dep = '55' . $zap_dep;
}
$zap_dep_msg = urlencode("*Olá! Gostaria de informações e agendamento para a unidade:* " . $dep_name);
?>
				
<!-- Banner Start -->
<div class="page-banner" style="background-image: url(<?= $dep_banner_src; ?>);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1><?php echo htmlspecialchars($dep_name); ?></h1>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->

<!-- Department Main Start -->
<section class="blog" style="padding: 60px 0; background: #f8fafc;">
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				
				<!-- Card Principal de Detalhes -->
				<div class="department-main-card" style="background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 30px;">
					<div class="image-holder" style="border-radius: 12px; overflow: hidden; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
						<img class="img-responsive center-block" src="<?= $dep_photo_src; ?>" alt="<?php echo htmlspecialchars($dep_name); ?>" style="width: 100%; max-height: 420px; object-fit: cover;">
					</div>
					
					<div class="text">
						<h2 style="color: #0f172a; font-weight: 800; font-size: 26px; margin-bottom: 16px;"><?php echo htmlspecialchars($dep_name); ?></h2>
						
						<!-- Grid de Contato Rápido da Clínica -->
						<div class="row" style="margin-bottom: 24px; margin-left: -6px; margin-right: -6px;">
							<?php if(!empty($dep_address)): ?>
								<div class="col-sm-6 mb-2" style="padding: 6px;">
									<div style="background: #f1f5f9; border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; gap: 10px; height: 100%;">
										<div style="width: 36px; height: 36px; border-radius: 50%; background: #0a6b74; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
											<i class="fa fa-map-marker"></i>
										</div>
										<div style="font-size: 13px; color: #334155; line-height: 1.4;">
											<strong style="color: #0f172a; display: block;">Localização:</strong>
											<a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($dep_address); ?>" target="_blank" rel="noopener" style="color: #0a6b74; text-decoration: none;">
												<?= htmlspecialchars($dep_address); ?>
											</a>
										</div>
									</div>
								</div>
							<?php endif; ?>

							<?php if(!empty($dep_phone)): ?>
								<div class="col-sm-6 mb-2" style="padding: 6px;">
									<div style="background: #f1f5f9; border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; gap: 10px; height: 100%;">
										<div style="width: 36px; height: 36px; border-radius: 50%; background: #0a6b74; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
											<i class="fa fa-phone"></i>
										</div>
										<div style="font-size: 13px; color: #334155;">
											<strong style="color: #0f172a; display: block;">Telefone:</strong>
											<a href="tel:+55<?= preg_replace('/[^0-9]/', '', $dep_phone); ?>" style="color: #0a6b74; font-weight: 600; text-decoration: none;">
												<?= htmlspecialchars($dep_phone); ?>
											</a>
										</div>
									</div>
								</div>
							<?php endif; ?>

							<?php if(!empty($dep_fax)): ?>
								<div class="col-sm-6 mb-2" style="padding: 6px;">
									<div style="background: #f1f5f9; border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; gap: 10px; height: 100%;">
										<div style="width: 36px; height: 36px; border-radius: 50%; background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
											<i class="fa fa-whatsapp"></i>
										</div>
										<div style="font-size: 13px; color: #334155;">
											<strong style="color: #0f172a; display: block;">WhatsApp Oficial:</strong>
											<a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $dep_fax); ?>?text=<?= $zap_dep_msg; ?>" target="_blank" rel="noopener" style="color: #10b981; font-weight: 700; text-decoration: none;">
												<?= htmlspecialchars($dep_fax); ?>
											</a>
										</div>
									</div>
								</div>
							<?php endif; ?>

							<?php if(!empty($dep_email)): ?>
								<div class="col-sm-6 mb-2" style="padding: 6px;">
									<div style="background: #f1f5f9; border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; gap: 10px; height: 100%;">
										<div style="width: 36px; height: 36px; border-radius: 50%; background: #0a6b74; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
											<i class="fa fa-envelope"></i>
										</div>
										<div style="font-size: 13px; color: #334155; word-break: break-all;">
											<strong style="color: #0f172a; display: block;">E-mail:</strong>
											<a href="mailto:<?= htmlspecialchars($dep_email); ?>" style="color: #0a6b74; text-decoration: none;">
												<?= htmlspecialchars($dep_email); ?>
											</a>
										</div>
									</div>
								</div>
							<?php endif; ?>
						</div>

						<!-- Descrição Completa -->
						<div class="dep-description" style="color: #334155; font-size: 15.5px; line-height: 1.8; margin-top: 16px;">
							<?php echo $dep_detail; ?>
						</div>

						<!-- Botão de Agendamento da Unidade -->
						<div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid #f1f5f9; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
							<a href="<?= BASE_URL; ?>" class="btn btn-default" style="border-radius: 8px; font-weight: 600; padding: 10px 20px;">
								<i class="fa fa-arrow-left" style="margin-right: 6px;"></i> Voltar ao Início
							</a>
							<a href="https://wa.me/<?= $zap_dep; ?>?text=<?= $zap_dep_msg; ?>" target="_blank" rel="noopener" class="btn btn-success" style="background: #10b981; border: none; border-radius: 8px; font-weight: 700; padding: 11px 24px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(16,185,129,0.35);">
								<i class="fa fa-whatsapp" style="font-size: 18px;"></i> Agendar com esta Unidade
							</a>
						</div>
					</div>
				</div>

				<!-- FAQ da Clínica se houver -->
				<?php if(!empty($arr_fq_title)): ?>
					<div class="department-faq-card" style="background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 30px;">
						<h3 style="font-size: 20px; font-weight: 800; color: #0a6b74; margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
							<i class="fa fa-question-circle"></i> Perguntas Frequentes desta Unidade
						</h3>
						<div class="panel-group" id="accordionFaqDep" role="tablist" aria-multiselectable="true">
							<?php for($i=0; $i<count($arr_fq_title); $i++): ?>
								<div class="panel panel-default" style="border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 12px; overflow: hidden; box-shadow: none;">
									<div class="panel-heading" role="tab" id="headingFaq<?= $i; ?>" style="background: #f8fafc; padding: 14px 18px;">
										<h4 class="panel-title" style="font-size: 15px; margin: 0;">
											<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordionFaqDep" href="#collapseFaq<?= $i; ?>" aria-expanded="false" style="color: #0f172a; font-weight: 700; text-decoration: none; display: flex; justify-content: space-between; align-items: center;">
												<span><?= htmlspecialchars($arr_fq_title[$i]); ?></span>
												<i class="fa fa-chevron-down" style="font-size: 12px; opacity: 0.5;"></i>
											</a>
										</h4>
									</div>
									<div id="collapseFaq<?= $i; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingFaq<?= $i; ?>">
										<div class="panel-body" style="padding: 16px 20px; color: #475569; font-size: 14.5px; line-height: 1.6; background: #fff;">
											<?= nl2br($arr_fq_content[$i]); ?>
										</div>
									</div>
								</div>
							<?php endfor; ?>
						</div>
					</div>
				<?php endif; ?>

			</div>

			<!-- Barra Lateral -->
			<div class="col-md-4">
				<div class="sidebar">
					
					<!-- Horário de Atendimento -->
					<?php if(!empty($arr_oh_day)): ?>
						<div class="widget" style="background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 24px;">
							<h4 style="font-size: 18px; font-weight: 800; color: #0a6b74; border-bottom: 2px solid #e6f3f4; padding-bottom: 10px; margin-bottom: 16px;">
								<i class="fa fa-clock-o" style="margin-right: 8px;"></i> Horário de Atendimento
							</h4>
							<ul style="list-style: none; padding-left: 0; margin-bottom: 0;">
								<?php for($i=0; $i<count($arr_oh_day); $i++): ?>
									<li style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13.5px;">
										<span style="font-weight: 600; color: #334155;"><?php echo htmlspecialchars($arr_oh_day[$i]); ?></span>
										<span class="badge" style="background: #e6f3f4; color: #0a6b74; font-weight: 700; padding: 4px 10px; border-radius: 6px;"><?php echo htmlspecialchars($arr_oh_time[$i]); ?></span>
									</li>
								<?php endfor; ?>
							</ul>
						</div>
					<?php endif; ?>

					<!-- Outras Especialidades / Unidades -->
					<div class="widget" style="background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
						<h4 style="font-size: 18px; font-weight: 800; color: #0a6b74; border-bottom: 2px solid #e6f3f4; padding-bottom: 10px; margin-bottom: 16px;">
							<i class="fa fa-hospital-o" style="margin-right: 8px;"></i> Clínicas & Especialidades
						</h4>
						<ul style="list-style: none; padding-left: 0; margin-bottom: 0;">
							<?php
							$statement = $pdo->prepare("SELECT * FROM tbl_department ORDER BY dep_name ASC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row_d) {
								$is_current = ($row_d['dep_slug'] == $dep_slug);
								?>
								<li style="margin-bottom: 8px;">
									<a href="<?php echo BASE_URL; ?>department/<?php echo $row_d['dep_slug']; ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 8px; font-weight: <?= $is_current ? '700' : '500'; ?>; color: <?= $is_current ? '#0a6b74' : '#334155'; ?>; background: <?= $is_current ? '#e6f3f4' : 'transparent'; ?>; text-decoration: none; transition: all 0.2s ease;">
										<span><?php echo htmlspecialchars($row_d['dep_name']); ?></span>
										<i class="fa fa-chevron-right" style="font-size: 11px; opacity: <?= $is_current ? '1' : '0.4'; ?>;"></i>
									</a>
								</li>
								<?php
							}
							?>							
						</ul>
					</div>

				</div>
			</div>

		</div>
	</div>
</section>
<!-- Department Main End -->
			
<?php require_once('footer.php'); ?>