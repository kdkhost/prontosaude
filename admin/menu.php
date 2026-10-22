<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if(isset($_POST['form1'])) {
	if(!empty($_POST['arr_order'])) {
		foreach($_POST['arr_order'] as $key=>$value) {
			$statement = $pdo->prepare("UPDATE tbl_menu SET menu_order=? WHERE menu_id=?");
			$statement->execute(array($value,$key));
		}	
		$success_message = 'A ordem dos menus foi atualizada com sucesso.';
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
					<li class="breadcrumb-item active" aria-current="page">Menus</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="menu-add.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Adicionar Novo Menu</a>
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
				<div><?php echo $error_message; unset($error_message); ?></div>
			</div>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		<?php endif; ?>

		<?php if(!empty($success_message)): ?>
		<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
			<div class="d-flex align-items-center">
				<i class="bi bi-check-circle-fill fs-5 me-2"></i>
				<div><?php echo $success_message; unset($success_message); ?></div>
			</div>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		<?php endif; ?>

		<div class="card card-outline card-primary shadow-sm mb-4">        
			<div class="card-header bg-body-tertiary">
				<h5 class="card-title fw-semibold mb-0">
					<i class="bi bi-list-nested me-2 text-primary"></i> Itens de Navegação Cadastrados
				</h5>
			</div>
			<div class="card-body table-responsive p-0">
				<form action="" method="post">
					<table id="example1" class="table table-hover table-striped align-middle w-100 mb-0">
						<thead>
							<tr>
								<th style="width: 70px;"># ID</th>
								<th>Tipo</th>
								<th>Nome do Menu</th>
								<th>Slug / Destino</th>
								<th style="width: 120px;">Ordem</th>
								<th>Menu Pai</th>
								<th>URL Custom</th>
								<th style="width: 110px;" class="text-center">Ações</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$statement = $pdo->prepare("SELECT * FROM tbl_menu ORDER BY menu_order ASC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$badgeClass = ($row['menu_type'] == 'Category') ? 'bg-info' : (($row['menu_type'] == 'Page') ? 'bg-primary' : 'bg-secondary');
								?>
								<tr>
									<td class="fw-bold text-muted"><?php echo $row['menu_id']; ?></td>
									<td><span class="badge <?php echo $badgeClass; ?>"><?php echo $row['menu_type']; ?></span></td>
									<td class="fw-semibold"><?php echo htmlspecialchars($row['menu_name']); ?></td>
									<td class="text-muted small"><?php echo htmlspecialchars($row['category_or_page_slug']); ?></td>
									<td>
										<input type="number" name="arr_order[<?php echo $row['menu_id']; ?>]" class="form-control form-control-sm text-center" value="<?php echo $row['menu_order']; ?>" min="1" style="max-width: 90px;">
									</td>
									<td>
										<?php 
										if($row['menu_parent'] == 0) {
											echo '<span class="text-muted fst-italic">Nenhum (Raiz)</span>';
										} else {
											$st_p = $pdo->prepare("SELECT menu_name FROM tbl_menu WHERE menu_id=?");
											$st_p->execute(array($row['menu_parent']));
											$p_name = $st_p->fetchColumn();
											echo '<span class="badge bg-light text-dark border">' . htmlspecialchars($p_name ?: 'ID '.$row['menu_parent']) . '</span>';
										}
										?>
									</td>
									<td class="text-muted small"><?php echo htmlspecialchars($row['menu_url']); ?></td>
									<td class="text-center text-nowrap">
										<a href="menu-delete.php?id=<?php echo $row['menu_id']; ?>" class="btn btn-danger btn-sm" data-name="<?php echo htmlspecialchars($row['menu_name']); ?>">
											<i class="bi bi-trash3"></i> Excluir
										</a>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<div class="card-footer bg-body-tertiary d-flex justify-content-between align-items-center">
						<span class="text-muted small"><i class="bi bi-info-circle me-1"></i> Altere os valores de ordem e clique ao lado para salvar.</span>
						<button type="submit" class="btn btn-success px-4" name="form1">
							<i class="bi bi-arrow-repeat me-1"></i> Salvar Ordem dos Menus
						</button>
					</div>
				</form>
			</div>
		</div>

	</div>
</div>

<?php require_once('footer.php'); ?>