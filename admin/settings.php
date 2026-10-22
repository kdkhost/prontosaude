<?php
require_once('header.php');

$error_message = '';
$success_message = '';
$success_message2 = '';

// Recupera a aba ativa a partir de POST, GET ou padrão tab_1
$active_tab = $_POST['active_tab'] ?? ($_GET['tab'] ?? 'tab_1');

// Form 1: Logo
if(isset($_POST['form1'])) {
	$active_tab = 'tab_1';
	$valid = 1;

	$path = $_FILES['photo_logo']['name'] ?? '';
    $path_tmp = $_FILES['photo_logo']['tmp_name'] ?? '';

    if(empty($path)) {
    	$valid = 0;
        $error_message .= 'Por favor, selecione um arquivo de imagem para a logo.<br>';
    } else {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if(!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            $valid = 0;
            $error_message .= 'Formatos aceitos para logo: JPG, JPEG, PNG, GIF, WEBP ou SVG.<br>';
        }
    }

    if($valid == 1) {
    	// Removendo a foto existente
    	$statement = $pdo->prepare("SELECT logo FROM tbl_settings WHERE id=1");
    	$statement->execute();
    	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
    	foreach ($result as $row) {
    		$old_logo = $row['logo'];
    		if(!empty($old_logo) && file_exists('../assets/uploads/'.$old_logo)) {
    			@unlink('../assets/uploads/'.$old_logo);
    		}
    	}

    	// Atualizando o arquivo
    	$final_name = 'logo-'.time().'.'.$ext;
        move_uploaded_file($path_tmp, '../assets/uploads/'.$final_name);

        // Atualizando o banco de dados
		$statement = $pdo->prepare("UPDATE tbl_settings SET logo=? WHERE id=1");
		$statement->execute(array($final_name));

        $success_message = 'A Logo do portal foi atualizada com sucesso!';
    }
}

// Form 2: Favicon
if(isset($_POST['form2'])) {
	$active_tab = 'tab_2';
	$valid = 1;

	$path = $_FILES['photo_favicon']['name'] ?? '';
    $path_tmp = $_FILES['photo_favicon']['tmp_name'] ?? '';

    if(empty($path)) {
    	$valid = 0;
        $error_message .= 'Por favor, selecione um arquivo de imagem para o favicon.<br>';
    } else {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if(!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico', 'svg'])) {
            $valid = 0;
            $error_message .= 'Formatos aceitos para favicon: ICO, PNG, WEBP, JPG ou SVG.<br>';
        }
    }

    if($valid == 1) {
    	// Removendo a foto existente
    	$statement = $pdo->prepare("SELECT favicon FROM tbl_settings WHERE id=1");
    	$statement->execute();
    	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
    	foreach ($result as $row) {
    		$old_fav = $row['favicon'];
    		if(!empty($old_fav) && file_exists('../assets/uploads/'.$old_fav)) {
    			@unlink('../assets/uploads/'.$old_fav);
    		}
    	}

    	// Atualizando o arquivo
    	$final_name = 'favicon-'.time().'.'.$ext;
        move_uploaded_file($path_tmp, '../assets/uploads/'.$final_name);

        // Atualizando o banco de dados
		$statement = $pdo->prepare("UPDATE tbl_settings SET favicon=? WHERE id=1");
		$statement->execute(array($final_name));

        $success_message = 'O Favicon do portal foi atualizado com sucesso!';
    }
}

// Form 3: Conteúdo Geral
if(isset($_POST['form3'])) {
	$active_tab = 'tab_3';
	$statement = $pdo->prepare("UPDATE tbl_settings SET footer_about=?, footer_copyright=?, contact_address=?, contact_email=?, contact_phone=?, contact_fax=?, contact_map_iframe=? WHERE id=1");
	$statement->execute(array(
		$_POST['footer_about'] ?? '',
		$_POST['footer_copyright'] ?? '',
		$_POST['contact_address'] ?? '',
		$_POST['contact_email'] ?? '',
		$_POST['contact_phone'] ?? '',
		$_POST['contact_fax'] ?? '',
		$_POST['contact_map_iframe'] ?? ''
	));

	$success_message = 'As informações de conteúdo institucional e localização foram atualizadas com sucesso.';
}

// Form 4: E-mail de Recebimento
if(isset($_POST['form4'])) {
	$active_tab = 'tab_4';
	$statement = $pdo->prepare("UPDATE tbl_settings SET receive_email=?, receive_email_subject=?, receive_email_thank_you_message=? WHERE id=1");
	$statement->execute(array(
		$_POST['receive_email'] ?? '',
		$_POST['receive_email_subject'] ?? '',
		$_POST['receive_email_thank_you_message'] ?? ''
	));

	$success_message = 'As configurações de e-mail e mensagens de contato foram atualizadas com sucesso.';
}

// Form 5: Widgets e Notícias
if(isset($_POST['form5'])) {
	$active_tab = 'tab_5';
	$statement = $pdo->prepare("UPDATE tbl_settings SET total_recent_news_footer=?, total_popular_news_footer=?, total_recent_news_sidebar=?, total_popular_news_sidebar=?, total_recent_news_home_page=? WHERE id=1");
	$statement->execute(array(
		intval($_POST['total_recent_news_footer'] ?? 3),
		intval($_POST['total_popular_news_footer'] ?? 3),
		intval($_POST['total_recent_news_sidebar'] ?? 3),
		intval($_POST['total_popular_news_sidebar'] ?? 3),
		intval($_POST['total_recent_news_home_page'] ?? 3)
	));

	$success_message = 'As quantidades de notícias para rodapé, barra lateral e home foram atualizadas com sucesso.';
}

// Form 6: SEO / Meta Tags & Open Graph
if(isset($_POST['form6'])) {
	$active_tab = 'tab_6';

	// Garante coluna og_image em tbl_settings se ainda não existir
	try {
		$pdo->query("SELECT og_image FROM tbl_settings LIMIT 1");
	} catch (Exception $e) {
		try {
			$pdo->exec("ALTER TABLE tbl_settings ADD COLUMN og_image VARCHAR(255) NULL DEFAULT NULL AFTER meta_description_home");
		} catch (Exception $ex) {}
	}

	$path = $_FILES['og_image_home']['name'] ?? '';
	$path_tmp = $_FILES['og_image_home']['tmp_name'] ?? '';

	if(!empty($path)) {
		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
		if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
			$og_img_name = 'og-home-' . time() . '.' . $ext;
			if(move_uploaded_file($path_tmp, '../assets/uploads/' . $og_img_name)) {
				// Remove imagem antiga se for arquivo gerado e não default
				$st_old = $pdo->query("SELECT og_image FROM tbl_settings WHERE id=1");
				$old_data = $st_old->fetch(PDO::FETCH_ASSOC);
				if(!empty($old_data['og_image']) && file_exists('../assets/uploads/' . $old_data['og_image'])) {
					@unlink('../assets/uploads/' . $old_data['og_image']);
				}
				$st_img = $pdo->prepare("UPDATE tbl_settings SET og_image=? WHERE id=1");
				$st_img->execute([$og_img_name]);
			}
		} else {
			$error_message .= 'Formato inválido para imagem social. Utilize JPG, PNG ou WEBP.<br>';
		}
	}

	$clean_title = trim(strip_tags($_POST['meta_title_home'] ?? ''));
	$clean_keyword = trim(strip_tags($_POST['meta_keyword_home'] ?? ''));
	$clean_desc = trim(strip_tags($_POST['meta_description_home'] ?? ''));
	$clean_desc = preg_replace('/[\r\n\t]+/', ' ', $clean_desc);
	$clean_desc = preg_replace('/\s+/', ' ', $clean_desc);

	$statement = $pdo->prepare("UPDATE tbl_settings SET meta_title_home=?, meta_keyword_home=?, meta_description_home=? WHERE id=1");
	$statement->execute(array(
		$clean_title,
		$clean_keyword,
		$clean_desc
	));

	if(empty($error_message)) {
		$success_message = 'As configurações de SEO, Open Graph e meta tags da página inicial foram salvas com sucesso.';
	}
}

