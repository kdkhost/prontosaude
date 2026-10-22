<?php include_once 'header.php'; ?>

<!-- Banner Start -->
<div class="page-banner" style="background-image: url(<?= BASE_URL; ?>assets/uploads/department-1.png); background-size: cover; background-position: center;">
	<div class="overlay" style="background: linear-gradient(135deg, rgba(7, 71, 77, 0.88) 0%, rgba(10, 107, 116, 0.75) 100%);"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1>Trabalhe Conosco</h1>
					<p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; max-width: 600px; margin: 10px auto 0 auto;">
						Faça parte de uma equipe comprometida com a excelência, o acolhimento e a saúde integrada.
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->

<!-- Vagas Section Start -->
<section class="service-v1" style="padding: 70px 0; background: #f8fafc;">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2>Oportunidades em Aberto</h2>
					<p>Confira as vagas disponíveis em nossa rede e envie sua candidatura para crescer profissionalmente conosco.</p>
				</div>
			</div>
		</div>

		<?php
			$statement = $pdo->prepare("SELECT * FROM tbl_trabalhe WHERE status=1 ORDER BY id DESC");
			$statement->execute();
			$vagas = $statement->fetchAll(PDO::FETCH_ASSOC);
			$total_vagas = count($vagas);
		?>

		<div class="row">
			<?php if($total_vagas > 0): ?>
				<?php foreach ($vagas as $row): 
					$foto_vaga = (!empty($row['foto']) && file_exists('assets/uploads/' . $row['foto'])) 
								 ? BASE_URL . 'assets/uploads/' . $row['foto'] 
								 : BASE_URL . 'assets/images/sem-foto.gif';
					$salario_text = (!empty(trim($row['salario'] ?? ''))) ? htmlspecialchars($row['salario']) : 'A Combinar';
					$whats_text = urlencode("Olá! Gostaria de me candidatar para a vaga de: *" . $row['titulo'] . "* anunciada no site da Pronto Saúde.");
				?>
				<div class="col-sm-6 col-md-4">
					<div class="service-card-box h-100" style="margin-bottom: 30px;">
						<div class="photo" style="background-image:url(<?= $foto_vaga; ?>); height: 200px; position: relative;">
							<span class="service-card-price-badge" style="background: rgba(16, 185, 129, 0.95);">
								<i class="fa fa-briefcase me-1"></i> Vaga Aberta
							</span>
						</div>
						<div class="text">
							<h3><?= htmlspecialchars($row['titulo']); ?></h3>
							
							<?php if(!empty($row['local'])): ?>
								<p style="margin-bottom: 6px; font-size: 0.88rem; color: #64748b;">
									<i class="fa fa-map-marker text-primary" style="margin-right: 5px;"></i> <?= htmlspecialchars($row['local']); ?>
								</p>
							<?php endif; ?>

							<?php if(!empty($row['carga'])): ?>
								<p style="margin-bottom: 6px; font-size: 0.88rem; color: #64748b;">
									<i class="fa fa-clock-o text-primary" style="margin-right: 5px;"></i> <?= htmlspecialchars($row['carga']); ?>
								</p>
							<?php endif; ?>

							<p style="margin-bottom: 15px; font-size: 0.88rem; color: #64748b;">
								<i class="fa fa-money text-success" style="margin-right: 5px;"></i> Salário: <strong><?= $salario_text; ?></strong>
							</p>

							<p class="button" style="display: flex; gap: 8px;">
								<button type="button" class="btn btn-outline-primary w-100" data-toggle="modal" data-target="#modalVaga-<?= $row['id']; ?>" style="border-radius: 8px; font-weight: 600; padding: 10px;">
									<i class="fa fa-info-circle me-1"></i> Detalhes
								</button>
								<a href="https://wa.me/+5521985831464?text=<?= $whats_text; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success w-100" style="border-radius: 8px; font-weight: 700; padding: 10px; background: #25d366; border: none;">
									<i class="fa fa-whatsapp me-1"></i> Candidatar
								</a>
							</p>
						</div>
					</div>

					<!-- Modal Detalhes da Vaga -->
					<div class="modal fade" id="modalVaga-<?= $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel-<?= $row['id']; ?>" aria-hidden="true">
						<div class="modal-dialog modal-lg" role="document">
							<div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.25);">
								<div class="modal-header" style="background: linear-gradient(135deg, #075259, #0a6b74); color: #ffffff; padding: 18px 24px;">
									<h4 class="modal-title font-weight-bold" id="modalLabel-<?= $row['id']; ?>" style="color: #ffffff; margin: 0;">
										<i class="fa fa-briefcase me-2"></i> <?= htmlspecialchars($row['titulo']); ?>
									</h4>
									<button type="button" class="close" data-dismiss="modal" aria-label="Fechar" style="color: #ffffff; opacity: 0.85; font-size: 28px; line-height: 1;">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body" style="padding: 28px 24px; font-size: 0.95rem; line-height: 1.7;">
									<?php if(!empty($row['requisitos'])): ?>
										<div class="mb-4">
											<h5 class="text-primary font-weight-bold" style="color: #0a6b74;"><i class="fa fa-check-square-o me-2"></i> Requisitos</h5>
											<div><?= nl2br($row['requisitos']); ?></div>
										</div>
									<?php endif; ?>

									<?php if(!empty($row['beneficios'])): ?>
										<div class="mb-4">
											<h5 class="text-primary font-weight-bold" style="color: #0a6b74;"><i class="fa fa-gift me-2"></i> Benefícios Oferecidos</h5>
											<div><?= nl2br($row['beneficios']); ?></div>
										</div>
									<?php endif; ?>

									<div class="row pt-3" style="border-top: 1px solid #e2e8f0;">
										<div class="col-sm-4 mb-2">
											<strong class="text-muted d-block small uppercase">Carga Horária:</strong>
											<span><?= !empty($row['carga']) ? htmlspecialchars($row['carga']) : 'A combinar'; ?></span>
										</div>
										<div class="col-sm-4 mb-2">
											<strong class="text-muted d-block small uppercase">Salário:</strong>
											<span class="text-success font-weight-bold"><?= $salario_text; ?></span>
										</div>
										<div class="col-sm-4 mb-2">
											<strong class="text-muted d-block small uppercase">Localidade:</strong>
											<span><?= !empty($row['local']) ? htmlspecialchars($row['local']) : 'Rede Pronto Saúde'; ?></span>
										</div>
									</div>
								</div>
								<div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 24px;">
									<button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">Fechar</button>
									<a href="https://wa.me/+5521985831464?text=<?= $whats_text; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success" style="border-radius: 8px; font-weight: 700; background: #25d366; border: none;">
										<i class="fa fa-whatsapp me-1"></i> Enviar Candidatura via WhatsApp
									</a>
								</div>
							</div>
						</div>
					</div>

				</div>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="col-md-12 text-center" style="margin-bottom: 40px;">
					<div class="p-5" style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
						<i class="fa fa-briefcase text-muted" style="font-size: 48px; opacity: 0.5; margin-bottom: 16px;"></i>
						<h4>No momento não há vagas abertas para preenchimento imediato.</h4>
						<p class="text-muted">Mesmo assim, adoraríamos conhecer o seu talento! Cadastre seu currículo em nosso banco de talentos abaixo.</p>
					</div>
				</div>
			<?php endif; ?>

			<!-- Card Envio Espontâneo -->
			<div class="col-md-12">
				<div style="background: linear-gradient(135deg, #075259, #0a6b74); border-radius: 16px; padding: 40px 30px; color: #ffffff; box-shadow: 0 10px 30px rgba(10, 107, 116, 0.25);">
					<div class="row align-items-center">
						<div class="col-md-8">
							<h3 style="color: #ffffff; margin-top: 0; font-weight: 700;">Envie seu Currículo para o Banco de Talentos</h3>
							<p style="color: rgba(255,255,255,0.9); font-size: 1.05rem; margin-bottom: 0;">
								Estamos em constante expansão e sempre abertos a novos profissionais médicos, odontológicos, assistenciais e administrativos.<br>
								Encaminhe seu currículo atualizado com pretensão para: 
								<a href="mailto:supervisao@prontosauderj.com.br" style="color: #92fe9d; font-weight: 700; text-decoration: underline;">
									supervisao@prontosauderj.com.br
								</a>
							</p>
						</div>
						<div class="col-md-4 text-center" style="margin-top: 15px;">
							<a href="mailto:supervisao@prontosauderj.com.br?subject=Envio%20de%20Currículo%20-%20Banco%20de%20Talentos%20Pronto%20Saúde" class="btn btn-light" style="padding: 13px 26px; border-radius: 50px; font-weight: 700; color: #0a6b74; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
								<i class="fa fa-envelope-o me-1"></i> Enviar por E-mail
							</a>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</section>
<!-- Vagas Section End -->

<?php include_once 'footer.php'; ?>