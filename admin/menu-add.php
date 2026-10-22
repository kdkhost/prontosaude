<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';
$active_tab = 'tab_1';

if(isset($_POST['form1'])) {
	$active_tab = 'tab_1';
	$valid = 1;

	if(empty($_POST['menu_name_and_category_or_page_slug'])) {
        $valid = 0;
        $error_message .= "Você deve selecionar uma categoria como nome do menu.<br>";
    }
    if(empty($_POST['menu_order'])) {
        $valid = 0;
        $error_message .= "A ordem do menu não pode ficar vazia.<br>";
    }
    if($_POST['menu_parent'] == '') {
        $valid = 0;
        $error_message .= "Você deve selecionar um menu pai.<br>";
    }

	if($valid == 1) {
    	$mns = explode(':1:1:',$_POST['menu_name_and_category_or_page_slug']);

		// saving into the database
		$statement = $pdo->prepare("INSERT INTO tbl_menu (menu_type,menu_name,category_or_page_slug,menu_order,menu_parent,menu_url) VALUES (?,?,?,?,?,?)");
		$statement->execute(array('Category',$mns[0],$mns[1],$_POST['menu_order'],$_POST['menu_parent'],''));

    	$success_message = 'Menu de categoria adicionado com sucesso.';
    }

}

if(isset($_POST['form2'])) {
	$active_tab = 'tab_2';
	$valid = 1;

	if(empty($_POST['menu_name_and_category_or_page_slug'])) {
        $valid = 0;
        $error_message .= "Você deve selecionar uma página como nome do menu.<br>";
    }
    if(empty($_POST['menu_order'])) {
        $valid = 0;
        $error_message .= "A ordem do menu não pode ficar vazia.<br>";
    }
    if($_POST['menu_parent'] == '') {
        $valid = 0;
        $error_message .= "Você deve selecionar um menu pai.<br>";
    }

	if($valid == 1) {
    	$mns = explode(':1:1:',$_POST['menu_name_and_category_or_page_slug']);

		// saving into the database
		$statement = $pdo->prepare("INSERT INTO tbl_menu (menu_type,menu_name,category_or_page_slug,menu_order,menu_parent,menu_url) VALUES (?,?,?,?,?,?)");
		$statement->execute(array('Page',$mns[0],$mns[1],$_POST['menu_order'],$_POST['menu_parent'],''));

    	$success_message = 'Menu de página adicionado com sucesso.';
    }

}

