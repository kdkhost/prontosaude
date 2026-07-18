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
				?>
				<div class="col-sm-6 col-md-4 col-lg-4">
					<div class="item border border-info">
						<?php if($row['foto'] == ""){?>
						<div class="photo" 
						    style="background-image:url(../assets/uploads/dental/sem-foto.gif);">
						</div>
						<?php }else{?>
						<div class="photo" 
						    style="background-image:url(../assets/uploads/<?= $row['foto']; ?>);">
						</div>
						<?php }?>
						<div class="text">
							<div class="inner">
								<h3>
								    <a href="#" data-toggle="modal" data-target="#staticBackdrop-<?= $row['id_den']; ?>">
								        <?php echo $row['servico']; ?>
								    </a>
								</h3>
								<p>
								    <?= substr(strip_tags($row['descricao']), 0, 200) . ' ...'; ?>
								</p>
								<p class="button">
									<a href="#" data-toggle="modal" data-target="#staticBackdrop-<?= $row['id_den']; ?>">
									    Saiba Mais
									</a>
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