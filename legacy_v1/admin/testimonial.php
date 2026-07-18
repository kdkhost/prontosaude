<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Visualizar Depoimentos</h1>
	</div>
	<div class="content-header-right">
		<a href="testimonial-add.php" class="btn btn-primary btn-sm">Add Depoimento</a>
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
								<th width="30">ID</th>
								<th>Foto</th>
								<th width="100">Nome</th>
								<th width="100">Designação</th>
								<th width="100">Empres</th>
								<th>Depoimento</th>
								<th width="80">Ação</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT
														
														id,
														name,
														designation,
														company,
														photo,
														comment

							                           	FROM tbl_testimonial
							                           	
							                           	");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?= $i; ?></td>
									<td style="width:60px;"><img src="<?= BASE_URL; ?>assets/uploads/<?= $row['photo']; ?>" alt="<?= $row['name']; ?>" style="height:60px; width:60px;"></td>
									<td><?= $row['name']; ?></td>
									<td><?= $row['designation']; ?></td>
									<td><?= $row['company']; ?></td>
									<td><?= $row['comment']; ?></td>
									<td>										
										<a href="testimonial-edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-xs">Editar</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="testimonial-delete.php?id=<?= $row['id']; ?>" data-toggle="modal" data-target="#confirm-delete-<?= $row['id']; ?>">Deletar</a>  
									</td>
								</tr>
								<div class="modal fade" id="confirm-delete-<?= $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>Tem certeza de que quer deletar esse Depoimento?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                <a href="testimonial-delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-ok">Deletar</a>
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