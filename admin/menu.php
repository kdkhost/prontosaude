<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if(isset($_POST['form1'])) {
	if(!empty($_POST['arr_order'])) {
		foreach($_POST['arr_order'] as $key => $value) {
			$statement = $pdo->prepare("UPDATE tbl_menu SET menu_order=? WHERE menu_id=?");
			$statement->execute(array(intval($value), intval($key)));
		}	
		$success_message = 'A ordem dos menus foi atualizada com sucesso!';
	}
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Gerenciar Menus</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Navegação & Menus</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="menu-add.php" class="btn btn-primary btn-sm shadow-sm"><i class="bi bi-plus-lg me-1"></i> Adicionar Novo Menu</a>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
	<div class="container-fluid">

		<?php if(!empty($error_message)): ?>
		<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
			<div class="d-flex align-items-center">
				<i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
				<div><?php echo $error_message; ?></div>
			</div>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		<?php endif; ?>

		<?php if(!empty($success_message)): ?>
		<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
			<div class="d-flex align-items-center">
				<i class="bi bi-check-circle-fill fs-5 me-2"></i>
				<div><?php echo $success_message; ?></div>
			</div>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		<?php endif; ?>

		<div class="card card-outline card-primary shadow-sm mb-4">        
			<div class="card-header bg-body-tertiary d-flex flex-wrap align-items-center justify-content-between gap-2">
				<h5 class="card-title fw-semibold mb-0">
					<i class="bi bi-list-nested me-2 text-primary"></i> Itens de Navegação Cadastrados
				</h5>
				<span class="badge bg-secondary-subtle text-secondary small">Navbar Principal</span>
			</div>
			<div class="card-body table-responsive p-0">
				<form action="" method="post" id="formMenuOrder">
					<table id="example1" class="table table-hover table-striped align-middle w-100 mb-0">
						<thead>
							<tr>
								<th style="width: 60px;" class="text-center"># ID</th>
								<th style="width: 100px;">Tipo</th>
								<th>Nome do Menu</th>
								<th>Destino / Slug</th>
								<th style="width: 120px;" class="text-center">Ordem</th>
								<th>Menu Pai</th>
								<th>URL Custom</th>
								<th style="width: 170px;" class="text-center">Ações</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$statement = $pdo->prepare("SELECT * FROM tbl_menu ORDER BY menu_order ASC, menu_id ASC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$badgeClass = ($row['menu_type'] == 'Category') ? 'bg-info text-dark' : (($row['menu_type'] == 'Page') ? 'bg-primary' : 'bg-secondary');
								$typeLabel  = ($row['menu_type'] == 'Category') ? 'Categoria' : (($row['menu_type'] == 'Page') ? 'Página' : 'Personalizado');
								?>
								<tr>
									<td class="text-center fw-bold text-muted"><?php echo $row['menu_id']; ?></td>
									<td><span class="badge <?php echo $badgeClass; ?> rounded-pill"><?php echo $typeLabel; ?></span></td>
									<td class="fw-semibold">
										<?php if($row['menu_parent'] > 0): ?>
											<span class="text-muted me-1">↳</span>
										<?php endif; ?>
										<?php echo htmlspecialchars($row['menu_name']); ?>
									</td>
									<td class="text-muted small">
										<?php if(!empty($row['category_or_page_slug'])): ?>
											<code><?php echo htmlspecialchars($row['category_or_page_slug']); ?></code>
										<?php else: ?>
											<span class="text-muted fst-italic">-</span>
										<?php endif; ?>
									</td>
									<td class="text-center">
										<input type="number" name="arr_order[<?php echo $row['menu_id']; ?>]" class="form-control form-control-sm text-center mx-auto" value="<?php echo $row['menu_order']; ?>" min="1" style="max-width: 85px;">
									</td>
									<td>
										<?php 
										if($row['menu_parent'] == 0) {
											echo '<span class="text-muted small"><i class="bi bi-house-door me-1"></i>Raiz (Principal)</span>';
										} else {
											$st_p = $pdo->prepare("SELECT menu_name FROM tbl_menu WHERE menu_id=?");
											$st_p->execute(array($row['menu_parent']));
											$p_name = $st_p->fetchColumn();
											echo '<span class="badge bg-light text-dark border"><i class="bi bi-folder2-open me-1 text-primary"></i>' . htmlspecialchars($p_name ?: 'ID #'.$row['menu_parent']) . '</span>';
										}
										?>
									</td>
									<td class="text-muted small">
										<?php if(!empty($row['menu_url'])): ?>
											<a href="<?php echo htmlspecialchars($row['menu_url']); ?>" target="_blank" class="text-decoration-none text-truncate d-inline-block" style="max-width: 160px;" title="<?php echo htmlspecialchars($row['menu_url']); ?>">
												<i class="bi bi-box-arrow-up-right me-1"></i><?php echo htmlspecialchars($row['menu_url']); ?>
											</a>
										<?php else: ?>
											<span class="text-muted fst-italic">-</span>
										<?php endif; ?>
									</td>
									<td class="text-center text-nowrap">
										<div class="d-flex justify-content-center gap-1">
											<a href="menu-edit.php?id=<?php echo $row['menu_id']; ?>" class="btn btn-primary btn-sm shadow-xs" title="Editar Conteúdo do Menu">
												<i class="bi bi-pencil-square"></i> Editar
											</a>
											<button type="button" class="btn btn-danger btn-sm shadow-xs btn-delete-menu" data-id="<?php echo $row['menu_id']; ?>" data-name="<?php echo htmlspecialchars($row['menu_name']); ?>" title="Excluir Item de Menu">
												<i class="bi bi-trash3"></i> Excluir
											</button>
										</div>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<div class="card-footer bg-body-tertiary d-flex flex-wrap justify-content-between align-items-center gap-2 p-3">
						<span class="text-muted small"><i class="bi bi-info-circle me-1 text-primary"></i> Ajuste os números de ordem e clique para salvar a nova sequência de exibição.</span>
						<button type="submit" class="btn btn-success px-4 shadow-sm" name="form1">
							<i class="bi bi-arrow-repeat me-1"></i> Salvar Ordem dos Menus
						</button>
					</div>
				</form>
			</div>
		</div>

	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirmação de exclusão moderna com SweetAlert2
    document.querySelectorAll('.btn-delete-menu').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var menuId = this.getAttribute('data-id');
            var menuName = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Excluir Item de Menu?',
                html: 'Tem certeza que deseja excluir o menu <strong>' + menuName + '</strong>?<br><span class="text-danger small">Submenus vinculados poderão ficar sem item pai.</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = 'menu-delete.php?id=' + menuId;
                }
            });
        });
    });
});
</script>

<?php require_once('footer.php'); ?>