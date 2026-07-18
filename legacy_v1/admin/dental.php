<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Visualizar Serviços Dentários</h1>
	</div>
	<div class="content-header-right">
		<a href="dental-add.php" class="btn btn-primary btn-sm">Adcionar Serviço</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th width="50">ID</th>
								<th width="140">Foto</th>
								<th width="100">Serviço</th>
								<th>Descrição</th>
								<th width="140">Ação</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT * FROM tbl_dental");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?= $i; ?></td>
									<td style="width:150px;">
								    <?php if($row['foto'] != ""){ ?>
								        <img src="<?= BASE_URL; ?>assets/uploads/<?= $row['foto']; ?>" alt="<?= $row['servico']; ?>" style="width:140px;">
								    <?php }else{ ?>
								        <img src="<?= BASE_URL; ?>assets/uploads/dental/sem-foto.gif" alt="<?= $row['servico']; ?>" style="width:140px;">
								    <?php } ?>
									</td>
									<td><?= $row['servico']; ?></td>
									<td><?= $row['descricao']; ?></td>
									<td>										
										<a href="dental-edit.php?id_den=<?= $row['id_den']; ?>" class="btn btn-primary btn-xs">Editar</a>
										<a href="#" class="btn btn-danger btn-xs" 
										data-href="dental-delete.php?id_den=<?= $row['id_den']; ?>" data-toggle="modal" data-target="#confirm-delete-<?= $row['id_den']; ?>">
										    Deletar
										</a>  
									</td>
								</tr>
								<div class="modal fade" id="confirm-delete-<?= $row['id_den']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                <h4 class="modal-title" id="myModalLabel">Confirmação de Remoção</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>Tem certeza de que deseja remover item ("<b class='text-uppercase text-danger'><?= $row['servico'] ?></b>")?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                <a href="dental-delete.php?id_den=<?= $row['id_den']; ?>" class="btn btn-danger btn-ok">Deletar</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<?php } ?>							
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>


</section>

<?php require_once('footer.php'); ?>