<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Gerenciar Serviços Médicos & Cards</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Gerenciar Serviços Médicos & Cards</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="service-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Novo Serviço</a>
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
					<table id="example1" class="table table-bordered table-striped dt-responsive nowrap">
						<thead>
							<tr>
								<th style="width: 50px;">ID</th>
								<th style="width: 90px;">Foto</th>
								<th>Serviço</th>
								<th>Valor no Card</th>
								<th>Benefícios</th>
								<th>Botão / Destino</th>
								<th style="width: 140px;">Ações</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							$statement = $pdo->prepare("SELECT * FROM tbl_service ORDER BY id DESC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								$benefits_list = !empty($row['benefits']) ? explode("\n", str_replace("\r", "", $row['benefits'])) : [];
								$benefits_preview = htmlspecialchars(implode(" • ", array_slice($benefits_list, 0, 3)));
								if(count($benefits_list) > 3) { $benefits_preview .= " ..."; }
							?>
							<tr>
								<td><?= $i; ?></td>
								<td>
									<img class="rounded border object-fit-cover" src="<?= BASE_URL; ?>assets/uploads/<?= htmlspecialchars($row['photo']); ?>" alt="<?= htmlspecialchars($row['name']); ?>" style="width: 80px; height: 55px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
								</td>
								<td>
									<div class="fw-bold fs-6"><?= htmlspecialchars($row['name']); ?></div>
									<small class="text-muted"><i class="bi bi-link-45deg"></i> <?= htmlspecialchars($row['slug']); ?></small>
								</td>
								<td>
									<?php if(!empty($row['price'])): ?>
										<span class="badge bg-success-subtle text-success border border-success-subtle fs-6">R$ <?= htmlspecialchars($row['price']); ?></span>
									<?php else: ?>
										<span class="badge bg-secondary-subtle text-secondary">Sob Consulta</span>
									<?php endif; ?>
								</td>
								<td>
									<?php if(!empty($benefits_list)): ?>
										<span class="badge bg-info-subtle text-info border border-info-subtle mb-1"><?= count($benefits_list); ?> item(ns)</span>
										<div class="small text-muted text-truncate" style="max-width: 250px;" title="<?= $benefits_preview; ?>">
											<?= $benefits_preview; ?>
										</div>
									<?php else: ?>
										<small class="text-muted">Apenas descrição</small>
									<?php endif; ?>
								</td>
								<td>
									<span class="badge bg-primary"><?= htmlspecialchars($row['button_text'] ?: 'Agendar Consulta'); ?></span>
									<?php if(!empty($row['button_url'])): ?>
										<a href="<?= htmlspecialchars($row['button_url']); ?>" target="_blank" class="d-block small text-truncate text-decoration-none mt-1" style="max-width: 180px;" title="<?= htmlspecialchars($row['button_url']); ?>">
											<i class="bi bi-box-arrow-up-right me-1"></i><?= htmlspecialchars($row['button_url']); ?>
										</a>
									<?php else: ?>
										<small class="text-muted d-block mt-1">Padrão: service/<?= htmlspecialchars($row['slug']); ?></small>
									<?php endif; ?>
								</td>
								<td>
									<div class="d-flex align-items-center gap-1">
										<a href="service-edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm" title="Editar Serviço">
											<i class="bi bi-pencil-square"></i>
										</a>
										<a href="service-delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm btn-delete-service" data-name="<?= htmlspecialchars($row['name']); ?>" title="Excluir Serviço">
											<i class="bi bi-trash3"></i>
										</a>
									</div>
								</td>
							</tr>
							<?php } ?>							
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

</div>
</div>

<script>
document.addEventListener('click', function(e) {
	const btn = e.target.closest('.btn-delete-service');
	if(!btn) return;
	e.preventDefault();
	const href = btn.getAttribute('href');
	const name = btn.getAttribute('data-name') || 'este serviço';

	if (window.Swal) {
		Swal.fire({
			title: 'Excluir Serviço?',
			html: `Tem certeza de que deseja excluir o serviço <b>${name}</b>?<br><small class="text-danger">A foto de destaque e o banner associados também serão removidos.</small>`,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#dc3545',
			cancelButtonColor: '#6c757d',
			confirmButtonText: 'Sim, excluir!',
			cancelButtonText: 'Cancelar'
		}).then((result) => {
			if (result.isConfirmed) {
				if (window.loadPageSPA) {
					window.loadPageSPA(href);
				} else {
					window.location.href = href;
				}
			}
		});
	} else {
		if (confirm(`Confirma a exclusão do serviço ${name}?`)) {
			window.location.href = href;
		}
	}
});
</script>

<?php require_once('footer.php'); ?>