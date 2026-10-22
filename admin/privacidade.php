<?php 
require_once('header.php'); 
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Política de Privacidade</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Política de Privacidade</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="privacidade-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Política de Privacidade</a>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div class="card card-outline card-primary shadow-sm mb-4">
				<div class="card-body table-responsive">
				    
				    <?php if(!empty($_SESSION['sucesso'])){?>
				    <div class="callout callout-success">
        			    <p><?= $_SESSION['sucesso']; ?></p>
        			</div>
				    <?php unset($_SESSION['sucesso']); } if(!empty($_SESSION['erro'])){ ?>
				        <div class="callout callout-danger">
				            <p><?= $_SESSION['erro']; ?></p>
        			    </div>
				    <?php unset($_SESSION['erro']);} ?>
				    
					<table id="example1" class="table table-hover table-striped align-middle w-100">
						<thead>
							<tr>
								<th width="50">ID</th>
								<th width="140">Banner</th>
								<th width="100">Título</th>
								<th>Detalhes</th>
								<th width="140">Ação</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT id, titulo, banner, detalhes FROM tbl_privacidade WHERE id=1 ");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
							    $banner = $row['banner'];
							    $id     = $row['id'];
								$i++;
								?>
								<tr>
									<td><?= $i; ?></td>
									<td style="width:150px;">
									    <?php
									        $caminho = "../assets/uploads/privacidade/" . $banner;
									    
									        if(file_exists($caminho) && isset($banner)){?>
									            <img src="<?= BASE_URL; ?>assets/uploads/privacidade/<?= $banner; ?>" alt="<?= $row['titulo']; ?>" style="width:140px;">
									        <?php }else{ ?>
									            <img src="<?= BASE_URL; ?>assets/images/no-image-found.png" alt="<?= $row['titulo']; ?>" style="width:140px;">
									        <?php }?>
									        <div class="clearfix"><br></div>
									        <button type="button" class="btn btn-warning w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal-<?=$id;?>">
                                              Trocar Banner
                                            </button>
									        <!-- Modal -->
                                            <div class="modal fade" id="exampleModal-<?=$id;?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                              <div class="modal-dialog">
                                                <div class="modal-content">
                                                  <div class="modal-header">
                                                    <b><?= $row['titulo']; ?></b>
                                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                      <span aria-hidden="true">&times;</span>
                                                    </button>
                                                  </div>
                                                  <div class="modal-body">
                                                    <div class="">
    									                <form action="privacidade-edit.php" method="Post" enctype="multipart/form-data">
    									                    <div class="container-fluid">
    									                        <div class="row">
    									                            <input type="hidden" name="id" value="<?=$id;?>">
        									                        <input type="file" name="banner">
        									                    </div>
        									                    <br>
        									                    <div class="row float-end">
        									                        <button type="button" class="btn btn-danger btn-sm text-uppercase" data-bs-dismiss="modal">
        									                            <span class="glyphicon glyphicon-remove"></span> 
        									                            Cancelar
        									                        </button>
                                                                    <button type="submit" name="UpdateBanner" class="btn btn-success btn-sm text-uppercase">
                                                                        <span class="glyphicon glyphicon-floppy-disk"></span> 
                                                                        Atualizar
                                                                    </button>
        									                    </div>
    									                    </div>
    									                </form>
    									            </div>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
									            
									</td>
									<td><?= $row['titulo']; ?></td>
									<td>
									    <div class="panel panel-default">
                                            <div class="panel-body">
                                                <?= $row['detalhes']; ?>
                                            </div>
                                        </div>
									</td>
									<td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="privacidade-edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
										<a href="#" class="btn btn-danger btn-sm" data-href="privacidade-delete.php?id=<?= $row['id']; ?>" 
										    data-target="#confirm-delete-<?= $row['id']; ?>"><i class="bi bi-trash3"></i> Excluir</a></div></td>
								</tr>
								<div class="modal fade" id="confirm-delete-<?= $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                                <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>Você tem certeza de que quer deletar essa Política de Privacidade?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <a href="privacidade-delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-ok">Deletar</a>
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



</div>
</div>
<?php require_once('footer.php'); ?>