<?php
ob_start();
session_start();
include("config.php");
$error_message = '';
$success_message = '';
$error_message1 = '';
$success_message1 = '';
$success_message2 = '';

// Check if the user is logged in or not
if(!isset($_SESSION['user'])) {
	header('location: login.php');
	exit;
}
if(isset($_SESSION['user']['full_name'])) {
	$_SESSION['user']['full_name'] = str_replace(['Pronto Sa?de', 'Sa?de'], ['Pronto Saúde', 'Saúde'], $_SESSION['user']['full_name']);
}

// Current Page Access Level check for all pages
$cur_page = substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);

if($_SESSION['user']['role']=='Admin') {
	if( $cur_page == 'user.php' || $cur_page == 'user-add.php' || $cur_page == 'user-edit.php' || $cur_page == 'user-delete.php' ) {
		header('location: index.php');
		exit;
	}
}

if($_SESSION['user']['role']=='Publisher') {
	if( $cur_page != 'index.php' 
	    && $cur_page != 'profile-edit.php' 
	    && $cur_page != 'subscriber.php' 
	    && $cur_page != 'news.php'
	    && $cur_page != 'news-add.php' 
	    && $cur_page != 'news-edit.php' 
	    && $cur_page != 'news-delete.php' 
	    && $cur_page != 'file.php'
		&& $cur_page != 'file-add.php' 
	    && $cur_page != 'file-edit.php' 
	    && $cur_page != 'file-delete.php' 
	    && $cur_page != 'photo.php'
		&& $cur_page != 'photo-add.php' 
	    && $cur_page != 'photo-edit.php' 
	    && $cur_page != 'photo-delete.php'
	    && $cur_page != 'video.php'
		&& $cur_page != 'video-add.php' 
	    && $cur_page != 'video-edit.php' 
	    && $cur_page != 'video-delete.php'
	) {
		header('location: index.php');
		exit;
	}
}

