<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Vagas</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Vagas</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="vagas-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Vagas</a>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
<div class="container-fluid">
    <?php if(!empty($_SESSION['msg'])): ?>
    <div class="callout callout-success">
    	<p><?= $_SESSION['msg']; ?></p>
    	<?php unset($_SESSION['msg']);?>
    </div>
    <?php endif; ?>
	<div class="row">
		<div class="col-md-12">
			<div class="card card-outline card-primary shadow-sm mb-4">
				<div class="card-body table-responsive">
					<table id="example1" class="table table-hover table-striped align-middle w-100">
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
										<img class="rounded border object-fit-cover" style="width:60px;height:60px;"
											src="<?= BASE_URL; ?>assets/uploads/<?= ($row['foto'] != '') ? $row['foto'] : 'vagas/sem-foto.gif'; ?>"
											alt="<?= htmlspecialchars($row['titulo']); ?>">
									</td>
									<td class="fw-semibold"><?= $row['titulo']; ?></td>
									<td><?= $row['requisitos']; ?></td>
									<td class="text-nowrap">
										<div class="d-flex flex-wrap align-items-center gap-1">
											<a href="vagas-edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
												<i class="bi bi-pencil-square"></i> Editar
											</a>
											<a href="#" class="btn btn-danger btn-sm" data-href="vagas-delete.php?id=<?= $row['id']; ?>" data-target="#confirm-delete">
												<i class="bi bi-trash3"></i> Excluir
											</a>
											<form method="POST" action="vagas-edit.php" class="d-inline">
												<input type="hidden" name="id" value="<?= $row['id']; ?>">
												<input type="hidden" name="status" value="<?= ($row['status'] == 1) ? 0 : 1; ?>">
												<input class="btn btn-sm <?= ($row['status'] == 1) ? 'btn-success' : 'btn-warning'; ?>"
													name="BtnStatus" type="submit" value="<?= ($row['status'] == 1) ? 'Ativo' : 'Inativo'; ?>">
											</form>
										</div>
									</td>
								</tr>
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