<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';
$active_tab = 'tab_1';

if(isset($_POST['form1'])) {
	$active_tab = 'tab_1';
	$valid = 1;
	
	$path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' && $ext!='webp' ) {
            $valid = 0;
            $error_message .= 'Você precisa enviar um arquivo de imagem válido (JPG, PNG, GIF ou WEBP).<br>';
        }
    }

    if($valid == 1) {
    	if($path=='') {
    		// updating the database
			$statement = $pdo->prepare("UPDATE tbl_advertisement_home SET adv_url=?, adv_status=? WHERE adv_id=1");
			$statement->execute(array($_POST['adv_url'],$_POST['adv_status']));
    	} else {
    		if(!empty($_POST['previous_photo']) && file_exists('../assets/uploads/'.$_POST['previous_photo'])) {
    			unlink('../assets/uploads/'.$_POST['previous_photo']);
    		}

			$final_name = 'adv-1'.'.'.$ext;
			move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );
			
			// updating the database
			$statement = $pdo->prepare("UPDATE tbl_advertisement_home SET adv_photo=?, adv_url=?, adv_status=? WHERE adv_id=1");
			$statement->execute(array($final_name,$_POST['adv_url'],$_POST['adv_status']));
    	}
    	$success_message = 'Anúncio do Cabeçalho atualizado com sucesso.';
    }
}


if(isset($_POST['form2'])) {
	$active_tab = 'tab_2';
	$valid = 1;
	
	$path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' && $ext!='webp' ) {
            $valid = 0;
            $error_message .= 'Você precisa enviar um arquivo de imagem válido (JPG, PNG, GIF ou WEBP).<br>';
        }
    }

    if($valid == 1) {
    	if($path=='') {
    		// updating the database
			$statement = $pdo->prepare("UPDATE tbl_advertisement_home SET adv_url=?, adv_status=? WHERE adv_id=2");
			$statement->execute(array($_POST['adv_url'],$_POST['adv_status']));
    	} else {
    		if(!empty($_POST['previous_photo']) && file_exists('../assets/uploads/'.$_POST['previous_photo'])) {
    			unlink('../assets/uploads/'.$_POST['previous_photo']);
    		}

			$final_name = 'adv-2'.'.'.$ext;
			move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );
			
			// updating the database
			$statement = $pdo->prepare("UPDATE tbl_advertisement_home SET adv_photo=?, adv_url=?, adv_status=? WHERE adv_id=2");
			$statement->execute(array($final_name,$_POST['adv_url'],$_POST['adv_status']));
    	}
    	$success_message = 'Anúncio Abaixo das Notícias atualizado com sucesso.';
    }
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Publicidades da Página Inicial</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Publicidades Home</li>
				</ol>
			</div>
		</div>
	</div>
</div>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_advertisement_home");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
$adv_photo = [];
$adv_url = [];
$adv_status = [];
foreach ($result as $row) {
	$adv_photo[] = $row['adv_photo'];
	$adv_url[] = $row['adv_url'];
	$adv_status[] = $row['adv_status'];
}
?>

