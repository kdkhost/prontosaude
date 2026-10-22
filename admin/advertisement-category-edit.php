<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path != '') {
    	$ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }
       
    if($valid == 1) {

    	if($path == '') {
    		// updating into the database
			$statement = $pdo->prepare("UPDATE tbl_advertisement_category SET adv_url=? WHERE adv_id=?");
			$statement->execute(array($_POST['adv_url'],$_REQUEST['id']));
    	} else {
    		unlink('../assets/uploads/'.$_POST['previous_photo']);

    		$final_name = 'adv-category-'.$_REQUEST['id'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        	// updating into the database
			$statement = $pdo->prepare("UPDATE tbl_advertisement_category SET adv_photo=?, adv_url=? WHERE adv_id=?");
			$statement->execute(array($final_name,$_POST['adv_url'],$_REQUEST['id']));
    	}
    	
    	$success_message = 'Advertisement (Category) is updated successfully.';
    }
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_advertisement_category WHERE adv_id=?");
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
				<h3 class="mb-1 fw-bold">Editar Banner Publicitário (Após Categorias da Home)</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Banner Publicitário (Após Categorias da Home)</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="advertisement-category.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>

<?php							
foreach ($result as $row) {
	$adv_photo = $row['adv_photo'];
	$adv_url = $row['adv_url'];
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

			<form action="" method="post" enctype="multipart/form-data">

				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">
						
						<div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto Atual</label>
				            <div class="col-12 col-sm-9 col-lg-6">
				                <img src="../assets/uploads/<?php echo $adv_photo; ?>" class="existing-photo" style="width:300px;">

				                <input type="hidden" name="previous_photo" value="<?php echo $adv_photo; ?>">
				            </div>
				        </div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nova Foto <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="file" name="photo">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">URL</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="adv_url" value="<?php echo $adv_url ?>">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Salvar</button>
<a href="advertisement-category.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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