<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['file_title'])) {
        $valid = 0;
        $error_message .= "File title can not be empty<br>";
    }

    $path = $_FILES['file']['name'];
    $path_tmp = $_FILES['file']['tmp_name'];

    if($path != '') {
    	$ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' && $ext!='pdf' && $ext!='doc' && $ext!='docx' && $ext!='ppt' && $ext!='pptx' && $ext!='xls' && $ext!='xlsx' && $ext!='zip' && $ext!='rar' ) {
            $valid = 0;
            $error_message .= 'Only these files are allowed: jpg, jpeg, gif, png, pdf, doc, docx, ppt, pptx, xls, xlsx, zip, rar<br>';
        }
    }

    if($valid == 1) {

    	if($path == '') {
    		// updating into the database
			$statement = $pdo->prepare("UPDATE tbl_file SET file_title=? WHERE file_id=?");
			$statement->execute(array($_POST['file_title'],$_REQUEST['id']));
    	} else {
    		unlink('../assets/uploads/'.$_POST['previous_file']);

    		$final_name = 'file-'.$_REQUEST['id'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        	// updating into the database
			$statement = $pdo->prepare("UPDATE tbl_file SET file_title=?, file_name=? WHERE file_id=?");
			$statement->execute(array($_POST['file_name'],$final_name,$_REQUEST['id']));
    	}
    	
    	$success_message = 'File is updated successfully.';
    }
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_file WHERE file_id=?");
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
				<h3 class="mb-1 fw-bold">Editar Arquivo</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Arquivo</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="file.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>

<?php							
foreach ($result as $row) {
	$file_title = $row['file_title'];
	$file_name = $row['file_name'];
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
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título do Arquivo <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="file_title" value="<?php echo $file_title; ?>">
							</div>
						</div>
						<div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Arquivo Atual</label>
				            <div class="col-12 col-sm-9 col-lg-6">
				            	<?php
        						$fn = explode('.',$file_name);
		                    	if($fn[1] == 'jpg' || $fn[1] == 'jpeg' || $fn[1] == 'png' || $fn[1] == 'gif') {
		                    		?>
										<img src="../assets/uploads/<?php echo $file_name; ?>" class="existing-photo" style="width:300px;">
		                    		<?php
		                    	} else {
		                    		echo $row['file_name'];
		                    	}
		                    	?>
				                <input type="hidden" name="previous_file" value="<?php echo $file_name; ?>">
				            </div>
				        </div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Novo Arquivo <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="file" name="file">
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Salvar</button>
<a href="file.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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