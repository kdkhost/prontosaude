<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	$path = $_FILES['photo_logo']['name'];
    $path_tmp = $_FILES['photo_logo']['tmp_name'];

    if($path == '') {
    	$valid = 0;
        $error_message .= 'Você precisa adcionar uma foto<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'Você precisa enviar um arquivo jpg, jpeg, gif ou png<br>';
        }
    }

    if($valid == 1) {
    	// removing the existing photo
    	$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
    	$statement->execute();
    	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
    	foreach ($result as $row) {
    		$logo = $row['logo'];
    		unlink('../assets/uploads/'.$logo);
    	}

    	// updating the data
    	$final_name = 'logo'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
		$statement = $pdo->prepare("UPDATE tbl_settings SET logo=? WHERE id=1");
		$statement->execute(array($final_name));

        $success_message = 'Sua Logo foi atualizada com sucesso.';
    	
    }
}

if(isset($_POST['form2'])) {
	$valid = 1;

	$path = $_FILES['photo_favicon']['name'];
    $path_tmp = $_FILES['photo_favicon']['tmp_name'];

    if($path == '') {
    	$valid = 0;
        $error_message .= 'Sua favicon não foi atualizada<br>';
    } else {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'Você precisa enviar um arquivo jpg, jpeg, gif ou png<br>';
        }
    }

    if($valid == 1) {
    	// removing the existing photo
    	$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
    	$statement->execute();
    	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
    	foreach ($result as $row) {
    		$favicon = $row['favicon'];
    		unlink('../assets/uploads/'.$favicon);
    	}

    	// updating the data
    	$final_name = 'favicon'.'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

        // updating the database
		$statement = $pdo->prepare("UPDATE tbl_settings SET favicon=? WHERE id=1");
		$statement->execute(array($final_name));

        $success_message = 'Favicon atualizado com sucesso.';
    	
    }
}

if(isset($_POST['form3'])) {
	
	// updating the database
	$statement = $pdo->prepare("UPDATE tbl_settings SET footer_about=?, footer_copyright=?, contact_address=?, contact_email=?, contact_phone=?, contact_fax=?, contact_map_iframe=? WHERE id=1");
	$statement->execute(array($_POST['footer_about'],$_POST['footer_copyright'],$_POST['contact_address'],$_POST['contact_email'],$_POST['contact_phone'],$_POST['contact_fax'],$_POST['contact_map_iframe']));

	$success_message = 'As configurações gerais de conteúdo foram atualizadas com sucesso.';
    
}

if(isset($_POST['form4'])) {
	// updating the database
	$statement = $pdo->prepare("UPDATE tbl_settings SET receive_email=?, receive_email_subject=?,receive_email_thank_you_message=? WHERE id=1");
	$statement->execute(array($_POST['receive_email'],$_POST['receive_email_subject'],$_POST['receive_email_thank_you_message']));

	$success_message = 'As informações de configurações do formulário de contato foram atualizadas com sucesso.';
}


if(isset($_POST['form5'])) {
	// updating the database
	$statement = $pdo->prepare("UPDATE tbl_settings SET total_recent_news_footer=?, total_popular_news_footer=?, total_recent_news_sidebar=?, total_popular_news_sidebar=?, total_recent_news_home_page=? WHERE id=1");
	$statement->execute(array($_POST['total_recent_news_footer'],$_POST['total_popular_news_footer'],$_POST['total_recent_news_sidebar'],$_POST['total_popular_news_sidebar'],$_POST['total_recent_news_home_page']));

	$success_message = 'As configurações da barra lateral foram atualizadas com sucesso.';
}

if(isset($_POST['form6'])) {
	// updating the database
	$statement = $pdo->prepare("UPDATE tbl_settings SET meta_title_home=?, meta_keyword_home=?, meta_description_home=? WHERE id=1");
	$statement->execute(array($_POST['meta_title_home'],$_POST['meta_keyword_home'],$_POST['meta_description_home']));

	$success_message = 'As configurações do Home Meta foram atualizadas com sucesso.';
}

