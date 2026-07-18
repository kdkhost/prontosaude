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

<section class="content-header">
	<div class="content-header-left">
		<h1>Edit Pricing Item</h1>
	</div>
	<div class="content-header-right">
		<a href="pricing-item.php" class="btn btn-primary btn-sm">View All</a>
	</div>
</section>

<?php							
foreach ($result as $row) {
	$pricing_item_name = $row['pricing_item_name'];
	$pricing_plan_id   = $row['pricing_plan_id'];
}
?>


<section class="content">

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

			<form class="form-horizontal" action="" method="post">

				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Pricing Item Name <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="pricing_item_name" value="<?php echo $pricing_item_name; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Select Pricing Plan <span>*</span></label>
							<div class="col-sm-4">
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
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Submit</button>
							</div>
						</div>
					</div>
				</div>

			</form>


		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>