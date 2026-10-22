<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
    
	$valid = 1;

	if(empty($_POST['name'])) {
		$valid = 0;
		$error_message .= 'O nome é obrigatório.<br>';
	}

    if(empty($_POST['designation_id'])) {
		$valid = 0;
		$error_message .= 'A expecialidade principal é obrigatória.<br>';
	}

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'Você deve fazer o upload do arquivo jpg, jpeg, gif ou png para foto do médico<br>';
        }
    }


    $path1 = $_FILES['banner']['name'];
    $path_tmp1 = $_FILES['banner']['tmp_name'];

    if($path1!='') {
        $ext1 = pathinfo( $path1, PATHINFO_EXTENSION );
        $file_name1 = basename( $path1, '.' . $ext1 );
        if( $ext1!='jpg' && $ext1!='png' && $ext1!='jpeg' && $ext1!='gif' ) {
            $valid = 0;
            $error_message .= 'Você deve fazer o upload do arquivo jpg, jpeg, gif ou png para banner<br>';
        }
    }

	if($valid == 1) {

		if($_POST['slug'] == '') {
    		// generate slug
    		$temp_string = strtolower($_POST['name']);
    		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	} else {
    		$temp_string = strtolower($_POST['slug']);
    		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	}

    	// if slug already exists, then rename it
		$statement = $pdo->prepare("SELECT * FROM tbl_doctor WHERE slug=? AND name!=?");
		$statement->execute(array($slug,$_POST['current_doctor_name']));
		$total = $statement->rowCount();
		if($total) {
			$slug = $slug.'-1';
		}


		if($path == '' && $path1 == '') {
			$statement = $pdo->prepare("UPDATE tbl_doctor SET name=?, slug=?, designation_id=?, registro=?, clinica=?, degree=?, detail=?, facebook=?, twitter=?, linkedin=?, youtube=?, google_plus=?, instagram=?, flickr=?, address=?, practice_location=?, phone=?, email=?, website=?, status=?, meta_title=?, meta_keyword=?, meta_description=? WHERE id=?");
    		$statement->execute(array($_POST['name'],$slug,$_POST['designation_id'],$_POST['registro'],$_POST['clinica'],$_POST['degree'],$_POST['detail'],$_POST['facebook'],$_POST['twitter'],$_POST['linkedin'],$_POST['youtube'],$_POST['google_plus'],$_POST['instagram'],$_POST['flickr'],$_POST['address'],$_POST['practice_location'],$_POST['phone'],$_POST['email'],$_POST['website'],$_POST['status'],$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description'],$_REQUEST['id']));
		}

		if($path != '' && $path1 == '') {
			unlink('../assets/uploads/'.$_POST['current_photo']);

			$final_name = 'doctor-'.$_REQUEST['id'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

			$statement = $pdo->prepare("UPDATE tbl_doctor SET name=?, slug=?, designation_id=?, registro=?, clinica=?, photo=?, degree=?, detail=?, facebook=?, twitter=?, linkedin=?, youtube=?, google_plus=?, instagram=?, flickr=?, address=?, practice_location=?, phone=?, email=?, website=?, status=?, meta_title=?, meta_keyword=?, meta_description=? WHERE id=?");
    		$statement->execute(array($_POST['name'],$slug,$_POST['designation_id'],$_POST['registro'],$_POST['clinica'],$final_name,$_POST['degree'],$_POST['detail'],$_POST['facebook'],$_POST['twitter'],$_POST['linkedin'],$_POST['youtube'],$_POST['google_plus'],$_POST['instagram'],$_POST['flickr'],$_POST['address'],$_POST['practice_location'],$_POST['phone'],$_POST['email'],$_POST['website'],$_POST['status'],$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description'],$_REQUEST['id']));
		}

		if($path == '' && $path1 != '') {
			unlink('../assets/uploads/'.$_POST['current_banner']);

			$final_name1 = 'doctor-banner-'.$_REQUEST['id'].'.'.$ext1;
        	move_uploaded_file( $path_tmp1, '../assets/uploads/'.$final_name1 );

			$statement = $pdo->prepare("UPDATE tbl_doctor SET name=?, slug=?, designation_id=?, registro=?, clinica=?, banner=?, degree=?, detail=?, facebook=?, twitter=?, linkedin=?, youtube=?, google_plus=?, instagram=?, flickr=?, address=?, practice_location=?, phone=?, email=?, website=?, status=?, meta_title=?, meta_keyword=?, meta_description=? WHERE id=?");
    		$statement->execute(array($_POST['name'],$slug,$_POST['designation_id'],$_POST['registro'],$_POST['clinica'],$final_name1,$_POST['degree'],$_POST['detail'],$_POST['facebook'],$_POST['twitter'],$_POST['linkedin'],$_POST['youtube'],$_POST['google_plus'],$_POST['instagram'],$_POST['flickr'],$_POST['address'],$_POST['practice_location'],$_POST['phone'],$_POST['email'],$_POST['website'],$_POST['status'],$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description'],$_REQUEST['id']));
		}

		if($path != '' && $path1 != '') {
			unlink('../assets/uploads/'.$_POST['current_photo']);
			unlink('../assets/uploads/'.$_POST['current_banner']);

			$final_name = 'doctor-'.$_REQUEST['id'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

			$final_name1 = 'doctor-banner-'.$_REQUEST['id'].'.'.$ext1;
        	move_uploaded_file( $path_tmp1, '../assets/uploads/'.$final_name1 );

			$statement = $pdo->prepare("UPDATE tbl_doctor SET name=?, slug=?, designation_id=?, registro=?, clinica=?, photo=?, banner=?, degree=?, detail=?, facebook=?, twitter=?, linkedin=?, youtube=?, google_plus=?, instagram=?, flickr=?, address=?, practice_location=?, phone=?, email=?, website=?, status=?, meta_title=?, meta_keyword=?, meta_description=? WHERE id=?");
    		$statement->execute(array($_POST['name'],$slug,$_POST['designation_id'],$_POST['registro'],$_POST['clinica'],$final_name,$final_name1,$_POST['degree'],$_POST['detail'],$_POST['facebook'],$_POST['twitter'],$_POST['linkedin'],$_POST['youtube'],$_POST['google_plus'],$_POST['instagram'],$_POST['flickr'],$_POST['address'],$_POST['practice_location'],$_POST['phone'],$_POST['email'],$_POST['website'],$_POST['status'],$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description'],$_REQUEST['id']));
		}

	    $success_message = 'Médico cadastrado com sucesso!';
	}
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_doctor WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Editar Médico</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Editar Médico</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="doctor.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
			</div>
		</div>
	</div>
</div>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_doctor WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $idd               = $row['id'];
	$name              = $row['name'];
	$slug              = $row['slug'];
	$designation_id    = $row['designation_id'];
	$registro          = $row['registro'];
	$clinica           = $row['clinica'];
	$photo             = $row['photo'];
	$banner            = $row['banner'];
	$degree            = $row['degree'];
	$detail            = $row['detail'];
	$facebook          = $row['facebook'];
	$twitter           = $row['twitter'];
	$linkedin          = $row['linkedin'];
	$youtube           = $row['youtube'];
	$google_plus       = $row['google_plus'];
	$instagram         = $row['instagram'];
	$flickr            = $row['flickr'];
	$address           = $row['address'];
	$practice_location = $row['practice_location'];
	$phone             = $row['phone'];
	$email             = $row['email'];
	$website           = $row['website'];
	$status            = $row['status'];
	$meta_title        = $row['meta_title'];
	$meta_keyword      = $row['meta_keyword'];
	$meta_description  = $row['meta_description'];
}
?>

<div class="app-content">
<div class="container-fluid">

	<div class="row">
		<div class="col-md-12">

			<?php if($error_message): ?>
			<div class="callout callout-danger">
			<h4>Please correct the following errors:</h4>
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
				<input type="hidden" name="current_photo" value="<?php echo $photo; ?>">
				<input type="hidden" name="current_banner" value="<?php echo $banner; ?>">
				<input type="hidden" name="current_doctor_name" value="<?php echo $name; ?>">
				<div class="card card-outline card-primary shadow-sm mb-4">
					<div class="card-body">
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="name" value="<?php echo $name; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Slug</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control ps-slug-locked" name="slug" value="<?php echo $slug; ?>" readonly style="background-color: var(--bs-tertiary-bg, #f1f5f9); cursor: not-allowed; border-style: dashed;">
								<div class="form-text small text-body-secondary mt-1 ps-slug-helper d-flex align-items-center gap-1">
									<i class="bi bi-lock-fill text-primary"></i> <span class="fw-semibold">Slug automático:</span> gerado sozinho pelo sistema (edição manual desativada)
								</div>
							</div>
						</div>
						<div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Designação <span class="text-danger">*</span></label>
				            <div class="col-12 col-sm-9 col-lg-6">
				            	<select class="form-control select2" name="designation_id" style="width:300px;">
								<?php
				            	$i=0;
				            	$statement = $pdo->prepare("SELECT * FROM tbl_designation ORDER BY designation_name ASC");
				            	$statement->execute(array('Active'));
				            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
				            	foreach ($result as $row) {
									?>
									<option value="<?php echo $row['designation_id']; ?>" <?php if($row['designation_id']==$designation_id){echo 'selected';} ?>><?php echo $row['designation_name']; ?></option>
	                                <?php
								}
								?>
								</select>
				            </div>
				        </div>
				        
				        <div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Clínica <span class="text-danger">*</span></label>
				            <div class="col-12 col-sm-9 col-lg-6">
				            	<select class="form-control select2" name="clinica" style="width:300px;">
								<?php
				            	$i=0;
				            	$statement = $pdo->prepare("SELECT * FROM tbl_department AS t1 JOIN tbl_doctor AS t2 WHERE t1.dep_id=t2.clinica AND t2.id=? ORDER BY t1.dep_name ASC");
				            	$statement->execute(array($_REQUEST['id']));
				            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
				            	foreach ($result as $row) {
									?>
									<option value="<?php echo $row['dep_id']; ?>" <?php if($row['dep_id']==$clinica){echo 'selected';} ?>><?php echo $row['dep_name']; ?></option>
	                                <?php
								}
								?>
								</select>
				            </div>
				        </div>
				        
				        <div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Registro <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" name="registro" class="form-control" value="<?=$registro?>">
							</div>
						</div>
				        
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto Atual</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $photo; ?>" alt="Doctor Photo" style="width:200px;">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input type="file" name="photo"><div class="form-text">Only jpg, jpeg, gif and png are allowed</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Banner Atual</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $banner; ?>" alt="Doctor Banner" style="width:200px;">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Banner <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input type="file" name="banner"><div class="form-text">Only jpg, jpeg, gif and png are allowed</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Formação</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="degree" value="<?php echo $degree; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Detalhes</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control" name="detail" id="1"><?php echo $detail; ?></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Facebook</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="facebook" value="<?php echo $facebook; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Twitter</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="twitter" value="<?php echo $twitter; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">LinkedIn</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="linkedin" value="<?php echo $linkedin; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">YouTube</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="youtube" value="<?php echo $youtube; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Google Plus</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="google_plus" value="<?php echo $google_plus; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Instagram</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="instagram" value="<?php echo $instagram; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Flickr</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="flickr" value="<?php echo $flickr; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Buscar por CEP</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<div class="row g-2">
									<div class="col-7">
										<div class="input-group">
											<span class="input-group-text bg-body-tertiary"><i class="bi bi-geo-alt-fill text-primary"></i></span>
											<input type="text" class="form-control mask-cep" id="busca_cep" placeholder="00000-000" maxlength="9">
										</div>
									</div>
									<div class="col-5">
										<input type="text" class="form-control" id="numero_endereco" placeholder="Nº (auto focus)">
									</div>
								</div>
								<div class="form-text small text-body-secondary mt-1">
									<i class="bi bi-info-circle text-primary me-1"></i>Digite o CEP para autocompletar o endereço. O cursor focará no número assim que localizado.
								</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Endereço</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<textarea class="form-control" name="address" style="height: 140px"><?php echo $address; ?></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Local de Atendimento</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<textarea class="form-control" name="practice_location" style="height: 140px"><?php echo $practice_location; ?></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Telefone</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="phone" value="<?php echo $phone; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">E-mail</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="email" value="<?php echo $email; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Website</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="website" value="<?php echo $website; ?>">
							</div>
						</div>				        
				        <div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ativo?</label>
				            <div class="col-12 col-sm-9 col-lg-6">
				                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status" value="Active" <?php if($status == 'Active') { echo 'checked'; } ?>> <label class="form-check-label">Yes</label></div>
				                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status" value="Inactive" <?php if($status == 'Inactive') { echo 'checked'; } ?>> <label class="form-check-label">No</label></div>
				            </div>
				        </div>
						<div class="row mb-3 mt-4"><div class="col-12"><h5 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-search me-2"></i>SEO Information</h5></div></div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Título</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input type="text" autocomplete="off" class="form-control" name="meta_title" value="<?php echo $meta_title; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Palavras-chave</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input type="text" autocomplete="off" class="form-control" name="meta_keyword" value="<?php echo $meta_keyword; ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Descrição</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control" name="meta_description" style="height:140px;"><?php echo $meta_description; ?></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Salvar</button>
<a href="doctor.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
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