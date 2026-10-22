<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

    if(empty($_POST['designation_name'])) {
        $valid = 0;
        $error_message .= "Designation Name can not be empty<br>";
    } else {
		// Duplicate designation checking
    	// current designation name that is in the database
    	$statement = $pdo->prepare("SELECT * FROM tbl_designation WHERE designation_id=?");
		$statement->execute(array($_REQUEST['id']));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		foreach($result as $row) {
			$current_designation_name = $row['designation_name'];
		}

		$statement = $pdo->prepare("SELECT * FROM tbl_designation WHERE designation_name=? and designation_name!=?");
    	$statement->execute(array($_POST['designation_name'],$current_designation_name));
    	$total = $statement->rowCount();							
    	if($total) {
    		$valid = 0;
        	$error_message .= 'Designation name already exists<br>';
    	}
    }

    if($valid == 1) {

		// updating into the database
		$statement = $pdo->prepare("UPDATE tbl_designation SET designation_name=? WHERE designation_id=?");
		$statement->execute(array($_POST['designation_name'],$_REQUEST['id']));

    	$success_message = 'Designation is updated successfully.';
    }
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_designation WHERE designation_id=?");
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
				<h3 class="mb-1 fw-bold">Editar Designação</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Designação</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="designation.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>

<?php							
foreach ($result as $row) {
	$designation_name = $row['designation_name'];
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
	                    <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome da Designação <span class="text-danger">*</span></label>
	                    <div class="col-12 col-sm-9 col-lg-6">
	                        <input type="text" class="form-control" name="designation_name" value="<?php echo $designation_name; ?>" autocomplete="off">
	                    </div>
	                </div>
	                <div class="row mb-3">
	                	<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
	                    <div class="col-12 col-sm-9 col-lg-6">
	                      <button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Atualizar</button>
<a href="designation.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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