<div class="app-content">
	<div class="container-fluid">

		<?php if(!empty($error_message)): ?>
		<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
			<div class="d-flex align-items-center">
				<i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
				<div><?php echo $error_message; unset($error_message); ?></div>
			</div>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		<?php endif; ?>

		<?php if(!empty($success_message)): ?>
		<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
			<div class="d-flex align-items-center">
				<i class="bi bi-check-circle-fill fs-5 me-2"></i>
				<div><?php echo $success_message; unset($success_message); ?></div>
			</div>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		<?php endif; ?>

		<div class="card card-primary card-outline card-outline-tabs shadow-sm mb-4">
			<div class="card-header p-0 border-bottom-0">
				<ul class="nav nav-tabs" id="advHomeTabs" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_1') ? 'active' : ''; ?>" id="tab1-btn" data-bs-toggle="pill" data-bs-target="#tab_1" type="button" role="tab" aria-controls="tab_1" aria-selected="<?= ($active_tab == 'tab_1') ? 'true' : 'false'; ?>">
							<i class="bi bi-layout-text-window me-1"></i> No Cabeçalho (Topo)
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_2') ? 'active' : ''; ?>" id="tab2-btn" data-bs-toggle="pill" data-bs-target="#tab_2" type="button" role="tab" aria-controls="tab_2" aria-selected="<?= ($active_tab == 'tab_2') ? 'true' : 'false'; ?>">
							<i class="bi bi-newspaper me-1"></i> Abaixo das Notícias em Destaque
						</button>
					</li>						
				</ul>
			</div>

			<div class="card-body p-4">
				<div class="tab-content" id="advHomeTabsContent">
					
					<!-- Aba 1: No Cabeçalho -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_1') ? 'show active' : ''; ?>" id="tab_1" role="tabpanel" aria-labelledby="tab1-btn">
						<form action="" method="post" enctype="multipart/form-data">
							<div class="row mb-3 align-items-center">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Banner Atual</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<div class="p-2 border rounded bg-light d-inline-block">
										<?php if(!empty($adv_photo[0])): ?>
										<img src="../assets/uploads/<?php echo $adv_photo[0]; ?>" class="img-fluid rounded" style="max-height:120px;">
										<?php else: ?>
										<span class="text-muted small">Nenhum banner cadastrado</span>
										<?php endif; ?>
									</div>
									<input type="hidden" name="previous_photo" value="<?php echo $adv_photo[0] ?? ''; ?>">
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Novo Banner</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="file" name="photo" class="form-control" accept="image/*">
									<small class="text-muted">Formatos: JPG, PNG, GIF, WEBP</small>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">URL de Destino</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="text" class="form-control" name="adv_url" value="<?php echo htmlspecialchars($adv_url[0] ?? ''); ?>" placeholder="https://...">
								</div>
							</div>
							<div class="row mb-3 align-items-center">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Exibir no Site?</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="adv_status" id="adv1_show" value="Show" <?php if(($adv_status[0] ?? '') == 'Show') { echo 'checked'; } ?>>
										<label class="form-check-label fw-semibold text-success" for="adv1_show"><i class="bi bi-eye me-1"></i> Sim (Exibir)</label>
									</div>
									<div class="form-check form-check-inline ms-3">
										<input class="form-check-input" type="radio" name="adv_status" id="adv1_hide" value="Hide" <?php if(($adv_status[0] ?? '') == 'Hide') { echo 'checked'; } ?>>
										<label class="form-check-label fw-semibold text-muted" for="adv1_hide"><i class="bi bi-eye-slash me-1"></i> Não (Ocultar)</label>
									</div>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i> Atualizar Banner</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Aba 2: Abaixo das Notícias -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_2') ? 'show active' : ''; ?>" id="tab_2" role="tabpanel" aria-labelledby="tab2-btn">
						<form action="" method="post" enctype="multipart/form-data">
							<div class="row mb-3 align-items-center">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Banner Atual</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<div class="p-2 border rounded bg-light d-inline-block">
										<?php if(!empty($adv_photo[1])): ?>
										<img src="../assets/uploads/<?php echo $adv_photo[1]; ?>" class="img-fluid rounded" style="max-height:120px;">
										<?php else: ?>
										<span class="text-muted small">Nenhum banner cadastrado</span>
										<?php endif; ?>
									</div>
									<input type="hidden" name="previous_photo" value="<?php echo $adv_photo[1] ?? ''; ?>">
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Novo Banner</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="file" name="photo" class="form-control" accept="image/*">
									<small class="text-muted">Formatos: JPG, PNG, GIF, WEBP</small>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">URL de Destino</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="text" class="form-control" name="adv_url" value="<?php echo htmlspecialchars($adv_url[1] ?? ''); ?>" placeholder="https://...">
								</div>
							</div>
							<div class="row mb-3 align-items-center">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Exibir no Site?</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="adv_status" id="adv2_show" value="Show" <?php if(($adv_status[1] ?? '') == 'Show') { echo 'checked'; } ?>>
										<label class="form-check-label fw-semibold text-success" for="adv2_show"><i class="bi bi-eye me-1"></i> Sim (Exibir)</label>
									</div>
									<div class="form-check form-check-inline ms-3">
										<input class="form-check-input" type="radio" name="adv_status" id="adv2_hide" value="Hide" <?php if(($adv_status[1] ?? '') == 'Hide') { echo 'checked'; } ?>>
										<label class="form-check-label fw-semibold text-muted" for="adv2_hide"><i class="bi bi-eye-slash me-1"></i> Não (Ocultar)</label>
									</div>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="form2"><i class="bi bi-check-lg me-1"></i> Atualizar Banner</button>
								</div>
							</div>
						</form>
					</div>

				</div>
			</div>
		</div>

	</div>
</div>

<?php require_once('footer.php'); ?>