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
	$statement = $pdo->prepare("SELECT * FROM tbl_doctor WHERE slug=?");
	$statement->execute(array($_REQUEST['slug']));
	$total = $statement->rowCount();
	if( $total == 0 )
	{
		header('location: '.BASE_URL);
		exit;
	}
}

// Getting the detailed data of a doctor from slug
$statement = $pdo->prepare("SELECT * FROM tbl_doctor WHERE slug=?");
$statement->execute(array($_REQUEST['slug']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);				
foreach ($result as $row) {
    extract($row);
}

$statement = $pdo->prepare("SELECT * FROM tbl_designation WHERE designation_id=?");
$statement->execute(array($designation_id));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);				
$designation_name = '';
foreach ($result as $row)
{
	$designation_name = $row['designation_name'];
}

$stmt = $pdo->prepare("SELECT * FROM tbl_designation WHERE designation_id");
$stmt->execute(); 
$data = $stmt->fetchAll();

$doc_banner_src = (!empty($banner) && file_exists('assets/uploads/' . $banner)) ? BASE_URL . 'assets/uploads/' . $banner : BASE_URL . 'assets/uploads/department-1.png';
$doc_photo_src = (!empty($photo) && file_exists('assets/uploads/' . $photo)) ? BASE_URL . 'assets/uploads/' . $photo : BASE_URL . 'assets/images/sem-foto.gif';
$zap_num = !empty($phone) ? preg_replace('/[^0-9]/', '', $phone) : (!empty($contact_phone) ? preg_replace('/[^0-9]/', '', $contact_phone) : '5521985831464');
if (strlen($zap_num) <= 11 && substr($zap_num, 0, 2) !== '55') {
    $zap_num = '55' . $zap_num;
}
$zap_doc_msg = urlencode("*Olá! Gostaria de agendar uma consulta com Dr(a).* " . $name);
?>

<!-- Banner Start -->
<div class="page-banner" style="background-image:url(<?= $doc_banner_src; ?>);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1>Dr(a). <?php echo htmlspecialchars($name); ?></h1>
					<?php if(!empty($designation_name)): ?>
						<p style="color: rgba(255,255,255,0.9); font-size: 1.15rem; margin-top: 8px; font-weight: 500;">
							<i class="fa fa-user-md" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($designation_name); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->

