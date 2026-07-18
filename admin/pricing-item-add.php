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

		// saving into the database
		$statement = $pdo->prepare("INSERT INTO tbl_pricing_item (pricing_item_name,pricing_plan_id) VALUES (?,?)");
		$statement->execute(array($_POST['pricing_item_name'],$_POST['pricing_plan_id']));

    	$success_message = 'Pricing Item is added successfully.';
    }
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Add Pricing Item</h1>
	</div>
	<div class="content-header-right">
		<a href="pricing-item.php" class="btn btn-primary btn-sm">View All</a>
	</div>
</section>


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
								<input type="text" class="form-control" name="pricing_item_name">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Select Pricing Plan <span>*</span></label>
							<div class="col-sm-4">
								<select class="form-control" name="pricing_plan_id">
									<option value="">Select a pricing plan</option>
									<?php
									$statement = $pdo->prepare("SELECT * FROM tbl_pricing_plan ORDER BY pricing_plan_id ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
									foreach ($result as $row) {
										echo '<option value="'.$row['pricing_plan_id'].'">'.$row['pricing_plan_name'].'</option>';
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