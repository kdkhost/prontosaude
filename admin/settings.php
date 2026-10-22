<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';
$active_tab = 'tab_1';

if(isset($_POST['form1'])) {
	$active_tab = 'tab_1';
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
	$active_tab = 'tab_2';
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
	$active_tab = 'tab_3';
	// updating the database
	$statement = $pdo->prepare("UPDATE tbl_settings SET footer_about=?, footer_copyright=?, contact_address=?, contact_email=?, contact_phone=?, contact_fax=?, contact_map_iframe=? WHERE id=1");
	$statement->execute(array($_POST['footer_about'],$_POST['footer_copyright'],$_POST['contact_address'],$_POST['contact_email'],$_POST['contact_phone'],$_POST['contact_fax'],$_POST['contact_map_iframe']));

	$success_message = 'As configurações gerais de conteúdo foram atualizadas com sucesso.';
    
}

if(isset($_POST['form4'])) {
	$active_tab = 'tab_4';
	// updating the database
	$statement = $pdo->prepare("UPDATE tbl_settings SET receive_email=?, receive_email_subject=?,receive_email_thank_you_message=? WHERE id=1");
	$statement->execute(array($_POST['receive_email'],$_POST['receive_email_subject'],$_POST['receive_email_thank_you_message']));

	$success_message = 'As informações de configurações do formulário de contato foram atualizadas com sucesso.';
}


if(isset($_POST['form5'])) {
	$active_tab = 'tab_5';
	// updating the database
	$statement = $pdo->prepare("UPDATE tbl_settings SET total_recent_news_footer=?, total_popular_news_footer=?, total_recent_news_sidebar=?, total_popular_news_sidebar=?, total_recent_news_home_page=? WHERE id=1");
	$statement->execute(array($_POST['total_recent_news_footer'],$_POST['total_popular_news_footer'],$_POST['total_recent_news_sidebar'],$_POST['total_popular_news_sidebar'],$_POST['total_recent_news_home_page']));

	$success_message = 'As configurações da barra lateral foram atualizadas com sucesso.';
}

if(isset($_POST['form6'])) {
	$active_tab = 'tab_6';
	// updating the database
	$statement = $pdo->prepare("UPDATE tbl_settings SET meta_title_home=?, meta_keyword_home=?, meta_description_home=? WHERE id=1");
	$statement->execute(array($_POST['meta_title_home'],$_POST['meta_keyword_home'],$_POST['meta_description_home']));

	$success_message = 'As configurações do Home Meta foram atualizadas com sucesso.';
}

if(isset($_POST['form7'])) {
	$active_tab = 'tab_7';

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
	$active_tab = 'tab_8';
	// updating the database
	$statement = $pdo->prepare("UPDATE tbl_settings SET color=? WHERE id=1");
	$statement->execute(array($_POST['color']));

	$success_message = 'A cor foi atualizada com sucesso.';
}

