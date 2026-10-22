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

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Adicionar Política de Privacidade</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Adicionar Política de Privacidade</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="privacidade.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
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

			<form action="" method="post" enctype="multipart/form-data">
				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="titulo" value="<?php if(isset($_POST['titulo'])){echo $_POST['titulo'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Descrição <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea id="1" class="form-control" name="detalhes" id="1"><?php if(isset($_POST['detalhes'])){echo $_POST['detalhes'];} ?></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Cadastrar</button>
<a href="privacidade.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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