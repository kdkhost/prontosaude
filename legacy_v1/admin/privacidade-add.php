<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['titulo'])) {
		$valid = 0;
		$error_message .= 'O Título não pode ficar Vazio<br>';
	}

	if(empty($_POST['detalhes'])) {
		$valid = 0;
		$error_message .= 'O detalhe não pode ser vazio<br>';
	}
	

	if($valid == 1) {

		$statement = $pdo->prepare("INSERT INTO tbl_privacidade (titulo, detalhes) VALUES (?,?)");
		$statement->execute(array($_POST['titulo'],$_POST['detalhes']));
			
		$success_message = 'Privacidade adcionada com Sucesso!';

		unset($_POST['titulo']);
		unset($_POST['detalhes']);
		unset($_POST['status']);
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Add Privacidade</h1>
	</div>
	<div class="content-header-right">
		<a href="privacidade.php" class="btn btn-primary btn-sm">Visualizar todas</a>
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

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Título <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="titulo" value="<?php if(isset($_POST['titulo'])){echo $_POST['titulo'];} ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Descrição <span>*</span></label>
							<div class="col-sm-9">
								<textarea id="1" class="form-control" name="detalhes" id="1"><?php if(isset($_POST['detalhes'])){echo $_POST['detalhes'];} ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Cadastrar</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>