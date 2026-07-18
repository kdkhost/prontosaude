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



<section class="content">

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

			<form class="form-horizontal" action="convenio-update.php" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="id_conv" value="<?= $id; ?>">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Título <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="titulo" value="<?= $titulo; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Sub-Título <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="subtitulo" value="<?= $subtitulo; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Descrição<span>*</span></label>
							<div class="col-sm-9">
								<textarea id="1" class="form-control" name="descricao" id="1"><?= $descricao; ?></textarea>
							</div>
						</div>
						
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Título do Banner<span>*</span></label>
							<div class="col-sm-9">
								<input class="form-control" name="titulo_banner" value="<?= $nome; ?>">
							</div>
						</div>
						
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Banner Atual</label>
							<div class="col-sm-9 page-banner" style="padding-top:5px">
								<?php if($banner != ""){?>
								    <img src="<?= BASE_URL; ?>assets/uploads/<?= $banner; ?>" alt="Foto Banner" style="width:100%;">
								<?php }else{?>
								    <img src="<?= BASE_URL; ?>assets/uploads/haoc-banner-agende-uma-consulta-1.jpg" alt="Foto Banner" style="width:100%;">
								<?php } ?>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Banner </label>
							<div class="col-sm-6" style="padding-top:5px">
								<input type="file" name="banner">(Somente jpg, jpeg, gif e png será permitido)
							</div>
						</div>
						
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="BtnConv">Atualizar</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>