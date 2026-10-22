<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Médicos</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Médicos</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="extra.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
				<a href="doctor-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Cadastrar Médico</a>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div class="card card-outline card-primary shadow-sm mb-4">
			    <?php if(!empty($_SESSION['msg'])){ ?>
		            <div id="alert" class="callout callout-success">
		                <?php echo $_SESSION['msg']; unset($_SESSION['msg']);?>
		            </div>
		        <?php }elseif(!empty($_SESSION['erro'])){?>
		            <div id="alert" class="callout callout-danger">
		                <?php echo $_SESSION['erro']; unset($_SESSION['erro']);?>
		            </div>
		        <?php } ?>
				<div class="card-body table-responsive">
					<table id="example1" class="table table-hover table-striped align-middle w-100">
						<thead>
							<tr>
								<!--<th>SL</th>-->
								<th>Foto</th>
								<th>Nome</th>
								<th>Designação</th>
								<th class="text-center" width="60">Status</th>
								<th width="12%">Ação</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT
														t1.id,
														t1.name,
														t1.designation_id,
														t1.photo,
														t1.status,

														t2.designation_id,
														t2.designation_name

							                           	FROM tbl_doctor t1
							                           	JOIN tbl_designation t2
							                           	ON t1.designation_id = t2.designation_id
							                           	");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<!--<td><?php echo $i; ?></td>-->
									<td style="width:60px;">
									    <img class="rounded border object-fit-cover" src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>" 
									    alt="<?php echo $row['name']; ?>" style="width:60px; height:60px;">
									</td>
									<td><?php echo $row['name']; ?></td>
									<td><?php echo $row['designation_name']; ?></td>
									<td class="text-center">
										<div class="d-inline-flex align-items-center gap-2">
											<div class="form-check form-switch mb-0">
												<input class="form-check-input pronto-status-switch" type="checkbox" role="switch" data-entity="doctor" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>" <?php echo ($row['status'] == 'Active') ? 'checked' : ''; ?> title="Ativar ou desativar médico no site">
											</div>
											<span class="badge status-badge <?php echo ($row['status'] == 'Active') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'; ?> rounded-pill small">
												<?php echo ($row['status'] == 'Active') ? 'Ativo' : 'Inativo'; ?>
											</span>
										</div>
									</td>
									<td class="text-nowrap"><div class="d-flex flex-wrap gap-1"><a href="doctor-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Editar</a>
										<a href="#" class="btn btn-danger btn-sm" data-href="doctor-delete.php?id=<?php echo $row['id']; ?>" data-target="#confirm-delete"><i class="bi bi-trash3"></i> Excluir</a></div></td>
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