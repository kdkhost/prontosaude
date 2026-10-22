<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Notícias</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Notícias</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="news-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Notícia</a>
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
					<table id="example1" class="table table-hover table-striped align-middle w-100">
						<thead>
							<tr>
								<th>Id</th>
								<th>Miniatura</th>
								<th width="180">Título</th>
								<th width="280">Detalhes</th>
								<th>Categoria</th>
								<th class="text-center" width="110">Status</th>
								<th>Ação</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT

														t1.news_id,
														t1.news_title,
														t1.news_content,
														t1.photo,
														t1.category_id,
														t1.status,

														t2.category_id,
														t2.category_name

							                           	FROM tbl_news t1
							                           	JOIN tbl_category t2
							                           	ON t1.category_id = t2.category_id
							                           	");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td>
										<?php
										if($row['photo'] == '')
										{
											echo '<img src="../assets/uploads/no-photo1.jpg" alt="" style="width:100px;">';
										}
										else
										{
											echo '<img src="../assets/uploads/'.$row['photo'].'" alt="'.$row['news_title'].'" style="width:100px;">';
										}
										?>
									</td>
									<td><?php if(strlen($row['news_title']) > 40){echo substr($row['news_title'],0,40).' ...';} else {echo $row['news_title'];} ?></td>
									<td><?php if(strlen($row['news_content']) > 180){echo substr($row['news_content'],0,180).' ...';} else {echo $row['news_content'];} ?></td>
									<td>
										<?php echo $row['category_name']; ?>
									</td>
									<td class="text-center">
										<div class="d-inline-flex align-items-center gap-2">
											<div class="form-check form-switch mb-0">
												<input class="form-check-input pronto-status-switch" type="checkbox" role="switch" data-entity="news" data-id="<?php echo $row['news_id']; ?>" data-name="<?php echo htmlspecialchars($row['news_title']); ?>" data-active-text="Publicada" data-inactive-text="Rascunho" <?php echo (($row['status'] ?? 'Active') == 'Active') ? 'checked' : ''; ?> title="Publicar ou salvar como rascunho">
											</div>
											<span class="badge status-badge <?php echo (($row['status'] ?? 'Active') == 'Active') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle'; ?> rounded-pill small">
												<?php echo (($row['status'] ?? 'Active') == 'Active') ? 'Publicada' : 'Rascunho'; ?>
											</span>
										</div>
									</td>
									<td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="news-edit.php?id=<?php echo $row['news_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
										<a href="#" class="btn btn-danger btn-sm" data-href="news-delete.php?id=<?php echo $row['news_id']; ?>" data-target="#confirm-delete-<?$row['news_id']?>"><i class="bi bi-trash3"></i> Excluir</a></div></td>
								</tr>
								
								<div class="modal fade" id="confirm-delete-<?$row['news_id']?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Tem certeza de que deseja excluir este item?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <a href="news-delete.php?id=<?php echo $row['news_id']; ?>" class="btn btn-danger btn-ok">Deletar</a>
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

</div>
</div>


<?php require_once('footer.php'); ?>