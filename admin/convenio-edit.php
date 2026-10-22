<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_convenio WHERE id_conv = 1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $conv) {
    $titulo     = $conv["titulo"];
    $subtitulo  = $conv["subtitulo"];
    $descricao  = $conv["descricao"];
    $banner     = $conv["banner"];
    $nome       = $conv["titulo_banner"];
    $id         = $conv["id_conv"];
}

?>



<div class="app-content">
<div class="container-fluid">

	<div class="row">
		<div class="col-md-12">

			<?php if($error_message): ?>
			<div class="callout callout-danger">
				<p>
				<?= $error_message; ?>
				</p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
				<p><?= $success_message; ?></p>
			</div>
			<?php endif; ?>

			<form action="convenio-update.php" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="id_conv" value="<?= $id; ?>">
				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="titulo" value="<?= $titulo; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="subtitulo" value="<?= $subtitulo; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Descrição <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea id="1" class="form-control" name="descricao" id="1"><?= $descricao; ?></textarea>
							</div>
						</div>
						
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título do Banner <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input class="form-control" name="titulo_banner" value="<?= $nome; ?>">
							</div>
						</div>
						
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Banner Atual</label>
							<div class="col-sm-9 page-banner" style="padding-top:5px">
								<?php if($banner != ""){?>
								    <img src="<?= BASE_URL; ?>assets/uploads/<?= $banner; ?>" alt="Foto Banner" style="width:100%;">
								<?php }else{?>
								    <img src="<?= BASE_URL; ?>assets/uploads/haoc-banner-agende-uma-consulta-1.jpg" alt="Foto Banner" style="width:100%;">
								<?php } ?>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Banner</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="file" name="banner"><div class="form-text">Somente jpg, jpeg, gif e png será permitido</div>
							</div>
						</div>
						
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-success" name="BtnConv">Atualizar</button>
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