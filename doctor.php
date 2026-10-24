<?php require_once('header.php'); ?>

<?php
// Preventing the direct access of this page.
$slug = trim($_REQUEST['slug'] ?? '');
if(empty($slug))
{
	header('location: ' . BASE_URL);
	exit;
}

// Getting the detailed data of a doctor from slug
$statement = $pdo->prepare("SELECT * FROM tbl_doctor WHERE slug=?");
$statement->execute(array($slug));
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row)
{
	header('location: ' . BASE_URL);
	exit;
}

extract($row);

// Getting designation name
$designation_name = '';
if (!empty($designation_id)) {
	$statement = $pdo->prepare("SELECT designation_name FROM tbl_designation WHERE designation_id=?");
	$statement->execute(array($designation_id));
	$designation_name = (string)$statement->fetchColumn();
}

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
			<div class="col-md-12 text-center">
				<div class="banner-text">
					<span class="ps-section-badge mb-2"><i class="fas fa-user-md me-1"></i> Especialista Pronto Saúde</span>
					<h1 class="mt-2 mb-2">Dr(a). <?php echo htmlspecialchars($name); ?></h1>
					<?php if(!empty($designation_name)): ?>
						<p class="lead text-white-50 mx-auto mb-0" style="max-width: 680px; font-size: 1.1rem; font-weight: 500;">
							<i class="fas fa-stethoscope me-1 text-white"></i> <?php echo htmlspecialchars($designation_name); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->

