<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if(!isset($_REQUEST['id'])) {
	header('location: menu.php');
	exit;
} else {
	$statement = $pdo->prepare("SELECT * FROM tbl_menu WHERE menu_id=?");
	$statement->execute(array($_REQUEST['id']));
	$menu_item = $statement->fetch(PDO::FETCH_ASSOC);
	if(!$menu_item) {
		header('location: menu.php');
		exit;
	}
}

if(isset($_POST['form1'])) {
	$valid = 1;

	$menu_name             = trim($_POST['menu_name'] ?? '');
	$menu_type             = trim($_POST['menu_type'] ?? 'Other');
	$category_or_page_slug = trim($_POST['category_or_page_slug'] ?? '');
	$menu_url              = trim($_POST['menu_url'] ?? '');
	$menu_order            = intval($_POST['menu_order'] ?? 1);
	$menu_parent           = intval($_POST['menu_parent'] ?? 0);

	if(empty($menu_name)) {
        $valid = 0;
        $error_message .= "O nome do menu não pode ficar vazio.<br>";
    }

    if($menu_order < 1) {
    	$menu_order = 1;
    }

    // Impede que um menu seja pai de si mesmo
    if($menu_parent == $_REQUEST['id']) {
        $valid = 0;
        $error_message .= "Um item de menu não pode ser pai de si mesmo.<br>";
    }

    if($valid == 1) {
		$statement = $pdo->prepare("UPDATE tbl_menu SET menu_type=?, menu_name=?, category_or_page_slug=?, menu_order=?, menu_parent=?, menu_url=? WHERE menu_id=?");
		$statement->execute(array($menu_type, $menu_name, $category_or_page_slug, $menu_order, $menu_parent, $menu_url, $_REQUEST['id']));

    	$success_message = 'Item de menu atualizado com sucesso!';

    	// Recarrega os dados atualizados
    	$statement = $pdo->prepare("SELECT * FROM tbl_menu WHERE menu_id=?");
		$statement->execute(array($_REQUEST['id']));
		$menu_item = $statement->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Editar Menu</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item"><a href="menu.php">Gerenciar Menus</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Menu #<?php echo $menu_item['menu_id']; ?></li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="menu.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar à Lista de Menus</a>
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
			<div class="card-header bg-body-tertiary">
				<h5 class="card-title fw-semibold mb-0">
					<i class="bi bi-pencil-square text-primary me-2"></i> Configurações do Item de Navegação
				</h5>
			</div>

			<div class="card-body p-4">
				<form action="" method="post">
					
					<div class="row mb-3">
						<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Tipo de Menu <span class="text-danger">*</span></label>
						<div class="col-12 col-sm-9 col-lg-6">
							<select class="form-select" name="menu_type" id="menu_type_select" required>
								<option value="Page" <?php echo ($menu_item['menu_type'] == 'Page') ? 'selected' : ''; ?>>Página Institucional (Page)</option>
								<option value="Category" <?php echo ($menu_item['menu_type'] == 'Category') ? 'selected' : ''; ?>>Categoria de Notícias/Blog (Category)</option>
								<option value="Other" <?php echo ($menu_item['menu_type'] == 'Other') ? 'selected' : ''; ?>>Link Personalizado / Outro (Other)</option>
							</select>
							<div class="form-text text-muted">Define o comportamento e o destino do link na barra de navegação.</div>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome do Menu <span class="text-danger">*</span></label>
						<div class="col-12 col-sm-9 col-lg-6">
							<input type="text" class="form-control" name="menu_name" value="<?php echo htmlspecialchars($menu_item['menu_name']); ?>" required placeholder="Ex: Início, Especialidades, Sobre Nós">
							<div class="form-text text-muted">Texto exibido diretamente para os visitantes no menu do site.</div>
						</div>
					</div>

					<!-- Destino de Página -->
					<div class="row mb-3" id="row_page_select">
						<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Página de Destino</label>
						<div class="col-12 col-sm-9 col-lg-6">
							<select class="form-select" id="page_slug_picker">
								<option value="">Selecione para preencher o slug abaixo...</option>
								<?php
								try {
									$st_pages = $pdo->query("SELECT page_name, page_slug FROM tbl_page ORDER BY page_name ASC");
									while($p = $st_pages->fetch(PDO::FETCH_ASSOC)) {
										$sel = ($p['page_slug'] === $menu_item['category_or_page_slug']) ? 'selected' : '';
										echo '<option value="'.htmlspecialchars($p['page_slug']).'" '.$sel.'>'.htmlspecialchars($p['page_name']).' ('.htmlspecialchars($p['page_slug']).')</option>';
									}
								} catch (Exception $e) {}
								?>
							</select>
						</div>
					</div>

					<!-- Destino de Categoria -->
					<div class="row mb-3" id="row_category_select">
						<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Categoria de Destino</label>
						<div class="col-12 col-sm-9 col-lg-6">
							<select class="form-select" id="category_slug_picker">
								<option value="">Selecione para preencher o slug abaixo...</option>
								<?php
								try {
									$st_cats = $pdo->query("SELECT category_name, category_slug FROM tbl_category ORDER BY category_name ASC");
									while($c = $st_cats->fetch(PDO::FETCH_ASSOC)) {
										$sel = ($c['category_slug'] === $menu_item['category_or_page_slug']) ? 'selected' : '';
										echo '<option value="'.htmlspecialchars($c['category_slug']).'" '.$sel.'>'.htmlspecialchars($c['category_name']).' ('.htmlspecialchars($c['category_slug']).')</option>';
									}
								} catch (Exception $e) {}
								?>
							</select>
						</div>
					</div>

					<div class="row mb-3" id="row_slug">
						<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Slug / Destino Interno</label>
						<div class="col-12 col-sm-9 col-lg-6">
							<input type="text" class="form-control" name="category_or_page_slug" id="category_or_page_slug" value="<?php echo htmlspecialchars($menu_item['category_or_page_slug']); ?>" placeholder="Ex: sobre-nos, servicos, contato">
							<div class="form-text text-muted">Identificador da página ou categoria acessada pelo sistema de rotas.</div>
						</div>
					</div>

					<div class="row mb-3" id="row_custom_url">
						<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">URL Externa / Personalizada</label>
						<div class="col-12 col-sm-9 col-lg-6">
							<input type="text" class="form-control" name="menu_url" id="menu_url" value="<?php echo htmlspecialchars($menu_item['menu_url']); ?>" placeholder="Ex: https://wa.me/... ou #inicio">
							<div class="form-text text-muted">Utilize para links externos, WhatsApp ou links âncora (#).</div>
						</div>
					</div>

					<div class="row mb-3">
						<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Menu Pai</label>
						<div class="col-12 col-sm-9 col-lg-6">
							<select class="form-select" name="menu_parent">
								<option value="0" <?php echo ($menu_item['menu_parent'] == 0) ? 'selected' : ''; ?>>Nenhum (Item Principal / Raiz)</option>
								<?php
								$st_all = $pdo->prepare("SELECT menu_id, menu_name FROM tbl_menu WHERE menu_id != ? ORDER BY menu_order ASC");
								$st_all->execute(array($_REQUEST['id']));
								while($m = $st_all->fetch(PDO::FETCH_ASSOC)) {
									$sel = ($m['menu_id'] == $menu_item['menu_parent']) ? 'selected' : '';
									echo '<option value="'.$m['menu_id'].'" '.$sel.'>'.htmlspecialchars($m['menu_name']).' (ID #'.$m['menu_id'].')</option>';
								}
								?>
							</select>
							<div class="form-text text-muted">Selecione outro menu para tornar este item um submenu suspenso (dropdown).</div>
						</div>
					</div>

					<div class="row mb-4">
						<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ordem de Exibição <span class="text-danger">*</span></label>
						<div class="col-12 col-sm-9 col-lg-6">
							<input type="number" class="form-control" name="menu_order" value="<?php echo $menu_item['menu_order']; ?>" min="1" required style="max-width: 130px;">
							<div class="form-text text-muted">Itens com menor numeração aparecem primeiro da esquerda para a direita.</div>
						</div>
					</div>

					<div class="row mb-3">
						<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
						<div class="col-12 col-sm-9 col-lg-6">
							<button type="submit" class="btn btn-primary px-4 shadow-sm" name="form1">
								<i class="bi bi-check-lg me-1"></i> Salvar Alterações
							</button>
							<a href="menu.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
						</div>
					</div>

				</form>
			</div>
		</div>

	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('menu_type_select');
    var rowPage = document.getElementById('row_page_select');
    var rowCategory = document.getElementById('row_category_select');
    var rowSlug = document.getElementById('row_slug');
    var rowUrl = document.getElementById('row_custom_url');
    var slugInput = document.getElementById('category_or_page_slug');
    var pagePicker = document.getElementById('page_slug_picker');
    var catPicker = document.getElementById('category_slug_picker');

    function updateFormVisibility() {
        var val = typeSelect.value;
        if (val === 'Page') {
            rowPage.style.display = '';
            rowCategory.style.display = 'none';
            rowSlug.style.display = '';
            rowUrl.style.display = 'none';
        } else if (val === 'Category') {
            rowPage.style.display = 'none';
            rowCategory.style.display = '';
            rowSlug.style.display = '';
            rowUrl.style.display = 'none';
        } else {
            rowPage.style.display = 'none';
            rowCategory.style.display = 'none';
            rowSlug.style.display = 'none';
            rowUrl.style.display = '';
        }
    }

    if (pagePicker) {
        pagePicker.addEventListener('change', function() {
            if (this.value) slugInput.value = this.value;
        });
    }

    if (catPicker) {
        catPicker.addEventListener('change', function() {
            if (this.value) slugInput.value = this.value;
        });
    }

    typeSelect.addEventListener('change', updateFormVisibility);
    updateFormVisibility();
});
</script>

<?php require_once('footer.php'); ?>
