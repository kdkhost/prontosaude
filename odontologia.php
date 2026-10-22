<?php include_once 'header.php'; ?>

<!-- Service Start -->
<section class="service-v1">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2>
					    Serviços Odontológicos
					</h2>
					<p>
					    Excelência no Atendimento
					</p>
				</div>
			</div>
		</div>
		<div class="row">
			<?php
			$statement = $pdo->prepare("SELECT * FROM tbl_dental ORDER BY id_den ASC");
			$statement->execute();
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
			foreach ($result as $row) {
				$foto = $row['foto'];
				$foto_src = (!empty($foto) && file_exists('assets/uploads/'.$foto)) ? BASE_URL.'assets/uploads/'.$foto : BASE_URL.'assets/images/sem-foto.gif';
				$link_dest = !empty($row['link']) ? $row['link'] : '#';
				$is_external_link = (strpos($link_dest, 'http') === 0);
				$btn_label = !empty($row['button_text']) ? $row['button_text'] : 'Agendar Avaliação';
				$benefits_items = !empty($row['beneficios']) ? explode("\n", str_replace("\r", "", $row['beneficios'])) : [];
				?>
				<div class="col-sm-6 col-md-4 col-lg-4">
					<div class="item border border-info service-card-box">
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
										<a href="#" data-toggle="modal" data-target="#staticBackdrop-<?= $row['id_den']; ?>"><?= htmlspecialchars($row['servico']); ?></a>
									<?php endif; ?>
								</h3>
								<p>
									<?= substr(strip_tags($row['descricao']), 0, 160) . '...'; ?>
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
										<a href="#" data-toggle="modal" data-target="#staticBackdrop-<?= $row['id_den']; ?>"><?= htmlspecialchars($btn_label); ?></a>
									<?php endif; ?>
								</p>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Modal -->
                <div class="modal fade" id="staticBackdrop-<?= $row['id_den']?>" 
                data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header text-uppercase">
                            <?= $row['servico']; ?>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <table class="table">
                          <thead>
                            <tr>
                              <th scope="col">Foto</th>
                              <th scope="col">Detalhes</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>
                                  <?php if($row['foto'] != ""){ ?>
                                    <img class="img img-responsive" style="height:auto; max-width:640px;" src="../assets/uploads/<?= $row['foto']; ?>" alt="<?=$row['servico'];?>" />
                                  <?php }else{ ?>
                                    <img class="img img-responsive" style="height:auto; max-width:640px;" src="../assets/uploads/dental/sem-foto.gif" alt="<?=$row['servico'];?>" />
                                  <?php } ?>
                              </td>
                              <td>
                                <?= $row['desc_total']; ?>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">
                            Fechar
                        </button>
                        <a href="https://wa.me/+5521985831464?&text=<?php echo "*Gostaria de Agendar uma Consulta para:* " . $row['servico'] ;?>" type="button" class="btn btn-success">
                            Agendar
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