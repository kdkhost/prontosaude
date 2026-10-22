<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
        
        //var_dump($inicio, $fim);
        //die("<br> fazendo manutenção no site!");
    
    
	$valid = 1;


	if(empty($_POST['name'])) {
		$valid = 0;
		$error_message .= 'Nome do Médico não pode ser Vazio!<br>';
	}

    if(empty($_POST['designation_id'])) {
		$valid = 0;
		$error_message .= 'Você deve ter que selecionar uma designação<br>';
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
    } else {
    	$valid = 0;
        $error_message .= 'Você deve selecionar uma foto para a foto do médico<br>';
    }


    $path1 = $_FILES['banner']['name'];
    $path_tmp1 = $_FILES['banner']['tmp_name'];

    if($path1!='') {
        $ext1 = pathinfo( $path1, PATHINFO_EXTENSION );
        $file_name1 = basename( $path1, '.' . $ext1 );
        if( $ext1!='jpg' && $ext1!='png' && $ext1!='jpeg' && $ext1!='gif' ) {
            $valid = 0;
            $error_message .= 'Você precisa enviar uma imagem jpg, jpeg, gif ou png para banner<br>';
        }
    } else {
    	$valid = 0;
        $error_message .= 'Você deve ter que selecionar uma foto para banner<br>';
    }

	if($valid == 1) {

		// getting auto increment id
		$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_doctor'");
		$statement->execute();
		$result = $statement->fetchAll();
		foreach($result as $row) {
			$ai_id=$row[10];
		}

		if($_POST['slug'] == '') {
    		// generate slug
    		$temp_string = strtolower($_POST['name']);
    		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	} else {
    		$temp_string = strtolower($_POST['slug']);
    		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	}

    	// if slug already exists, then rename it
		$statement = $pdo->prepare("SELECT * FROM tbl_doctor WHERE slug=?");
		$statement->execute(array($slug));
		$total = $statement->rowCount();
		if($total) {
			$slug = $slug.'-1';
		}

		$final_name = 'doctor-'.$ai_id.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        $final_name1 = 'doctor-banner-'.$ai_id.'.'.$ext1;
        move_uploaded_file( $path_tmp1, '../assets/uploads/'.$final_name1 );

	//var_dump($_POST);die();
		$statement = $pdo->prepare("INSERT INTO tbl_doctor (name,slug,designation_id,registro,clinica,photo,banner,degree,detail,facebook,twitter,linkedin,youtube,google_plus,instagram,flickr,address,practice_location,phone,email,website,status,meta_title,meta_keyword,meta_description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
		$statement->execute(array($_POST['name'],$slug,$_POST['designation_id'],$_POST['registro'],$_POST['clinica'],$final_name,$final_name1,$_POST['degree'],$_POST['detail'],$_POST['facebook'],$_POST['twitter'],$_POST['linkedin'],$_POST['youtube'],$_POST['google_plus'],$_POST['instagram'],$_POST['flickr'],$_POST['address'],$_POST['practice_location'],$_POST['phone'],$_POST['email'],$_POST['website'],$_POST['status'],$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description']));
                                            
		$success_message = 'Médico Adicionado com Sucesso!';

		unset($_POST['name']);
		unset($_POST['registro']);
		unset($_POST['slug']);
		unset($_POST['degree']);
		unset($_POST['detail']);
		unset($_POST['facebook']);
		unset($_POST['twitter']);
		unset($_POST['linkedin']);
		unset($_POST['youtube']);
		unset($_POST['google_plus']);
		unset($_POST['instagram']);
		unset($_POST['flickr']);
		unset($_POST['address']);
		unset($_POST['practice_location']);
		unset($_POST['phone']);
		unset($_POST['email']);
		unset($_POST['website']);
		unset($_POST['meta_title']);
		unset($_POST['meta_keyword']);
		unset($_POST['meta_description']);
	}
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Adicionar Médico</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Adicionar Médico</li>
				</ol>
			</div>
			<div class="d-flex flex-wrap align-items-center gap-2">
				<a href="doctor.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i> Ver Listagem</a>
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
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nome <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="name" value="<?php if(isset($_POST['name'])){echo $_POST['name'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Registro <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" placeholder="Exemplo: CRM, CRFA, CRN4, CRP, CREFITO, CRO" autocomplete="off" class="form-control" name="registro" value="<?php if(isset($_POST['registro'])){echo $_POST['registro'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Slug</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control ps-slug-locked" name="slug" placeholder="Gerado automaticamente..." value="<?php if(isset($_POST['slug'])){echo $_POST['slug'];} ?>" readonly style="background-color: var(--bs-tertiary-bg, #f1f5f9); cursor: not-allowed; border-style: dashed;">
								<div class="form-text small text-body-secondary mt-1 ps-slug-helper d-flex align-items-center gap-1">
									<i class="bi bi-lock-fill text-primary"></i> <span class="fw-semibold">Slug automático:</span> gerado sozinho pelo sistema (edição manual desativada)
								</div>
							</div>
						</div>
						
						<div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Especialidade <span class="text-danger">*</span></label>
				            <div class="col-12 col-sm-9 col-lg-6">
				            	<select class="form-control select2" name="designation_id">
				            		<option value="">Selecione a Especialidade</option>
				            		<?php
						            	$i=0;
						            	$statement = $pdo->prepare("SELECT * FROM tbl_designation ORDER BY designation_name ASC");
						            	$statement->execute();
						            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
						            	foreach ($result as $row) {
						            		?>
											<option value="<?php echo $row['designation_id']; ?>"><?php echo $row['designation_name']; ?></option>
						            		<?php
						            	}
					            	?>
				            	</select>
				            </div>
				        </div>
						
				        <div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Clínica <span class="text-danger">*</span></label>
				            <div class="col-12 col-sm-9 col-lg-6">
				            	<select class="form-control select2" name="clinica">
				            		<?php
						            	$i=0;
						            	$st = $pdo->prepare("SELECT * FROM tbl_department ORDER BY dep_name ASC");
						            	$st->execute();
						            	$resultados = $st->fetchAll(PDO::FETCH_ASSOC);
						            	foreach ($resultados as $clin) {
						            		?>
											<option value="<?php echo $clin['dep_id']; ?>"><?php echo $clin['dep_name']; ?></option>
						            		<?php
						            	}
					            	?>
				            	</select>
				            </div>
				        </div>
						
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto do Médico <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="file" class="form-control" name="photo"><div class="form-text">Apenas jpg, jpeg, gif e png são permitidos</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Banner <span class="text-danger">*</span></label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="file" class="form-control" name="banner"><div class="form-text">Apenas jpg, jpeg, gif e png são permitidos</div>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Posição na Empresa</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="degree" value="<?php if(isset($_POST['degree'])){echo $_POST['degree'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Informações Complementares</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control" name="detail" id="1"><?php if(isset($_POST['detail'])){echo $_POST['detail'];} ?></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Facebook</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="facebook" value="<?php if(isset($_POST['facebook'])){echo $_POST['facebook'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Twitter</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="twitter" value="<?php if(isset($_POST['twitter'])){echo $_POST['twitter'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">LinkedIn</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="linkedin" value="<?php if(isset($_POST['linkedin'])){echo $_POST['linkedin'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">YouTube</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="youtube" value="<?php if(isset($_POST['youtube'])){echo $_POST['youtube'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Google Plus</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="google_plus" value="<?php if(isset($_POST['google_plus'])){echo $_POST['google_plus'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Instagram</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="instagram" value="<?php if(isset($_POST['instagram'])){echo $_POST['instagram'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Flickr</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="flickr" value="<?php if(isset($_POST['flickr'])){echo $_POST['flickr'];} ?>">
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
								<textarea class="form-control" name="address" style="height: 140px"><?php if(isset($_POST['address'])){echo $_POST['address'];} ?></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Local de Trabalho</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<textarea class="form-control" name="practice_location" style="height: 140px"><?php if(isset($_POST['practice_location'])){echo $_POST['practice_location'];} ?></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Telefone</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="phone" value="<?php if(isset($_POST['phone'])){echo $_POST['phone'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Email</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="email" value="<?php if(isset($_POST['email'])){echo $_POST['email'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Site</label>
							<div class="col-12 col-sm-9 col-lg-6">
								<input type="text" autocomplete="off" class="form-control" name="website" value="<?php if(isset($_POST['website'])){echo $_POST['website'];} ?>">
							</div>
						</div>				        
				        <div class="row mb-3">
				            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ativo?</label>
				            <div class="col-12 col-sm-9 col-lg-6">
				                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status" value="Active" checked> <label class="form-check-label">Sim</label></div>
				                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status" value="Inactive"> <label class="form-check-label">Não</label></div>
				            </div>
				        </div>
						<div class="row mb-3 mt-4"><div class="col-12"><h5 class="fw-bold text-primary border-bottom pb-2 mb-0"><i class="bi bi-search me-2"></i>Informações de SEO</h5></div></div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Título</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input type="text" autocomplete="off" class="form-control" name="meta_title" value="<?php if(isset($_POST['meta_title'])){echo $_POST['meta_title'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Palavras-chave</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<input type="text" autocomplete="off" class="form-control" name="meta_keyword" value="<?php if(isset($_POST['meta_keyword'])){echo $_POST['meta_keyword'];} ?>">
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Descrição</label>
							<div class="col-12 col-sm-9 col-lg-9">
								<textarea class="form-control" name="meta_description" style="height:140px;"><?php if(isset($_POST['meta_description'])){echo $_POST['meta_description'];} ?></textarea>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
							<div class="col-12 col-sm-9 col-lg-6">
								<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Adicionar</button>
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