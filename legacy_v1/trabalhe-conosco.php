<?php include_once 'header.php'; ?>
<style>
    .fundo{
        background: rgb(10,107,116);
        background: -moz-linear-gradient(351deg, rgba(10,107,116,1) 35%, rgba(133,181,186,1) 100%);
        background: -webkit-linear-gradient(351deg, rgba(10,107,116,1) 35%, rgba(133,181,186,1) 100%);
        background: linear-gradient(90deg, #00C9FF 0%, #92FE9D 100%);
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#0a6b74",endColorstr="#85b5ba",GradientType=1);
    }
</style>

<!-- Service Start -->
<section class="service-v1">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="heading">
					<h2>
					    Trabalhe Conosco
					</h2>
				</div>
			</div>
		</div>
		<?php
			$statement = $pdo->prepare("SELECT * FROM tbl_trabalhe");
			$statement->execute();
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		?>
		<div class="row">
			<?php foreach ($result as $row) { if($row['status'] != 0){?>
				<!--<div class="col-sm-6 col-md-4 col-lg-4">
					<div class="item border border-info">
						<?php if($row['foto'] == ""){?>
						<div class="photo" 
						    style="background-image:url(../assets/uploads/vagas/sem-foto.gif);">
						</div>
						<?php }else{?>
						<div class="photo" 
						    style="background-image:url(../assets/uploads/<?= $row['foto']; ?>);">
						</div>
						<?php }?>
						<div class="text">
							<div class="inner">
								<h3>
								    <a href="#" data-toggle="modal" data-target="#staticBackdrop-<?= $row['id']; ?>">
								        <?php echo $row['titulo']; ?>
								    </a>
								</h3>
								<p class="button">
									<a href="#" data-toggle="modal" data-target="#staticBackdrop-<?= $row['id']; ?>">
									    Saiba Mais
									</a>
								</p>
							</div>
						</div>
					</div>
				</div>
                <div class="modal fade fundo" id="staticBackdrop-<?= $row['id']?>" 
                data-backdrop="static" data-keyboard="false" tabindex="-1">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header text-uppercase">
                            <b>Vaga Aberta:</b> <?= $row['titulo']; ?>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="content" style="padding:40px;">
                                    <div class="col-sm-6">
                                        <h2>Requisitos</h2>
                                        <p>
                                            <?= $row['requisitos']; ?>
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <h2>Carga Horária</h2>
                                        <?= $row['carga']; ?>
                                    </div>
                                    
                                    <div class="col-sm-6"><hr>
                                        <h2>Salário</h2>
                                        <?php if($row['salario'] == ''){echo "à Combinar.";}else{echo $row['salario']; }?>
                                        <hr>
                                        <h2>Benefícios</h2>
                                        <?= $row['beneficios']; ?>
                                    </div>
                                    <div class="col-sm-6"><hr>
                                    <h2>Local de Trabalho</h2>
                                        <?= $row['local']; ?>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="modal-footer" style="border-style: solid; border-top: none;">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">
                                Fechar
                            </button>
                            <a href="https://wa.me/+5521985831464?&text=
                            <?php echo "Gostaria de participar do processo seletivo para vaga disponível de: *" . $row['titulo'] ;?>*" 
                            type="button" class="btn btn-success">
                                Agendar
                            </a>
                        </div>
                    </div>
                  </div>
                </div>-->
			<?php }}?>
			<div class="col-md-12">
			    <img style="max-height:380px; height:100%; border-radius:3px;" src="../assets/images/trabalhe-conosco/trabalhe_conosco.png" class="img img-responsive img-thumbnail" alt="Trabalhe Conosco" />
			    <p>
			        Envie seu currículo para nós através do endereço eletrônico:<br>
			        <a href="mailto:supervisao@prontosauderj.com.br" style="text-decoraton:none;">
			            <b>
			                supervisao@prontosauderj.com.br
			            </b>
			        </a>
			    </p>
			</div>
		</div>
	</div>
</section>
<!-- Service End -->

<?php include_once 'footer.php'; ?>