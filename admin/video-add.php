<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

    if(empty($_POST['video_title'])) {
        $valid = 0;
        $error_message .= "Video title can not be empty<br>";
    }

    if(empty($_POST['video_iframe'])) {
        $valid = 0;
        $error_message .= "Video iframe code can not be empty<br>";
    }
    
    if(empty($_POST['v_category_id'])) {
        $valid = 0;
        $error_message .= "You must have to select a video category<br>";
    }
    
    if($valid == 1) {

		// saving into the database
		$statement = $pdo->prepare("INSERT INTO tbl_video (video_title,video_iframe,v_category_id) VALUES (?,?,?)");
		$statement->execute(array($_POST['video_title'],$_POST['video_iframe'],$_POST['v_category_id']));

    	$success_message = 'Video is added successfully.';
    }
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Adicionar Vídeo</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Adicionar Vídeo</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="video.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
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

			<form action="" method="post">

				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título do Vídeo <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" class="form-control" name="video_title">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Código do iframe <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control" name="video_iframe" style="height:200px;"></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Categoria de Vídeo <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<select class="form-control" name="v_category_id">
									<option value="">Select a video category</option>
									<?php
									$statement = $pdo->prepare("SELECT * FROM tbl_category_video ORDER BY v_category_name ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
									foreach ($result as $row) {
										echo '<option value="'.$row['v_category_id'].'">'.$row['v_category_name'].'</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Salvar</button>
<a href="video.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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