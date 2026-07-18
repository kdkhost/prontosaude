<?php
ob_start();
session_start();
include("config.php");
$error_message = '';
$success_message = '';
$error_message1 = '';
$success_message1 = '';

// Check if the user is logged in or not
if(!isset($_SESSION['user'])) {
	header('location: login.php');
	exit;
}

// A consulta original que estava aqui foi removida. O tbl_settings será carregado depois e obteremos receive_email de lá se necessário.



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
?>
    <?php
    	// Getting the basic data for the website from database
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
        	$favicon = $row['favicon'];
        	$receive_email = $row['receive_email'];
        }
	?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Painel Admin | <?= $row['empresa']; ?></title>
	
	
	<style>
	    ::-webkit-scrollbar-track {
            background-color: #85b5ba29;
        }
        ::-webkit-scrollbar {
            width: 6px;
            background: #85b5ba29;
        }
        ::-webkit-scrollbar-thumb {
            background: #0a6b7440;
        }
        ::-webkit-scrollbar {
            width: 12px;
        }
         
        ::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); 
            border-radius: 10px;
        }
         
        ::-webkit-scrollbar-thumb {
            border-radius: 10px;
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.5); 
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #24a167;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #0a6b74;
            color: white;
        }
        .select2-container--default .select2-results__option[aria-selected=true], .select2-container--default .select2-results__option[aria-selected=true]:hover {
            color: #f0f0f0;
        }
	</style>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	
    <!-- Favicon -->
	<link href="<?php echo BASE_URL; ?>assets/uploads/<?php echo $favicon; ?>" rel="shortcut icon" type="image/png">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/ionicons.min.css">
	<link rel="stylesheet" href="css/datepicker3.css">
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/select2.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.css">
	<link rel="stylesheet" href="css/jquery.fancybox.css">
	<link rel="stylesheet" href="css/AdminLTE.min.css">
	<link rel="stylesheet" href="css/_all-skins.min.css">
	<link rel="stylesheet" href="css/on-off-switch.css"/>
	<link rel="stylesheet" href="style.css">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="https://www.gardeniaorchidea.com.br/assets/plugins/editor.summernote/lang/summernote-pt-BR.js"></script>
</head>

