<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

    if(empty($_POST['designation_name'])) {
        $valid = 0;
        $error_message .= "Nome de designação não pode ficar vazio<br>";
    } else {
    	// Duplicate Category checking
    	$statement = $pdo->prepare("SELECT * FROM tbl_designation WHERE designation_name=?");
    	$statement->execute(array($_POST['designation_name']));
    	$total = $statement->rowCount();
    	if($total)
    	{
    		$valid = 0;
        	$error_message .= "O nome da designação já existe<br>";
    	}
    }

    if($valid == 1) {

		// Saving data into the main table tbl_designation
		$statement = $pdo->prepare("INSERT INTO tbl_designation (designation_name) VALUES (?)");
		$statement->execute(array($_POST['designation_name']));
	
    	$success_message = 'A designação foi adicionada com sucesso.';
    }
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Adicionar designação</h1>
	</div>
	<div class="content-header-right">
		<a href="designation.php" class="btn btn-primary btn-sm">Ver todas</a>
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
							<label for="" class="col-sm-3 control-label">Nome da Designação <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="designation_name" autocomplete="off">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Enviar</button>
							</div>
						</div>
					</div>
				</div>
			</form>


		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>