// Form 7: Seções da Home
if(isset($_POST['form7'])) {
	$active_tab = 'tab_7';

	$home_status_service = isset($_POST['home_status_service']) ? 1 : 0;
	$home_status_department = isset($_POST['home_status_department']) ? 1 : 0;
	$home_status_doctor = isset($_POST['home_status_doctor']) ? 1 : 0;
	$home_status_pricing = isset($_POST['home_status_pricing']) ? 1 : 0;
	$home_status_testimonial = isset($_POST['home_status_testimonial']) ? 1 : 0;
	$home_status_news = isset($_POST['home_status_news']) ? 1 : 0;
	$home_status_partner = isset($_POST['home_status_partner']) ? 1 : 0;

	$statement = $pdo->prepare("UPDATE tbl_settings SET 
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
		$_POST['home_title_service'] ?? '',
		$_POST['home_subtitle_service'] ?? '',
		$home_status_service,
		$_POST['home_title_department'] ?? '',
		$_POST['home_subtitle_department'] ?? '',
		$home_status_department,
		$_POST['home_title_doctor'] ?? '',
		$_POST['home_subtitle_doctor'] ?? '',
		$home_status_doctor,
		$_POST['home_title_pricing'] ?? '',
		$_POST['home_subtitle_pricing'] ?? '',
		$home_status_pricing,
		$_POST['home_title_testimonial'] ?? '',
		$_POST['home_subtitle_testimonial'] ?? '',
		$home_status_testimonial,
		$_POST['home_title_news'] ?? '',
		$_POST['home_subtitle_news'] ?? '',
		$home_status_news,
		$_POST['home_title_partner'] ?? '',
		$_POST['home_subtitle_partner'] ?? '',
		$home_status_partner
	));

	$success_message = 'Os títulos, subtítulos e status das seções da Home foram atualizados com sucesso.';
}

// Form 8: Cor
if(isset($_POST['form8'])) {
	$active_tab = 'tab_8';
	$statement = $pdo->prepare("UPDATE tbl_settings SET color=? WHERE id=1");
	$statement->execute(array($_POST['color'] ?? '0a6b74'));

	$success_message = 'A cor temática principal do portal foi atualizada com sucesso.';
}

// Preloader
if(isset($_POST['BtnPreloader'])) {
	$active_tab = 'tab_9';
	$preloader = intval($_POST['preloader'] ?? 0);
    $update = $pdo->prepare("UPDATE tbl_settings SET preloader=? WHERE id=1");
	$update->execute(array($preloader));
	    	
	if($preloader == 1){
	    $success_message = 'O <b>Preloader</b> de carregamento foi ativado com sucesso.';
	} else {
	    $success_message2 = 'O <b>Preloader</b> de carregamento foi desativado com sucesso.';
	}
}

// Form reCAPTCHA
if(isset($_POST['form_recaptcha'])) {
	$active_tab = 'tab_10';
	$recaptcha_status = isset($_POST['recaptcha_status']) ? 1 : 0;
	$recaptcha_site_key = trim($_POST['recaptcha_site_key'] ?? '');
	$recaptcha_secret_key = trim($_POST['recaptcha_secret_key'] ?? '');

	$statement = $pdo->prepare("UPDATE tbl_settings SET recaptcha_status=?, recaptcha_site_key=?, recaptcha_secret_key=? WHERE id=1");
	$statement->execute(array($recaptcha_status, $recaptcha_site_key, $recaptcha_secret_key));

	$success_message = 'As credenciais e configurações do Google reCAPTCHA foram atualizadas com sucesso.';
}

// Carrega os dados mais recentes do banco
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$row = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

$logo                            = $row['logo'] ?? '';
$favicon                         = $row['favicon'] ?? '';
$footer_about                    = $row['footer_about'] ?? '';
$footer_copyright                = $row['footer_copyright'] ?? '';
$contact_address                 = $row['contact_address'] ?? '';
$contact_email                   = $row['contact_email'] ?? '';
$contact_phone                   = $row['contact_phone'] ?? '';
$contact_fax                     = $row['contact_fax'] ?? '';
$contact_map_iframe              = $row['contact_map_iframe'] ?? '';
$receive_email                   = $row['receive_email'] ?? '';
$receive_email_subject           = $row['receive_email_subject'] ?? '';
$receive_email_thank_you_message = $row['receive_email_thank_you_message'] ?? '';
$total_recent_news_footer        = $row['total_recent_news_footer'] ?? 3;
$total_popular_news_footer       = $row['total_popular_news_footer'] ?? 3;
$total_recent_news_sidebar       = $row['total_recent_news_sidebar'] ?? 3;
$total_popular_news_sidebar      = $row['total_popular_news_sidebar'] ?? 3;
$total_recent_news_home_page     = $row['total_recent_news_home_page'] ?? 3;
$meta_title_home                 = $row['meta_title_home'] ?? '';
$meta_keyword_home               = $row['meta_keyword_home'] ?? '';
$meta_description_home           = $row['meta_description_home'] ?? '';
$og_image_home                   = $row['og_image'] ?? '';
$home_title_service              = $row['home_title_service'] ?? '';
$home_subtitle_service           = $row['home_subtitle_service'] ?? '';
$home_status_service             = $row['home_status_service'] ?? 0;
$home_title_department           = $row['home_title_department'] ?? '';
$home_subtitle_department        = $row['home_subtitle_department'] ?? '';
$home_status_department          = $row['home_status_department'] ?? 0;
$home_title_doctor               = $row['home_title_doctor'] ?? '';
$home_subtitle_doctor            = $row['home_subtitle_doctor'] ?? '';
$home_status_doctor              = $row['home_status_doctor'] ?? 0;
$home_title_pricing              = $row['home_title_pricing'] ?? '';
$home_subtitle_pricing           = $row['home_subtitle_pricing'] ?? '';
$home_status_pricing             = $row['home_status_pricing'] ?? 0;
$home_title_testimonial          = $row['home_title_testimonial'] ?? '';
$home_subtitle_testimonial       = $row['home_subtitle_testimonial'] ?? '';
$home_status_testimonial         = $row['home_status_testimonial'] ?? 0;
$home_title_news                 = $row['home_title_news'] ?? '';
$home_subtitle_news              = $row['home_subtitle_news'] ?? '';
$home_status_news                = $row['home_status_news'] ?? 0;
$home_title_partner              = $row['home_title_partner'] ?? '';
$home_subtitle_partner           = $row['home_subtitle_partner'] ?? '';
$home_status_partner             = $row['home_status_partner'] ?? 0;
$color                           = $row['color'] ?? '0a6b74';
$preloader                       = $row['preloader'] ?? 0;
$recaptcha_status                = $row['recaptcha_status'] ?? 0;
$recaptcha_site_key              = $row['recaptcha_site_key'] ?? '';
$recaptcha_secret_key            = $row['recaptcha_secret_key'] ?? '';
?>

<!-- Cabeçalho da Página com Breadcrumb Moderno -->
<div class="app-content-header">
	<div class="container-fluid">
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
			<div>
				<h3 class="mb-1 fw-bold text-body">
					<i class="bi bi-gear-wide-connected text-primary me-2"></i>Configurações do Sistema
				</h3>
				<ol class="breadcrumb mb-0 small">
					<li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
					<li class="breadcrumb-item active" aria-current="page">Configurações</li>
				</ol>
			</div>
			<div class="d-flex align-items-center gap-2">
				<a href="<?= BASE_URL; ?>" target="_blank" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm px-3">
					<i class="bi bi-globe"></i> Ver Portal
				</a>
			</div>
		</div>
	</div>
</div>

