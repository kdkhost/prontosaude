<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Visualizar Vagas</h1>
	</div>
	<div class="content-header-right">
		<a href="vagas-add.php" class="btn btn-primary btn-sm">Adcionar Vagas</a>
	</div>
</section>

<section class="content">
    <?php if($_SESSION['msg']): ?>
    <div class="callout callout-success">
    	<p><?= $_SESSION['msg']; ?></p>
    	<?php unset($_SESSION['msg']);?>
    </div>
    <?php endif; ?>
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th width="50">ID</th>
								<th width="70">Foto</th>
								<th width="90">Título</th>
								<th>Detalhes da Vaga</th>
								<th width="150">Ação</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT * FROM tbl_trabalhe");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?= $i; ?></td>
									<td style="width:70px;">
								    <?php if($row['foto'] != ""){ ?>
								        <img src="<?= BASE_URL; ?>assets/uploads/<?= $row['foto']; ?>" alt="<?= $row['titulo']; ?>" style="width:70px;">
								    <?php }else{ ?>
								        <img src="<?= BASE_URL; ?>assets/uploads/vagas/sem-foto.gif" alt="<?= $row['titulo']; ?>" style="width:70px;">
								    <?php } ?>
									</td>
									<td><?= $row['titulo']; ?></td>
									<td><?= $row['requisitos']; ?></td>
									<td>										
										<a href="vagas-edit.php?id=<?= $row['id']; ?>" class="btn btn-block btn-primary btn-xs">
										    <i class="fa fa-edit fa-2x"></i>
										</a>
										<a href="#" class="btn btn-danger btn-block btn-xs" 
										data-href="vagas-delete.php?id=<?= $row['id']; ?>" data-toggle="modal" data-target="#confirm-delete-<?= $row['id']; ?>">
										    <i class="fa fa-trash fa-2x"></i>
										</a>
										<form method="POST" action="vagas-edit.php">
										        <input type="hidden" name="id" value="<?= $row['id']; ?>">
										        <input type="hidden" name="status" value="<?php if($row['status'] == 1){echo 0;}else{echo 1;}?>">
										        <input  style="margin-top:5px;" class="text-uppercase btn btn-block <?php if($row['status'] == 1){echo "btn-success";}else{echo "btn-warning";}?> btn-xs" 
										        name="BtnStatus" type="submit" value="<?php if($row['status'] == 1){echo "Ativo";}else{echo "Inativo";}?>">
										</form>
									</td>
								<div class="modal fade" id="confirm-delete-<?= $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                <h4 class="modal-title" id="myModalLabel">Confirmação de Remoção</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>Tem certeza de que deseja remover item ("<b class='text-uppercase text-danger'><?= $row['titulo'] ?></b>")?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                <a href="vagas-delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-ok">Deletar</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<?php }?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>


</section>

<?php require_once('footer.php'); ?>