if(isset($_POST['form_recaptcha'])) {
	$active_tab = 'tab_10';
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
	$active_tab = 'tab_9';
	$preloader = $_POST['preloader'];
    $update = $pdo->prepare("UPDATE tbl_settings SET preloader=? WHERE id=1");
	$update->execute(array($preloader));
	    	
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
		
		<?php if(!empty($success_message2)): ?>
		<div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
			<div class="d-flex align-items-center">
				<i class="bi bi-exclamation-circle-fill fs-5 me-2"></i>
				<div><?php echo $success_message2; unset($success_message2); ?></div>
			</div>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
		<?php endif; ?>

		<div class="card card-primary card-outline card-outline-tabs shadow-sm mb-4">
			<div class="card-header p-0 border-bottom-0">
				<ul class="nav nav-tabs" id="settingsTabs" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_1') ? 'active' : ''; ?>" id="tab1-btn" data-bs-toggle="pill" data-bs-target="#tab_1" type="button" role="tab" aria-controls="tab_1" aria-selected="<?= ($active_tab == 'tab_1') ? 'true' : 'false'; ?>">
							<i class="bi bi-image me-1"></i> Logo
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_2') ? 'active' : ''; ?>" id="tab2-btn" data-bs-toggle="pill" data-bs-target="#tab_2" type="button" role="tab" aria-controls="tab_2" aria-selected="<?= ($active_tab == 'tab_2') ? 'true' : 'false'; ?>">
							<i class="bi bi-star me-1"></i> Favicon
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_3') ? 'active' : ''; ?>" id="tab3-btn" data-bs-toggle="pill" data-bs-target="#tab_3" type="button" role="tab" aria-controls="tab_3" aria-selected="<?= ($active_tab == 'tab_3') ? 'true' : 'false'; ?>">
							<i class="bi bi-sliders me-1"></i> Conteúdo Geral
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_4') ? 'active' : ''; ?>" id="tab4-btn" data-bs-toggle="pill" data-bs-target="#tab_4" type="button" role="tab" aria-controls="tab_4" aria-selected="<?= ($active_tab == 'tab_4') ? 'true' : 'false'; ?>">
							<i class="bi bi-envelope me-1"></i> E-mail
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_5') ? 'active' : ''; ?>" id="tab5-btn" data-bs-toggle="pill" data-bs-target="#tab_5" type="button" role="tab" aria-controls="tab_5" aria-selected="<?= ($active_tab == 'tab_5') ? 'true' : 'false'; ?>">
							<i class="bi bi-newspaper me-1"></i> Notícias
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_6') ? 'active' : ''; ?>" id="tab6-btn" data-bs-toggle="pill" data-bs-target="#tab_6" type="button" role="tab" aria-controls="tab_6" aria-selected="<?= ($active_tab == 'tab_6') ? 'true' : 'false'; ?>">
							<i class="bi bi-search me-1"></i> SEO / Meta
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_7') ? 'active' : ''; ?>" id="tab7-btn" data-bs-toggle="pill" data-bs-target="#tab_7" type="button" role="tab" aria-controls="tab_7" aria-selected="<?= ($active_tab == 'tab_7') ? 'true' : 'false'; ?>">
							<i class="bi bi-layout-text-window-reverse me-1"></i> Seções Home
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_8') ? 'active' : ''; ?>" id="tab8-btn" data-bs-toggle="pill" data-bs-target="#tab_8" type="button" role="tab" aria-controls="tab_8" aria-selected="<?= ($active_tab == 'tab_8') ? 'true' : 'false'; ?>">
							<i class="bi bi-palette me-1"></i> Cor
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_9') ? 'active' : ''; ?>" id="tab9-btn" data-bs-toggle="pill" data-bs-target="#tab_9" type="button" role="tab" aria-controls="tab_9" aria-selected="<?= ($active_tab == 'tab_9') ? 'true' : 'false'; ?>">
							<i class="bi bi-hourglass-split me-1"></i> Preloader
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab == 'tab_10') ? 'active' : ''; ?>" id="tab10-btn" data-bs-toggle="pill" data-bs-target="#tab_10" type="button" role="tab" aria-controls="tab_10" aria-selected="<?= ($active_tab == 'tab_10') ? 'true' : 'false'; ?>">
							<i class="bi bi-shield-check text-primary me-1"></i> reCAPTCHA
						</button>
					</li>
				</ul>
			</div>

			<div class="card-body p-4">
				<div class="tab-content" id="settingsTabsContent">

					<!-- Aba 1: Logo -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_1') ? 'show active' : ''; ?>" id="tab_1" role="tabpanel" aria-labelledby="tab1-btn">
						<form action="" method="post" enctype="multipart/form-data">
							<div class="row mb-3 align-items-center">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Logo Atual</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<div class="p-2 border rounded bg-light d-inline-block">
										<img src="../assets/uploads/<?php echo $logo; ?>" class="existing-photo" style="max-height:80px; object-fit:contain;">
									</div>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Nova Foto</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="file" name="photo_logo" class="form-control" accept="image/*">
									<small class="text-muted">Formatos aceitos: JPG, JPEG, PNG, GIF, WEBP</small>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="form1">
										<i class="bi bi-check-lg me-1"></i> Atualizar Logo
									</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Aba 2: Favicon -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_2') ? 'show active' : ''; ?>" id="tab_2" role="tabpanel" aria-labelledby="tab2-btn">
						<form action="" method="post" enctype="multipart/form-data">
							<div class="row mb-3 align-items-center">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Favicon Atual</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<div class="p-2 border rounded bg-light d-inline-block">
										<img src="../assets/uploads/<?php echo $favicon; ?>" class="existing-photo" style="height:40px; width:40px; object-fit:contain;">
									</div>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Novo Favicon</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="file" name="photo_favicon" class="form-control" accept="image/*">
									<small class="text-muted">Recomendado tamanho 32x32 ou 64x64 px (PNG ou ICO).</small>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="form2">
										<i class="bi bi-check-lg me-1"></i> Atualizar Favicon
									</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Aba 3: Conteúdo Geral -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_3') ? 'show active' : ''; ?>" id="tab_3" role="tabpanel" aria-labelledby="tab3-btn">
						<form action="" method="post">
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Rodapé - Sobre Nós</label>
								<div class="col-12 col-sm-9 col-lg-9">
									<textarea class="form-control" name="footer_about" rows="4"><?php echo $footer_about; ?></textarea>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Rodapé - Copyright</label>
								<div class="col-12 col-sm-9 col-lg-9">
									<input class="form-control" type="text" name="footer_copyright" value="<?php echo htmlspecialchars($footer_copyright); ?>">
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
									<textarea class="form-control" name="contact_address" rows="3"><?php echo $contact_address; ?></textarea>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Email de Contato</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="email" class="form-control" name="contact_email" value="<?php echo htmlspecialchars($contact_email); ?>">
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Telefone de Contato</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="text" class="form-control" name="contact_phone" value="<?php echo htmlspecialchars($contact_phone); ?>">
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">FAX para Contato</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="text" class="form-control" name="contact_fax" value="<?php echo htmlspecialchars($contact_fax); ?>">
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Mapa (iFrame)</label>
								<div class="col-12 col-sm-9 col-lg-9">
									<textarea class="form-control font-monospace small" name="contact_map_iframe" rows="4"><?php echo $contact_map_iframe; ?></textarea>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="form3">
										<i class="bi bi-check-lg me-1"></i> Atualizar Conteúdo
									</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Aba 4: Configurações de e-mail -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_4') ? 'show active' : ''; ?>" id="tab_4" role="tabpanel" aria-labelledby="tab4-btn">
						<form action="" method="post">
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Email de Recebimento <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="email" class="form-control" name="receive_email" value="<?php echo htmlspecialchars($receive_email); ?>" required>
								</div>
							</div>									
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Assunto do Email <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-9">
									<input type="text" class="form-control" name="receive_email_subject" value="<?php echo htmlspecialchars($receive_email_subject); ?>" required>
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Mensagem de Agradecimento <span class="text-danger">*</span></label>
								<div class="col-12 col-sm-9 col-lg-9">
									<textarea class="form-control" name="receive_email_thank_you_message" rows="3" required><?php echo $receive_email_thank_you_message; ?></textarea>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="form4">
										<i class="bi bi-check-lg me-1"></i> Atualizar E-mail
									</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Aba 5: Notícias -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_5') ? 'show active' : ''; ?>" id="tab_5" role="tabpanel" aria-labelledby="tab5-btn">
						<form action="" method="post">
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Rodapé (Notícias Recentes)</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="number" class="form-control" name="total_recent_news_footer" value="<?php echo $total_recent_news_footer; ?>" min="0">
								</div>
							</div>		
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Rodapé (Notícias Populares)</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="number" class="form-control" name="total_popular_news_footer" value="<?php echo $total_popular_news_footer; ?>" min="0">
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Barra Lateral (Recentes)</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="number" class="form-control" name="total_recent_news_sidebar" value="<?php echo $total_recent_news_sidebar; ?>" min="0">
								</div>
							</div>		
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Barra Lateral (Populares)</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="number" class="form-control" name="total_popular_news_sidebar" value="<?php echo $total_popular_news_sidebar; ?>" min="0">
								</div>
							</div>
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Página Inicial (Recentes)</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<input type="number" class="form-control" name="total_recent_news_home_page" value="<?php echo $total_recent_news_home_page; ?>" min="0">
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="form5">
										<i class="bi bi-check-lg me-1"></i> Atualizar Notícias
									</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Aba 6: SEO / Meta -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_6') ? 'show active' : ''; ?>" id="tab_6" role="tabpanel" aria-labelledby="tab6-btn">
						<form action="" method="post">
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Título</label>
								<div class="col-12 col-sm-9 col-lg-9">
									<input type="text" name="meta_title_home" class="form-control" value="<?php echo htmlspecialchars($meta_title_home); ?>">
								</div>
							</div>		
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Palavras-chave (Keywords)</label>
								<div class="col-12 col-sm-9 col-lg-9">
									<textarea class="form-control" name="meta_keyword_home" rows="3"><?php echo $meta_keyword_home; ?></textarea>
								</div>
							</div>	
							<div class="row mb-3">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Meta Descrição</label>
								<div class="col-12 col-sm-9 col-lg-9">
									<textarea class="form-control" name="meta_description_home" rows="4"><?php echo $meta_description_home; ?></textarea>
								</div>
							</div>	
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="form6">
										<i class="bi bi-check-lg me-1"></i> Atualizar SEO
									</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Aba 7: Seções Home -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_7') ? 'show active' : ''; ?>" id="tab_7" role="tabpanel" aria-labelledby="tab7-btn">
						<form action="" method="post">
							<div class="accordion mb-4 shadow-sm rounded overflow-hidden" id="accordionHomeSections">

								<!-- Seção 1: Serviços -->
								<div class="accordion-item">
									<h2 class="accordion-header" id="headingService">
										<button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseService" aria-expanded="false" aria-controls="collapseService">
											<i class="bi bi-briefcase me-2 text-primary"></i> Seção de Serviços
										</button>
									</h2>
									<div id="collapseService" class="accordion-collapse collapse" aria-labelledby="headingService" data-bs-parent="#accordionHomeSections">
										<div class="accordion-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_service" class="form-control" value="<?php echo htmlspecialchars($home_title_service); ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_service" class="form-control" value="<?php echo htmlspecialchars($home_subtitle_service); ?>">
												</div>
											</div>
											<div class="row mb-3 align-items-center">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Exibir na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<div class="form-check form-switch">
														<input class="form-check-input" type="checkbox" name="home_status_service" value="1" id="swService" <?php if($home_status_service == 1) {echo 'checked';} ?>>
														<label class="form-check-label" for="swService">Ativar exibição</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Seção 2: Departamentos -->
								<div class="accordion-item">
									<h2 class="accordion-header" id="headingDept">
										<button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDept" aria-expanded="false" aria-controls="collapseDept">
											<i class="bi bi-hospital me-2 text-primary"></i> Seção de Departamentos
										</button>
									</h2>
									<div id="collapseDept" class="accordion-collapse collapse" aria-labelledby="headingDept" data-bs-parent="#accordionHomeSections">
										<div class="accordion-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_department" class="form-control" value="<?php echo htmlspecialchars($home_title_department); ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_department" class="form-control" value="<?php echo htmlspecialchars($home_subtitle_department); ?>">
												</div>
											</div>
											<div class="row mb-3 align-items-center">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Exibir na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<div class="form-check form-switch">
														<input class="form-check-input" type="checkbox" name="home_status_department" value="1" id="swDept" <?php if($home_status_department == 1) {echo 'checked';} ?>>
														<label class="form-check-label" for="swDept">Ativar exibição</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Seção 3: Médicos / Especialistas -->
								<div class="accordion-item">
									<h2 class="accordion-header" id="headingDoctor">
										<button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDoctor" aria-expanded="false" aria-controls="collapseDoctor">
											<i class="bi bi-person-badge me-2 text-primary"></i> Seção de Médicos / Especialistas
										</button>
									</h2>
									<div id="collapseDoctor" class="accordion-collapse collapse" aria-labelledby="headingDoctor" data-bs-parent="#accordionHomeSections">
										<div class="accordion-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_doctor" class="form-control" value="<?php echo htmlspecialchars($home_title_doctor); ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_doctor" class="form-control" value="<?php echo htmlspecialchars($home_subtitle_doctor); ?>">
												</div>
											</div>
											<div class="row mb-3 align-items-center">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Exibir na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<div class="form-check form-switch">
														<input class="form-check-input" type="checkbox" name="home_status_doctor" value="1" id="swDoctor" <?php if($home_status_doctor == 1) {echo 'checked';} ?>>
														<label class="form-check-label" for="swDoctor">Ativar exibição</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Seção 4: Planos e Preços -->
								<div class="accordion-item">
									<h2 class="accordion-header" id="headingPricing">
										<button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePricing" aria-expanded="false" aria-controls="collapsePricing">
											<i class="bi bi-tag me-2 text-primary"></i> Seção de Planos e Preços
										</button>
									</h2>
									<div id="collapsePricing" class="accordion-collapse collapse" aria-labelledby="headingPricing" data-bs-parent="#accordionHomeSections">
										<div class="accordion-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_pricing" class="form-control" value="<?php echo htmlspecialchars($home_title_pricing); ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_pricing" class="form-control" value="<?php echo htmlspecialchars($home_subtitle_pricing); ?>">
												</div>
											</div>
											<div class="row mb-3 align-items-center">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Exibir na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<div class="form-check form-switch">
														<input class="form-check-input" type="checkbox" name="home_status_pricing" value="1" id="swPricing" <?php if($home_status_pricing == 1) {echo 'checked';} ?>>
														<label class="form-check-label" for="swPricing">Ativar exibição</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Seção 5: Depoimentos -->
								<div class="accordion-item">
									<h2 class="accordion-header" id="headingTestimonial">
										<button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTestimonial" aria-expanded="false" aria-controls="collapseTestimonial">
											<i class="bi bi-chat-quote me-2 text-primary"></i> Seção de Depoimentos
										</button>
									</h2>
									<div id="collapseTestimonial" class="accordion-collapse collapse" aria-labelledby="headingTestimonial" data-bs-parent="#accordionHomeSections">
										<div class="accordion-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_testimonial" class="form-control" value="<?php echo htmlspecialchars($home_title_testimonial); ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_testimonial" class="form-control" value="<?php echo htmlspecialchars($home_subtitle_testimonial); ?>">
												</div>
											</div>
											<div class="row mb-3 align-items-center">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Exibir na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<div class="form-check form-switch">
														<input class="form-check-input" type="checkbox" name="home_status_testimonial" value="1" id="swTestimonial" <?php if($home_status_testimonial == 1) {echo 'checked';} ?>>
														<label class="form-check-label" for="swTestimonial">Ativar exibição</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Seção 6: Notícias Recentes -->
								<div class="accordion-item">
									<h2 class="accordion-header" id="headingNews">
										<button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNews" aria-expanded="false" aria-controls="collapseNews">
											<i class="bi bi-newspaper me-2 text-primary"></i> Seção de Notícias
										</button>
									</h2>
									<div id="collapseNews" class="accordion-collapse collapse" aria-labelledby="headingNews" data-bs-parent="#accordionHomeSections">
										<div class="accordion-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_news" class="form-control" value="<?php echo htmlspecialchars($home_title_news); ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_news" class="form-control" value="<?php echo htmlspecialchars($home_subtitle_news); ?>">
												</div>
											</div>
											<div class="row mb-3 align-items-center">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Exibir na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<div class="form-check form-switch">
														<input class="form-check-input" type="checkbox" name="home_status_news" value="1" id="swNews" <?php if($home_status_news == 1) {echo 'checked';} ?>>
														<label class="form-check-label" for="swNews">Ativar exibição</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Seção 7: Parceiros -->
								<div class="accordion-item">
									<h2 class="accordion-header" id="headingPartner">
										<button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePartner" aria-expanded="false" aria-controls="collapsePartner">
											<i class="bi bi-people me-2 text-primary"></i> Seção de Parceiros
										</button>
									</h2>
									<div id="collapsePartner" class="accordion-collapse collapse" aria-labelledby="headingPartner" data-bs-parent="#accordionHomeSections">
										<div class="accordion-body">
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Título</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_title_partner" class="form-control" value="<?php echo htmlspecialchars($home_title_partner); ?>">
												</div>
											</div>
											<div class="row mb-3">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Subtítulo</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<input type="text" name="home_subtitle_partner" class="form-control" value="<?php echo htmlspecialchars($home_subtitle_partner); ?>">
												</div>
											</div>
											<div class="row mb-3 align-items-center">
												<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Exibir na Home?</label>
												<div class="col-12 col-sm-9 col-lg-9">
													<div class="form-check form-switch">
														<input class="form-check-input" type="checkbox" name="home_status_partner" value="1" id="swPartner" <?php if($home_status_partner == 1) {echo 'checked';} ?>>
														<label class="form-check-label" for="swPartner">Ativar exibição</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

							</div>

							<div class="row mb-3">
								<div class="col-12">
									<button type="submit" class="btn btn-primary px-4" name="form7">
										<i class="bi bi-check-lg me-1"></i> Atualizar Seções da Home
									</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Aba 8: Cor -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_8') ? 'show active' : ''; ?>" id="tab_8" role="tabpanel" aria-labelledby="tab8-btn">
						<form action="" method="post">
							<div class="row mb-3 align-items-center">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Cor Principal</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<div class="input-group">
										<span class="input-group-text bg-body-tertiary"><i class="bi bi-eyedropper text-primary"></i></span>
										<input type="text" name="color" class="form-control jscolor" value="<?php echo htmlspecialchars($color); ?>">
									</div>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="form8">
										<i class="bi bi-check-lg me-1"></i> Atualizar Cor
									</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Aba 9: Preloader -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_9') ? 'show active' : ''; ?>" id="tab_9" role="tabpanel" aria-labelledby="tab9-btn">
						<form action="" method="post">
							<div class="row mb-3 align-items-center">
								<label class="col-sm-3 col-lg-2 col-form-label fw-semibold">Status do Preloader</label>
								<div class="col-12 col-sm-9 col-lg-6">
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="preloader" id="preloader_on" <?php if($preloader == 1){echo 'checked';}?> value="1">
										<label class="form-check-label fw-semibold text-success" for="preloader_on"><i class="bi bi-play-circle me-1"></i> Ativar</label>
									</div>
									<div class="form-check form-check-inline ms-3">
										<input class="form-check-input" type="radio" name="preloader" id="preloader_off" <?php if($preloader == 0){echo 'checked';}?> value="0">
										<label class="form-check-label fw-semibold text-muted" for="preloader_off"><i class="bi bi-stop-circle me-1"></i> Desativar</label>
									</div>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-sm-3 col-lg-2 d-none d-sm-block"></div>
								<div class="col-12 col-sm-9 col-lg-6">
									<button type="submit" class="btn btn-primary px-4" name="BtnPreloader">
										<i class="bi bi-check-lg me-1"></i> Atualizar Preloader
									</button>
								</div>
							</div>
						</form>
					</div>

					<!-- Aba 10: reCAPTCHA -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_10') ? 'show active' : ''; ?>" id="tab_10" role="tabpanel" aria-labelledby="tab10-btn">
						<form action="" method="post">
							<div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
								<h5 class="fw-bold text-primary mb-0">
									<i class="bi bi-shield-lock-fill me-2"></i> Proteção contra Robôs & Spam (Google reCAPTCHA v2 / v3)
								</h5>
								<span class="badge <?= ($recaptcha_status == 1) ? 'bg-success' : 'bg-secondary'; ?>">
									<?= ($recaptcha_status == 1) ? 'Ativo no Site' : 'Desativado'; ?>
								</span>
							</div>

							<div class="alert alert-info d-flex align-items-center gap-3 mb-4 rounded-3 border-0 shadow-sm" style="background: rgba(13, 202, 240, 0.12); color: #055160;">
								<i class="bi bi-info-circle-fill fs-3 text-info"></i>
								<div class="small">
									O <strong>Google reCAPTCHA</strong> previne ataques de robôs e envio em massa de spam em formulários públicos (Contato, Mensagens, etc).<br>
									Para obter gratuitamente suas chaves, acesse o console oficial do <a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener noreferrer" class="fw-bold text-decoration-underline text-info-emphasis">Google reCAPTCHA Admin</a>.
								</div>
							</div>

							<div class="row mb-4 align-items-center">
								<label class="col-sm-3 col-lg-2 col-form-label fw-bold">Status do reCAPTCHA:</label>
								<div class="col-sm-9 col-lg-6">
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
								<label for="recaptcha_site_key" class="col-sm-3 col-lg-2 col-form-label fw-bold">Chave do Site (Site Key):</label>
								<div class="col-sm-9 col-lg-6">
									<div class="input-group">
										<span class="input-group-text bg-body-secondary"><i class="bi bi-key-fill text-primary"></i></span>
										<input type="text" name="recaptcha_site_key" id="recaptcha_site_key" class="form-control" value="<?= htmlspecialchars($recaptcha_site_key); ?>" placeholder="Ex: 6LdXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX">
									</div>
									<small class="text-muted">Chave pública utilizada para carregar e renderizar o widget no site público.</small>
								</div>
							</div>

							<div class="row mb-4">
								<label for="recaptcha_secret_key" class="col-sm-3 col-lg-2 col-form-label fw-bold">Chave Secreta (Secret Key):</label>
								<div class="col-sm-9 col-lg-6">
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
								<div class="col-sm-9 offset-sm-3 offset-lg-2">
									<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" name="form_recaptcha">
										<i class="bi bi-check-circle-fill"></i> Salvar Configurações do reCAPTCHA
									</button>
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