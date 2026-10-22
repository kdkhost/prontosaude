<?php 
require_once('header.php'); 

// Endpoint AJAX para alternar status do Cartão Fidelidade / Plano
if(isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
	header('Content-Type: application/json');
	$id = (int)($_POST['id'] ?? 0);
	$status = (int)($_POST['status'] ?? 0);
	$stmt = $pdo->prepare("UPDATE tbl_pricing_plan SET status = ? WHERE pricing_plan_id = ?");
	$stmt->execute([$status, $id]);
	echo json_encode([
		'success' => true,
		'status' => $status,
		'message' => $status == 1 ? 'Cartão Fidelidade / Plano ATIVADO com sucesso!' : 'Cartão Fidelidade / Plano DESATIVADO com sucesso!'
	]);
	exit;
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Gerenciar Planos de Saúde e Cartão Fidelidade</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Gerenciar Planos de Saúde e Cartão Fidelidade</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="pricing-plan-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Novo Cartão / Plano</a>
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
								<th style="width: 40px;">ID</th>
								<th style="width: 80px;">Imagem</th>
								<th>Nome do Plano / Cartão</th>
								<th>Preço / Período</th>
								<th>Benefícios</th>
								<th>Botão / Destino</th>
								<th style="width: 100px;">Status</th>
								<th style="width: 120px;">Ações</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							$statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan ORDER BY pricing_plan_id ASC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);

							foreach ($result as $row) {
								$i++;
								$photo = $row['pricing_plan_photo'] ?? '';
								$photo_src = '';
								if(!empty($photo) && file_exists('../assets/uploads/' . $photo)) {
									$photo_src = BASE_URL . 'assets/uploads/' . $photo;
								} elseif(!empty($photo) && file_exists('../assets/images/planos/' . $photo)) {
									$photo_src = BASE_URL . 'assets/images/planos/' . $photo;
								} else {
									$photo_src = BASE_URL . 'assets/images/sem-foto.gif';
								}

								// Conta benefícios
								$stmtItems = $pdo->prepare("SELECT pricing_item_name FROM tbl_pricing_item WHERE pricing_plan_id = ?");
								$stmtItems->execute([$row['pricing_plan_id']]);
								$items = $stmtItems->fetchAll(PDO::FETCH_COLUMN);
								$total_items = count($items);
								$items_preview = htmlspecialchars(implode(" • ", array_slice($items, 0, 4)));
								if(count($items) > 4) { $items_preview .= " ..."; }
							?>
							<tr>
								<td><?= $i; ?></td>
								<td>
									<img class="rounded border object-fit-cover" src="<?= $photo_src; ?>" alt="<?= htmlspecialchars($row['pricing_plan_name']); ?>" style="width: 75px; height: 50px; object-fit: contain; background: rgba(0,0,0,0.03); border-radius: 6px; padding: 2px; border: 1px solid #e2e8f0;">
								</td>
								<td>
									<div class="fw-bold fs-6"><?= htmlspecialchars($row['pricing_plan_name']); ?></div>
									<?php if(!empty($row['pricing_plan_badge'])): ?>
										<span class="badge bg-warning text-dark me-1"><i class="bi bi-star-fill me-1"></i><?= htmlspecialchars($row['pricing_plan_badge']); ?></span>
									<?php endif; ?>
									<?php if(!empty($row['pricing_plan_popular'])): ?>
										<span class="badge bg-success-subtle text-success border border-success-subtle">Destaque</span>
									<?php endif; ?>
								</td>
								<td>
									<span class="fw-bold text-primary fs-6">R$ <?= htmlspecialchars($row['pricing_plan_price']); ?></span>
									<small class="text-muted"><?= htmlspecialchars($row['pricing_plan_period'] ?? '/mês'); ?></small>
								</td>
								<td>
									<span class="badge bg-info-subtle text-info border border-info-subtle mb-1"><?= $total_items; ?> benefício(s)</span>
									<div class="small text-muted text-truncate" style="max-width: 250px;" title="<?= $items_preview; ?>">
										<?= $items_preview ?: 'Nenhum benefício configurado'; ?>
									</div>
								</td>
								<td>
									<span class="badge bg-secondary"><?= htmlspecialchars($row['pricing_plan_button_text'] ?: 'Contratar'); ?></span>
									<?php if(!empty($row['pricing_plan_button_url'])): ?>
										<a href="<?= htmlspecialchars($row['pricing_plan_button_url']); ?>" target="_blank" class="d-block small text-truncate text-decoration-none mt-1" style="max-width: 180px;" title="<?= htmlspecialchars($row['pricing_plan_button_url']); ?>">
											<i class="bi bi-link-45deg"></i> <?= htmlspecialchars($row['pricing_plan_button_url']); ?>
										</a>
									<?php else: ?>
										<small class="text-muted d-block mt-1">Padrão: fidelidade.php</small>
									<?php endif; ?>
								</td>
								<td>
									<div class="d-flex align-items-center gap-2">
										<div class="form-check form-switch mb-0">
											<input class="form-check-input switch-plan-status" type="checkbox" role="switch" data-id="<?= $row['pricing_plan_id']; ?>" <?= ($row['status'] ?? 1) == 1 ? 'checked' : ''; ?> title="Ativar ou Desativar Plano">
										</div>
										<span class="badge status-badge-<?= $row['pricing_plan_id']; ?> <?= ($row['status'] ?? 1) == 1 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle'; ?>">
											<?= ($row['status'] ?? 1) == 1 ? 'Ativo' : 'Inativo'; ?>
										</span>
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center gap-1">
										<a href="pricing-plan-edit.php?id=<?= $row['pricing_plan_id']; ?>" class="btn btn-primary btn-sm" title="Editar Plano e Benefícios">
											<i class="bi bi-pencil-square"></i>
										</a>
										<a href="pricing-plan-delete.php?id=<?= $row['pricing_plan_id']; ?>" class="btn btn-danger btn-sm btn-delete-plan" data-name="<?= htmlspecialchars($row['pricing_plan_name']); ?>" title="Excluir Plano">
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
// Toggle de Status Ativo / Inativo via AJAX com feedback instantâneo
document.addEventListener('change', function(e) {
	const toggle = e.target.closest('.switch-plan-status');
	if (!toggle) return;
	const id = toggle.getAttribute('data-id');
	const isChecked = toggle.checked ? 1 : 0;
	const badge = document.querySelector('.status-badge-' + id);

	fetch('pricing-plan.php', {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: new URLSearchParams({ action: 'toggle_status', id: id, status: isChecked })
	})
	.then(res => res.json())
	.then(data => {
		if (data.success) {
			if (window.notificar) {
				window.notificar('success', data.message);
			} else if (window.toastr) {
				toastr.success(data.message);
			}
			if (badge) {
				if (isChecked) {
					badge.className = 'badge status-badge-' + id + ' bg-success-subtle text-success border border-success-subtle';
					badge.textContent = 'Ativo';
				} else {
					badge.className = 'badge status-badge-' + id + ' bg-secondary-subtle text-secondary border border-secondary-subtle';
					badge.textContent = 'Inativo';
				}
			}
		}
	})
	.catch(() => {
		toggle.checked = !isChecked;
		if (window.notificar) {
			window.notificar('error', 'Erro ao alterar o status do plano.');
		}
	});
});

document.addEventListener('click', function(e) {
	const btn = e.target.closest('.btn-delete-plan');
	if(!btn) return;
	e.preventDefault();
	const href = btn.getAttribute('href');
	const name = btn.getAttribute('data-name') || 'este plano';

	if (window.Swal) {
		Swal.fire({
			title: 'Excluir Plano?',
			html: `Tem certeza de que deseja excluir o plano <b>${name}</b>?<br><small class="text-danger">Todos os benefícios e configurações deste plano também serão excluídos.</small>`,
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
		window.location.href = href;
	}
});
</script>

<?php require_once('footer.php'); ?>