if(isset($_POST['form3'])) {
	$active_tab = 'tab_3';
	$valid = 1;

	if(empty($_POST['menu_name'])) {
        $valid = 0;
        $error_message .= "O nome do menu não pode ficar vazio.<br>";
    }
    if(empty($_POST['menu_order'])) {
        $valid = 0;
        $error_message .= "A ordem do menu não pode ficar vazia.<br>";
    }
    if(empty($_POST['menu_url'])) {
        $valid = 0;
        $error_message .= "A URL do menu não pode ficar vazia.<br>";
    }
    if($_POST['menu_parent'] == '') {
        $valid = 0;
        $error_message .= "Você deve selecionar um menu pai.<br>";
    }

	if($valid == 1) {
		// saving into the database
		$statement = $pdo->prepare("INSERT INTO tbl_menu (menu_type,menu_name,category_or_page_slug,menu_order,menu_parent,menu_url) VALUES (?,?,?,?,?,?)");
		$statement->execute(array('Other',$_POST['menu_name'],'',$_POST['menu_order'],$_POST['menu_parent'],$_POST['menu_url']));

    	$success_message = 'Menu personalizado adicionado com sucesso.';
    }

}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Adicionar Menu</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item"><a href="menu.php">Menus</a></li>
					<li class="breadcrumb-item active" aria-current="page">Adicionar Menu</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="menu.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
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

		<div class="card card-primary card-outline card-outline-tabs shadow-sm mb-4">
			<div class="card-header p-0 border-bottom-0">
				<ul class="nav nav-tabs" id="menuAddTabs" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_1') ? 'active' : ''; ?>" id="tab1-btn" data-bs-toggle="pill" data-bs-target="#tab_1" type="button" role="tab" aria-controls="tab_1" aria-selected="<?= ($active_tab == 'tab_1') ? 'true' : 'false'; ?>">
							<i class="bi bi-folder me-1"></i> Categoria como Menu
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_2') ? 'active' : ''; ?>" id="tab2-btn" data-bs-toggle="pill" data-bs-target="#tab_2" type="button" role="tab" aria-controls="tab_2" aria-selected="<?= ($active_tab == 'tab_2') ? 'true' : 'false'; ?>">
							<i class="bi bi-file-earmark-text me-1"></i> Página como Menu
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_3') ? 'active' : ''; ?>" id="tab3-btn" data-bs-toggle="pill" data-bs-target="#tab_3" type="button" role="tab" aria-controls="tab_3" aria-selected="<?= ($active_tab == 'tab_3') ? 'true' : 'false'; ?>">
							<i class="bi bi-link-45deg me-1"></i> Link / Outro Menu
						</button>
					</li>
				</ul>
			</div>

			<div class="card-body p-4">
				<div class="tab-content" id="menuAddTabsContent">

					<!-- Aba 1: Categoria como Menu -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_1') ? 'show active' : ''; ?>" id="tab_1" role="tabpanel" aria-labelledby="tab1-btn">
						<form action="" method="post">
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Categoria <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-6">
									<select class="form-select select2" name="menu_name_and_category_or_page_slug" style="width:100%;" required>
										<option value="">Selecione uma categoria</option>
										<?php
										$statement = $pdo->prepare("SELECT * FROM tbl_category ORDER BY category_name ASC");
										$statement->execute();
										$result = $statement->fetchAll(PDO::FETCH_ASSOC);		
										foreach ($result as $row) {
											echo '<option value="'.$row['category_name'].':1:1:'.$row['category_slug'].'">'.$row['category_name'].'</option>';
										}
										?>
									</select>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ordem de Exibição <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="number" class="form-control" name="menu_order" value="1" min="1" required>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Menu Pai <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-6">
									<select class="form-select select2" name="menu_parent" style="width:100%;" required>
										<option value="0">Nenhum (Menu Principal / Raiz)</option>
										<?php
										$statement = $pdo->prepare("SELECT * FROM tbl_menu ORDER BY menu_order ASC");
										$statement->execute();
										$result = $statement->fetchAll(PDO::FETCH_ASSOC);		
										foreach ($result as $row) {
											echo '<option value="'.$row['menu_id'].'">'.$row['menu_name'].'</option>';
										}
										?>
									</select>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i> Salvar Menu</button>
									<a href="menu.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
								</div>
							</div>
						</form>
					</div>

					<!-- Aba 2: Página como Menu -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_2') ? 'show active' : ''; ?>" id="tab_2" role="tabpanel" aria-labelledby="tab2-btn">
						<form action="" method="post">
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Página <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-6">
									<select class="form-select select2" name="menu_name_and_category_or_page_slug" style="width:100%;" required>
										<option value="">Selecione uma página</option>
										<?php
										$statement = $pdo->prepare("SELECT * FROM tbl_page ORDER BY page_name ASC");
										$statement->execute();
										$result = $statement->fetchAll(PDO::FETCH_ASSOC);		
										foreach ($result as $row) {
											echo '<option value="'.$row['page_name'].':1:1:'.$row['page_slug'].'">'.$row['page_name'].'</option>';
										}
										?>
									</select>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ordem de Exibição <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="number" class="form-control" name="menu_order" value="1" min="1" required>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Menu Pai <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-6">
									<select class="form-select select2" name="menu_parent" style="width:100%;" required>
										<option value="0">Nenhum (Menu Principal / Raiz)</option>
										<?php
										$statement = $pdo->prepare("SELECT * FROM tbl_menu ORDER BY menu_order ASC");
										$statement->execute();
										$result = $statement->fetchAll(PDO::FETCH_ASSOC);		
										foreach ($result as $row) {
											echo '<option value="'.$row['menu_id'].'">'.$row['menu_name'].'</option>';
										}
										?>
									</select>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="form2"><i class="bi bi-check-lg me-1"></i> Salvar Menu</button>
									<a href="menu.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
								</div>
							</div>
						</form>
					</div>

					<!-- Aba 3: Outro Menu / Link Personalizado -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_3') ? 'show active' : ''; ?>" id="tab_3" role="tabpanel" aria-labelledby="tab3-btn">
						<form action="" method="post">
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome do Menu <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="text" class="form-control" name="menu_name" placeholder="Ex: Central de Ajuda" required>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ordem de Exibição <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="number" class="form-control" name="menu_order" value="1" min="1" required>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">URL de Destino <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="text" class="form-control" name="menu_url" placeholder="https://... ou caminho relativo" required>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Menu Pai <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-6">
									<select class="form-select select2" name="menu_parent" style="width:100%;" required>
										<option value="0">Nenhum (Menu Principal / Raiz)</option>
										<?php
										$statement = $pdo->prepare("SELECT * FROM tbl_menu ORDER BY menu_order ASC");
										$statement->execute();
										$result = $statement->fetchAll(PDO::FETCH_ASSOC);		
										foreach ($result as $row) {
											echo '<option value="'.$row['menu_id'].'">'.$row['menu_name'].'</option>';
										}
										?>
									</select>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="form3"><i class="bi bi-check-lg me-1"></i> Salvar Menu</button>
									<a href="menu.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
								</div>
							</div>
						</form>
					</div>

				</div>
			</div>
		</div>

	</div>
</div>

<?php require_once('footer.php'); ?>