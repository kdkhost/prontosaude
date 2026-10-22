<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Parceiros</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Parceiros</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="partner-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Parceiro</a>
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
								<th width="30">SL</th>
								<th>Foto</th>
								<th width="100">Nome</th>
								<th width="100">URL</th>
								<th width="80">Ação</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT
														
														id,
														name,
														url,
														photo

							                           	FROM tbl_partner
							                           	
							                           	");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td style="width:130px;"><img class="rounded border object-fit-cover" src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>" alt="<?php echo $row['name']; ?>" style="width:120px;"></td>
									<td><?php echo $row['name']; ?></td>
									<td><?php echo $row['url']; ?></td>
									<td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="partner-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
										<a href="#" class="btn btn-danger btn-sm" data-href="partner-delete.php?id=<?php echo $row['id']; ?>" data-target="#confirm-delete"><i class="bi bi-trash3"></i> Excluir</a></div></td>
								</tr>
								<?php
							}
							?>							
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>



</div>
</div>
<?php require_once('footer.php'); ?>