<body class="hold-transition fixed skin-blue sidebar-mini">

	<div class="wrapper">

		<header class="main-header">

			<a href="index.php" class="logo">
				<span class="logo-lg">
				    <img style="height:40px; width:180px;" src="<?= BASE_URL . "assets/uploads/" . $row['logo'] ;?>" alt="" class="" />
				</span>
				<span class="logo-sm">
				    <img style="height:40px; width:180px; margin-left:-3px;" src="<?= BASE_URL . "assets/uploads/" . $row['logo'] ;?>" alt="" class="" />
				</span>
			</a>

			<nav class="navbar navbar-static-top">
				
				<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
					<span class="sr-only">Toggle navigation</span>
				</a>

				<span class="hidden-xs" style="float:left;line-height:50px;color:#fff;padding-left:15px;font-size:18px;">Painel Administrativo</span>
							

				<div class="navbar-custom-menu">
				    
					<ul class="nav navbar-nav">
					    <li>
					        <a href="<?= BASE_URL ?>" target="new" class="text-uppercase" style="font-size:14px;">
							    <i class="fa fa-globe mr-2"></i> 
							    Ver Site
							</a>
					    </li>
						<li class="dropdown user user-menu">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<img src="../assets/uploads/<?= $_SESSION['user']['photo']; ?>" class="user-image" alt="<?= $_SESSION['user']['full_name']; ?>">
								<span class="hidden-xs">
								    <?= $_SESSION['user']['full_name']; ?>
								</span>
							</a>
							<ul class="dropdown-menu">
								<li class="user-footer">
										<a href="profile-edit.php" class="btn btn-default btn-flat">
										    Editar Perfil
										</a>
										<a href="logout.php" class="btn btn-default btn-flat">
										    Deslogar
										</a>
								</li>
							</ul>
						</li>
					</ul>
				</div>

			</nav>
		</header>

  		<?php $cur_page = substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1); ?>

  		<aside class="main-sidebar">
    		<section class="sidebar">
      
      			<ul class="sidebar-menu">

			        <li class="treeview <?php if($cur_page == 'index.php') {echo 'active';} ?>">
			          <a href="index.php">
			            <i class="fa fa-laptop"></i> <span>Painel</span>
			          </a>
			        </li>


					<?php if($_SESSION['user']['role'] == 'Super Admin'): ?>
			        <li class="treeview <?php if( ($cur_page == 'user-add.php')||($cur_page == 'user.php')||($cur_page == 'user-edit.php') ) {echo 'active';} ?>">
			          <a href="user.php">
			            <i class="fa fa-user-plus"></i> <span>Usuário</span>
			          </a>
			        </li>
			    	<?php endif; ?>

					

					<?php 
						if($_SESSION['user']['role'] == 'Super Admin' 
					      || $_SESSION['user']['role'] == 'Admin'):
					?>
			        <li class="treeview <?php if( ($cur_page == 'settings.php') ) {echo 'active';} ?>">
			          <a href="settings.php">
			            <i class="fa fa-cog"></i> <span>Configurações</span>
			          </a>
			        </li>
			        <?php endif; ?>



			        <?php 
						if($_SESSION['user']['role'] == 'Super Admin' 
					      || $_SESSION['user']['role'] == 'Admin'):
					?>
			        <li class="treeview <?php if( ($cur_page == 'page-add.php')||($cur_page == 'page.php')||($cur_page == 'page-edit.php') ) {echo 'active';} ?>">
			          <a href="page.php">
			            <i class="fa fa-file-text"></i> <span>Páginas</span>
			          </a>
			        </li>
			        <?php endif; ?>


			        <?php 
						if($_SESSION['user']['role'] == 'Super Admin'):
					?>
			        <li class="treeview <?php if( ($cur_page == 'menu-add.php')||($cur_page == 'menu.php')||($cur_page == 'menu-edit.php') ) {echo 'active';} ?>">
			          <a href="menu.php">
			            <i class="fa fa-bars"></i> <span>Menu</span>
			          </a>
			        </li>
			        <?php endif; ?>
			        

					<li class="treeview <?php if( ($cur_page == 'category-add.php')||($cur_page == 'category.php')||($cur_page == 'category-edit.php') || ($cur_page == 'news-add.php')||($cur_page == 'news.php')||($cur_page == 'news-edit.php') || ($cur_page == 'comment.php') ) {echo 'active';} ?>">
						<a href="#">
							<i class="fa fa-bell-o"></i>
							<span>Seção de notícias</span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">
							<li><a href="category.php"><i class="fa fa-circle-o"></i> Categoria</a></li>
							<li><a href="news.php"><i class="fa fa-circle-o"></i> Notícias</a></li>
							<?php if($_SESSION['user']['role'] == 'Super Admin'): ?>
							<li><a href="comment.php"><i class="fa fa-circle-o"></i> Comentários</a></li>
							<?php endif; ?>
						</ul>
					</li>

										

					<li class="treeview <?php if( ($cur_page == 'designation-add.php')||($cur_page == 'designation.php')||($cur_page == 'designation-edit.php') || ($cur_page == 'doctor-add.php')||($cur_page == 'doctor.php')||($cur_page == 'doctor-edit.php')||($cur_page == 'extra-add.php')||($cur_page == 'extra.php')||($cur_page == 'extra-edit.php') ) {echo 'active';} ?>">
						<a href="#">
							<i class="fa fa-heartbeat"></i>
							<span>Seção do Médico</span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">
							<li><a href="designation.php"><i class="fa fa-circle-o"></i> Designação</a></li>
							<li><a href="doctor.php"><i class="fa fa-circle-o"></i> Médico</a></li>
							<li><a href="extra-add.php"><i class="fa fa-circle-o"></i> Funções Extras</a></li>
						</ul>
					</li>

					

					<li class="treeview <?php if(($cur_page == 'termos-edit.php')||($cur_page == 'convenio.php')||($cur_page == 'termos-add.php')||($cur_page == 'termos.php')||($cur_page == 'vagas-edit.php')||($cur_page == 'vagas-add.php')||($cur_page == 'trabalhe-conosco.php')||($cur_page == 'privacidade-edit.php')||($cur_page == 'privacidade-add.php')||($cur_page == 'privacidade.php')||($cur_page == 'dental-edit.php')||($cur_page == 'dental-add.php')||($cur_page == 'dental.php')||($cur_page == 'slider-add.php')||($cur_page == 'slider.php')||($cur_page == 'slider-edit.php') || ($cur_page == 'testimonial-add.php')||($cur_page == 'testimonial.php')||($cur_page == 'testimonial-edit.php') || ($cur_page == 'partner-add.php')||($cur_page == 'partner.php')||($cur_page == 'partner-edit.php') || ($cur_page == 'service-add.php')||($cur_page == 'service.php')||($cur_page == 'service-edit.php') || ($cur_page == 'department-add.php')||($cur_page == 'department.php')||($cur_page == 'department-edit.php') ) {echo 'active';} ?>">
						<a href="#">
							<i class="fa fa-wrench"></i>
							<span>Seção de Elementos</span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">
							<li><a href="slider.php"><i class="fa fa-picture-o"></i> Slider</a></li>
							<li class="treeview-item <?php if($cur_page == 'testimonial-add.php') {echo 'active';} ?>">
    						    <a href="testimonial.php"><i class="fa fa-circle-o"></i> 
    						        Depoimento
    						    </a>
							</li>
							<li><a href="partner.php"><i class="fa fa-users"></i> Parceiro</a></li>
							<li><a href="service.php"><i class="fa fa-bullhorn"></i> Serviço</a></li>
							<li><a href="trabalhe-conosco.php"><i class="fa fa-briefcase"></i> Vagas de Emprego</a></li>
							<li><a href="dental.php"><i class="fa fa-arrow-circle-o-right"></i> Serviços Dentários</a></li>
							<li><a href="department.php"><i class="fa fa-university"></i> Departamento</a></li>
							<li><a href="privacidade.php"><i class="fa fa-low-vision"></i> Política de Privacidade</a></li>
							<li><a href="termos.php"><i class="fa fa-gavel"></i> Termos de Privacidade</a></li>
							<li><a href="convenio.php"><i class="fa fa-handshake-o"></i> Convênio Livre Escolha</a></li>
						</ul>
					</li>
					

					<li class="treeview <?php if( ($cur_page == 'pricing-plan-add.php')||($cur_page == 'pricing-plan.php')||($cur_page == 'pricing-plan-edit.php') || ($cur_page == 'pricing-item-add.php')||($cur_page == 'pricing-item.php')||($cur_page == 'pricing-item-edit.php') ) {echo 'active';} ?>">
						<a href="#">
							<i class="fa fa-money"></i>
							<span>Seção de Preços</span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">
							<li><a href="pricing-plan.php"><i class="fa fa-circle-o"></i> Planos</a></li>
							<li><a href="pricing-item.php"><i class="fa fa-circle-o"></i> Itens do Plano</a></li>
						</ul>
					</li>


					<li class="treeview <?php if( ($cur_page == 'faq-category-add.php')||($cur_page == 'faq-category.php')||($cur_page == 'faq-category-edit.php') || ($cur_page == 'faq-add.php')||($cur_page == 'faq.php')||($cur_page == 'faq-edit.php') ) {echo 'active';} ?>">
						<a href="#">
							<i class="fa fa-lightbulb-o"></i>
							<span>Seção FAQ</span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">
							<li><a href="faq-category.php"><i class="fa fa-circle-o"></i> Categoria FAQ</a></li>
							<li><a href="faq.php"><i class="fa fa-circle-o"></i> FAQ</a></li>
						</ul>
					</li>

					

			        


			        <li class="treeview <?php if( ($cur_page == 'photo-category-add.php')||($cur_page == 'photo-category.php')||($cur_page == 'photo-category-edit.php') || ($cur_page == 'photo-add.php')||($cur_page == 'photo.php')||($cur_page == 'photo-edit.php') || ($cur_page == 'video-category-add.php')||($cur_page == 'video-category.php')||($cur_page == 'video-category-edit.php') || ($cur_page == 'video-add.php')||($cur_page == 'video.php')||($cur_page == 'video-edit.php') ) {echo 'active';} ?>">
						<a href="#">
							<i class="fa fa-archive"></i>
							<span>Foto e Vídeo</span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">
							<li><a href="photo-category.php"><i class="fa fa-circle-o"></i> Categoria da Foto</a></li>
							<li><a href="photo.php"><i class="fa fa-circle-o"></i> Galeria de Fotos</a></li>
							<li><a href="video-category.php"><i class="fa fa-circle-o"></i> Categoria de vídeo</a></li>
							<li><a href="video.php"><i class="fa fa-circle-o"></i> Vídeo</a></li>
						</ul>
					</li>

					
					<?php if($_SESSION['user']['role'] == 'Super Admin'): ?>
			        <li class="treeview <?php if( ($cur_page == 'advertisement-home.php') || ($cur_page == 'advertisement-sidebar.php') || ($cur_page == 'advertisement-sidebar-add.php') || ($cur_page == 'advertisement-sidebar-edit.php') || ($cur_page == 'advertisement-category.php') || ($cur_page == 'advertisement-category-add.php') || ($cur_page == 'advertisement-category-edit.php') ) {echo 'active';} ?>">
						<a href="#">
							<i class="fa fa-podcast"></i>
							<span>Propaganda</span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">
							<li><a href="advertisement-home.php"><i class="fa fa-circle-o"></i> Anúncio (Início)</a></li>
							<li><a href="advertisement-sidebar.php"><i class="fa fa-circle-o"></i> Anúncio (barra lateral)</a></li>
							<li><a href="advertisement-category.php"><i class="fa fa-circle-o"></i> Anúncio (categoria)</a></li>
						</ul>
					</li>
					<?php endif; ?>


					<li class="treeview <?php if( ($cur_page == 'file-add.php')||($cur_page == 'file.php')||($cur_page == 'file-edit.php') ) {echo 'active';} ?>">
			          <a href="file.php">
			            <i class="fa fa-upload"></i> 
			            <span>Upload de arquivo (mídia)</span>
			          </a>
			        </li>


					<?php 
						if($_SESSION['user']['role'] == 'Super Admin' 
					      || $_SESSION['user']['role'] == 'Admin'):
					?>
			        <li class="treeview <?php if( ($cur_page == 'social-media.php') ) {echo 'active';} ?>">
			          <a href="social-media.php">
			            <i class="fa fa-address-book"></i> <span>Mídia Social</span>
			          </a>
			        </li>
			        <?php endif; ?>

                    <?php if($_SESSION['user']['role'] == 'Super Admin'): ?>
			        <li class="treeview <?php if($cur_page == 'subscriber.php') {echo 'active';} ?>">
			          <a href="subscriber.php">
			            <i class="fa fa-users"></i> <span>Assinante</span>
			          </a>
			        </li>
			        <?php endif;?>
			        
					<li class="treeview <?php if( ($cur_page == 'profile-edit.php') ) {echo 'active';} ?>">
						<a href="profile-edit.php" class="">
						    <i class="fa fa-edit"></i>
						    <span>Editar Perfil</span>
						</a>
					</li>
			        
			        <li class="treeview" style="background-color: #3a0101; border-left-color: #f50000;">
			            <a href="logout.php">
			                <i class="fa fa-sign-out text-danger"></i> 
			                <span class="text-danger">
			                    Deslogar
			                </span>
			            </a>
			        </li>
        
      			</ul>
    		</section>
  		</aside>

  		<div class="content-wrapper">