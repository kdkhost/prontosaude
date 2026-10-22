<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

    if(empty($_POST['p_category_name'])) {
        $valid = 0;
        $error_message .= "Photo Category Name can not be empty<br>";
    } else {
		// Duplicate Category checking
    	// current category name that is in the database
    	$statement = $pdo->prepare("SELECT * FROM tbl_category_photo WHERE p_category_id=?");
		$statement->execute(array($_REQUEST['id']));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		foreach($result as $row) {
			$current_category_name = $row['p_category_name'];
		}

		$statement = $pdo->prepare("SELECT * FROM tbl_category_photo WHERE p_category_name=? and p_category_name!=?");
    	$statement->execute(array($_POST['p_category_name'],$current_category_name));
    	$total = $statement->rowCount();							
    	if($total) {
    		$valid = 0;
        	$error_message .= 'Photo Category name already exists<br>';
    	}
    }

    if($valid == 1) {

    	// updating into the database
		$statement = $pdo->prepare("UPDATE tbl_category_photo SET p_category_name=?, status=? WHERE p_category_id=?");
		$statement->execute(array($_POST['p_category_name'],$_POST['status'],$_REQUEST['id']));

    	$success_message = 'Photo Category is updated successfully.';
    }
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_category_photo WHERE p_category_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Editar Categoria de Foto</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Categoria de Foto</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="photo-category.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>

<?php							
foreach ($result as $row) {
	$p_category_name = $row['p_category_name'];
	$status = $row['status'];
}
?>


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

			<form action="" method="post">
				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome da Categoria <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="p_category_name" value="<?php echo $p_category_name; ?>">
							</div>
						</div>
						<div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ativo?</label>
				            <div class="col-12 col-sm-9 col-lg-6">
				                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status" value="Active" <?php if($status == 'Active') { echo 'checked'; } ?>> <label class="form-check-label">Yes</label></div>
				                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status" value="Inactive" <?php if($status == 'Inactive') { echo 'checked'; } ?>> <label class="form-check-label">No</label></div>
				            </div>
				        </div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Atualizar</button>
<a href="photo-category.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>


</div>
</div>

<?php require_once('footer.php'); ?>