// Getting basic settings from database
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$row = $statement->fetch(PDO::FETCH_ASSOC) ?: [];
$favicon = $row['favicon'] ?? 'favicon.png';
$receive_email = $row['receive_email'] ?? '';
$empresa = $row['empresa'] ?? 'Pronto Saúde';
$logo = $row['logo'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
	<title>Painel Administrativo | <?= htmlspecialchars($empresa); ?></title>
	
	<!-- Favicon -->
	<link href="<?= BASE_URL; ?>assets/uploads/<?= htmlspecialchars($favicon); ?>" rel="shortcut icon" type="image/png">
	
	<!-- Fontes do Sistema -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
	
	<!-- Ícones: Bootstrap Icons e FontAwesome 6 -->
	<link rel="stylesheet" href="css/bootstrap-icons.min.css">
	<link rel="stylesheet" href="css/font-awesome6.min.css">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	
	<!-- OverlayScrollbars -->
	<link rel="stylesheet" href="css/overlayscrollbars.min.css">
	
	<!-- AdminLTE 4 Core (baseado em Bootstrap 5.3) -->
	<link rel="stylesheet" href="css/adminlte4.min.css">
	
	<!-- Toastr e ApexCharts -->
	<link rel="stylesheet" href="css/toastr.min.css">
	<link rel="stylesheet" href="css/apexcharts.css">
	
	<!-- Componentes e Plugins Legados Compatibilizados -->
	<link rel="stylesheet" href="css/select2.min.css">
	<link rel="stylesheet" href="css/datepicker3.css">
	<link rel="stylesheet" href="css/on-off-switch.css">
	<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
	
	<!-- DataTables & DataTables Responsive (AdminLTE 3/4 Style) -->
	<link rel="stylesheet" href="css/dataTables.bootstrap.css">
	<link rel="stylesheet" href="css/responsive.bootstrap4.min.css">
	<link rel="stylesheet" href="css/responsive.dataTables.min.css">
	
	<!-- Folha de Estilos de Compatibilidade e Customizações AdminLTE 4 -->
	<link rel="stylesheet" href="css/adminlte4-compat.css?v=<?= time(); ?>">
	<link rel="stylesheet" href="style.css?v=<?= time(); ?>">
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
	<div class="app-wrapper">

		<!-- Barra de Navegação Superior (Header / Navbar) Fixa -->
		<nav class="app-header navbar navbar-expand bg-body shadow-sm sticky-top">
			<div class="container-fluid">
				<!-- Lado Esquerdo: Toggle de Sidebar e Título -->
				<ul class="navbar-nav align-items-center">
					<li class="nav-item">
						<a class="nav-link text-body" data-lte-toggle="sidebar" href="#" role="button" title="Recolher / Expandir Menu">
							<i class="bi bi-list fs-4"></i>
						</a>
					</li>
					<li class="nav-item d-none d-md-block ms-2">
						<span class="badge text-bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-semibold">
							<i class="bi bi-shield-check me-1"></i> Pronto Saúde • Gestão Integrada
						</span>
					</li>
				</ul>

				<!-- Lado Direito: Ações, Alternador de Tema e Usuário -->
				<ul class="navbar-nav ms-auto align-items-center gap-2">
					<!-- Botão Ver Site -->
					<li class="nav-item">
						<a href="<?= BASE_URL ?>" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm px-3">
							<i class="bi bi-globe"></i>
							<span class="d-none d-sm-inline">Ver Site</span>
						</a>
					</li>

					<!-- Alternador de Tema (Dark / Light) -->
					<li class="nav-item dropdown">
						<button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Alternar Tema">
							<i class="bi bi-circle-half theme-icon-active"></i>
							<span class="d-none d-md-inline ms-1" id="bd-theme-text">Tema</span>
						</button>
						<ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
							<li>
								<button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-theme-value="light">
									<i class="bi bi-sun-fill text-warning"></i> Modo Claro
								</button>
							</li>
							<li>
								<button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-theme-value="dark">
									<i class="bi bi-moon-stars-fill text-info"></i> Modo Escuro
								</button>
							</li>
							<li>
								<button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-theme-value="auto">
									<i class="bi bi-circle-half text-secondary"></i> Automático
								</button>
							</li>
						</ul>
					</li>

					<!-- Menu do Usuário -->
					<li class="nav-item dropdown user-menu">
						<a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-1" data-bs-toggle="dropdown">
							<img src="../assets/uploads/<?= htmlspecialchars($_SESSION['user']['photo'] ?? 'user.png'); ?>" class="rounded-circle border shadow-sm" alt="<?= htmlspecialchars($_SESSION['user']['full_name']); ?>" style="width: 36px; height: 36px; object-fit: cover;">
							<span class="d-none d-lg-inline fw-semibold text-body"><?= htmlspecialchars($_SESSION['user']['full_name']); ?></span>
						</a>
						<ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 260px;">
							<li class="text-center p-3 border-bottom bg-body-tertiary rounded-top">
								<img src="../assets/uploads/<?= htmlspecialchars($_SESSION['user']['photo'] ?? 'user.png'); ?>" class="rounded-circle shadow-sm mb-2" alt="Foto" style="width: 70px; height: 70px; object-fit: cover;">
								<h6 class="mb-0 fw-bold"><?= htmlspecialchars($_SESSION['user']['full_name']); ?></h6>
								<small class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($_SESSION['user']['role']); ?></small>
							</li>
							<li class="p-2">
								<a href="profile-edit.php" class="dropdown-item d-flex align-items-center gap-2 py-2 rounded">
									<i class="bi bi-person-gear text-primary"></i> Editar Perfil
								</a>
								<div class="dropdown-divider my-1"></div>
								<a href="logout.php" class="dropdown-item d-flex align-items-center gap-2 py-2 rounded text-danger" id="btn-header-logout">
									<i class="bi bi-box-arrow-right"></i> Deslogar
								</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</nav>

		<!-- Barra Lateral (Sidebar / Aside) -->
		<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
			<!-- Logo do Painel -->
			<div class="sidebar-brand">
				<a href="index.php" class="brand-link text-decoration-none d-flex align-items-center justify-content-center">
					<?php if(!empty($logo)): ?>
						<img src="<?= BASE_URL . "assets/uploads/" . $logo ;?>" alt="<?= htmlspecialchars($empresa); ?>" class="brand-image">
					<?php else: ?>
						<span class="brand-text fw-bold text-white fs-5"><i class="bi bi-hospital me-2 text-info"></i><?= htmlspecialchars($empresa); ?></span>
					<?php endif; ?>
				</a>
			</div>

			<!-- Menu Lateral Navegável Agrupado com Treeview e Modo Acordeão -->
			<div class="sidebar-wrapper">
				<?php
					$user_role = $_SESSION['user']['role'] ?? '';

					// 1. Administração
					$admin_pages = [
						'settings.php', 'seguranca.php', 'secoes.php', 
						'page.php', 'page-add.php', 'page-edit.php', 
						'menu.php', 'menu-add.php', 'menu-edit.php', 
						'user.php', 'user-add.php', 'user-edit.php'
					];
					$is_admin_open = in_array($cur_page, $admin_pages);

					// 2. Notícias & Conteúdo
					$news_pages = [
						'category.php', 'category-add.php', 'category-edit.php', 
						'news.php', 'news-add.php', 'news-edit.php', 
						'comment.php'
					];
					$is_news_open = in_array($cur_page, $news_pages);

					// 3. Corpo Clínico
					$doc_pages = [
						'doctor.php', 'doctor-add.php', 'doctor-edit.php', 
						'designation.php', 'designation-add.php', 'designation-edit.php', 
						'extra.php', 'extra-add.php', 'extra-edit.php'
					];
					$is_doc_open = in_array($cur_page, $doc_pages);

					// 4. Serviços & Especialidades
					$service_pages = [
						'service.php', 'service-add.php', 'service-edit.php', 
						'dental.php', 'dental-add.php', 'dental-edit.php', 
						'department.php', 'department-add.php', 'department-edit.php', 
						'convenio.php', 'convenio-edit.php', 'convenio-update.php'
					];
					$is_service_open = in_array($cur_page, $service_pages);

					// 5. Planos & Preços
					$price_pages = [
						'pricing-plan.php', 'pricing-plan-add.php', 'pricing-plan-edit.php', 
						'pricing-item.php', 'pricing-item-add.php', 'pricing-item-edit.php'
					];
					$is_price_open = in_array($cur_page, $price_pages);

					// 6. Galeria & Mídias
					$media_pages = [
						'photo.php', 'photo-add.php', 'photo-edit.php', 
						'photo-category.php', 'photo-category-add.php', 'photo-category-edit.php', 
						'video.php', 'video-add.php', 'video-edit.php', 
						'video-category.php', 'video-category-add.php', 'video-category-edit.php', 
						'file.php', 'file-add.php', 'file-edit.php'
					];
					$is_media_open = in_array($cur_page, $media_pages);

					// 7. Elementos do Portal
					$elem_pages = [
						'slider.php', 'slider-add.php', 'slider-edit.php', 
						'testimonial.php', 'testimonial-add.php', 'testimonial-edit.php', 
						'partner.php', 'partner-add.php', 'partner-edit.php', 
						'trabalhe-conosco.php', 'vagas-add.php', 'vagas-edit.php', 
						'faq.php', 'faq-add.php', 'faq-edit.php', 
						'faq-category.php', 'faq-category-add.php', 'faq-category-edit.php', 
						'privacidade.php', 'privacidade-add.php', 'privacidade-edit.php', 
						'termos.php', 'termos-add.php', 'termos-edit.php'
					];
					$is_elem_open = in_array($cur_page, $elem_pages);

					// 8. Banners Publicitários
					$ad_pages = [
						'advertisement-home.php', 
						'advertisement-sidebar.php', 'advertisement-sidebar-add.php', 'advertisement-sidebar-edit.php', 
						'advertisement-category.php', 'advertisement-category-add.php', 'advertisement-category-edit.php'
					];
					$is_ad_open = in_array($cur_page, $ad_pages);

					// 9. Engajamento & Comunicação
					$engage_pages = [
						'social-media.php', 
						'subscriber.php', 'subscriber-csv.php'
					];
					$is_engage_open = in_array($cur_page, $engage_pages);
				?>
				<nav class="mt-2 mb-4">
					<ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="true">
						
						<!-- 1. PAINEL PRINCIPAL -->
						<li class="nav-item">
							<a href="index.php" class="nav-link <?= ($cur_page == 'index.php') ? 'active' : '' ?>">
								<i class="nav-icon bi bi-speedometer2 text-info"></i>
								<p>Painel Principal</p>
							</a>
						</li>

						<!-- 2. ADMINISTRAÇÃO & CONFIGURAÇÕES -->
						<?php if($user_role == 'Super Admin' || $user_role == 'Admin'): ?>
						<li class="nav-item has-treeview <?= $is_admin_open ? 'menu-open' : '' ?>">
							<a href="#" class="nav-link <?= $is_admin_open ? 'active' : '' ?>">
								<i class="nav-icon bi bi-gear-wide-connected text-primary"></i>
								<p>
									Administração
									<i class="nav-arrow bi bi-chevron-right"></i>
								</p>
							</a>
							<ul class="nav nav-treeview">
								<li class="nav-item">
									<a href="settings.php" class="nav-link <?= ($cur_page == 'settings.php') ? 'active' : '' ?>">
										<i class="nav-icon bi bi-gear-fill text-secondary"></i>
										<p>Configurações Globais</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="seguranca.php" class="nav-link <?= ($cur_page == 'seguranca.php') ? 'active' : '' ?>">
										<i class="nav-icon bi bi-shield-check text-danger"></i>
										<p>Segurança & WAF</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="secoes.php" class="nav-link <?= ($cur_page == 'secoes.php') ? 'active' : '' ?>">
										<i class="nav-icon bi bi-toggles text-success"></i>
										<p>Controle de Seções</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="page.php" class="nav-link <?= in_array($cur_page, ['page.php', 'page-add.php', 'page-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-file-earmark-text-fill text-warning"></i>
										<p>Gestor de Páginas</p>
									</a>
								</li>
								<?php if($user_role == 'Super Admin'): ?>
								<li class="nav-item">
									<a href="menu.php" class="nav-link <?= in_array($cur_page, ['menu.php', 'menu-add.php', 'menu-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-menu-button-wide-fill text-light"></i>
										<p>Menu de Navegação</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="user.php" class="nav-link <?= in_array($cur_page, ['user.php', 'user-add.php', 'user-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-people-fill text-info"></i>
										<p>Usuários do Sistema</p>
									</a>
								</li>
								<?php endif; ?>
							</ul>
						</li>
						<?php endif; ?>

						<!-- 3. PORTAL DE NOTÍCIAS & BLOG -->
						<li class="nav-item has-treeview <?= $is_news_open ? 'menu-open' : '' ?>">
							<a href="#" class="nav-link <?= $is_news_open ? 'active' : '' ?>">
								<i class="nav-icon bi bi-newspaper text-success"></i>
								<p>
									Portal de Notícias
									<i class="nav-arrow bi bi-chevron-right"></i>
								</p>
							</a>
							<ul class="nav nav-treeview">
								<li class="nav-item">
									<a href="news.php" class="nav-link <?= in_array($cur_page, ['news.php', 'news-add.php', 'news-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-file-post"></i>
										<p>Artigos e Postagens</p>
									</a>
								</li>
								<?php if($user_role != 'Publisher'): ?>
								<li class="nav-item">
									<a href="category.php" class="nav-link <?= in_array($cur_page, ['category.php', 'category-add.php', 'category-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-tag"></i>
										<p>Categorias do Blog</p>
									</a>
								</li>
								<?php endif; ?>
								<?php if($user_role == 'Super Admin'): ?>
								<li class="nav-item">
									<a href="comment.php" class="nav-link <?= ($cur_page == 'comment.php') ? 'active' : '' ?>">
										<i class="nav-icon bi bi-chat-dots"></i>
										<p>Comentários</p>
									</a>
								</li>
								<?php endif; ?>
							</ul>
						</li>

						<!-- 4. CORPO CLÍNICO & ATENDIMENTO -->
						<?php if($user_role != 'Publisher'): ?>
						<li class="nav-item has-treeview <?= $is_doc_open ? 'menu-open' : '' ?>">
							<a href="#" class="nav-link <?= $is_doc_open ? 'active' : '' ?>">
								<i class="nav-icon bi bi-heart-pulse-fill text-danger"></i>
								<p>
									Corpo Clínico
									<i class="nav-arrow bi bi-chevron-right"></i>
								</p>
							</a>
							<ul class="nav nav-treeview">
								<li class="nav-item">
									<a href="doctor.php" class="nav-link <?= in_array($cur_page, ['doctor.php', 'doctor-add.php', 'doctor-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-person-badge"></i>
										<p>Médicos Cadastrados</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="designation.php" class="nav-link <?= in_array($cur_page, ['designation.php', 'designation-add.php', 'designation-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-award"></i>
										<p>Especialidades</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="extra.php" class="nav-link <?= in_array($cur_page, ['extra.php', 'extra-add.php', 'extra-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-clock-history"></i>
										<p>Horários e Funções</p>
									</a>
								</li>
							</ul>
						</li>
						<?php endif; ?>

						<!-- 5. SERVIÇOS & CLÍNICAS -->
						<?php if($user_role != 'Publisher'): ?>
						<li class="nav-item has-treeview <?= $is_service_open ? 'menu-open' : '' ?>">
							<a href="#" class="nav-link <?= $is_service_open ? 'active' : '' ?>">
								<i class="nav-icon bi bi-hospital text-info"></i>
								<p>
									Serviços & Clínicas
									<i class="nav-arrow bi bi-chevron-right"></i>
								</p>
							</a>
							<ul class="nav nav-treeview">
								<li class="nav-item">
									<a href="service.php" class="nav-link <?= in_array($cur_page, ['service.php', 'service-add.php', 'service-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-check2-circle"></i>
										<p>Serviços Médicos</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="dental.php" class="nav-link <?= in_array($cur_page, ['dental.php', 'dental-add.php', 'dental-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-bandaid"></i>
										<p>Serviços Dentários</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="department.php" class="nav-link <?= in_array($cur_page, ['department.php', 'department-add.php', 'department-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-building-add"></i>
										<p>Clínicas / Departamentos</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="convenio.php" class="nav-link <?= in_array($cur_page, ['convenio.php', 'convenio-edit.php', 'convenio-update.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-shield-plus"></i>
										<p>Convênios Atendidos</p>
									</a>
								</li>
							</ul>
						</li>
						<?php endif; ?>

						<!-- 6. PLANOS & PREÇOS -->
						<?php if($user_role != 'Publisher'): ?>
						<li class="nav-item has-treeview <?= $is_price_open ? 'menu-open' : '' ?>">
							<a href="#" class="nav-link <?= $is_price_open ? 'active' : '' ?>">
								<i class="nav-icon bi bi-cash-stack text-warning"></i>
								<p>
									Planos & Preços
									<i class="nav-arrow bi bi-chevron-right"></i>
								</p>
							</a>
							<ul class="nav nav-treeview">
								<li class="nav-item">
									<a href="pricing-plan.php" class="nav-link <?= in_array($cur_page, ['pricing-plan.php', 'pricing-plan-add.php', 'pricing-plan-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-credit-card"></i>
										<p>Planos de Saúde</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="pricing-item.php" class="nav-link <?= in_array($cur_page, ['pricing-item.php', 'pricing-item-add.php', 'pricing-item-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-list-check"></i>
										<p>Itens dos Planos</p>
									</a>
								</li>
							</ul>
						</li>
						<?php endif; ?>

						<!-- 7. GALERIA & MÍDIAS -->
						<li class="nav-item has-treeview <?= $is_media_open ? 'menu-open' : '' ?>">
							<a href="#" class="nav-link <?= $is_media_open ? 'active' : '' ?>">
								<i class="nav-icon bi bi-camera-reels-fill text-purple"></i>
								<p>
									Galeria & Mídias
									<i class="nav-arrow bi bi-chevron-right"></i>
								</p>
							</a>
							<ul class="nav nav-treeview">
								<li class="nav-item">
									<a href="photo.php" class="nav-link <?= in_array($cur_page, ['photo.php', 'photo-add.php', 'photo-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-image"></i>
										<p>Galeria de Fotos</p>
									</a>
								</li>
								<?php if($user_role != 'Publisher'): ?>
								<li class="nav-item">
									<a href="photo-category.php" class="nav-link <?= in_array($cur_page, ['photo-category.php', 'photo-category-add.php', 'photo-category-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-folder-image"></i>
										<p>Categorias de Fotos</p>
									</a>
								</li>
								<?php endif; ?>
								<li class="nav-item">
									<a href="video.php" class="nav-link <?= in_array($cur_page, ['video.php', 'video-add.php', 'video-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-play-btn"></i>
										<p>Galeria de Vídeos</p>
									</a>
								</li>
								<?php if($user_role != 'Publisher'): ?>
								<li class="nav-item">
									<a href="video-category.php" class="nav-link <?= in_array($cur_page, ['video-category.php', 'video-category-add.php', 'video-category-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-folder-play"></i>
										<p>Categorias de Vídeos</p>
									</a>
								</li>
								<?php endif; ?>
								<li class="nav-item">
									<a href="file.php" class="nav-link <?= in_array($cur_page, ['file.php', 'file-add.php', 'file-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-cloud-arrow-up-fill"></i>
										<p>Upload de Arquivos</p>
									</a>
								</li>
							</ul>
						</li>

						<!-- 8. ELEMENTOS DO PORTAL -->
						<?php if($user_role != 'Publisher'): ?>
						<li class="nav-item has-treeview <?= $is_elem_open ? 'menu-open' : '' ?>">
							<a href="#" class="nav-link <?= $is_elem_open ? 'active' : '' ?>">
								<i class="nav-icon bi bi-grid-1x2-fill text-info"></i>
								<p>
									Elementos do Portal
									<i class="nav-arrow bi bi-chevron-right"></i>
								</p>
							</a>
							<ul class="nav nav-treeview">
								<li class="nav-item">
									<a href="slider.php" class="nav-link <?= in_array($cur_page, ['slider.php', 'slider-add.php', 'slider-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-images"></i>
										<p>Sliders / Destaques</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="testimonial.php" class="nav-link <?= in_array($cur_page, ['testimonial.php', 'testimonial-add.php', 'testimonial-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-quote"></i>
										<p>Depoimentos</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="partner.php" class="nav-link <?= in_array($cur_page, ['partner.php', 'partner-add.php', 'partner-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-handshake"></i>
										<p>Parceiros Institucionais</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="trabalhe-conosco.php" class="nav-link <?= in_array($cur_page, ['trabalhe-conosco.php', 'vagas-add.php', 'vagas-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-briefcase"></i>
										<p>Vagas de Emprego</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="faq.php" class="nav-link <?= in_array($cur_page, ['faq.php', 'faq-add.php', 'faq-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-question-square"></i>
										<p>Perguntas & Respostas</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="faq-category.php" class="nav-link <?= in_array($cur_page, ['faq-category.php', 'faq-category-add.php', 'faq-category-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-folder"></i>
										<p>Categorias de FAQ</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="privacidade.php" class="nav-link <?= in_array($cur_page, ['privacidade.php', 'privacidade-add.php', 'privacidade-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-shield-lock"></i>
										<p>Política de Privacidade</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="termos.php" class="nav-link <?= in_array($cur_page, ['termos.php', 'termos-add.php', 'termos-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-file-earmark-check"></i>
										<p>Termos de Uso</p>
									</a>
								</li>
							</ul>
						</li>
						<?php endif; ?>

						<!-- 9. BANNERS PUBLICITÁRIOS -->
						<?php if($user_role == 'Super Admin'): ?>
						<li class="nav-item has-treeview <?= $is_ad_open ? 'menu-open' : '' ?>">
							<a href="#" class="nav-link <?= $is_ad_open ? 'active' : '' ?>">
								<i class="nav-icon bi bi-badge-ad-fill text-warning"></i>
								<p>
									Banners Publicitários
									<i class="nav-arrow bi bi-chevron-right"></i>
								</p>
							</a>
							<ul class="nav nav-treeview">
								<li class="nav-item">
									<a href="advertisement-home.php" class="nav-link <?= ($cur_page == 'advertisement-home.php') ? 'active' : '' ?>">
										<i class="nav-icon bi bi-aspect-ratio"></i>
										<p>Banner Inicial</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="advertisement-sidebar.php" class="nav-link <?= in_array($cur_page, ['advertisement-sidebar.php', 'advertisement-sidebar-add.php', 'advertisement-sidebar-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-layout-sidebar"></i>
										<p>Barra Lateral</p>
									</a>
								</li>
								<li class="nav-item">
									<a href="advertisement-category.php" class="nav-link <?= in_array($cur_page, ['advertisement-category.php', 'advertisement-category-add.php', 'advertisement-category-edit.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-grid"></i>
										<p>Categorias</p>
									</a>
								</li>
							</ul>
						</li>
						<?php endif; ?>

						<!-- 10. ENGAJAMENTO & SOCIAL -->
						<li class="nav-item has-treeview <?= $is_engage_open ? 'menu-open' : '' ?>">
							<a href="#" class="nav-link <?= $is_engage_open ? 'active' : '' ?>">
								<i class="nav-icon bi bi-broadcast text-primary"></i>
								<p>
									Engajamento
									<i class="nav-arrow bi bi-chevron-right"></i>
								</p>
							</a>
							<ul class="nav nav-treeview">
								<?php if($user_role == 'Super Admin' || $user_role == 'Admin'): ?>
								<li class="nav-item">
									<a href="social-media.php" class="nav-link <?= ($cur_page == 'social-media.php') ? 'active' : '' ?>">
										<i class="nav-icon bi bi-share-fill"></i>
										<p>Redes Sociais</p>
									</a>
								</li>
								<?php endif; ?>

								<?php if($user_role == 'Super Admin' || $user_role == 'Publisher'): ?>
								<li class="nav-item">
									<a href="subscriber.php" class="nav-link <?= in_array($cur_page, ['subscriber.php', 'subscriber-csv.php']) ? 'active' : '' ?>">
										<i class="nav-icon bi bi-envelope-heart-fill"></i>
										<p>Inscritos Newsletter</p>
									</a>
								</li>
								<?php endif; ?>
							</ul>
						</li>

						<!-- 11. MINHA CONTA & SESSÃO -->
						<li class="nav-header">MINHA CONTA</li>
						<li class="nav-item">
							<a href="profile-edit.php" class="nav-link <?= ($cur_page == 'profile-edit.php') ? 'active' : '' ?>">
								<i class="nav-icon bi bi-person-fill-gear text-info"></i>
								<p>Editar Meus Dados</p>
							</a>
						</li>
						<li class="nav-item mt-2">
							<a href="logout.php" class="nav-link text-danger border border-danger-subtle bg-danger-subtle" id="btn-sidebar-logout">
								<i class="nav-icon bi bi-power"></i>
								<p class="fw-bold">Encerrar Sessão</p>
							</a>
						</li>
					</ul>
				</nav>
			</div>
		</aside>

		<!-- Conteúdo Principal da Aplicação -->
		<main class="app-main">