if(isset($_POST['form7'])) {

	if(isset($_POST['home_status_service'])) {
		$home_status_service = 1;
	} else {
		$home_status_service = 0;
	}

	if(isset($_POST['home_status_department'])) {
		$home_status_department = 1;
	} else {
		$home_status_department = 0;
	}

	if(isset($_POST['home_status_doctor'])) {
		$home_status_doctor = 1;
	} else {
		$home_status_doctor = 0;
	}

	if(isset($_POST['home_status_pricing'])) {
		$home_status_pricing = 1;
	} else {
		$home_status_pricing = 0;
	}

	if(isset($_POST['home_status_testimonial'])) {
		$home_status_testimonial = 1;
	} else {
		$home_status_testimonial = 0;
	}

	if(isset($_POST['home_status_news'])) {
		$home_status_news = 1;
	} else {
		$home_status_news = 0;
	}

	if(isset($_POST['home_status_partner'])) {
		$home_status_partner = 1;
	} else {
		$home_status_partner = 0;
	}


	// updating the database
	$statement = $pdo->prepare("UPDATE 
	                           
	                           tbl_settings 
	                           
	                           SET 
	                           home_title_service=?, 
	                           home_subtitle_service=?,
	                           home_status_service=?, 
	                           home_title_department=?,
	                           home_subtitle_department=?,
	                           home_status_department=?, 
	                           home_title_doctor=?,
	                           home_subtitle_doctor=?,
	                           home_status_doctor=?, 
	                           home_title_pricing=?,
	                           home_subtitle_pricing=?,
	                           home_status_pricing=?, 
	                           home_title_testimonial=?,
	                           home_subtitle_testimonial=?,
	                           home_status_testimonial=?, 
	                           home_title_news=?,
	                           home_subtitle_news=?,
	                           home_status_news=?, 
	                           home_title_partner=?,
	                           home_subtitle_partner=?,
	                           home_status_partner=?

	                           WHERE id=1");
	$statement->execute(array(
	                          $_POST['home_title_service'],
	                          $_POST['home_subtitle_service'],
	                          $home_status_service,
	                          $_POST['home_title_department'],
	                          $_POST['home_subtitle_department'],
	                          $home_status_department,
	                          $_POST['home_title_doctor'],
	                          $_POST['home_subtitle_doctor'],
	                          $home_status_doctor,
	                          $_POST['home_title_pricing'],
	                          $_POST['home_subtitle_pricing'],
	                          $home_status_pricing,
	                          $_POST['home_title_testimonial'],
	                          $_POST['home_subtitle_testimonial'],
	                          $home_status_testimonial,
	                          $_POST['home_title_news'],
	                          $_POST['home_subtitle_news'],
	                          $home_status_news,
	                          $_POST['home_title_partner'],
	                          $_POST['home_subtitle_partner'],
	                          $home_status_partner,
	                    ));

	$success_message = 'Início Título e Subtítulo são atualizados com sucesso.';
}

if(isset($_POST['form8'])) {
	// updating the database
	$statement = $pdo->prepare("UPDATE tbl_settings SET color=? WHERE id=1");
	$statement->execute(array($_POST['color']));

	$success_message = 'A cor foi atualizada com sucesso.';
}

if(isset($_POST['form_recaptcha'])) {
	$recaptcha_status = isset($_POST['recaptcha_status']) ? 1 : 0;
	$recaptcha_site_key = trim($_POST['recaptcha_site_key'] ?? '');
	$recaptcha_secret_key = trim($_POST['recaptcha_secret_key'] ?? '');

	$statement = $pdo->prepare("UPDATE tbl_settings SET recaptcha_status=?, recaptcha_site_key=?, recaptcha_secret_key=? WHERE id=1");
	$statement->execute(array($recaptcha_status, $recaptcha_site_key, $recaptcha_secret_key));

	$success_message = 'As configurações do Google reCAPTCHA foram atualizadas com sucesso.';
}
?>

<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold">Definições</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Definições</li>
				</ol>
			</div>
		</div>
	</div>
</div>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
foreach ($result as $row) {
	$logo                            = $row['logo'];
	$favicon                         = $row['favicon'];
	$footer_about                    = $row['footer_about'];
	$footer_copyright                = $row['footer_copyright'];
	$contact_address                 = $row['contact_address'];
	$contact_email                   = $row['contact_email'];
	$contact_phone                   = $row['contact_phone'];
	$contact_fax                     = $row['contact_fax'];
	$contact_map_iframe              = $row['contact_map_iframe'];
	$receive_email                   = $row['receive_email'];
	$receive_email_subject           = $row['receive_email_subject'];
	$receive_email_thank_you_message = $row['receive_email_thank_you_message'];
	$total_recent_news_footer        = $row['total_recent_news_footer'];
	$total_popular_news_footer       = $row['total_popular_news_footer'];
	$total_recent_news_sidebar       = $row['total_recent_news_sidebar'];
	$total_popular_news_sidebar      = $row['total_popular_news_sidebar'];
	$total_recent_news_home_page     = $row['total_recent_news_home_page'];
	$meta_title_home                 = $row['meta_title_home'];
	$meta_keyword_home               = $row['meta_keyword_home'];
	$meta_description_home           = $row['meta_description_home'];
	$home_title_service              = $row['home_title_service'];
	$home_subtitle_service           = $row['home_subtitle_service'];
	$home_status_service             = $row['home_status_service'];
	$home_title_department           = $row['home_title_department'];
	$home_subtitle_department        = $row['home_subtitle_department'];
	$home_status_department          = $row['home_status_department'];
	$home_title_doctor               = $row['home_title_doctor'];
	$home_subtitle_doctor            = $row['home_subtitle_doctor'];
	$home_status_doctor              = $row['home_status_doctor'];
	$home_title_pricing              = $row['home_title_pricing'];
	$home_subtitle_pricing           = $row['home_subtitle_pricing'];
	$home_status_pricing             = $row['home_status_pricing'];
	$home_title_testimonial          = $row['home_title_testimonial'];
	$home_subtitle_testimonial       = $row['home_subtitle_testimonial'];
	$home_status_testimonial         = $row['home_status_testimonial'];
	$home_title_news                 = $row['home_title_news'];
	$home_subtitle_news              = $row['home_subtitle_news'];
	$home_status_news                = $row['home_status_news'];
	$home_title_partner              = $row['home_title_partner'];
	$home_subtitle_partner           = $row['home_subtitle_partner'];
	$home_status_partner             = $row['home_status_partner'];
	$color                           = $row['color'];
	$preloader                       = $row['preloader'];
	$recaptcha_status                = $row['recaptcha_status'] ?? 0;
	$recaptcha_site_key              = $row['recaptcha_site_key'] ?? '';
	$recaptcha_secret_key            = $row['recaptcha_secret_key'] ?? '';
}

if(isset($_POST['BtnPreloader'])){
    $update = $pdo->prepare("UPDATE tbl_settings SET preloader=? WHERE id=1");
	$update->execute(array($_POST['preloader']));
	    	
	if($preloader == 1){
	    $success_message = 'O <b>Preloader</b> foi Ativado com Sucesso.';
	}else{
	    $success_message2 = 'O <b>Preloader</b> foi Desligado com Sucesso.';
	}
	unset($_POST['BtnPreloader']);
}
?>


<div class="app-content">
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<?php if(!empty($error_message)): ?>
			<div class="callout callout-danger">
			
			<p>
			<?php echo $error_message; unset($error_message); ?>
			</p>
			</div>
			<?php endif; ?>

			<?php if(!empty($success_message)): ?>
			<div class="callout callout-success">
    			<p>
    			    <?php echo $success_message; unset($success_message); ?>
    			</p>
			</div>
			<?php endif; ?>
			
			<?php if(!empty($success_message2)): ?>
			<div class="callout callout-warning">
    			<p>
    			    <?php echo $success_message2; unset($success_message2); ?>
    			</p>
			</div>
			<?php endif; ?>
		</div>
	</div>

</div>
</div>

<div class="app-content">
<div class="container-fluid">

	<div class="row">
		<div class="col-md-12">
							
				<div class="nav-tabs-custom mb-4">
					<ul class="nav nav-tabs">
						<li class="nav-item"><a class="nav-link active" href="#tab_1" data-bs-toggle="tab">Logo</a></li>
						<li class="nav-item"><a class="nav-link" href="#tab_2" data-bs-toggle="tab">Favicon</a></li>
						<li class="nav-item"><a class="nav-link" href="#tab_3" data-bs-toggle="tab">Conteúdo Geral</a></li>
						<li class="nav-item"><a class="nav-link" href="#tab_4" data-bs-toggle="tab">Configurações de e-mail</a></li>
						<li class="nav-item"><a class="nav-link" href="#tab_5" data-bs-toggle="tab">Notícia</a></li>
						<li class="nav-item"><a class="nav-link" href="#tab_6" data-bs-toggle="tab">Página inicial Meta</a></li>
						<li class="nav-item"><a class="nav-link" href="#tab_7" data-bs-toggle="tab">Título e legendas da página inicial</a></li>
						<li class="nav-item"><a class="nav-link" href="#tab_8" data-bs-toggle="tab">Cor</a></li>
						<li class="nav-item"><a class="nav-link" href="#tab_9" data-bs-toggle="tab">Preloader</a></li>
						<li class="nav-item"><a class="nav-link" href="#tab_10" data-bs-toggle="tab"><i class="bi bi-shield-check text-primary me-1"></i> reCAPTCHA</a></li>
					</ul>
					<div class="tab-content">
          				<div class="tab-pane active" id="tab_1">


          					<form action="" method="post" enctype="multipart/form-data">
          					<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
							            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto Atual</label>
							            <div class="col-12 col-sm-9 col-lg-6">
							                <img src="../assets/uploads/<?php echo $logo; ?>" class="existing-photo" style="height:80px;">
							            </div>
							        </div>
									<div class="row mb-3">
							            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nova Foto</label>
							            <div class="col-12 col-sm-9 col-lg-6">
							                <input type="file" name="photo_logo">
							            </div>
							        </div>
							        <div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
											<button type="submit" class="btn btn-primary px-4" name="form1"><i class="bi bi-check-lg me-1"></i>Atualizar Logo</button>
										</div>
									</div>
								</div>
							</div>
							</form>

							


          				</div>
          				<div class="tab-pane" id="tab_2">

          					<form action="" method="post" enctype="multipart/form-data">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
							            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Foto Atual</label>
							            <div class="col-12 col-sm-9 col-lg-6">
							                <img src="../assets/uploads/<?php echo $favicon; ?>" class="existing-photo" style="height:40px;">
							            </div>
							        </div>
									<div class="row mb-3">
							            <label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nova Foto</label>
							            <div class="col-12 col-sm-9 col-lg-6">
							                <input type="file" name="photo_favicon">
							            </div>
							        </div>
							        <div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
											<button type="submit" class="btn btn-primary px-4" name="form2"><i class="bi bi-check-lg me-1"></i>Atualizar Favicon</button>
										</div>
									</div>
								</div>
							</div>
							</form>


          				</div>
          				<div class="tab-pane" id="tab_3">

							<form action="" method="post">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Rodapé - Sobre Nós</label>
										<div class="col-12 col-sm-9 col-lg-9">
											<textarea class="form-control" name="footer_about" id="1"><?php echo $footer_about; ?></textarea>
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Rodapé - Copyright</label>
										<div class="col-12 col-sm-9 col-lg-9">
											<input class="form-control" type="text" name="footer_copyright" value="<?php echo $footer_copyright; ?>">
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
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Endereço do Estabelecimento</label>
										<div class="col-12 col-sm-9 col-lg-6">
											<textarea class="form-control" name="contact_address" style="height:140px;"><?php echo $contact_address; ?></textarea>
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Email de Contato</label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="contact_email" value="<?php echo $contact_email; ?>">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Telefone de Contato</label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="contact_phone" value="<?php echo $contact_phone; ?>">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">FAX para Contato</label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="contact_fax" value="<?php echo $contact_fax; ?>">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Mapa (iFrame)</label>
										<div class="col-12 col-sm-9 col-lg-9">
											<textarea class="form-control" name="contact_map_iframe" style="height:200px;"><?php echo $contact_map_iframe; ?></textarea>
										</div>
									</div>
									<div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
											<button type="submit" class="btn btn-primary px-4" name="form3"><i class="bi bi-check-lg me-1"></i>Atualizar</button>
										</div>
									</div>
								</div>
							</div>
							</form>


          				</div>

          				<div class="tab-pane" id="tab_4">

          					<form action="" method="post">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Email <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="receive_email" value="<?php echo $receive_email; ?>">
										</div>
									</div>									
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Assunto do Email <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-9">
											<input type="text" class="form-control" name="receive_email_subject" value="<?php echo $receive_email_subject; ?>">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Mensagem de Agradecimento <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-9">
											<textarea class="form-control" name="receive_email_thank_you_message"><?php echo $receive_email_thank_you_message; ?></textarea>
										</div>
									</div>
									<div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
											<button type="submit" class="btn btn-primary px-4" name="form4"><i class="bi bi-check-lg me-1"></i>Atualizar</button>
										</div>
									</div>
								</div>
							</div>
							</form>


          				</div>

          				<div class="tab-pane" id="tab_5">

          					<form action="" method="post">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Rodapé (Quantas notícias recentes?) <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="total_recent_news_footer" value="<?php echo $total_recent_news_footer; ?>">
										</div>
									</div>		
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Rodapé (Quantas notícias populares?) <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="total_popular_news_footer" value="<?php echo $total_popular_news_footer; ?>">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Barra lateral (Quantas notícias recentes?) <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="total_recent_news_sidebar" value="<?php echo $total_recent_news_sidebar; ?>">
										</div>
									</div>		
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Barra lateral (Quantas notícias populares?) <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="total_popular_news_sidebar" value="<?php echo $total_popular_news_sidebar; ?>">
										</div>
									</div>
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Home Page (Quantas notícias recentes?) <span class="text-danger">*</span></label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" class="form-control" name="total_recent_news_home_page" value="<?php echo $total_recent_news_home_page; ?>">
										</div>
									</div>
									<div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
											<button type="submit" class="btn btn-primary px-4" name="form5"><i class="bi bi-check-lg me-1"></i>Atualizar</button>
										</div>
									</div>
								</div>
							</div>
							</form>


          				</div>


          				<div class="tab-pane" id="tab_6">

          					<form action="" method="post">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Título</label>
										<div class="col-12 col-sm-9 col-lg-9">
											<input type="text" name="meta_title_home" class="form-control" value="<?php echo $meta_title_home ?>">
										</div>
									</div>		
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Palavras-chave</label>
										<div class="col-12 col-sm-9 col-lg-9">
											<textarea class="form-control" name="meta_keyword_home" style="height:100px;"><?php echo $meta_keyword_home ?></textarea>
										</div>
									</div>	
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Descrição</label>
										<div class="col-12 col-sm-9 col-lg-9">
											<textarea class="form-control" name="meta_description_home" style="height:200px;"><?php echo $meta_description_home ?></textarea>
										</div>
									</div>	
									<div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
											<button type="submit" class="btn btn-primary px-4" name="form6"><i class="bi bi-check-lg me-1"></i>Atualizar</button>
										</div>
									</div>
								</div>
							</div>
							</form>


          				</div>



          				<div class="tab-pane" id="tab_7">

          					<form action="" method="post">
							
									
									
							<div class="box-group" id="accordion">
								
								<!-- Service Section -->
								<div class="panel box box-primary">
									<div class="card-header">
										<h4 class="card-title fw-semibold">
											<a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapse1">
												Serviço
											</a>
										</h4>
									</div>
									<div id="collapse1" class="panel-collapse collapse">
										<div class="card-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_service" class="form-control" value="<?php echo $home_title_service; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_service" class="form-control" value="<?php echo $home_subtitle_service; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Mostrar na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="checkbox" name="home_status_service" value="<?php echo $home_status_service; ?>" <?php if($home_status_service == 1) {echo 'checked';} ?>>
												</div>
											</div>											
										</div>
									</div>
								</div>
								<!-- // Service Section -->


								<!-- Department Section -->
								<div class="panel box box-primary">
									<div class="card-header">
										<h4 class="card-title fw-semibold">
											<a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapse2">
												Departamento
											</a>
										</h4>
									</div>
									<div id="collapse2" class="panel-collapse collapse">
										<div class="card-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_department" class="form-control" value="<?php echo $home_title_department; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_department" class="form-control" value="<?php echo $home_subtitle_department; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Mostrar na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="checkbox" name="home_status_department" value="<?php echo $home_status_department; ?>" <?php if($home_status_department == 1) {echo 'checked';} ?>>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- // Department Section -->



								<!-- Doctor Section -->
								<div class="panel box box-primary">
									<div class="card-header">
										<h4 class="card-title fw-semibold">
											<a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapse3">
												Doutor
											</a>
										</h4>
									</div>
									<div id="collapse3" class="panel-collapse collapse">
										<div class="card-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_doctor" class="form-control" value="<?php echo $home_title_doctor; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_doctor" class="form-control" value="<?php echo $home_subtitle_doctor; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Mostrar na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="checkbox" name="home_status_doctor" value="<?php echo $home_status_doctor; ?>" <?php if($home_status_doctor == 1) {echo 'checked';} ?>>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- // Doctor Section -->



								<!-- Pricing Section -->
								<div class="panel box box-primary">
									<div class="card-header">
										<h4 class="card-title fw-semibold">
											<a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapse4">
												Preços
											</a>
										</h4>
									</div>
									<div id="collapse4" class="panel-collapse collapse">
										<div class="card-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_pricing" class="form-control" value="<?php echo $home_title_pricing; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_pricing" class="form-control" value="<?php echo $home_subtitle_pricing; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Mostrar na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="checkbox" name="home_status_pricing" value="<?php echo $home_status_pricing; ?>" <?php if($home_status_pricing == 1) {echo 'checked';} ?>>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- // Pricing Section -->



								<!-- Testimonial Section -->
								<div class="panel box box-primary">
									<div class="card-header">
										<h4 class="card-title fw-semibold">
											<a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapse5">
												Testemunho
											</a>
										</h4>
									</div>
									<div id="collapse5" class="panel-collapse collapse">
										<div class="card-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_testimonial" class="form-control" value="<?php echo $home_title_testimonial; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_testimonial" class="form-control" value="<?php echo $home_subtitle_testimonial; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Mostrar na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="checkbox" name="home_status_testimonial" value="<?php echo $home_status_testimonial; ?>" <?php if($home_status_testimonial == 1) {echo 'checked';} ?>>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- // Testimonial Section -->



								<!-- Latest News Section -->
								<div class="panel box box-primary">
									<div class="card-header">
										<h4 class="card-title fw-semibold">
											<a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapse6">
												Últimas notícias
											</a>
										</h4>
									</div>
									<div id="collapse6" class="panel-collapse collapse">
										<div class="card-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_news" class="form-control" value="<?php echo $home_title_news; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_news" class="form-control" value="<?php echo $home_subtitle_news; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Mostrar na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="checkbox" name="home_status_news" value="<?php echo $home_status_news; ?>" <?php if($home_status_news == 1) {echo 'checked';} ?>>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- // Latest News Section -->



								<!-- Partner Section -->
								<div class="panel box box-primary">
									<div class="card-header">
										<h4 class="card-title fw-semibold">
											<a data-bs-toggle="collapse" data-bs-parent="#accordion" href="#collapse7">
												Parceiro
											</a>
										</h4>
									</div>
									<div id="collapse7" class="panel-collapse collapse">
										<div class="card-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_partner" class="form-control" value="<?php echo $home_title_partner; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_partner" class="form-control" value="<?php echo $home_subtitle_partner; ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Mostrar na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="checkbox" name="home_status_partner" value="<?php echo $home_status_partner; ?>" <?php if($home_status_partner == 1) {echo 'checked';} ?>>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- // Partner Section -->

								
							</div>


							<div class="row mb-3">
								<div class="col-sm-6">
									<button type="submit" class="btn btn-primary px-4" name="form7"><i class="bi bi-check-lg me-1"></i>Atualizar</button>
								</div>
							</div>
							

							</form>


          				</div>




          				<div class="tab-pane" id="tab_8">
          					<form action="" method="post">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Selecione a cor</label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="text" name="color" class="form-control jscolor" value="<?php echo $color; ?>">
										</div>
									</div>
									<div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
											<button type="submit" class="btn btn-primary px-4" name="form8"><i class="bi bi-check-lg me-1"></i>Atualizar</button>
										</div>
									</div>
								</div>
							</div>
							</form>
          				</div>
          				
          				<div class="tab-pane" id="tab_9">
          					<form action="" method="post">
							<div class="card card-outline card-primary shadow-sm mb-4">
								<div class="card-body">
									<div class="row mb-3">
										<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Ativar Preloader?</label>
										<div class="col-12 col-sm-9 col-lg-6">
											<input type="radio" name="preloader" <?php if($preloader == 1){echo 'checked="checked"';}?> value="1"> Ativar <br>
											<input type="radio" name="preloader" <?php if($preloader == 0){echo 'checked="checked"';}?> value="0"> Desativar
										</div>
									</div>
									<div class="row mb-3">
										<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
										<div class="col-12 col-sm-9 col-lg-6">
											<button type="submit" class="btn btn-success" name="BtnPreloader">Atualizar</button>
										</div>
									</div>
								</div>
							</div>
							</form>
          				</div>
          				
          				<div class="tab-pane" id="tab_10">
          					<form action="" method="post">
							<div class="card card-outline card-primary shadow-sm border-0 my-3">
								<div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between">
									<h5 class="card-title fw-bold text-primary mb-0">
										<i class="bi bi-shield-lock-fill me-2"></i> Proteção contra Robôs & Spam (Google reCAPTCHA v2 / v3)
									</h5>
									<span class="badge <?= ($recaptcha_status == 1) ? 'bg-success' : 'bg-secondary'; ?>">
										<?= ($recaptcha_status == 1) ? 'Ativo no Site' : 'Desativado'; ?>
									</span>
								</div>
								<div class="card-body p-4">
									<div class="alert alert-info d-flex align-items-center gap-3 mb-4 rounded-3 border-0 shadow-sm" style="background: rgba(13, 202, 240, 0.12); color: #055160;">
										<i class="bi bi-info-circle-fill fs-3 text-info"></i>
										<div class="small">
											O <strong>Google reCAPTCHA</strong> previne ataques de robôs e envio em massa de spam em formulários públicos (Contato, Mensagens, etc).<br>
											Para obter gratuitamente suas chaves, acesse o console oficial do <a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener noreferrer" class="fw-bold text-decoration-underline text-info-emphasis">Google reCAPTCHA Admin</a>.
										</div>
									</div>

									<div class="row mb-4 align-items-center">
										<label class="col-sm-3 col-form-label fw-bold">Status do reCAPTCHA:</label>
										<div class="col-sm-9">
											<div class="form-check form-switch form-switch-md">
												<input class="form-check-input" type="checkbox" name="recaptcha_status" id="recaptcha_status" value="1" <?= ($recaptcha_status == 1) ? 'checked' : ''; ?>>
												<label class="form-check-label fw-semibold" for="recaptcha_status">
													<?= ($recaptcha_status == 1) ? '<span class="text-success fw-bold">Ativado nos Formulários</span>' : '<span class="text-muted">Desativado</span>'; ?>
												</label>
											</div>
											<small class="text-muted d-block mt-1">Ao desativar, os formulários públicos funcionarão normalmente sem exigir desafio de captcha.</small>
										</div>
									</div>

									<div class="row mb-3">
										<label for="recaptcha_site_key" class="col-sm-3 col-form-label fw-bold">Chave do Site (Site Key):</label>
										<div class="col-sm-9">
											<div class="input-group">
												<span class="input-group-text bg-body-secondary"><i class="bi bi-key-fill text-primary"></i></span>
												<input type="text" name="recaptcha_site_key" id="recaptcha_site_key" class="form-control" value="<?= htmlspecialchars($recaptcha_site_key); ?>" placeholder="Ex: 6LdXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX">
											</div>
											<small class="text-muted">Chave pública utilizada para carregar e renderizar o widget no site público.</small>
										</div>
									</div>

									<div class="row mb-4">
										<label for="recaptcha_secret_key" class="col-sm-3 col-form-label fw-bold">Chave Secreta (Secret Key):</label>
										<div class="col-sm-9">
											<div class="input-group">
												<span class="input-group-text bg-body-secondary"><i class="bi bi-shield-fill-check text-success"></i></span>
												<input type="password" name="recaptcha_secret_key" id="recaptcha_secret_key" class="form-control" value="<?= htmlspecialchars($recaptcha_secret_key); ?>" placeholder="Ex: 6LdYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY">
												<button class="btn btn-outline-secondary" type="button" onclick="const f=document.getElementById('recaptcha_secret_key'); f.type = (f.type==='password')?'text':'password'; this.querySelector('i').classList.toggle('bi-eye'); this.querySelector('i').classList.toggle('bi-eye-slash');">
													<i class="bi bi-eye"></i>
												</button>
											</div>
											<small class="text-muted">Chave privada utilizada pelo backend para validar as respostas com a API do Google.</small>
										</div>
									</div>

									<div class="row">
										<div class="col-sm-9 offset-sm-3">
											<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" name="form_recaptcha">
												<i class="bi bi-check-circle-fill"></i> Salvar Configurações do reCAPTCHA
											</button>
										</div>
									</div>
								</div>
							</div>
							</form>
          				</div>
				</div>

				

			</form>
		</div>
	</div>


</div>
</div>
</div>

<?php require_once('footer.php'); ?>