<!-- Doctor Detail Start -->
<section class="doctor-detail" style="padding: 60px 0; background: #f8fafc;">
	<div class="container">
		<div class="row">
			
			<!-- Card Lateral do Médico -->
			<div class="col-md-4">
				<div style="background: #ffffff; border-radius: 16px; padding: 28px 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; text-align: center; margin-bottom: 30px;">
					<div style="width: 170px; height: 170px; margin: 0 auto 20px auto; border-radius: 50%; overflow: hidden; border: 4px solid #e6f3f4; box-shadow: 0 8px 20px rgba(10,107,116,0.15);">
						<img src="<?= $doc_photo_src; ?>" alt="<?php echo htmlspecialchars($name); ?>" style="width: 100%; height: 100%; object-fit: cover;">
					</div>
					
					<h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 6px;">
						<?php echo htmlspecialchars($name); ?>
					</h2>
					
					<div style="margin-bottom: 12px;">
						<span style="display: inline-block; background: #e6f3f4; color: #0a6b74; font-weight: 700; font-size: 13px; padding: 5px 14px; border-radius: 20px;">
							<?php echo htmlspecialchars($designation_name); ?>
						</span>
					</div>

					<?php if(!empty($degree)): ?>
						<p style="color: #64748b; font-size: 13.5px; margin-bottom: 16px; line-height: 1.5;">
							<i class="fa fa-graduation-cap" style="color: #0a6b74; margin-right: 4px;"></i>
							<?php echo htmlspecialchars($degree); ?>
						</p>
					<?php endif; ?>

					<?php if(!empty($registro)): ?>
						<p style="color: #0a6b74; font-weight: 700; font-size: 13px; background: #f1f5f9; padding: 6px 12px; border-radius: 8px; margin-bottom: 18px;">
							<i class="fa fa-id-card-o" style="margin-right: 5px;"></i> Registro: <?php echo htmlspecialchars($registro); ?>
						</p>
					<?php endif; ?>

					<!-- Redes Sociais -->
					<div style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; margin-bottom: 22px;">
						<?php if(!empty($facebook)): ?>
							<a href="<?php echo htmlspecialchars($facebook); ?>" target="_blank" rel="noopener" style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #3b5998; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;"><i class="fa fa-facebook"></i></a>
						<?php endif; ?>

						<?php if(!empty($instagram)): ?>
							<a href="<?php echo htmlspecialchars($instagram); ?>" target="_blank" rel="noopener" style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #e1306c; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;"><i class="fa fa-instagram"></i></a>
						<?php endif; ?>

						<?php if(!empty($linkedin)): ?>
							<a href="<?php echo htmlspecialchars($linkedin); ?>" target="_blank" rel="noopener" style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #0077b5; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;"><i class="fa fa-linkedin"></i></a>
						<?php endif; ?>

						<?php if(!empty($youtube)): ?>
							<a href="<?php echo htmlspecialchars($youtube); ?>" target="_blank" rel="noopener" style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #ff0000; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;"><i class="fa fa-youtube"></i></a>
						<?php endif; ?>

						<?php if(!empty($twitter)): ?>
							<a href="<?php echo htmlspecialchars($twitter); ?>" target="_blank" rel="noopener" style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #1da1f2; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;"><i class="fa fa-twitter"></i></a>
						<?php endif; ?>
					</div>

					<div style="padding-top: 18px; border-top: 1px solid #f1f5f9;">
						<a href="https://wa.me/<?= $zap_num; ?>?text=<?= $zap_doc_msg; ?>" target="_blank" rel="noopener" class="btn btn-success btn-block" style="background: #10b981; border: none; border-radius: 10px; font-weight: 700; padding: 13px 18px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(16,185,129,0.35); font-size: 15px; color: #ffffff; text-decoration: none;">
							<i class="fa fa-whatsapp" style="font-size: 19px;"></i> Agendar Consulta
						</a>
					</div>
				</div>
			</div>

			<!-- Coluna Principal com Abas Modernas -->
			<div class="col-md-8">
				<div style="background: #ffffff; border-radius: 16px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 30px;">
					
					<!-- Navegação de Abas Estilizada -->
					<ul class="nav nav-tabs" style="border-bottom: 2px solid #e2e8f0; margin-bottom: 24px; display: flex; gap: 8px;">
						<li class="active">
							<a href="#tab1" data-toggle="tab" style="border: none; border-radius: 8px 8px 0 0; font-weight: 700; padding: 12px 20px; color: #0a6b74; font-size: 14.5px;">
								<i class="fa fa-user-circle-o" style="margin-right: 6px;"></i> Sobre o Especialista
							</a>
						</li>
						<?php if(!empty($address) || !empty($phone) || !empty($email) || !empty($website)): ?>
						<li>
							<a href="#tab2" data-toggle="tab" style="border: none; border-radius: 8px 8px 0 0; font-weight: 700; padding: 12px 20px; color: #64748b; font-size: 14.5px;">
								<i class="fa fa-map-marker" style="margin-right: 6px;"></i> Contato & Local
							</a>
						</li>
						<?php endif; ?>
						<li>
							<a href="#tab3" data-toggle="tab" style="border: none; border-radius: 8px 8px 0 0; font-weight: 700; padding: 12px 20px; color: #64748b; font-size: 14.5px;">
								<i class="fa fa-calendar-check-o" style="margin-right: 6px;"></i> Atendimento & Horários
							</a>
						</li>
					</ul>
					
					<!-- Conteúdo das Abas -->
					<div class="tab-content">
						
						<!-- Aba 1: Sobre -->
						<div class="tab-pane fade active in" id="tab1">
							<h3 style="font-size: 19px; font-weight: 800; color: #0a6b74; margin-top: 0; margin-bottom: 16px;">
								Apresentação Profissional
							</h3>
							<div class="content" style="color: #334155; font-size: 15px; line-height: 1.8;">
								<?php if(!empty($detail)): ?>
									<?php echo $detail; ?>
								<?php else: ?>
									<p style="color: #64748b; font-style: italic;">
										Informações detalhadas sobre a atuação clínica e especializações do(a) Dr(a). <?php echo htmlspecialchars($name); ?>. Para agendar sua consulta, entre em contato através de nossos canais de atendimento.
									</p>
								<?php endif; ?>
							</div>
						</div>
						
						<!-- Aba 2: Contato -->
						<div class="tab-pane fade" id="tab2">
							<h3 style="font-size: 19px; font-weight: 800; color: #0a6b74; margin-top: 0; margin-bottom: 20px;">
								Canais de Atendimento
							</h3>
							<div class="row">
								<?php if(!empty($address)): ?>
									<div class="col-md-6" style="margin-bottom: 16px;">
										<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; align-items: flex-start; gap: 14px;">
											<div style="width: 42px; height: 42px; border-radius: 10px; background: #e6f3f4; color: #0a6b74; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
												<i class="fa fa-map-marker"></i>
											</div>
											<div>
												<h4 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">Endereço</h4>
												<p style="margin: 0; font-size: 13.5px;">
													<a href="https://www.google.com/maps/place/<?= urlencode($address); ?>" target="_blank" rel="noopener" style="color: #0a6b74; text-decoration: none;">
														<?= htmlspecialchars($address); ?>
													</a>
												</p>
											</div>
										</div>
									</div>
								<?php endif; ?>

								<?php if(!empty($phone)): ?>
									<div class="col-md-6" style="margin-bottom: 16px;">
										<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; align-items: flex-start; gap: 14px;">
											<div style="width: 42px; height: 42px; border-radius: 10px; background: #e6f3f4; color: #0a6b74; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
												<i class="fa fa-phone"></i>
											</div>
											<div>
												<h4 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">Telefone</h4>
												<p style="margin: 0; font-size: 13.5px;">
													<a href="tel:+55<?= preg_replace('/[^0-9]/', '', $phone); ?>" style="color: #0a6b74; text-decoration: none; font-weight: 600;">
														<?= htmlspecialchars($phone); ?>
													</a>
												</p>
											</div>
										</div>
									</div>
								<?php endif; ?>

								<?php if(!empty($email)): ?>
									<div class="col-md-6" style="margin-bottom: 16px;">
										<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; align-items: flex-start; gap: 14px;">
											<div style="width: 42px; height: 42px; border-radius: 10px; background: #e6f3f4; color: #0a6b74; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
												<i class="fa fa-envelope"></i>
											</div>
											<div>
												<h4 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">E-mail</h4>
												<p style="margin: 0; font-size: 13.5px;">
													<a href="mailto:<?= htmlspecialchars($email); ?>" style="color: #0a6b74; text-decoration: none;">
														<?= htmlspecialchars($email); ?>
													</a>
												</p>
											</div>
										</div>
									</div>
								<?php endif; ?>

								<?php if(!empty($website)): ?>
									<div class="col-md-6" style="margin-bottom: 16px;">
										<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; align-items: flex-start; gap: 14px;">
											<div style="width: 42px; height: 42px; border-radius: 10px; background: #e6f3f4; color: #0a6b74; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
												<i class="fa fa-globe"></i>
											</div>
											<div>
												<h4 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">Website</h4>
												<p style="margin: 0; font-size: 13.5px;">
													<a href="<?= htmlspecialchars($website); ?>" target="_blank" rel="noopener" style="color: #0a6b74; text-decoration: none;">
														<?= htmlspecialchars($website); ?>
													</a>
												</p>
											</div>
										</div>
									</div>
								<?php endif; ?>
							</div>
						</div>
						
						<!-- Aba 3: Atendimento e Horários -->
						<div class="tab-pane fade" id="tab3">
							<h3 style="font-size: 19px; font-weight: 800; color: #0a6b74; margin-top: 0; margin-bottom: 18px;">
								Horários e Especialidades Atendidas
							</h3>

							<?php
							$i=0;
							$medico = $pdo->prepare("SELECT * FROM tbl_doctor 
							AS t1 JOIN tbl_especialidade 
							AS t2 JOIN tbl_department_openning_hour 
							AS t3 JOIN tbl_designation 
							AS t4 
							WHERE t4.designation_id=t2.funcao AND t2.semana=t3.oh_id AND t1.id=t2.medico AND t1.slug=?");
							$medico->execute(array($_REQUEST['slug']));
							$view = $medico->fetchAll(PDO::FETCH_ASSOC);

							if(empty($view)):
							?>
								<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; text-align: center; color: #64748b;">
									<i class="fa fa-calendar-o" style="font-size: 28px; margin-bottom: 10px; display: block; color: #0a6b74;"></i>
									Atendimento regular conforme agendamento prévio. Entre em contato com a nossa recepção via WhatsApp para verificar as datas disponíveis.
								</div>
							<?php else: ?>
								<div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
									<table class="table" style="margin-bottom: 0;">
										<thead>
											<tr style="background: #0a6b74; color: #ffffff;">
												<th style="padding: 14px 18px; font-weight: 700; border: none;">Especialidade</th>
												<th style="padding: 14px 18px; font-weight: 700; border: none;">Dia da Semana</th>
												<th style="padding: 14px 18px; font-weight: 700; border: none; text-align: right;">Horário</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach($view as $dias): $i++; ?>
											<tr style="border-bottom: 1px solid #f1f5f9;">
												<td style="padding: 14px 18px; vertical-align: middle; font-weight: 600; color: #0f172a;">
													<i class="fa fa-stethoscope text-success" style="margin-right: 6px;"></i>
													<?= htmlspecialchars($dias['designation_name']); ?>
												</td>
												<td style="padding: 14px 18px; vertical-align: middle; color: #475569;">
													<?= htmlspecialchars($dias['oh_day']); ?>
												</td>
												<td style="padding: 14px 18px; vertical-align: middle; text-align: right;">
													<?php if(empty($dias['inicio']) && empty($dias['fim'])): ?>
														<span style="background: #fef3c7; color: #92400e; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 20px;">
															Sob Demanda
														</span>
													<?php else: ?>
														<span style="background: #dcfce7; color: #166534; font-weight: 700; font-size: 13px; padding: 4px 12px; border-radius: 20px;">
															<?= date("H:i", strtotime($dias['inicio'])) . " às " . date("H:i", strtotime($dias['fim'])); ?>
														</span>
													<?php endif; ?>
												</td>
											</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							<?php endif; ?>

						</div>

					</div>
				</div>
			</div>

		</div>
	</div>
</section>
<!-- Doctor Detail End -->

<!-- Seção Outros Médicos Qualificados -->
<section class="doctor-v2" style="padding: 60px 0; background: #ffffff;">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading-normal" style="text-align: center; margin-bottom: 40px;">
					<h2 style="font-size: 28px; font-weight: 800; color: #0a6b74; margin-bottom: 8px;">Conheça Nossa Equipe Médica</h2>
					<p style="color: #64748b; font-size: 16px;">Profissionais especializados e dedicados ao cuidado da sua saúde integral</p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
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
					                           WHERE t1.status = ?
					                           ORDER BY t1.id DESC");
					$statement->execute(array('Active'));
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
					foreach ($result as $row) {
						$other_photo = (!empty($row['photo']) && file_exists('assets/uploads/' . $row['photo'])) ? BASE_URL . 'assets/uploads/' . $row['photo'] : BASE_URL . 'assets/images/sem-foto.gif';
						?>
						<div class="item wow fadeInUp" style="padding: 10px;">
							<div style="background: #ffffff; border-radius: 14px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 8px 24px rgba(0,0,0,0.05); text-align: center; padding-bottom: 20px; transition: all 0.3s ease;">
								<div style="height: 220px; background-image:url(<?= $other_photo; ?>); background-size: cover; background-position: center; position: relative;"></div>
								<div style="padding: 18px 14px 10px 14px;">
									<h3 style="font-size: 17px; font-weight: 800; margin: 0 0 6px 0;">
										<a href="<?php echo BASE_URL; ?>doctor/<?php echo $row['slug']; ?>" style="color: #0f172a; text-decoration: none;">
											<?php echo htmlspecialchars($row['name']); ?>
										</a>
									</h3>
									<p style="color: #0a6b74; font-weight: 600; font-size: 13px; margin-bottom: 12px;">
										<?php echo htmlspecialchars($row['designation_name']); ?>
									</p>
									<a href="<?php echo BASE_URL; ?>doctor/<?php echo $row['slug']; ?>" class="btn btn-sm btn-outline-primary" style="border-radius: 20px; font-weight: 700; font-size: 12.5px; padding: 6px 16px;">
										Ver Perfil
									</a>
								</div>
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

<?php require_once('footer.php'); ?>