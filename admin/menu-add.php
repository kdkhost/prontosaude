<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['menu_name_and_category_or_page_slug'])) {
        $valid = 0;
        $error_message .= "You must have to select a category as menu name<br>";
    }
    if(empty($_POST['menu_order'])) {
        $valid = 0;
        $error_message .= "Menu Order can not be empty<br>";
    }
    if( $_POST['menu_parent'] == '') {
        $valid = 0;
        $error_message .= "You must have to select a parent for this menu<br>";
    }

	if($valid == 1) {

    	$mns = explode(':1:1:',$_POST['menu_name_and_category_or_page_slug']);

		// saving into the database
		$statement = $pdo->prepare("INSERT INTO tbl_menu (menu_type,menu_name,category_or_page_slug,menu_order,menu_parent,menu_url) VALUES (?,?,?,?,?,?)");
		$statement->execute(array('Category',$mns[0],$mns[1],$_POST['menu_order'],$_POST['menu_parent'],''));

    	$success_message = 'Menu is added successfully.';
    }

}
?>


<?php
if(isset($_POST['form2'])) {
	$valid = 1;

	if(empty($_POST['menu_name_and_category_or_page_slug'])) {
        $valid = 0;
        $error_message .= "You must have to select a page as menu name<br>";
    }
    if(empty($_POST['menu_order'])) {
        $valid = 0;
        $error_message .= "Menu Order can not be empty<br>";
    }
    if( $_POST['menu_parent'] == '') {
        $valid = 0;
        $error_message .= "You must have to select a parent for this menu<br>";
    }

	if($valid == 1) {

    	$mns = explode(':1:1:',$_POST['menu_name_and_category_or_page_slug']);

		// saving into the database
		$statement = $pdo->prepare("INSERT INTO tbl_menu (menu_type,menu_name,category_or_page_slug,menu_order,menu_parent,menu_url) VALUES (?,?,?,?,?,?)");
		$statement->execute(array('Page',$mns[0],$mns[1],$_POST['menu_order'],$_POST['menu_parent'],''));

    	$success_message = 'Menu is added successfully.';
    }

}
?>


<?php
if(isset($_POST['form3'])) {
	$valid = 1;

	if(empty($_POST['menu_name'])) {
        $valid = 0;
        $error_message .= "Menu Name can not be empty<br>";
    }
    if(empty($_POST['menu_order'])) {
        $valid = 0;
        $error_message .= "Menu Order can not be empty<br>";
    }
    if(empty($_POST['menu_url'])) {
        $valid = 0;
        $error_message .= "Menu URL can not be empty<br>";
    }
    if( $_POST['menu_parent'] == '') {
        $valid = 0;
        $error_message .= "You must have to select a parent for this menu<br>";
    }

	if($valid == 1) {

		// saving into the database
		$statement = $pdo->prepare("INSERT INTO tbl_menu (menu_type,menu_name,category_or_page_slug,menu_order,menu_parent,menu_url) VALUES (?,?,?,?,?,?)");
		$statement->execute(array('Other',$_POST['menu_name'],'',$_POST['menu_order'],$_POST['menu_parent'],$_POST['menu_url']));

    	$success_message = 'Menu is added successfully.';
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
	<div class="row">
		<div class="col-md-12">
			<?php if($error_message): ?>
			<div class="callout callout-danger">
			
			<p>
			<?php echo $error_message; ?>
			</p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
			
			<p><?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>
		</div>
	</div>

</div>
</div>

<div class="app-content">
<div class="container-fluid">

	<div class="row">
		<div class="col-md-12">

			<div class="nav-tabs-custom mb-4">
				<ul class="nav nav-tabs">
					<li class="nav-item"><a class="nav-link active" href="#tab_1" data-bs-toggle="tab">Categoria como Menu</a></li>
					<li class="nav-item"><a class="nav-link" href="#tab_2" data-bs-toggle="tab">Página como Menu</a></li>
					<li class="nav-item"><a class="nav-link" href="#tab_3" data-bs-toggle="tab">Other Menu</a></li>
				</ul>
				<div class="tab-content">
      				<div class="tab-pane active" id="tab_1">


						<form action="" method="post">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Categoria <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<select class="form-control select2" name="menu_name_and_category_or_page_slug" style="width:250px;">
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
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ordem <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="menu_order">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Menu Pai <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<select class="form-control select2" name="menu_parent" style="width:250px;">
												<option value="">Select a parent for this menu</option>
												<option value="0">No Parent</option>
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
											<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Salvar</button>
<a href="menu.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
										</div>
									</div>
								</div>
							</div>
						</form>


      				</div>
      				<div class="tab-pane" id="tab_2">


						<form action="" method="post">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Página <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<select class="form-control select2" name="menu_name_and_category_or_page_slug" style="width:250px;">
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
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ordem <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="menu_order">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Menu Pai <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<select class="form-control select2" name="menu_parent" style="width:250px;">
												<option value="">Select a parent for this menu</option>
												<option value="0">No Parent</option>
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
											<button type="submit" class="btn btn-primary px-4" name="form2"><i class="bi bi-check-lg me-1"></i>Salvar</button>
										</div>
									</div>
								</div>
							</div>
						</form>



      				</div>
      				<div class="tab-pane" id="tab_3">


						<form action="" method="post">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome do Menu <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="menu_name">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ordem <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="menu_order">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">URL <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="menu_url">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Menu Pai <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<select class="form-control select2" name="menu_parent" style="width:250px;">
												<option value="">Select a parent for this menu</option>
												<option value="0">No Parent</option>
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
											<button type="submit" class="btn btn-primary px-4" name="form3"><i class="bi bi-check-lg me-1"></i>Salvar</button>
										</div>
									</div>
								</div>
							</div>
						</form>

      				</div>
      			</div>
      		</div>
			
		</div>
	</div>


</div>
</div>

<?php require_once('footer.php'); ?>