<div class="app-content">
	<div class="container-fluid">

		<!-- Feedback e Alertas Modernos -->
		<?php if(!empty($error_message)): ?>
		<div class="callout callout-danger shadow-sm mb-4">
			<div class="d-flex align-items-center">
				<i class="bi bi-exclamation-triangle-fill text-danger fs-4 me-3"></i>
				<div><?= $error_message; ?></div>
			</div>
		</div>
		<?php endif; ?>

		<?php if(!empty($success_message)): ?>
		<div class="callout callout-success shadow-sm mb-4">
			<div class="d-flex align-items-center">
				<i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
				<div><?= $success_message; ?></div>
			</div>
		</div>
		<?php endif; ?>

		<?php if(!empty($success_message2)): ?>
		<div class="callout callout-warning shadow-sm mb-4">
			<div class="d-flex align-items-center">
				<i class="bi bi-exclamation-circle-fill text-warning fs-4 me-3"></i>
				<div><?= $success_message2; ?></div>
			</div>
		</div>
		<?php endif; ?>

		<!-- Card Principal com Abas no Padrão Oficial AdminLTE 4 -->
		<div class="card card-primary card-outline card-outline-tabs shadow-sm mb-4">
			
			<!-- Cabeçalho das Abas com Barra de Navegação Horizontal Scrollable -->
			<div class="card-header p-0 border-bottom">
				<ul class="nav nav-tabs ps-tabs-scrollable" id="settingsTabs" role="tablist">
					<li class="nav-item" role="presentation">
						<a class="nav-link <?= ($active_tab == 'tab_1') ? 'active' : ''; ?>" id="tab1-btn" data-bs-toggle="tab" data-bs-target="#tab_1" href="#tab_1" role="tab" aria-controls="tab_1" aria-selected="<?= ($active_tab == 'tab_1') ? 'true' : 'false'; ?>">
							<i class="bi bi-image me-1"></i> Logo
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link <?= ($active_tab == 'tab_2') ? 'active' : ''; ?>" id="tab2-btn" data-bs-toggle="tab" data-bs-target="#tab_2" href="#tab_2" role="tab" aria-controls="tab_2" aria-selected="<?= ($active_tab == 'tab_2') ? 'true' : 'false'; ?>">
							<i class="bi bi-star me-1"></i> Favicon
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link <?= ($active_tab == 'tab_3') ? 'active' : ''; ?>" id="tab3-btn" data-bs-toggle="tab" data-bs-target="#tab_3" href="#tab_3" role="tab" aria-controls="tab_3" aria-selected="<?= ($active_tab == 'tab_3') ? 'true' : 'false'; ?>">
							<i class="bi bi-sliders me-1"></i> Conteúdo Geral
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link <?= ($active_tab == 'tab_4') ? 'active' : ''; ?>" id="tab4-btn" data-bs-toggle="tab" data-bs-target="#tab_4" href="#tab_4" role="tab" aria-controls="tab_4" aria-selected="<?= ($active_tab == 'tab_4') ? 'true' : 'false'; ?>">
							<i class="bi bi-envelope me-1"></i> Contato & E-mail
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link <?= ($active_tab == 'tab_5') ? 'active' : ''; ?>" id="tab5-btn" data-bs-toggle="tab" data-bs-target="#tab_5" href="#tab_5" role="tab" aria-controls="tab_5" aria-selected="<?= ($active_tab == 'tab_5') ? 'true' : 'false'; ?>">
							<i class="bi bi-newspaper me-1"></i> Notícias
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link <?= ($active_tab == 'tab_6') ? 'active' : ''; ?>" id="tab6-btn" data-bs-toggle="tab" data-bs-target="#tab_6" href="#tab_6" role="tab" aria-controls="tab_6" aria-selected="<?= ($active_tab == 'tab_6') ? 'true' : 'false'; ?>">
							<i class="bi bi-search me-1"></i> SEO / Meta Tags
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link <?= ($active_tab == 'tab_7') ? 'active' : ''; ?>" id="tab7-btn" data-bs-toggle="tab" data-bs-target="#tab_7" href="#tab_7" role="tab" aria-controls="tab_7" aria-selected="<?= ($active_tab == 'tab_7') ? 'true' : 'false'; ?>">
							<i class="bi bi-layout-text-window-reverse me-1"></i> Seções Home
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link <?= ($active_tab == 'tab_8') ? 'active' : ''; ?>" id="tab8-btn" data-bs-toggle="tab" data-bs-target="#tab_8" href="#tab_8" role="tab" aria-controls="tab_8" aria-selected="<?= ($active_tab == 'tab_8') ? 'true' : 'false'; ?>">
							<i class="bi bi-palette me-1"></i> Cores
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link <?= ($active_tab == 'tab_9') ? 'active' : ''; ?>" id="tab9-btn" data-bs-toggle="tab" data-bs-target="#tab_9" href="#tab_9" role="tab" aria-controls="tab_9" aria-selected="<?= ($active_tab == 'tab_9') ? 'true' : 'false'; ?>">
							<i class="bi bi-hourglass-split me-1"></i> Preloader
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link <?= ($active_tab == 'tab_10') ? 'active' : ''; ?>" id="tab10-btn" data-bs-toggle="tab" data-bs-target="#tab_10" href="#tab_10" role="tab" aria-controls="tab_10" aria-selected="<?= ($active_tab == 'tab_10') ? 'true' : 'false'; ?>">
							<i class="bi bi-shield-check text-primary me-1"></i> reCAPTCHA
						</a>
					</li>
				</ul>
			</div>

			<div class="card-body p-4">
				<div class="tab-content" id="settingsTabsContent">

					<!-- ========================================================
					     ABA 1: LOGO DO PORTAL
					     ======================================================== -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_1') ? 'show active' : ''; ?>" id="tab_1" role="tabpanel" aria-labelledby="tab1-btn">
						<form action="" method="post" enctype="multipart/form-data">
							<input type="hidden" name="active_tab" value="tab_1">
							
							<div class="row g-4 align-items-center">
								<div class="col-lg-5">
									<label class="form-label fw-bold text-body mb-2">Logo Ativa no Portal</label>
									<div class="p-3 border rounded-3 bg-body-tertiary text-center shadow-xs" style="min-height: 140px; display: flex; align-items: center; justify-content: center; background-image: radial-gradient(rgba(0,0,0,0.06) 1px, transparent 0); background-size: 12px 12px;">
										<?php if(!empty($logo) && file_exists('../assets/uploads/'.$logo)): ?>
											<img src="../assets/uploads/<?= htmlspecialchars($logo); ?>" alt="Logo Atual" class="img-fluid" style="max-height: 90px; object-fit: contain;">
										<?php else: ?>
											<span class="text-body-secondary fst-italic"><i class="bi bi-image me-1"></i> Nenhuma logo cadastrada</span>
										<?php endif; ?>
									</div>
									<div class="form-text small mt-2">
										<i class="bi bi-info-circle text-primary me-1"></i>Recomendado formato PNG ou WEBP transparente, com resolução sugerida de <strong>240x70 px</strong>.
									</div>
								</div>

								<div class="col-lg-7">
									<label class="form-label fw-bold text-body mb-2">Enviar Nova Logo</label>
									<input type="file" name="photo_logo" class="form-control" accept="image/png, image/jpeg, image/jpg, image/webp, image/svg+xml">
									<div class="d-flex align-items-center gap-2 mt-3">
										<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" name="form1">
											<i class="bi bi-cloud-arrow-up-fill"></i> Atualizar Logo
										</button>
									</div>
								</div>
							</div>
						</form>
					</div>

					<!-- ========================================================
					     ABA 2: FAVICON
					     ======================================================== -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_2') ? 'show active' : ''; ?>" id="tab_2" role="tabpanel" aria-labelledby="tab2-btn">
						<form action="" method="post" enctype="multipart/form-data">
							<input type="hidden" name="active_tab" value="tab_2">

							<div class="row g-4 align-items-center">
								<div class="col-lg-5">
									<label class="form-label fw-bold text-body mb-2">Favicon Ativo & Simulação de Aba</label>
									<div class="border rounded-3 p-3 bg-body-tertiary shadow-xs">
										<!-- Mockup de aba do navegador -->
										<div class="d-flex align-items-center gap-2 px-3 py-2 bg-body rounded-2 border shadow-sm" style="max-width: 260px;">
											<?php if(!empty($favicon) && file_exists('../assets/uploads/'.$favicon)): ?>
												<img src="../assets/uploads/<?= htmlspecialchars($favicon); ?>" alt="Favicon" width="20" height="20" style="object-fit: contain;">
											<?php else: ?>
												<i class="bi bi-globe text-primary"></i>
											<?php endif; ?>
											<span class="small fw-semibold text-truncate text-body">Pronto Saúde • Portal</span>
											<i class="bi bi-x ms-auto text-body-secondary small"></i>
										</div>
									</div>
									<div class="form-text small mt-2">
										<i class="bi bi-info-circle text-primary me-1"></i>O favicon aparece na aba dos navegadores e favoritos. Dimensões recomendadas: <strong>32x32 px</strong> ou <strong>64x64 px</strong> (PNG ou ICO).
									</div>
								</div>

								<div class="col-lg-7">
									<label class="form-label fw-bold text-body mb-2">Enviar Novo Favicon</label>
									<input type="file" name="photo_favicon" class="form-control" accept="image/x-icon, image/png, image/webp, image/svg+xml">
									<div class="d-flex align-items-center gap-2 mt-3">
										<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" name="form2">
											<i class="bi bi-cloud-arrow-up-fill"></i> Atualizar Favicon
										</button>
									</div>
								</div>
							</div>
						</form>
					</div>

					<!-- ========================================================
					     ABA 3: CONTEÚDO GERAL & LOCALIZAÇÃO
					     ======================================================== -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_3') ? 'show active' : ''; ?>" id="tab_3" role="tabpanel" aria-labelledby="tab3-btn">
						<form action="" method="post">
							<input type="hidden" name="active_tab" value="tab_3">

							<!-- Seção 1: Identidade Institucional -->
							<div class="card border mb-4 shadow-xs">
								<div class="card-header bg-body-tertiary py-2 px-3 fw-bold text-primary">
									<i class="bi bi-building me-1"></i> Textos do Rodapé Institucional
								</div>
								<div class="card-body">
									<div class="mb-3">
										<label class="form-label fw-semibold text-body">Sobre a Clínica (Rodapé)</label>
										<textarea class="form-control" name="footer_about" rows="3" placeholder="Breve resumo institucional exibido no rodapé do portal..."><?= htmlspecialchars($footer_about); ?></textarea>
									</div>
									<div class="mb-0">
										<label class="form-label fw-semibold text-body">Texto de Direitos Autorais (Copyright)</label>
										<div class="input-group">
											<span class="input-group-text bg-body-secondary"><i class="bi bi-c-circle text-body-secondary"></i></span>
											<input class="form-control" type="text" name="footer_copyright" value="<?= htmlspecialchars($footer_copyright); ?>" placeholder="Ex: Todos os direitos reservados.">
										</div>
									</div>
								</div>
							</div>

							<!-- Seção 2: Contatos Rápidos -->
							<div class="card border mb-4 shadow-xs">
								<div class="card-header bg-body-tertiary py-2 px-3 fw-bold text-primary">
									<i class="bi bi-telephone-inbound me-1"></i> Canais de Atendimento ao Paciente
								</div>
								<div class="card-body">
									<div class="row g-3">
										<div class="col-md-4">
											<label class="form-label fw-semibold text-body">E-mail Institucional</label>
											<div class="input-group">
												<span class="input-group-text bg-body-secondary"><i class="bi bi-envelope-at text-primary"></i></span>
												<input type="email" class="form-control" name="contact_email" value="<?= htmlspecialchars($contact_email); ?>" placeholder="contato@clinica.com.br">
											</div>
										</div>
										<div class="col-md-4">
											<label class="form-label fw-semibold text-body">Telefone / WhatsApp Principal</label>
											<div class="input-group">
												<span class="input-group-text bg-body-secondary"><i class="bi bi-telephone text-success"></i></span>
												<input type="text" class="form-control mask-phone" name="contact_phone" value="<?= htmlspecialchars($contact_phone); ?>" placeholder="(00) 00000-0000">
											</div>
										</div>
										<div class="col-md-4">
											<label class="form-label fw-semibold text-body">FAX / Linha Secundária</label>
											<div class="input-group">
												<span class="input-group-text bg-body-secondary"><i class="bi bi-printer text-body-secondary"></i></span>
												<input type="text" class="form-control mask-phone" name="contact_fax" value="<?= htmlspecialchars($contact_fax); ?>" placeholder="(00) 0000-0000">
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Seção 3: Endereço & Localização Inteligente -->
							<div class="card border mb-4 shadow-xs">
								<div class="card-header bg-body-tertiary py-2 px-3 fw-bold text-primary">
									<i class="bi bi-geo-alt-fill me-1"></i> Endereço da Unidade Principal & Localização
								</div>
								<div class="card-body">
									
									<!-- Busca por CEP com ViaCEP e Auto Focus -->
									<div class="p-3 bg-body-tertiary rounded-3 border mb-3">
										<div class="row g-2 align-items-center">
											<div class="col-sm-4 col-md-3">
												<label class="form-label small fw-bold mb-1">Buscar por CEP:</label>
												<div class="input-group input-group-sm">
													<span class="input-group-text"><i class="bi bi-search text-primary"></i></span>
													<input type="text" class="form-control mask-cep" id="busca_cep" placeholder="00000-000" maxlength="9">
												</div>
											</div>
											<div class="col-sm-3 col-md-2">
												<label class="form-label small fw-bold mb-1">Número:</label>
												<input type="text" class="form-control form-control-sm" id="numero_endereco" placeholder="Nº">
											</div>
											<div class="col-sm-5 col-md-7">
												<span class="small text-body-secondary d-block mt-3">
													<i class="bi bi-lightning-charge-fill text-warning me-1"></i>Preenchimento automático do endereço via consulta oficial.
												</span>
											</div>
										</div>
									</div>

									<div class="mb-3">
										<label class="form-label fw-semibold text-body">Endereço Completo do Estabelecimento</label>
										<textarea class="form-control" name="contact_address" id="contact_address_field" rows="2" placeholder="Ex: Av. Principal, 1234, Sala 101 - Centro, Cidade - UF"><?= htmlspecialchars($contact_address); ?></textarea>
									</div>

									<div class="mb-3">
										<label class="form-label fw-semibold text-body">Código de Incorporação do Mapa (Google Maps iFrame)</label>
										<textarea class="form-control font-monospace small" name="contact_map_iframe" id="contact_map_iframe_field" rows="3" placeholder="<iframe src='https://www.google.com/maps/embed?...' ...></iframe>"><?= $contact_map_iframe; ?></textarea>
										<div class="form-text small">Cole o código de compartilhamento <code>&lt;iframe&gt;</code> gerado no Google Maps.</div>
									</div>

									<?php if(!empty($contact_map_iframe)): ?>
									<div class="border rounded-3 overflow-hidden shadow-xs mt-3">
										<div class="bg-body-secondary py-1 px-3 small fw-bold text-body-secondary">
											<i class="bi bi-eye me-1"></i> Pré-visualização do Mapa no Site:
										</div>
										<div class="ratio ratio-21x9" style="max-height: 220px;">
											<?= $contact_map_iframe; ?>
										</div>
									</div>
									<?php endif; ?>

								</div>
							</div>

							<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" name="form3">
								<i class="bi bi-check-circle-fill"></i> Salvar Conteúdo Geral
							</button>
						</form>
					</div>

					<!-- ========================================================
					     ABA 4: CONTATO & E-MAIL
					     ======================================================== -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_4') ? 'show active' : ''; ?>" id="tab_4" role="tabpanel" aria-labelledby="tab4-btn">
						<form action="" method="post">
							<input type="hidden" name="active_tab" value="tab_4">

							<div class="row g-4">
								<div class="col-lg-6">
									<div class="card border shadow-xs h-100">
										<div class="card-header bg-body-tertiary py-2 px-3 fw-bold text-primary">
											<i class="bi bi-inbox-fill me-1"></i> Destino das Mensagens
										</div>
										<div class="card-body">
											<div class="mb-3">
												<label class="form-label fw-semibold text-body">E-mail de Recebimento <span class="text-danger">*</span></label>
												<div class="input-group">
													<span class="input-group-text bg-body-secondary"><i class="bi bi-envelope-check text-primary"></i></span>
													<input type="email" class="form-control" name="receive_email" value="<?= htmlspecialchars($receive_email); ?>" required placeholder="recebimento@clinica.com.br">
												</div>
												<div class="form-text small">Para onde serão enviadas as dúvidas dos pacientes enviadas no formulário "Fale Conosco".</div>
											</div>

											<div class="mb-0">
												<label class="form-label fw-semibold text-body">Assunto do E-mail <span class="text-danger">*</span></label>
												<div class="input-group">
													<span class="input-group-text bg-body-secondary"><i class="bi bi-card-heading text-body-secondary"></i></span>
													<input type="text" class="form-control" name="receive_email_subject" value="<?= htmlspecialchars($receive_email_subject); ?>" required placeholder="Ex: Novo Contato via Portal Pronto Saúde">
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="col-lg-6">
									<div class="card border shadow-xs h-100">
										<div class="card-header bg-body-tertiary py-2 px-3 fw-bold text-primary">
											<i class="bi bi-chat-heart-fill me-1"></i> Resposta ao Paciente
										</div>
										<div class="card-body">
											<div class="mb-0">
												<label class="form-label fw-semibold text-body">Mensagem de Agradecimento <span class="text-danger">*</span></label>
												<textarea class="form-control" name="receive_email_thank_you_message" rows="5" required placeholder="Agradecemos seu contato! Responderemos em até 24 horas úteis..."><?= htmlspecialchars($receive_email_thank_you_message); ?></textarea>
												<div class="form-text small">Mensagem exibida ao paciente na tela imediatamente após o envio com sucesso.</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="mt-4">
								<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" name="form4">
									<i class="bi bi-check-circle-fill"></i> Salvar Configurações de E-mail
								</button>
							</div>
						</form>
					</div>

					<!-- ========================================================
					     ABA 5: NOTÍCIAS & WIDGETS
					     ======================================================== -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_5') ? 'show active' : ''; ?>" id="tab_5" role="tabpanel" aria-labelledby="tab5-btn">
						<form action="" method="post">
							<input type="hidden" name="active_tab" value="tab_5">

							<p class="text-body-secondary small mb-4">
								<i class="bi bi-info-circle text-primary me-1"></i>Defina a quantidade de postagens a serem exibidas nos blocos dinâmicos do portal.
							</p>

							<div class="row g-4">
								<!-- Rodapé -->
								<div class="col-md-4">
									<div class="card border shadow-xs h-100">
										<div class="card-header bg-body-tertiary py-2 px-3 fw-bold text-primary">
											<i class="bi bi-layout-sidebar-inset-reverse me-1"></i> Notícias no Rodapé
										</div>
										<div class="card-body">
											<div class="mb-3">
												<label class="form-label fw-semibold text-body">Postagens Recentes</label>
												<input type="number" class="form-control" name="total_recent_news_footer" value="<?= htmlspecialchars($total_recent_news_footer); ?>" min="0" max="20">
											</div>
											<div class="mb-0">
												<label class="form-label fw-semibold text-body">Postagens Populares</label>
												<input type="number" class="form-control" name="total_popular_news_footer" value="<?= htmlspecialchars($total_popular_news_footer); ?>" min="0" max="20">
											</div>
										</div>
									</div>
								</div>

								<!-- Barra Lateral -->
								<div class="col-md-4">
									<div class="card border shadow-xs h-100">
										<div class="card-header bg-body-tertiary py-2 px-3 fw-bold text-primary">
											<i class="bi bi-layout-sidebar-reverse me-1"></i> Notícias na Barra Lateral
										</div>
										<div class="card-body">
											<div class="mb-3">
												<label class="form-label fw-semibold text-body">Postagens Recentes</label>
												<input type="number" class="form-control" name="total_recent_news_sidebar" value="<?= htmlspecialchars($total_recent_news_sidebar); ?>" min="0" max="20">
											</div>
											<div class="mb-0">
												<label class="form-label fw-semibold text-body">Postagens Populares</label>
												<input type="number" class="form-control" name="total_popular_news_sidebar" value="<?= htmlspecialchars($total_popular_news_sidebar); ?>" min="0" max="20">
											</div>
										</div>
									</div>
								</div>

								<!-- Página Inicial -->
								<div class="col-md-4">
									<div class="card border shadow-xs h-100">
										<div class="card-header bg-body-tertiary py-2 px-3 fw-bold text-primary">
											<i class="bi bi-house me-1"></i> Notícias na Página Inicial
										</div>
										<div class="card-body">
											<div class="mb-0">
												<label class="form-label fw-semibold text-body">Postagens Recentes na Home</label>
												<input type="number" class="form-control" name="total_recent_news_home_page" value="<?= htmlspecialchars($total_recent_news_home_page); ?>" min="0" max="20">
												<div class="form-text small mt-2">Quantidade de cartões na seção de blog da Home.</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="mt-4">
								<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" name="form5">
									<i class="bi bi-check-circle-fill"></i> Salvar Quantidades de Notícias
								</button>
							</div>
						</form>
					</div>

					<!-- ========================================================
					     ABA 6: SEO & METADADOS DA HOME
					     ======================================================== -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_6') ? 'show active' : ''; ?>" id="tab_6" role="tabpanel" aria-labelledby="tab6-btn">
						<form action="" method="post" enctype="multipart/form-data">
							<input type="hidden" name="active_tab" value="tab_6">

							<!-- Prévias lado a lado: Google SERP + Card Social WhatsApp/Redes -->
							<div class="row g-4 mb-4">
								<!-- Simulador de Busca do Google (SERP Preview) -->
								<div class="col-lg-6">
									<div class="card border h-100 shadow-xs">
										<div class="card-header bg-body-tertiary py-2 px-3 fw-bold text-primary d-flex align-items-center justify-content-between">
											<span><i class="bi bi-google me-1"></i> Prévia no Google (SERP)</span>
											<span class="badge bg-secondary-subtle text-secondary small">Busca Orgânica</span>
										</div>
										<div class="card-body bg-body">
											<div class="p-3 border rounded-3 bg-body-tertiary h-100">
												<div class="small text-body-secondary d-flex align-items-center gap-1 mb-1">
													<i class="bi bi-globe"></i>
													<span><?= BASE_URL; ?></span>
												</div>
												<div class="h5 text-primary mb-1 fw-semibold text-truncate" id="serpTitlePreview">
													<?= !empty($meta_title_home) ? htmlspecialchars($meta_title_home) : 'Pronto Saúde • Cuidados Médicos e Especialidades'; ?>
												</div>
												<div class="small text-body-secondary" id="serpDescPreview" style="line-height: 1.4;">
													<?= !empty($meta_description_home) ? htmlspecialchars($meta_description_home) : 'Clínica médica moderna com especialidades integradas, agendamento de consultas e exames rápidos.'; ?>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Simulador de Compartilhamento Social (WhatsApp, Facebook, LinkedIn) -->
								<div class="col-lg-6">
									<div class="card border h-100 shadow-xs">
										<div class="card-header bg-body-tertiary py-2 px-3 fw-bold text-success d-flex align-items-center justify-content-between">
											<span><i class="bi bi-whatsapp me-1"></i> Prévia Social (WhatsApp / Redes)</span>
											<span class="badge bg-success-subtle text-success small">Proporção 1.91:1</span>
										</div>
										<div class="card-body bg-body">
											<div class="border rounded-3 overflow-hidden bg-body-tertiary shadow-xs">
												<div class="ratio ratio-16x9 bg-dark position-relative" style="max-height: 180px;">
													<?php
													$current_og_url = !empty($og_image_home) && file_exists('../assets/uploads/' . $og_image_home)
														? '../assets/uploads/' . $og_image_home
														: '../assets/uploads/department-1.png';
													?>
													<img src="<?= $current_og_url; ?>" id="ogImagePreviewHome" class="w-100 h-100 object-fit-cover" alt="Prévia Social Home">
												</div>
												<div class="p-3 bg-body">
													<div class="small text-uppercase text-body-secondary fw-semibold letter-spacing-1 mb-1">
														<?= parse_url(BASE_URL, PHP_URL_HOST) ?: 'prontosaude.com.br'; ?>
													</div>
													<div class="fw-bold text-body text-truncate mb-1" id="socialTitlePreviewHome">
														<?= !empty($meta_title_home) ? htmlspecialchars($meta_title_home) : 'Pronto Saúde • Cuidados Médicos e Especialidades'; ?>
													</div>
													<div class="small text-body-secondary text-truncate" id="socialDescPreviewHome">
														<?= !empty($meta_description_home) ? htmlspecialchars($meta_description_home) : 'Clínica médica moderna com especialidades integradas, agendamento de consultas e exames rápidos.'; ?>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Upload da Imagem com Dimensões e Proporções Oficiais -->
							<div class="card border-primary border-opacity-25 shadow-xs mb-4 bg-primary bg-opacity-10 rounded-3">
								<div class="card-body p-3">
									<div class="row align-items-center g-3">
										<div class="col-md-7">
											<div class="d-flex align-items-center gap-2 mb-1">
												<i class="bi bi-image-fill fs-5 text-primary"></i>
												<h6 class="mb-0 fw-bold text-primary">Imagem de Compartilhamento Social (Open Graph / og:image)</h6>
											</div>
											<p class="small text-body-secondary mb-2">
												Esta é a imagem exibida automaticamente em tamanho grande quando links do portal são compartilhados no <strong>WhatsApp, Facebook, LinkedIn e Twitter/X</strong>.
											</p>
											<div class="d-flex flex-wrap gap-2 small">
												<span class="badge bg-primary text-white"><i class="bi bi-aspect-ratio me-1"></i> Proporção Ideal: 1.91:1</span>
												<span class="badge bg-dark text-white"><i class="bi bi-arrows-fullscreen me-1"></i> Tamanho Recomendado: 1200 x 630 px</span>
												<span class="badge bg-light text-dark border"><i class="bi bi-file-earmark-check me-1"></i> Formatos: JPG, PNG, WEBP</span>
											</div>
										</div>
										<div class="col-md-5">
											<label class="form-label fw-semibold small text-body mb-1">Trocar Imagem de Compartilhamento da Home</label>
											<input type="file" name="og_image_home" id="ogImageFileInputHome" class="form-control" accept="image/png, image/jpeg, image/webp">
											<div class="form-text small mt-1">Dica: mantenha textos e logomarcas centralizados.</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row g-3">
								<div class="col-12">
									<div class="d-flex justify-content-between align-items-center">
										<label class="form-label fw-semibold text-body mb-1">Meta Título da Página Inicial (Title Tag)</label>
										<span class="small text-body-secondary" id="titleCharCount">0 / 60</span>
									</div>
									<input type="text" name="meta_title_home" id="metaTitleInput" class="form-control" value="<?= htmlspecialchars($meta_title_home); ?>" maxlength="80" placeholder="Ex: Pronto Saúde • Medicina Integrada e Exames em Nova Iguaçu">
									<div class="form-text small">Recomendado manter até 60 caracteres para não ser truncado no Google.</div>
								</div>

								<div class="col-12">
									<label class="form-label fw-semibold text-body mb-1">Palavras-chave (Meta Keywords)</label>
									<textarea class="form-control no-summernote" data-no-summernote="true" name="meta_keyword_home" rows="2" placeholder="saúde, clínica médica, exames, consultas, cardiologia, pediatria..."><?= htmlspecialchars(strip_tags($meta_keyword_home)); ?></textarea>
									<div class="form-text small">Separe as palavras-chave por vírgulas.</div>
								</div>

								<div class="col-12">
									<div class="d-flex justify-content-between align-items-center">
										<label class="form-label fw-semibold text-body mb-1">Meta Descrição da Página Inicial (Meta Description)</label>
										<span class="small text-body-secondary" id="descCharCount">0 / 160</span>
									</div>
									<textarea class="form-control no-summernote" data-no-summernote="true" name="meta_description_home" id="metaDescInput" rows="3" maxlength="200" placeholder="Resumo atrativo da clínica para os pacientes que encontram o site no Google..."><?= htmlspecialchars(trim(preg_replace('/\s+/', ' ', strip_tags($meta_description_home)))); ?></textarea>
									<div class="form-text small">Recomendado entre 140 e 160 caracteres (sem tags HTML).</div>
								</div>
							</div>

							<div class="mt-4">
								<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" name="form6">
									<i class="bi bi-check-circle-fill"></i> Salvar Metadados e Imagem de SEO
								</button>
							</div>
						</form>
					</div>

					<!-- ========================================================
					     ABA 7: SEÇÕES DA PÁGINA INICIAL
					     ======================================================== -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_7') ? 'show active' : ''; ?>" id="tab_7" role="tabpanel" aria-labelledby="tab7-btn">
						<form action="" method="post">
							<input type="hidden" name="active_tab" value="tab_7">

							<div class="alert alert-info d-flex align-items-center gap-2 mb-4 rounded-3 border-0 shadow-sm" style="background: rgba(13, 202, 240, 0.12); color: #055160;">
								<i class="bi bi-info-circle-fill fs-4 text-info"></i>
								<div class="small">
									Personalize os títulos, subtítulos e a visibilidade de cada uma das seções da página inicial do portal.
								</div>
							</div>

							<div class="row g-4">

								<!-- 1. Serviços -->
								<div class="col-md-6 col-xl-4">
									<div class="card border shadow-xs h-100">
										<div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3">
											<span class="fw-bold text-primary"><i class="bi bi-briefcase me-1"></i> Seção de Serviços</span>
											<div class="form-check form-switch m-0">
												<input class="form-check-input" type="checkbox" name="home_status_service" value="1" id="swService" <?= ($home_status_service == 1) ? 'checked' : ''; ?>>
											</div>
										</div>
										<div class="card-body">
											<div class="mb-2">
												<label class="form-label small fw-semibold text-body mb-1">Título</label>
												<input type="text" name="home_title_service" class="form-control form-control-sm" value="<?= htmlspecialchars($home_title_service); ?>">
											</div>
											<div class="mb-0">
												<label class="form-label small fw-semibold text-body mb-1">Subtítulo</label>
												<input type="text" name="home_subtitle_service" class="form-control form-control-sm" value="<?= htmlspecialchars($home_subtitle_service); ?>">
											</div>
										</div>
									</div>
								</div>

								<!-- 2. Departamentos / Especialidades -->
								<div class="col-md-6 col-xl-4">
									<div class="card border shadow-xs h-100">
										<div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3">
											<span class="fw-bold text-primary"><i class="bi bi-hospital me-1"></i> Departamentos</span>
											<div class="form-check form-switch m-0">
												<input class="form-check-input" type="checkbox" name="home_status_department" value="1" id="swDept" <?= ($home_status_department == 1) ? 'checked' : ''; ?>>
											</div>
										</div>
										<div class="card-body">
											<div class="mb-2">
												<label class="form-label small fw-semibold text-body mb-1">Título</label>
												<input type="text" name="home_title_department" class="form-control form-control-sm" value="<?= htmlspecialchars($home_title_department); ?>">
											</div>
											<div class="mb-0">
												<label class="form-label small fw-semibold text-body mb-1">Subtítulo</label>
												<input type="text" name="home_subtitle_department" class="form-control form-control-sm" value="<?= htmlspecialchars($home_subtitle_department); ?>">
											</div>
										</div>
									</div>
								</div>

								<!-- 3. Médicos / Equipe -->
								<div class="col-md-6 col-xl-4">
									<div class="card border shadow-xs h-100">
										<div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3">
											<span class="fw-bold text-primary"><i class="bi bi-person-badge me-1"></i> Equipe Médica</span>
											<div class="form-check form-switch m-0">
												<input class="form-check-input" type="checkbox" name="home_status_doctor" value="1" id="swDoctor" <?= ($home_status_doctor == 1) ? 'checked' : ''; ?>>
											</div>
										</div>
										<div class="card-body">
											<div class="mb-2">
												<label class="form-label small fw-semibold text-body mb-1">Título</label>
												<input type="text" name="home_title_doctor" class="form-control form-control-sm" value="<?= htmlspecialchars($home_title_doctor); ?>">
											</div>
											<div class="mb-0">
												<label class="form-label small fw-semibold text-body mb-1">Subtítulo</label>
												<input type="text" name="home_subtitle_doctor" class="form-control form-control-sm" value="<?= htmlspecialchars($home_subtitle_doctor); ?>">
											</div>
										</div>
									</div>
								</div>

								<!-- 4. Planos & Preços -->
								<div class="col-md-6 col-xl-4">
									<div class="card border shadow-xs h-100">
										<div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3">
											<span class="fw-bold text-primary"><i class="bi bi-tag me-1"></i> Planos & Preços</span>
											<div class="form-check form-switch m-0">
												<input class="form-check-input" type="checkbox" name="home_status_pricing" value="1" id="swPricing" <?= ($home_status_pricing == 1) ? 'checked' : ''; ?>>
											</div>
										</div>
										<div class="card-body">
											<div class="mb-2">
												<label class="form-label small fw-semibold text-body mb-1">Título</label>
												<input type="text" name="home_title_pricing" class="form-control form-control-sm" value="<?= htmlspecialchars($home_title_pricing); ?>">
											</div>
											<div class="mb-0">
												<label class="form-label small fw-semibold text-body mb-1">Subtítulo</label>
												<input type="text" name="home_subtitle_pricing" class="form-control form-control-sm" value="<?= htmlspecialchars($home_subtitle_pricing); ?>">
											</div>
										</div>
									</div>
								</div>

								<!-- 5. Depoimentos -->
								<div class="col-md-6 col-xl-4">
									<div class="card border shadow-xs h-100">
										<div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3">
											<span class="fw-bold text-primary"><i class="bi bi-chat-quote me-1"></i> Depoimentos</span>
											<div class="form-check form-switch m-0">
												<input class="form-check-input" type="checkbox" name="home_status_testimonial" value="1" id="swTestimonial" <?= ($home_status_testimonial == 1) ? 'checked' : ''; ?>>
											</div>
										</div>
										<div class="card-body">
											<div class="mb-2">
												<label class="form-label small fw-semibold text-body mb-1">Título</label>
												<input type="text" name="home_title_testimonial" class="form-control form-control-sm" value="<?= htmlspecialchars($home_title_testimonial); ?>">
											</div>
											<div class="mb-0">
												<label class="form-label small fw-semibold text-body mb-1">Subtítulo</label>
												<input type="text" name="home_subtitle_testimonial" class="form-control form-control-sm" value="<?= htmlspecialchars($home_subtitle_testimonial); ?>">
											</div>
										</div>
									</div>
								</div>

								<!-- 6. Notícias Recentes -->
								<div class="col-md-6 col-xl-4">
									<div class="card border shadow-xs h-100">
										<div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3">
											<span class="fw-bold text-primary"><i class="bi bi-newspaper me-1"></i> Blog & Notícias</span>
											<div class="form-check form-switch m-0">
												<input class="form-check-input" type="checkbox" name="home_status_news" value="1" id="swNews" <?= ($home_status_news == 1) ? 'checked' : ''; ?>>
											</div>
										</div>
										<div class="card-body">
											<div class="mb-2">
												<label class="form-label small fw-semibold text-body mb-1">Título</label>
												<input type="text" name="home_title_news" class="form-control form-control-sm" value="<?= htmlspecialchars($home_title_news); ?>">
											</div>
											<div class="mb-0">
												<label class="form-label small fw-semibold text-body mb-1">Subtítulo</label>
												<input type="text" name="home_subtitle_news" class="form-control form-control-sm" value="<?= htmlspecialchars($home_subtitle_news); ?>">
											</div>
										</div>
									</div>
								</div>

								<!-- 7. Parceiros -->
								<div class="col-md-6 col-xl-4">
									<div class="card border shadow-xs h-100">
										<div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3">
											<span class="fw-bold text-primary"><i class="bi bi-people me-1"></i> Convênios & Parceiros</span>
											<div class="form-check form-switch m-0">
												<input class="form-check-input" type="checkbox" name="home_status_partner" value="1" id="swPartner" <?= ($home_status_partner == 1) ? 'checked' : ''; ?>>
											</div>
										</div>
										<div class="card-body">
											<div class="mb-2">
												<label class="form-label small fw-semibold text-body mb-1">Título</label>
												<input type="text" name="home_title_partner" class="form-control form-control-sm" value="<?= htmlspecialchars($home_title_partner); ?>">
											</div>
											<div class="mb-0">
												<label class="form-label small fw-semibold text-body mb-1">Subtítulo</label>
												<input type="text" name="home_subtitle_partner" class="form-control form-control-sm" value="<?= htmlspecialchars($home_subtitle_partner); ?>">
											</div>
										</div>
									</div>
								</div>

							</div>

							<div class="mt-4">
								<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" name="form7">
									<i class="bi bi-check-circle-fill"></i> Salvar Todas as Seções da Home
								</button>
							</div>
						</form>
					</div>

					<!-- ========================================================
					     ABA 8: CORES & IDENTIDADE VISUAL
					     ======================================================== -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_8') ? 'show active' : ''; ?>" id="tab_8" role="tabpanel" aria-labelledby="tab8-btn">
						<form action="" method="post">
							<input type="hidden" name="active_tab" value="tab_8">

							<div class="row g-4">
								<div class="col-lg-6">
									<label class="form-label fw-bold text-body mb-2">Cor Principal do Site (Hexadecimal)</label>
									<div class="input-group input-group-lg mb-3">
										<span class="input-group-text bg-body-secondary"><i class="bi bi-eyedropper text-primary"></i></span>
										<input type="text" name="color" id="siteColorInput" class="form-control jscolor" value="<?= htmlspecialchars($color); ?>">
									</div>

									<label class="form-label small fw-semibold text-body mb-2">Paletas Oficiais Recomendadas:</label>
									<div class="d-flex flex-wrap gap-2 mb-4">
										<button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill" onclick="setColorValue('0a6b74')">
											<span style="width:14px;height:14px;border-radius:50%;background:#0a6b74;display:inline-block;"></span> Teal Hospitalar (#0a6b74)
										</button>
										<button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill" onclick="setColorValue('10b981')">
											<span style="width:14px;height:14px;border-radius:50%;background:#10b981;display:inline-block;"></span> Verde Saúde (#10b981)
										</button>
										<button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill" onclick="setColorValue('0284c7')">
											<span style="width:14px;height:14px;border-radius:50%;background:#0284c7;display:inline-block;"></span> Azul Clínico (#0284c7)
										</button>
										<button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill" onclick="setColorValue('06b6d4')">
											<span style="width:14px;height:14px;border-radius:50%;background:#06b6d4;display:inline-block;"></span> Ciano Moderno (#06b6d4)
										</button>
										<button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill" onclick="setColorValue('7c3aed')">
											<span style="width:14px;height:14px;border-radius:50%;background:#7c3aed;display:inline-block;"></span> Púrpura Médica (#7c3aed)
										</button>
									</div>

									<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" name="form8">
										<i class="bi bi-check-circle-fill"></i> Salvar Cor Principal
									</button>
								</div>

								<div class="col-lg-6">
									<label class="form-label fw-bold text-body mb-2">Amostra Visual no Portal</label>
									<div class="p-4 border rounded-3 bg-body-tertiary text-center shadow-xs">
										<div class="p-3 text-white rounded-3 mb-3 fw-bold shadow-sm" id="colorBannerPreview" style="background-color: #<?= htmlspecialchars($color); ?>;">
											<i class="bi bi-shield-plus fs-2 d-block mb-1"></i>
											Pronto Saúde • Medicina & Diagnóstico
										</div>
										<button type="button" class="btn text-white px-4 py-2 fw-semibold shadow-sm rounded-pill" id="colorBtnPreview" style="background-color: #<?= htmlspecialchars($color); ?>;">
											Botão de Ação Primária
										</button>
									</div>
								</div>
							</div>
						</form>
					</div>

					<!-- ========================================================
					     ABA 9: PRELOADER DE INICIALIZAÇÃO
					     ======================================================== -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_9') ? 'show active' : ''; ?>" id="tab_9" role="tabpanel" aria-labelledby="tab9-btn">
						<form action="" method="post">
							<input type="hidden" name="active_tab" value="tab_9">

							<div class="row g-4 align-items-center">
								<div class="col-lg-6">
									<div class="card border shadow-xs">
										<div class="card-header bg-body-tertiary py-2 px-3 fw-bold text-primary">
											<i class="bi bi-hourglass-split me-1"></i> Controle do Preloader
										</div>
										<div class="card-body">
											<p class="text-body-secondary small mb-3">
												O preloader exibe uma animação elegante durante o carregamento inicial dos elementos pesados da página.
											</p>

											<div class="d-flex align-items-center gap-4 py-2">
												<div class="form-check">
													<input class="form-check-input" type="radio" name="preloader" id="preloader_on" value="1" <?= ($preloader == 1) ? 'checked' : ''; ?>>
													<label class="form-check-label fw-bold text-success" for="preloader_on">
														<i class="bi bi-play-circle-fill me-1"></i> Preloader Ativado
													</label>
												</div>

												<div class="form-check">
													<input class="form-check-input" type="radio" name="preloader" id="preloader_off" value="0" <?= ($preloader == 0) ? 'checked' : ''; ?>>
													<label class="form-check-label fw-bold text-body-secondary" for="preloader_off">
														<i class="bi bi-stop-circle-fill me-1"></i> Desativado
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="mt-4">
										<button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm" name="BtnPreloader">
											<i class="bi bi-check-circle-fill"></i> Salvar Status do Preloader
										</button>
									</div>
								</div>

								<div class="col-lg-6">
									<div class="border rounded-3 p-4 bg-body-tertiary text-center shadow-xs">
										<div class="small fw-semibold text-body-secondary mb-3">Animação Demonstrativa</div>
										<div class="d-inline-flex align-items-center justify-content-center p-4 bg-body rounded-circle shadow-sm border border-2 border-primary" style="animation: pulse 1.8s infinite ease-in-out;">
											<i class="bi bi-heart-pulse-fill text-primary fs-1"></i>
										</div>
										<div class="small text-body-secondary mt-3">Carregamento suave hospitalar</div>
									</div>
								</div>
							</div>
						</form>
					</div>

					<!-- ========================================================
					     ABA 10: GOOGLE reCAPTCHA
					     ======================================================== -->
					<div class="tab-pane fade <?= ($active_tab == 'tab_10') ? 'show active' : ''; ?>" id="tab_10" role="tabpanel" aria-labelledby="tab10-btn">
						<form action="" method="post">
							<input type="hidden" name="active_tab" value="tab_10">

							<div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
								<h5 class="fw-bold text-primary mb-0">
									<i class="bi bi-shield-lock-fill me-2"></i> Proteção contra Robôs & Spam (Google reCAPTCHA v2 / v3)
								</h5>
								<span class="badge <?= ($recaptcha_status == 1) ? 'bg-success' : 'bg-secondary'; ?> px-3 py-2 fs-6">
									<?= ($recaptcha_status == 1) ? '<i class="bi bi-check-circle-fill me-1"></i> Ativo no Portal' : '<i class="bi bi-pause-circle-fill me-1"></i> Desativado'; ?>
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
											<?= ($recaptcha_status == 1) ? '<span class="text-success fw-bold">Ativado nos Formulários</span>' : '<span class="text-body-secondary">Desativado</span>'; ?>
										</label>
									</div>
									<small class="text-body-secondary d-block mt-1">Ao desativar, os formulários públicos funcionarão normalmente sem exigir desafio de captcha.</small>
								</div>
							</div>

							<div class="row mb-3">
								<label for="recaptcha_site_key" class="col-sm-3 col-lg-2 col-form-label fw-bold">Chave do Site (Site Key):</label>
								<div class="col-sm-9 col-lg-6">
									<div class="input-group">
										<span class="input-group-text bg-body-secondary"><i class="bi bi-key-fill text-primary"></i></span>
										<input type="text" name="recaptcha_site_key" id="recaptcha_site_key" class="form-control" value="<?= htmlspecialchars($recaptcha_site_key); ?>" placeholder="Ex: 6LdXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX">
									</div>
									<small class="text-body-secondary">Chave pública utilizada para carregar e renderizar o widget no site público.</small>
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
									<small class="text-body-secondary">Chave privada utilizada pelo backend para validar as respostas com a API do Google.</small>
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

				</div><!-- /.tab-content -->
			</div><!-- /.card-body -->
		</div><!-- /.card -->

	</div><!-- /.container-fluid -->
</div><!-- /.app-content -->

<!-- Estilos específicos e script síncrono para abas e interatividade -->
<style>
.ps-tabs-scrollable {
	display: flex;
	flex-wrap: nowrap;
	overflow-x: auto;
	overflow-y: hidden;
	-webkit-overflow-scrolling: touch;
	scrollbar-width: thin;
	gap: 4px;
	padding: 4px 8px 0 8px;
}
.ps-tabs-scrollable .nav-link {
	white-space: nowrap;
	flex-shrink: 0;
	border: none;
	border-radius: 8px 8px 0 0;
	padding: 10px 16px;
	font-weight: 600;
	font-size: 0.92rem;
	color: var(--bs-body-color);
	transition: all 0.2s ease;
	border-bottom: 3px solid transparent;
}
.ps-tabs-scrollable .nav-link:hover {
	color: var(--ps-primary, #0a6b74);
	background: rgba(10, 107, 116, 0.05);
}
.ps-tabs-scrollable .nav-link.active {
	color: var(--ps-primary, #0a6b74) !important;
	background-color: var(--bs-card-bg) !important;
	border-bottom: 3px solid var(--ps-primary, #0a6b74) !important;
	box-shadow: 0 -2px 8px rgba(0,0,0,0.04);
}
@keyframes pulse {
	0% { transform: scale(0.95); opacity: 0.8; }
	50% { transform: scale(1.05); opacity: 1; }
	100% { transform: scale(0.95); opacity: 0.8; }
}
</style>

<script>
(function() {
	// Função auxiliar de cores
	window.setColorValue = function(hex) {
		const input = document.getElementById('siteColorInput');
		if(input) {
			input.value = hex;
			if(input.jscolor) {
				input.jscolor.fromString(hex);
			}
			const banner = document.getElementById('colorBannerPreview');
			const btn = document.getElementById('colorBtnPreview');
			if(banner) banner.style.backgroundColor = '#' + hex;
			if(btn) btn.style.backgroundColor = '#' + hex;
		}
	};

	// Contadores e Preview do SEO
	const titleInput = document.getElementById('metaTitleInput');
	const descInput = document.getElementById('metaDescInput');
	const serpTitle = document.getElementById('serpTitlePreview');
	const serpDesc = document.getElementById('serpDescPreview');
	const socialTitle = document.getElementById('socialTitlePreviewHome');
	const socialDesc = document.getElementById('socialDescPreviewHome');
	const titleCount = document.getElementById('titleCharCount');
	const descCount = document.getElementById('descCharCount');
	const ogFileInput = document.getElementById('ogImageFileInputHome');
	const ogImgElem = document.getElementById('ogImagePreviewHome');

	function updateSEOPreview() {
		const titleVal = titleInput ? (titleInput.value.trim() || 'Pronto Saúde • Cuidados Médicos e Especialidades') : '';
		const descVal = descInput ? (descInput.value.trim() || 'Clínica médica moderna com especialidades integradas, agendamento de consultas e exames rápidos.') : '';

		if(titleInput && serpTitle && titleCount) {
			const len = titleInput.value.length;
			titleCount.textContent = len + ' / 60';
			titleCount.className = len > 60 ? 'small text-danger fw-bold' : 'small text-body-secondary';
			serpTitle.textContent = titleVal;
		}
		if(socialTitle) socialTitle.textContent = titleVal;

		if(descInput && serpDesc && descCount) {
			const len = descInput.value.length;
			descCount.textContent = len + ' / 160';
			descCount.className = len > 160 ? 'small text-danger fw-bold' : 'small text-body-secondary';
			serpDesc.textContent = descVal;
		}
		if(socialDesc) socialDesc.textContent = descVal;
	}

	if(titleInput) titleInput.addEventListener('input', updateSEOPreview);
	if(descInput) descInput.addEventListener('input', updateSEOPreview);
	updateSEOPreview();

	// Preview instantâneo da Imagem Open Graph
	if(ogFileInput && ogImgElem) {
		ogFileInput.addEventListener('change', function() {
			const file = this.files && this.files[0];
			if(file) {
				const reader = new FileReader();
				reader.onload = function(e) {
					ogImgElem.src = e.target.result;
				};
				reader.readAsDataURL(file);
			}
		});
	}

	// Sincronização Síncrona e Definitiva de Abas integrada ao motor global SPA
	function activateTab(tabId) {
		if(!tabId) return;
		tabId = tabId.replace('#', '');
		const targetBtn = document.querySelector(`a[href="#${tabId}"], button[data-bs-target="#${tabId}"]`);
		if (targetBtn) {
			if (window.switchProntoTab) {
				window.switchProntoTab(targetBtn);
			}
			try {
				targetBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
			} catch(e) {}
		}
	}

	// Ao carregar a página
	document.addEventListener('DOMContentLoaded', function() {
		const hash = window.location.hash;
		if(hash && hash.startsWith('#tab_')) {
			activateTab(hash);
		} else {
			const activePhpTab = '<?= $active_tab; ?>';
			if(activePhpTab) activateTab(activePhpTab);
		}
	});
})();
</script>

<?php require_once('footer.php'); ?>