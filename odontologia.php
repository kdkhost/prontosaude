<?php include_once 'header.php'; ?>

<?php
$zap_num = !empty($contact_phone) ? preg_replace('/[^0-9]/', '', $contact_phone) : '5521985831464';
if (strlen($zap_num) <= 11 && substr($zap_num, 0, 2) !== '55') {
    $zap_num = '55' . $zap_num;
}
?>

<!-- Banner Start -->
<div class="page-banner" style="background-image: url(<?= BASE_URL; ?>assets/uploads/department-1.png);">
	<div class="overlay"></div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="banner-text">
					<h1>Serviços Odontológicos</h1>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Banner End -->

<!-- Service Start -->
<section class="service-v1">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2>Serviços Odontológicos Especializados</h2>
					<p>Cuidado integral com seu sorriso, tecnologia de ponta e equipe acolhedora</p>
				</div>
			</div>
		</div>
		<div class="row">
			<?php
			$statement = $pdo->prepare("SELECT * FROM tbl_dental ORDER BY id_den ASC");
			$statement->execute();
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($result as $row) {
				$foto = $row['foto'] ?? '';
				$foto_src = (!empty($foto) && file_exists('assets/uploads/'.$foto)) ? BASE_URL.'assets/uploads/'.$foto : BASE_URL.'assets/images/sem-foto.gif';
				$link_dest = !empty($row['link']) ? $row['link'] : '#';
				$is_external_link = (strpos($link_dest, 'http') === 0);
				$btn_label = !empty($row['button_text']) ? $row['button_text'] : 'Agendar Avaliação';
				$benefits_items = !empty($row['beneficios']) ? explode("\n", str_replace("\r", "", $row['beneficios'])) : [];
				$zap_text = urlencode("*Olá! Gostaria de agendar uma avaliação odontológica para:* " . $row['servico']);
				?>
				<div class="col-sm-6 col-md-4 col-lg-4">
					<div class="item service-card-box">
						<div class="photo" style="background-image:url(<?= $foto_src; ?>); position: relative;">
							<?php if(!empty($row['valor'])): ?>
								<div class="service-card-price-badge">
									<small>A partir de</small> R$ <?= htmlspecialchars($row['valor']); ?>
								</div>
							<?php endif; ?>
						</div>
						<div class="text">
							<div class="inner">
								<h3>
									<?php if($is_external_link): ?>
										<a href="<?= htmlspecialchars($link_dest); ?>" target="_blank" rel="noopener"><?= htmlspecialchars($row['servico']); ?></a>
									<?php else: ?>
										<a href="#" data-toggle="modal" data-target="#modalOdonto-<?= $row['id_den']; ?>"><?= htmlspecialchars($row['servico']); ?></a>
									<?php endif; ?>
								</h3>
								<p>
									<?= substr(strip_tags($row['descricao']), 0, 150) . '...'; ?>
								</p>
								<?php if(!empty($benefits_items)): ?>
									<ul class="service-card-benefits">
										<?php foreach(array_slice($benefits_items, 0, 3) as $b_item): if(!empty(trim($b_item))): ?>
											<li><i class="fa fa-check text-success" style="margin-right: 5px;"></i> <?= htmlspecialchars(trim($b_item)); ?></li>
										<?php endif; endforeach; ?>
									</ul>
								<?php endif; ?>
								<p class="button">
									<?php if($is_external_link): ?>
										<a href="<?= htmlspecialchars($link_dest); ?>" target="_blank" rel="noopener"><?= htmlspecialchars($btn_label); ?></a>
									<?php else: ?>
										<a href="#" data-toggle="modal" data-target="#modalOdonto-<?= $row['id_den']; ?>"><?= htmlspecialchars($btn_label); ?></a>
									<?php endif; ?>
								</p>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Modal Moderno de Detalhes -->
                <div class="modal fade" id="modalOdonto-<?= $row['id_den']?>" 
                data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modalLabel-<?= $row['id_den']?>" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
                      <div class="modal-header" style="background: linear-gradient(135deg, #0a6b74 0%, #075259 100%); color: #fff; padding: 18px 24px; border: none;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.85; font-size: 24px; text-shadow: none;">
                          <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title font-weight-bold" id="modalLabel-<?= $row['id_den']?>" style="color: #fff; font-size: 1.25rem; margin: 0;">
                            <i class="fa fa-stethoscope" style="margin-right: 8px;"></i> <?= htmlspecialchars($row['servico']); ?>
                        </h4>
                      </div>
                      <div class="modal-body" style="padding: 24px; background: #f8fafc;">
                        <div class="row align-items-center">
                          <div class="col-md-5 mb-3">
                            <div style="border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); background: #fff;">
                              <img class="img-responsive center-block" style="width: 100%; height: 220px; object-fit: cover;" src="<?= $foto_src; ?>" alt="<?= htmlspecialchars($row['servico']); ?>" />
                            </div>
                            <?php if(!empty($row['valor'])): ?>
                              <div style="margin-top: 12px; background: #e6f3f4; border-left: 4px solid #0a6b74; padding: 10px 14px; border-radius: 6px;">
                                <small style="display:block; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Investimento</small>
                                <strong style="color: #0a6b74; font-size: 1.2rem;">A partir de R$ <?= htmlspecialchars($row['valor']); ?></strong>
                              </div>
                            <?php endif; ?>
                          </div>
                          <div class="col-md-7">
                            <h5 style="color: #0f172a; font-weight: 700; margin-top: 0; margin-bottom: 12px;">Sobre o Procedimento</h5>
                            <div style="color: #334155; font-size: 14.5px; line-height: 1.6; margin-bottom: 16px;">
                              <?= !empty($row['desc_total']) ? nl2br($row['desc_total']) : nl2br($row['descricao']); ?>
                            </div>
                            <?php if(!empty($benefits_items)): ?>
                              <div style="background: #fff; border-radius: 8px; padding: 12px 16px; border: 1px solid #e2e8f0;">
                                <strong style="font-size: 13px; color: #0a6b74; display: block; margin-bottom: 6px;">Destaques:</strong>
                                <ul style="list-style: none; padding-left: 0; margin-bottom: 0;">
                                  <?php foreach($benefits_items as $b_item): if(!empty(trim($b_item))): ?>
                                    <li style="margin-bottom: 4px; font-size: 13.5px; color: #475569;"><i class="fa fa-check-circle text-success" style="margin-right: 6px;"></i> <?= htmlspecialchars(trim($b_item)); ?></li>
                                  <?php endif; endforeach; ?>
                                </ul>
                              </div>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer" style="background: #fff; border-top: 1px solid #e2e8f0; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center;">
                        <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 8px; padding: 8px 18px; font-weight: 600;">
                            Fechar
                        </button>
                        <a href="https://wa.me/<?= $zap_num; ?>?text=<?= $zap_text; ?>" target="_blank" rel="noopener" class="btn btn-success" style="background: #10b981; border: none; border-radius: 8px; padding: 9px 22px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
                            <i class="fa fa-whatsapp" style="font-size: 18px;"></i> Agendar Avaliação
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
				<?php }?>
		</div>
	</div>
</section>
<!-- Service End -->

<?php include_once 'footer.php'; ?>