<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Gerenciar Serviços Odontológicos & Cards</h1>
	</div>
	<div class="content-header-right">
		<a href="dental-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> Adicionar Procedimento</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-striped dt-responsive nowrap">
						<thead>
							<tr>
								<th style="width: 50px;">ID</th>
								<th style="width: 90px;">Foto</th>
								<th>Procedimento</th>
								<th>Valor no Card</th>
								<th>Benefícios</th>
								<th>Botão / Destino</th>
								<th style="width: 140px;">Ações</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							$statement = $pdo->prepare("SELECT * FROM tbl_dental ORDER BY id_den DESC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								$benefits_list = !empty($row['beneficios']) ? explode("\n", str_replace("\r", "", $row['beneficios'])) : [];
								$benefits_preview = htmlspecialchars(implode(" • ", array_slice($benefits_list, 0, 3)));
								if(count($benefits_list) > 3) { $benefits_preview .= " ..."; }

								$foto = $row['foto'];
								$foto_src = (!empty($foto) && file_exists('../assets/uploads/'.$foto)) ? BASE_URL.'assets/uploads/'.$foto : BASE_URL.'assets/images/sem-foto.gif';
							?>
							<tr>
								<td><?= $i; ?></td>
								<td>
									<img src="<?= $foto_src; ?>" alt="<?= htmlspecialchars($row['servico']); ?>" style="width: 80px; height: 55px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
								</td>
								<td>
									<div class="fw-bold fs-6"><?= htmlspecialchars($row['servico']); ?></div>
									<div class="small text-muted text-truncate" style="max-width: 250px;"><?= htmlspecialchars(substr($row['descricao'], 0, 80)); ?>...</div>
								</td>
								<td>
									<?php if(!empty($row['valor'])): ?>
										<span class="badge bg-success-subtle text-success border border-success-subtle fs-6">R$ <?= htmlspecialchars($row['valor']); ?></span>
									<?php else: ?>
										<span class="badge bg-secondary-subtle text-secondary">Sob Avaliação</span>
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
									<span class="badge bg-primary"><?= htmlspecialchars($row['button_text'] ?: 'Agendar Avaliação'); ?></span>
									<?php if(!empty($row['link'])): ?>
										<a href="<?= htmlspecialchars($row['link']); ?>" target="_blank" class="d-block small text-truncate text-decoration-none mt-1" style="max-width: 180px;" title="<?= htmlspecialchars($row['link']); ?>">
											<i class="bi bi-box-arrow-up-right me-1"></i><?= htmlspecialchars($row['link']); ?>
										</a>
									<?php else: ?>
										<small class="text-muted d-block mt-1">Padrão: Modal / Contato</small>
									<?php endif; ?>
								</td>
								<td>
									<div class="d-flex align-items-center gap-1">
										<a href="dental-edit.php?id_den=<?= $row['id_den']; ?>" class="btn btn-primary btn-sm" title="Editar Procedimento">
											<i class="bi bi-pencil-square"></i>
										</a>
										<a href="dental-delete.php?id_den=<?= $row['id_den']; ?>" class="btn btn-danger btn-sm btn-delete-dental" data-name="<?= htmlspecialchars($row['servico']); ?>" title="Excluir Procedimento">
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
</section>

<script>
document.addEventListener('click', function(e) {
	const btn = e.target.closest('.btn-delete-dental');
	if(!btn) return;
	e.preventDefault();
	const href = btn.getAttribute('href');
	const name = btn.getAttribute('data-name') || 'este serviço odontológico';

	if (window.Swal) {
		Swal.fire({
			title: 'Excluir Procedimento?',
			html: `Tem certeza de que deseja excluir o procedimento <b>${name}</b>?<br><small class="text-danger">A foto associada também será removida permanentemente.</small>`,
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
		if (confirm(`Confirma a exclusão do procedimento ${name}?`)) {
			window.location.href = href;
		}
	}
});
</script>

<?php require_once('footer.php'); ?>