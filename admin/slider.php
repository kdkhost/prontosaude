<?php require_once('header.php'); ?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Visualizar Sliders</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Visualizar Sliders</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="slider-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Adicionar Slider</a>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div class="card card-outline card-primary shadow-sm mb-4">
				<div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between">
					<span class="fw-semibold text-primary"><i class="bi bi-images me-2"></i> Sliders Ativos no Carrossel Principal</span>
					<span class="badge bg-secondary-subtle text-secondary small">Hero Section</span>
				</div>
				<div class="card-body table-responsive">
					<table id="example1" class="table table-hover table-striped align-middle w-100">
						<thead>
							<tr>
								<th style="width: 50px;">SL</th>
								<th style="width: 130px;">Foto</th>
								<th>Título</th>
								<th>Subtítulo</th>
								<th>Conteúdo</th>
								<th>Botão</th>
								<th>Link do Botão</th>
								<th style="width: 90px;" class="text-center">Posição</th>
								<th style="width: 130px;" class="text-center">Status</th>
								<th style="width: 140px;" class="text-center">Ações</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT
														id,
														photo,
														heading,
														subheading,
														content,
														button_text,
														button_url,
														position,
														status
							                           	FROM tbl_slider
							                           	ORDER BY id DESC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								$posLabel = ($row['position'] == 'Center') ? 'Centro' : (($row['position'] == 'Right') ? 'Direita' : 'Esquerda');
								$posClass = ($row['position'] == 'Center') ? 'bg-primary' : (($row['position'] == 'Right') ? 'bg-info' : 'bg-secondary');
								?>
								<tr>
									<td class="text-muted fw-bold"><?php echo $i; ?></td>
									<td>
										<?php if(!empty($row['photo']) && file_exists('../assets/uploads/'.$row['photo'])): ?>
											<img class="rounded border object-fit-cover shadow-sm" src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $row['photo']; ?>" alt="<?php echo htmlspecialchars($row['heading']); ?>" style="width:110px; height:65px;">
										<?php else: ?>
											<div class="bg-light border rounded text-center text-muted py-2 small" style="width:110px;">Sem foto</div>
										<?php endif; ?>
									</td>
									<td class="fw-semibold text-wrap" style="max-width: 180px;"><?php echo htmlspecialchars($row['heading']); ?></td>
									<td class="small text-muted text-wrap" style="max-width: 220px;"><?php echo htmlspecialchars($row['subheading']); ?></td>
									<td class="small text-wrap" style="max-width: 240px;">
										<?php if(!empty($row['content'])): ?>
											<div class="text-truncate" style="max-width: 230px;" title="<?php echo htmlspecialchars($row['content']); ?>">
												<?php echo htmlspecialchars(mb_strimwidth($row['content'], 0, 90, '...')); ?>
											</div>
										<?php else: ?>
											<span class="text-muted fst-italic">Sem conteúdo extra</span>
										<?php endif; ?>
									</td>
									<td class="small text-nowrap"><?php echo htmlspecialchars($row['button_text']); ?></td>
									<td class="small text-truncate" style="max-width: 140px;">
										<?php if(!empty($row['button_url']) && $row['button_url'] !== '#'): ?>
											<a href="<?php echo htmlspecialchars($row['button_url']); ?>" target="_blank" class="text-decoration-none" title="<?php echo htmlspecialchars($row['button_url']); ?>">
												<i class="bi bi-box-arrow-up-right me-1"></i>Link
											</a>
										<?php else: ?>
											<span class="text-muted">#</span>
										<?php endif; ?>
									</td>
									<td class="text-center">
										<span class="badge <?php echo $posClass; ?>"><?php echo $posLabel; ?></span>
									</td>
									<td class="text-center">
										<div class="d-inline-flex align-items-center gap-2">
											<div class="form-check form-switch mb-0">
												<input class="form-check-input pronto-status-switch" type="checkbox" role="switch" data-entity="slider" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['heading'] ?: 'Slide #' . $i); ?>" <?php echo ($row['status'] == 'Active') ? 'checked' : ''; ?> title="Ativar ou desativar este slide na home">
											</div>
											<span class="badge status-badge <?php echo ($row['status'] == 'Active') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'; ?> rounded-pill small">
												<?php echo ($row['status'] == 'Active') ? 'Ativo' : 'Inativo'; ?>
											</span>
										</div>
									</td>
									<td class="text-center text-nowrap">
										<div class="d-flex justify-content-center gap-1">
											<a href="slider-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" title="Editar Slide">
												<i class="bi bi-pencil-square"></i> Editar
											</a>
											<button type="button" class="btn btn-danger btn-sm btn-delete-slider" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['heading'] ?: 'Slide #' . $i); ?>" title="Excluir Slide">
												<i class="bi bi-trash3"></i> Excluir
											</button>
										</div>
									</td>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirmação de exclusão moderna com SweetAlert2
    document.querySelectorAll('.btn-delete-slider').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var sliderId = this.getAttribute('data-id');
            var sliderName = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Excluir este Slider?',
                html: 'Tem certeza que deseja excluir o slide <strong>' + sliderName + '</strong>?<br><span class="text-danger small">A imagem correspondente também será apagada do servidor.</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = 'slider-delete.php?id=' + sliderId;
                }
            });
        });
    });
});
</script>

<?php require_once('footer.php'); ?>