<!-- Doctor Detail Start -->
<section class="doctor-detail" style="padding: 60px 0 80px 0;">
	<div class="container">
		<div class="row g-4">
			
			<!-- Card Lateral do Médico -->
			<div class="col-lg-4 col-md-5">
				<div style="background: #ffffff; border-radius: 16px; padding: 30px 24px; box-shadow: 0 4px 20px rgba(10,107,116,0.06); border: 1px solid #e2e8f0; text-align: center; position: sticky; top: 120px;">
					<div style="width: 170px; height: 170px; margin: 0 auto 20px auto; border-radius: 50%; overflow: hidden; border: 4px solid #e6f3f4; box-shadow: 0 8px 24px rgba(10,107,116,0.12);">
						<img src="<?= $doc_photo_src; ?>" alt="<?php echo htmlspecialchars($name); ?>" style="width: 100%; height: 100%; object-fit: cover;">
					</div>
					
					<h2 style="font-size: 22px; font-weight: 800; color: #0f2430; margin-top: 0; margin-bottom: 6px; font-family: 'Outfit', sans-serif;">
						<?php echo htmlspecialchars($name); ?>
					</h2>
					
					<div style="margin-bottom: 12px;">
						<span style="display: inline-block; background: #e6f3f4; color: #0a6b74; font-weight: 700; font-size: 13px; padding: 6px 16px; border-radius: 20px;">
							<i class="fas fa-user-md me-1"></i> <?php echo htmlspecialchars($designation_name); ?>
						</span>
					</div>

					<?php if(!empty($degree)): ?>
						<p style="color: #64748b; font-size: 13.5px; margin-bottom: 14px; line-height: 1.5;">
							<i class="fas fa-graduation-cap text-primary me-1"></i>
							<?php echo htmlspecialchars($degree); ?>
						</p>
					<?php endif; ?>

					<?php if(!empty($registro)): ?>
						<p style="color: #0a6b74; font-weight: 700; font-size: 13px; background: #f0f9fa; padding: 8px 12px; border-radius: 8px; margin-bottom: 18px; border: 1px solid rgba(10,107,116,0.12);">
							<i class="far fa-id-card me-1"></i> Registro: <?php echo htmlspecialchars($registro); ?>
						</p>
					<?php endif; ?>

					<!-- Redes Sociais com FontAwesome 6 -->
					<div style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; margin-bottom: 22px;">
						<?php if(!empty($facebook)): ?>
							<a href="<?php echo htmlspecialchars($facebook); ?>" target="_blank" rel="noopener" aria-label="Facebook" style="width: 38px; height: 38px; border-radius: 50%; background: #f1f5f9; color: #1877f2; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;"><i class="fab fa-facebook-f"></i></a>
						<?php endif; ?>

						<?php if(!empty($instagram)): ?>
							<a href="<?php echo htmlspecialchars($instagram); ?>" target="_blank" rel="noopener" aria-label="Instagram" style="width: 38px; height: 38px; border-radius: 50%; background: #f1f5f9; color: #e1306c; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;"><i class="fab fa-instagram"></i></a>
						<?php endif; ?>

						<?php if(!empty($linkedin)): ?>
							<a href="<?php echo htmlspecialchars($linkedin); ?>" target="_blank" rel="noopener" aria-label="LinkedIn" style="width: 38px; height: 38px; border-radius: 50%; background: #f1f5f9; color: #0077b5; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;"><i class="fab fa-linkedin-in"></i></a>
						<?php endif; ?>

						<?php if(!empty($youtube)): ?>
							<a href="<?php echo htmlspecialchars($youtube); ?>" target="_blank" rel="noopener" aria-label="YouTube" style="width: 38px; height: 38px; border-radius: 50%; background: #f1f5f9; color: #ff0000; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;"><i class="fab fa-youtube"></i></a>
						<?php endif; ?>

						<?php if(!empty($twitter)): ?>
							<a href="<?php echo htmlspecialchars($twitter); ?>" target="_blank" rel="noopener" aria-label="X Twitter" style="width: 38px; height: 38px; border-radius: 50%; background: #f1f5f9; color: #0f172a; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;"><i class="fab fa-x-twitter"></i></a>
						<?php endif; ?>
					</div>

					<div style="padding-top: 18px; border-top: 1px solid #f1f5f9;" class="d-flex flex-column gap-2">
						<a href="https://wa.me/<?= $zap_num; ?>?text=<?= $zap_doc_msg; ?>" target="_blank" rel="noopener" class="btn btn-success w-100 fw-bold py-3 rounded-3 d-flex align-items-center justify-content-center gap-2 shadow-sm text-decoration-none" style="background: #10b981; border: none; font-size: 15px;">
							<i class="fab fa-whatsapp fs-5"></i> Agendar Consulta
						</a>
						
						<a href="javascript:history.back()" class="btn btn-outline-secondary w-100 fw-bold py-2 rounded-3 text-decoration-none d-flex align-items-center justify-content-center gap-2 mt-1">
							<i class="fas fa-arrow-left"></i> Voltar
						</a>
					</div>
				</div>
			</div>

			<!-- Coluna Principal com Abas Modernas Bootstrap 5 -->
			<div class="col-lg-8 col-md-7">
				<div style="background: #ffffff; border-radius: 16px; padding: 32px 28px; box-shadow: 0 4px 20px rgba(10,107,116,0.06); border: 1px solid #e2e8f0; margin-bottom: 30px;">
					
					<!-- Navegação de Abas Bootstrap 5 -->
					<ul class="nav nav-pills gap-2 mb-4 border-bottom pb-3" id="docProfileTab" role="tablist">
						<li class="nav-item" role="presentation">
							<button class="nav-link active fw-bold px-3 py-2 rounded-3" id="tab-bio-btn" data-bs-toggle="pill" data-bs-target="#tab-bio" type="button" role="tab" aria-controls="tab-bio" aria-selected="true" style="font-size: 14.5px;">
								<i class="far fa-user me-2"></i> Apresentação
							</button>
						</li>
						<?php if(!empty($address) || !empty($phone) || !empty($email) || !empty($website)): ?>
						<li class="nav-item" role="presentation">
							<button class="nav-link fw-bold px-3 py-2 rounded-3" id="tab-contact-btn" data-bs-toggle="pill" data-bs-target="#tab-contact" type="button" role="tab" aria-controls="tab-contact" aria-selected="false" style="font-size: 14.5px;">
								<i class="fas fa-map-marker-alt me-2"></i> Contato & Local
							</button>
						</li>
						<?php endif; ?>
						<li class="nav-item" role="presentation">
							<button class="nav-link fw-bold px-3 py-2 rounded-3" id="tab-hours-btn" data-bs-toggle="pill" data-bs-target="#tab-hours" type="button" role="tab" aria-controls="tab-hours" aria-selected="false" style="font-size: 14.5px;">
								<i class="far fa-calendar-check me-2"></i> Atendimento & Horários
							</button>
						</li>
					</ul>
					
					<!-- Conteúdo das Abas Bootstrap 5 -->
					<div class="tab-content" id="docProfileTabContent">
						
						<!-- Aba 1: Sobre / Apresentação -->
						<div class="tab-pane fade show active" id="tab-bio" role="tabpanel" aria-labelledby="tab-bio-btn">
							<h3 style="font-size: 20px; font-weight: 800; color: #0a6b74; margin-top: 0; margin-bottom: 16px;">
								Apresentação Profissional
							</h3>
							<div class="content" style="color: #334155; font-size: 15.5px; line-height: 1.85;">
								<?php if(!empty($detail)): ?>
									<?php echo $detail; ?>
								<?php else: ?>
									<p style="color: #64748b; font-style: italic;">
										Informações detalhadas sobre a atuação clínica e especializações do(a) Dr(a). <?php echo htmlspecialchars($name); ?>. Para agendar sua consulta ou verificar a disponibilidade, entre em contato através de nossos canais de atendimento.
									</p>
								<?php endif; ?>
							</div>
						</div>
						
						<!-- Aba 2: Contato e Local -->
						<div class="tab-pane fade" id="tab-contact" role="tabpanel" aria-labelledby="tab-contact-btn">
							<h3 style="font-size: 20px; font-weight: 800; color: #0a6b74; margin-top: 0; margin-bottom: 20px;">
								Canais de Atendimento
							</h3>
							<div class="row g-3">
								<?php if(!empty($address)): ?>
									<div class="col-sm-6">
										<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; align-items: flex-start; gap: 14px; height: 100%;">
											<div style="width: 42px; height: 42px; border-radius: 10px; background: #e6f3f4; color: #0a6b74; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
												<i class="fas fa-map-marker-alt"></i>
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
									<div class="col-sm-6">
										<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; align-items: flex-start; gap: 14px; height: 100%;">
											<div style="width: 42px; height: 42px; border-radius: 10px; background: #e6f3f4; color: #0a6b74; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
												<i class="fas fa-phone-alt"></i>
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
									<div class="col-sm-6">
										<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; align-items: flex-start; gap: 14px; height: 100%;">
											<div style="width: 42px; height: 42px; border-radius: 10px; background: #e6f3f4; color: #0a6b74; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
												<i class="far fa-envelope"></i>
											</div>
											<div>
												<h4 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">E-mail</h4>
												<p style="margin: 0; font-size: 13.5px; word-break: break-all;">
													<a href="mailto:<?= htmlspecialchars($email); ?>" style="color: #0a6b74; text-decoration: none;">
														<?= htmlspecialchars($email); ?>
													</a>
												</p>
											</div>
										</div>
									</div>
								<?php endif; ?>

								<?php if(!empty($website)): ?>
									<div class="col-sm-6">
										<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; align-items: flex-start; gap: 14px; height: 100%;">
											<div style="width: 42px; height: 42px; border-radius: 10px; background: #e6f3f4; color: #0a6b74; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
												<i class="fas fa-globe"></i>
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
						<div class="tab-pane fade" id="tab-hours" role="tabpanel" aria-labelledby="tab-hours-btn">
							<h3 style="font-size: 20px; font-weight: 800; color: #0a6b74; margin-top: 0; margin-bottom: 18px;">
								Horários e Especialidades Atendidas
							</h3>

							<?php
							$statement_h = $pdo->prepare("SELECT * FROM tbl_doctor t1 
							JOIN tbl_especialidade t2 ON t1.id = t2.medico
							JOIN tbl_department_openning_hour t3 ON t2.semana = t3.oh_id
							JOIN tbl_designation t4 ON t2.funcao = t4.designation_id
							WHERE t1.slug = ?");
							$statement_h->execute(array($slug));
							$view_hours = $statement_h->fetchAll(PDO::FETCH_ASSOC);

							if(empty($view_hours)):
							?>
								<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; text-align: center; color: #64748b;">
									<i class="far fa-calendar-alt text-primary mb-3" style="font-size: 32px; display: block;"></i>
									<h5 class="fw-bold text-dark mb-2">Atendimento por Agendamento Prévio</h5>
									<p class="small text-muted mb-0">Consulte os dias e horários disponíveis diretamente com a nossa equipe via WhatsApp.</p>
								</div>
							<?php else: ?>
								<div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
									<table class="table align-middle mb-0">
										<thead>
											<tr style="background: #0a6b74; color: #ffffff;">
												<th style="padding: 14px 18px; font-weight: 700; border: none;">Especialidade</th>
												<th style="padding: 14px 18px; font-weight: 700; border: none;">Dia da Semana</th>
												<th style="padding: 14px 18px; font-weight: 700; border: none; text-align: right;">Horário</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach($view_hours as $dias): ?>
											<tr style="border-bottom: 1px solid #f1f5f9;">
												<td style="padding: 14px 18px; font-weight: 600; color: #0f172a;">
													<i class="fas fa-stethoscope text-success me-2"></i>
													<?= htmlspecialchars($dias['designation_name']); ?>
												</td>
												<td style="padding: 14px 18px; color: #475569;">
													<?= htmlspecialchars($dias['oh_day']); ?>
												</td>
												<td style="padding: 14px 18px; text-align: right;">
													<?php if(empty($dias['inicio']) && empty($dias['fim'])): ?>
														<span class="badge bg-warning-subtle text-warning-emphasis fw-bold px-3 py-2 rounded-pill">
															Sob Demanda
														</span>
													<?php else: ?>
														<span class="badge bg-success-subtle text-success fw-bold px-3 py-2 rounded-pill" style="font-size: 13px;">
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

<!-- Seção Outros Médicos Qualificados em Grid Moderno -->
<section style="padding: 60px 0; background: #ffffff; border-top: 1px solid #e2e8f0;">
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center mb-4">
				<span class="ps-section-badge mb-2"><i class="fas fa-user-md me-1"></i> Corpo Clínico</span>
				<h2 style="font-size: 26px; font-weight: 800; color: #0a6b74; margin-top: 8px; margin-bottom: 6px;">Outros Médicos da Pronto Saúde</h2>
				<p style="color: #64748b; font-size: 15px;">Profissionais especializados e dedicados ao cuidado da sua saúde integral</p>
			</div>
		</div>
		<div class="row g-3">
			<?php
			$statement = $pdo->prepare("SELECT 
										t1.id,
										t1.name,
										t1.slug,
										t1.designation_id,
										t1.photo,
										t2.designation_name
			                           FROM tbl_doctor t1
			                           JOIN tbl_designation t2
			                           ON t1.designation_id = t2.designation_id
			                           WHERE t1.status = 'Active' AND t1.slug != ?
			                           ORDER BY t1.id ASC
			                           LIMIT 4");
			$statement->execute(array($slug));
			$other_docs = $statement->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($other_docs as $o_row) {
				$o_photo = (!empty($o_row['photo']) && file_exists('assets/uploads/' . $o_row['photo'])) ? BASE_URL . 'assets/uploads/' . $o_row['photo'] : BASE_URL . 'assets/images/sem-foto.gif';
				$o_link = BASE_URL . 'doctor/' . $o_row['slug'];
				?>
				<div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
					<div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="border: 1px solid #e2e8f0 !important; transition: all 0.3s ease;">
						<div style="height: 200px; overflow: hidden; background: #07474d;">
							<img src="<?= $o_photo; ?>" alt="<?php echo htmlspecialchars($o_row['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
						</div>
						<div class="p-3 text-center d-flex flex-column flex-grow-1">
							<h5 class="fw-bold mb-1" style="font-size: 16px;">
								<a href="<?= $o_link; ?>" class="text-dark text-decoration-none">
									<?php echo htmlspecialchars($o_row['name']); ?>
								</a>
							</h5>
							<span class="small fw-semibold mb-3" style="color: #0a6b74;">
								<?php echo htmlspecialchars($o_row['designation_name']); ?>
							</span>
							<div class="mt-auto">
								<a href="<?= $o_link; ?>" class="btn btn-sm btn-outline-secondary w-100 fw-bold rounded-pill">
									Ver Perfil Completo
								</a>
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

<?php require_once('footer.php'); ?>