<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if(isset($_POST['form1'])) {
	$valid = 1;

    $path = $_FILES['photo']['name'] ?? '';
    $path_tmp = $_FILES['photo']['tmp_name'] ?? '';

    if($path != '') {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if(!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $valid = 0;
            $error_message .= 'Formato de imagem inválido. Formatos permitidos: JPG, JPEG, PNG, GIF ou WEBP.<br>';
        }
    }

	if($valid == 1) {
		$heading     = trim($_POST['heading'] ?? '');
		$subheading  = trim($_POST['subheading'] ?? '');
		$content     = trim($_POST['content'] ?? '');
		$button_text = trim($_POST['button_text'] ?? '');
		$button_url  = trim($_POST['button_url'] ?? '');
		$position    = $_POST['position'] ?? 'Left';
		$status      = $_POST['status'] ?? 'Active';

		if($path == '') {
			$statement = $pdo->prepare("UPDATE tbl_slider SET heading=?, subheading=?, content=?, button_text=?, button_url=?, position=?, status=? WHERE id=?");
    		$statement->execute(array($heading, $subheading, $content, $button_text, $button_url, $position, $status, $_REQUEST['id']));
		} else {
			if(!empty($_POST['current_photo']) && file_exists('../assets/uploads/'.$_POST['current_photo'])) {
				@unlink('../assets/uploads/'.$_POST['current_photo']);
			}

			$final_name = 'slider-'.$_REQUEST['id'].'-'.time().'.'.$ext;
        	move_uploaded_file($path_tmp, '../assets/uploads/'.$final_name);

        	$statement = $pdo->prepare("UPDATE tbl_slider SET photo=?, heading=?, subheading=?, content=?, button_text=?, button_url=?, position=?, status=? WHERE id=?");
    		$statement->execute(array($final_name, $heading, $subheading, $content, $button_text, $button_url, $position, $status, $_REQUEST['id']));
		}	   

	    $success_message = 'Slider atualizado com sucesso!';
	}
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: slider.php');
	exit;
} else {
	$statement = $pdo->prepare("SELECT * FROM tbl_slider WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if( $total == 0 ) {
		header('location: slider.php');
		exit;
	}
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Editar Slider</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item"><a href="slider.php">Visualizar Sliders</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Slider</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="slider.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar à Listagem</a>
			</div>
		</div>
	</div>
</div>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_slider WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$photo       = $row['photo'];
	$heading     = $row['heading'];
	$subheading  = $row['subheading'];
	$content     = $row['content'];
	$button_text = $row['button_text'];
	$button_url  = $row['button_url'];
	$position    = $row['position'];
	$status      = $row['status'];
}
?>

<div class="app-content">
<div class="container-fluid">

	<div class="row">
		<div class="col-md-12">

			<?php if(!empty($error_message)): ?>
			<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
				<div class="d-flex align-items-center">
					<i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
					<div><?php echo $error_message; ?></div>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
			<?php endif; ?>

			<?php if(!empty($success_message)): ?>
			<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
				<div class="d-flex align-items-center">
					<i class="bi bi-check-circle-fill fs-5 me-2"></i>
					<div><?php echo $success_message; ?></div>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
			<?php endif; ?>

			<form action="" method="post" enctype="multipart/form-data">
				<input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($photo); ?>">
				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-header bg-body-tertiary">
						<h5 class="card-title fw-semibold mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i> Informações do Slide Hero</h5>
					</div>
					<div class="card-body">
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto Atual</label>
							<div class="col-12 col-sm-9 col-lg-7">
								<?php if(!empty($photo) && file_exists('../assets/uploads/'.$photo)): ?>
									<img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $photo; ?>" alt="Slider Photo" class="img-thumbnail rounded shadow-sm" style="max-width: 320px; max-height: 180px; object-fit: cover;">
								<?php else: ?>
									<div class="p-3 bg-light rounded text-muted small">Sem imagem cadastrada</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nova Foto</label>
							<div class="col-12 col-sm-9 col-lg-7">
								<input type="file" name="photo" class="form-control">
								<div class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG, GIF, WEBP. Recomendado: 1920x800px.</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título Principal</label>
							<div class="col-12 col-sm-9 col-lg-7">
								<input type="text" autocomplete="off" class="form-control" name="heading" value="<?php echo htmlspecialchars($heading); ?>" placeholder="Ex: Clínica Médica & Odontologia Integrada">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
							<div class="col-12 col-sm-9 col-lg-7">
								<input type="text" autocomplete="off" class="form-control" name="subheading" value="<?php echo htmlspecialchars($subheading); ?>" placeholder="Ex: Consultas especializadas e diagnósticos de alta precisão">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Conteúdo / Texto Complementar</label>
							<div class="col-12 col-sm-9 col-lg-7">
								<textarea class="form-control" name="content" rows="4" placeholder="Insira o texto complementar detalhado que será exibido no slide da Hero principal..."><?php echo htmlspecialchars($content); ?></textarea>
								<div class="form-text text-muted">Este conteúdo agora é exibido dinamicamente na Hero da página principal abaixo do subtítulo.</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Texto do Botão</label>
							<div class="col-12 col-sm-9 col-lg-7">
								<input type="text" autocomplete="off" class="form-control" name="button_text" value="<?php echo htmlspecialchars($button_text); ?>" placeholder="Ex: Agendar Atendimento">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Link / URL do Botão</label>
							<div class="col-12 col-sm-9 col-lg-7">
								<input type="text" autocomplete="off" class="form-control" name="button_url" value="<?php echo htmlspecialchars($button_url); ?>" placeholder="Ex: https://wa.me/... ou servicos.php">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Posição do Texto</label>
							<div class="col-12 col-sm-9 col-lg-7">
								<select name="position" class="form-select">
									<option value="Left" <?php if($position == 'Left') {echo 'selected';} ?>>Esquerda (Padrão)</option>
									<option value="Center" <?php if($position == 'Center') {echo 'selected';} ?>>Centro</option>
									<option value="Right" <?php if($position == 'Right') {echo 'selected';} ?>>Direita</option>
								</select>
							</div>
						</div>      
				        <div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Status de Exibição</label>
				            <div class="col-12 col-sm-9 col-lg-7">
				                <div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" name="status" id="statusActive" value="Active" <?php if($status == 'Active') { echo 'checked'; } ?>> 
									<label class="form-check-label text-success fw-semibold" for="statusActive"><i class="bi bi-check-circle me-1"></i> Ativo (Exibir na Home)</label>
								</div>
				                <div class="form-check form-check-inline">
									<input class="form-check-input" type="radio" name="status" id="statusInactive" value="Inactive" <?php if($status == 'Inactive') { echo 'checked'; } ?>> 
									<label class="form-check-label text-danger fw-semibold" for="statusInactive"><i class="bi bi-x-circle me-1"></i> Inativo (Ocultar)</label>
								</div>
				            </div>
				        </div>						
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-7">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i> Salvar Alterações</button>
								<a href="slider.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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