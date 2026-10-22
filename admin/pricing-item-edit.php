<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['pricing_item_name'])) {
        $valid = 0;
        $error_message .= "Pricing Item Name can not be empty<br>";
    }
    
    if(empty($_POST['pricing_plan_id'])) {
        $valid = 0;
        $error_message .= "You must have to select a pricing plan<br>";
    }
        
    if($valid == 1) {

    	// updating into the database
		$statement = $pdo->prepare("UPDATE tbl_pricing_item SET pricing_item_name=?, pricing_plan_id=? WHERE pricing_item_id=?");
		$statement->execute(array($_POST['pricing_item_name'],$_POST['pricing_plan_id'],$_REQUEST['id']));
    	    	
    	$success_message = 'Pricing Item is updated successfully.';
    }
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_pricing_item WHERE pricing_item_id=?");
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
				<h3 class="mb-1 fw-bold">Editar Item de Preço</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Item de Preço</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="pricing-item.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>

<?php							
foreach ($result as $row) {
	$pricing_item_name = $row['pricing_item_name'];
	$pricing_plan_id   = $row['pricing_plan_id'];
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
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome do Item <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="pricing_item_name" value="<?php echo $pricing_item_name; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Plano de Preços <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<select class="form-control" name="pricing_plan_id">
									<?php
									$statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan ORDER BY pricing_plan_id ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
									foreach ($result as $row) {
										if($row['pricing_plan_id'] == $pricing_plan_id) {
											$selected = 'selected';
										} else {
											$selected = '';
										}
										echo '<option value="'.$row['pricing_plan_id'].'" '.$selected.'>'.$row['pricing_plan_name'].'</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Salvar</button>
<a href="pricing-item.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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