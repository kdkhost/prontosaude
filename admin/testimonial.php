<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Depoimentos</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Depoimentos</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="testimonial-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Depoimento</a>
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
								<th width="30">#</th>
								<th>Avatar</th>
								<th width="130">Paciente</th>
								<th width="140">Serviço Avaliado</th>
								<th width="130">Origem</th>
								<th>Depoimento</th>
								<th width="110" class="text-center">Status</th>
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
														comment,
														status

							                           	FROM tbl_testimonial
							                           	
							                           	");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?= $i; ?></td>
									<td style="width:60px;"><img class="rounded border object-fit-cover" src="<?= BASE_URL; ?>assets/uploads/<?= $row['photo']; ?>" alt="<?= $row['name']; ?>" style="height:60px; width:60px;"></td>
									<td><?= $row['name']; ?></td>
									<td><?= $row['designation']; ?></td>
									<td><?= $row['company']; ?></td>
									<td><?= $row['comment']; ?></td>
									<td class="text-center">
										<div class="d-inline-flex align-items-center gap-2">
											<div class="form-check form-switch mb-0">
												<input class="form-check-input pronto-status-switch" type="checkbox" role="switch" data-entity="testimonial" data-id="<?= $row['id']; ?>" data-name="<?= htmlspecialchars($row['name']); ?>" <?= ($row['status'] ?? 'Active') === 'Active' ? 'checked' : ''; ?> title="Ativar ou desativar este depoimento">
											</div>
											<span class="badge status-badge <?= ($row['status'] ?? 'Active') === 'Active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'; ?> rounded-pill small">
												<?= ($row['status'] ?? 'Active') === 'Active' ? 'Ativo' : 'Inativo'; ?>
											</span>
										</div>
									</td>
									<td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="testimonial-edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
										<a href="#" class="btn btn-danger btn-sm" data-href="testimonial-delete.php?id=<?= $row['id']; ?>" data-target="#confirm-delete-<?= $row['id']; ?>"><i class="bi bi-trash3"></i> Excluir</a></div></td>
								</tr>
								<div class="modal fade" id="confirm-delete-<?= $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                                <h4 class="modal-title" id="myModalLabel">Confirmação de Exclusão</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>Tem certeza de que quer deletar esse Depoimento?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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

</div>
</div>


<?php require_once('footer.php'); ?>