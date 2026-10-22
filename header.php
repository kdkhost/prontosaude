<?php
ob_start();
session_start();
include("admin/config.php");
include("admin/functions.php");
$error_message = '';
$success_message = '';
?>
<?php
// Remove subscrições inativas com mais de 24 horas de forma otimizada
try {
    $pdo->exec("DELETE FROM tbl_subscriber WHERE subs_active=0 AND subs_date_time < NOW() - INTERVAL 1 DAY");
} catch (Exception $e) {
    // Falha silenciosa
}
// Getting the basic data for the website from database
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
$row = $result[0] ?? [];
$logo = $row['logo'] ?? '';
$favicon = $row['favicon'] ?? '';

// Checagem de Modo de Manutenção
$is_maintenance = false;
if (file_exists(__DIR__ . '/.maintenance')) {
    $is_maintenance = true;
} elseif (!empty($row['maintenance_mode']) && $row['maintenance_mode'] == 1) {
    $is_maintenance = true;
}

if ($is_maintenance && empty($_SESSION['user'])) {
    $current_script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($current_script !== 'manutencao.php' && $current_script !== 'login.php') {
        header('Location: ' . BASE_URL . 'manutencao.php');
        exit;
    }
}

$contact_email = $row['contact_email'] ?? '';
$contact_phone = $row['contact_phone'] ?? '';
$color = $row['color'] ?? '';
$preloader  =   $row['preloader'] ?? 0;
$recaptcha_status    = $row['recaptcha_status'] ?? 0;
$recaptcha_site_key  = $row['recaptcha_site_key'] ?? '';
$recaptcha_secret_key= $row['recaptcha_secret_key'] ?? '';
$footer_about                = $row['footer_about'] ?? '';
$footer_copyright            = $row['footer_copyright'] ?? '';
$contact_address             = $row['contact_address'] ?? '';
$contact_fax                 = $row['contact_fax'] ?? '';
$total_recent_news_footer    = $row['total_recent_news_footer'] ?? 3;
$total_popular_news_footer   = $row['total_popular_news_footer'] ?? 3;
$total_recent_news_sidebar   = $row['total_recent_news_sidebar'] ?? 3;
$total_popular_news_sidebar  = $row['total_popular_news_sidebar'] ?? 3;
$total_recent_news_home_page = $row['total_recent_news_home_page'] ?? 3;
$meta_description_home       = $row['meta_description_home'] ?? '';
$meta_keyword_home           = $row['meta_keyword_home'] ?? '';
$meta_title_home             = $row['meta_title_home'] ?? '';
$home_title_service          = $row['home_title_service'] ?? '';
$home_subtitle_service       = $row['home_subtitle_service'] ?? '';
$home_status_service         = $row['home_status_service'] ?? 0;
$home_title_department       = $row['home_title_department'] ?? '';
$home_subtitle_department    = $row['home_subtitle_department'] ?? '';
$home_status_department      = $row['home_status_department'] ?? 0;
$home_title_doctor           = $row['home_title_doctor'] ?? '';
$home_subtitle_doctor        = $row['home_subtitle_doctor'] ?? '';
$home_status_doctor          = $row['home_status_doctor'] ?? 0;
$home_title_pricing          = $row['home_title_pricing'] ?? '';
$home_subtitle_pricing       = $row['home_subtitle_pricing'] ?? '';
$home_status_pricing         = $row['home_status_pricing'] ?? 0;
$home_title_testimonial      = $row['home_title_testimonial'] ?? '';
$home_subtitle_testimonial   = $row['home_subtitle_testimonial'] ?? '';
$home_status_testimonial     = $row['home_status_testimonial'] ?? 0;
$home_title_news             = $row['home_title_news'] ?? '';
$home_subtitle_news          = $row['home_subtitle_news'] ?? '';
$home_status_news            = $row['home_status_news'] ?? 0;
$home_title_partner          = $row['home_title_partner'] ?? '';
$home_subtitle_partner       = $row['home_subtitle_partner'] ?? '';
$home_status_partner         = $row['home_status_partner'] ?? 0;

// Getting the basic data for the website from database
$statement = $pdo->prepare("SELECT * FROM tbl_department WHERE dep_id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
$row2 = $result[0] ?? [];
$clinica = $row2['dep_name'] ?? '';
$email   = $row2['dep_email'] ?? '';

?>
<!DOCTYPE html>
<html dir="ltr" lang="pt-br">
    <head>

        <!-- Meta Tags -->	
        <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <meta name="robots" content="index, follow" />
        <meta name="theme-color" content="#0a6b74">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <link href="../assets/uploads/favicon.png" rel="shortcut icon" type="image/png">
        

        <!-- Showing the SEO related meta tags data -->
        <?php
// Getting the current page URL
        $cur_page = substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1);
        $req_slug = $_REQUEST['slug'] ?? '';

        $og_photo = '';
        $og_title = '';
        $og_slug = '';
        $og_description = '';
        $titulo = $meta_title_home;
        $detalhes = '';
        $atualizado = date('Y-m-d');
        $banner = '';

        if ($cur_page == 'news.php') {
            if (!empty($req_slug)) {
                $statement = $pdo->prepare("SELECT * FROM tbl_news WHERE news_slug=?");
                $statement->execute(array($req_slug));
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result as $row) {
                    $og_photo = $row['photo'] ?? '';
                    $og_title = $row['news_title'] ?? '';
                    $og_slug = $row['news_slug'] ?? '';
                    $og_description = substr(strip_tags($row['news_content'] ?? ''), 0, 200) . '...';
                    echo '<meta name="description" content="' . htmlspecialchars($row['meta_description'] ?? '') . '">';
                    echo '<meta name="keywords" content="' . htmlspecialchars($row['meta_keyword'] ?? '') . '">';
                    echo '<title>' . htmlspecialchars($row['meta_title'] ?? '') . '</title>';
                    echo '<meta property="og:type" content="article" />
                          <meta property="og:image" content="' . BASE_URL . 'assets/uploads/' . htmlspecialchars($og_photo) . '" />
                          <meta property="og:image:alt" content="' . htmlspecialchars($og_title) . '" />
                          <meta property="og:url" content="' . BASE_URL . 'news/' . htmlspecialchars($og_slug) . '" />';
                }
            }
            if (empty($og_title)) {
                echo '<title>Notícias | ' . htmlspecialchars($meta_title_home) . '</title>';
            }
        }

        if ($cur_page == 'page.php') {
            if (!empty($req_slug)) {
                $statement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_slug=?");
                $statement->execute(array($req_slug));
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result as $row) {
                    echo '<meta name="description" content="' . htmlspecialchars($row['meta_description'] ?? '') . '">';
                    echo '<meta name="keywords" content="' . htmlspecialchars($row['meta_keyword'] ?? '') . '">';
                    echo '<title>' . htmlspecialchars($row['meta_title'] ?? '') . '</title>';
                }
            }
        }

        if ($cur_page == 'category.php') {
            if (!empty($req_slug)) {
                $statement = $pdo->prepare("SELECT * FROM tbl_category WHERE category_slug=?");
                $statement->execute(array($req_slug));
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result as $row) {
                    echo '<meta name="description" content="' . htmlspecialchars($row['meta_description'] ?? '') . '">';
                    echo '<meta name="keywords" content="' . htmlspecialchars($row['meta_keyword'] ?? '') . '">';
                    echo '<title>' . htmlspecialchars($row['meta_title'] ?? '') . '</title>';
                    echo '<meta property="og:type" content="website" />
                          <meta property="og:image" content="" />
                          <meta property="og:image:alt" content="' . htmlspecialchars($row['category_name'] ?? '') . '" />
                          <meta property="og:url" content="" />';
                }
            }
        }

        if ($cur_page == 'index.php') {
                echo '<meta name="description" content="' . htmlspecialchars($meta_description_home) . '">';
                echo '<meta name="keywords" content="' . htmlspecialchars($meta_keyword_home) . '">';
                echo '<title>' . htmlspecialchars($meta_title_home) . '</title>';
                echo '<meta property="og:type" content="website" />
                      <meta property="og:image" content="' . BASE_URL . 'assets/uploads/department-1.png" />
                      <meta property="og:url" content="' . BASE_URL . '" />
                      <meta property="og:title" content="' . htmlspecialchars($meta_title_home) . '" />
                      <meta property="og:description" content="' . htmlspecialchars($meta_description_home) . '" />';
                $titulo = $meta_title_home;
        }
        if ($cur_page == "service.php") {
            if (!empty($req_slug)) {
                $statement = $pdo->prepare("SELECT * FROM tbl_service WHERE slug=?");
                $statement->execute(array($req_slug));
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result as $row) {
                    echo '<meta name="description" content="">';
                    echo '<title>' . htmlspecialchars($row['name'] ?? '') . '</title>';
                    echo '<meta property="og:type" content="website" />
                          <meta property="og:image" content="'. BASE_URL . 'assets/uploads/'. htmlspecialchars($row['photo'] ?? '') .'" />
                          <meta property="og:url" content=" " />
                          <meta property="og:title" content="' . htmlspecialchars($row['name'] ?? '') . '" />
                          <meta property="og:description" content="" />';
                }
            }
        }
        if ($cur_page == "odontologia.php") {
            echo "<title>Odontologia | Pronto Saúde</title>";
            echo "<meta name='description' content='Oferecemos tratamentos ortodônticos avançados, que corrigem a dentição, alinham a mordida e protegem os dentes. 
            Temos aparelhos que se encaixam perfeitamente ao seu estilo: colorido para quem quer causar ou estético para quem é mais discreto!'>";
            echo '<meta property="og:type" content="website" />
                  <meta property="og:image" content="" />
                  <meta property="og:image:alt" content="Odontologia" />
                  <meta property="og:url" content="" />';
        }
        if($cur_page == "privacidade.php"){
            $statement = $pdo->prepare("SELECT * FROM tbl_privacidade WHERE status=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row){
                $titulo         = $row['titulo'] ?? 'Política de Privacidade';
                $detalhes       = $row['detalhes'] ?? '';
                $atualizado     = $row['modificado'] ?? date('Y-m-d');
                $banner         = $row['banner'] ?? '';
            }
            
            echo '<title>' . htmlspecialchars($titulo) . '</title>';
            echo '<meta property="og:type" content="website" />
                  <meta property="og:image" content="" />
                  <meta property="og:image:alt" content="' . htmlspecialchars($titulo) . '" />
                  <meta property="og:url" content="" />
                  <meta property="og:title" content="' . htmlspecialchars($titulo) . '" />
                  <meta property="og:description" content="" />';
        }
        if($cur_page == "rsocial.php"){
            $statement = $pdo->prepare("SELECT * FROM tbl_termos WHERE status=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row){
                $titulo         = $row['titulo'] ?? 'Termos de Uso';
                $detalhes       = $row['detalhes'] ?? '';
                $atualizado     = $row['modificado'] ?? date('Y-m-d');
                $banner         = $row['banner'] ?? '';
            }
            
            echo '<title>' . htmlspecialchars($titulo) . '</title>';
        }
        
        if($cur_page == "trabalhe-conosco.php"){
            $statement = $pdo->prepare("SELECT * FROM tbl_trabalhe WHERE status=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row){
                $titulo         = $row['titulo'] ?? 'Trabalhe Conosco';
                $detalhes       = $row['detalhes'] ?? '';
                $atualizado     = $row['modificado'] ?? date('Y-m-d');
            }
            echo '<title>Trabalhe Conosco | Pronto Saúde</title>';
            echo "<meta name='description' content='Oferecemos vagas para diversos segmentos dentro da rede Pronto Saúde; 
            Vagas que podem mudar sua vida e ajudar no seu crescimento profissional e melhorias no seu estágio ...'>";
            echo '<meta property="og:type" content="website" />
                  <meta property="og:image" content="" />
                  <meta property="og:image:alt" content="Trabalhe Conosco" />
                  <meta property="og:url" content="" />
                  <meta property="og:description" content="" />';
        }
        
        if($cur_page == "convenio.php"){
            $statement = $pdo->prepare("SELECT * FROM tbl_convenio WHERE status=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row){
                $titulo         = $row['titulo'] ?? 'Convênio Livre Escolha';
                $detalhes       = $row['detalhes'] ?? '';
                $atualizado     = $row['modificado'] ?? date('Y-m-d');
            }
            echo '<title>Convênio Livre Escolha | Pronto Saúde</title>';
            echo "<meta name='description' content='Sistema de Livre escolha utilizado nos planos de saude. Neste tipo de contratação o plano de saude oferece ao consumidor a liberdade de escolher os profissionais ou os serviços que não pertençam à rede de prestadores de serviços da operadora.'>";
            echo '<meta property="og:type" content="website" />
                  <meta property="og:image" content="" />
                  <meta property="og:image:alt" content="Convênio Livre Escolha" />
                  <meta property="og:url" content="" />
                  <meta property="og:description" content="" />';
        }
        
        if ($cur_page == "servicos.php") {
            echo '<title>Serviços | Pronto Saúde</title>';
            echo '<meta name="description" content="">';
            echo '<meta property="og:type" content="website" />
                  <meta property="og:image" content="" />
                  <meta property="og:image:alt" content="Serviços" />
                  <meta property="og:url" content="" />
                  <meta property="og:description" content="" />';
        }
        
        if ($cur_page == "fidelidade.php") {
            echo '<title>Cartão Fidelidade | Pronto Saúde</title>';
            echo '<meta name="description" content="Conheça nossos Planos Fidelidade e aproveite nossos preços para aderir seu plano agora mesmo">';
            echo '<meta property="og:type" content="website" />
                      <meta property="og:image" content="" />
                      <meta property="og:image" content="" />
                      <meta property="og:image" content="" />
                      <meta property="og:image:alt" content="Cartão Fidelidade" />
                      <meta property="og:url" content="" />
                      <meta property="og:description" content="" />';
            }
        ?>


        <link rel="stylesheet" type="text/css" href="<?= BASE_URL; ?>assets/css/cookie.css" />
	    <script src="<?= BASE_URL; ?>assets/js/cookie.js" data-cfasync="false"></script>
	    
        <!-- Favicon -->
        <link href="<?= BASE_URL; ?>assets/uploads/<?= $favicon; ?>" rel="shortcut icon" type="image/png">
        
        <!-- Google Fonts: Plus Jakarta Sans (Tipografia Moderna Hospitalar) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        
        <!-- Stylesheets -->
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/toastr.min.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/slicknav.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/superfish.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/animate.css">

        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/jquery.bxslider.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/hover.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/magnific-popup.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/style.css?v=<?= time(); ?>">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/responsive.css?v=<?= time(); ?>">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/pronto-modern-frontend.css?v=<?= time(); ?>">
        <script src="<?= BASE_URL; ?>assets/js/modernizr.min.js"></script>

        <?php if ($cur_page == 'news.php' && !empty($og_title)): ?>
            <meta property="og:title" content="<?= htmlspecialchars($og_title); ?>">
            <meta property="og:type" content="website">
            <meta property="og:url" content="<?= BASE_URL; ?>news/<?= htmlspecialchars($og_slug); ?>">
            <meta property="og:description" content="<?= htmlspecialchars($og_description); ?>">
            <meta property="og:image" content="<?= BASE_URL; ?>assets/uploads/<?= htmlspecialchars($og_photo); ?>">
        <?php endif; ?>

        <style>

            <?php $color = '#' . $color; ?>
            .sf-menu li:hover a,
            .department-v2 .department-tab .nav-tabs > li > a,
            .doctor-v1 .item .text h3 a,
            .pricing-v1 .pricing-item:hover .price .hexa .amount,
            .pricing-v1 .pricing-item:hover .price .hexa .time,
            .pricing-v1 .pricing-item:hover .button a,
            .news-v1 .date .day:before,
            .blog h4,
            .widget ul li a:hover,
            .doctor-detail .doctor-detail-tab .nav-tabs>li>a,
            .heading-normal h2,
            .doctor-v2 .text h3 a {
                color: <?= $color; ?>!important;
            }

            .top-bar,
            .sf-menu li li:hover,
            .slider p.button a,
            .service-v1 .text p.button a:hover,
            .department-v2 .department-tab .nav-tabs > li.active > a,
            .department-v2 .department-tab .nav-tabs > li.active > a:hover,
            .department-v2 .department-tab .nav-tabs > li.active > a:focus,
            .department-v2 .department-tab .nav-tabs > li a:hover,
            .department-v2 .department-tab .nav-tabs > li a:focus,
            .department-v2 .department-tab .department-content p.button a,
            .doctor-v1 .item:hover .text,
            .doctor-v1 .social-icons ul li a,
            .pricing-v1 .pricing-item:hover,
            .pricing-v1 .pricing-item .price .hexa,
            .pricing-v1 .pricing-item .button a,
            .testimonial-v1 .overlay,
            .news-v1 .date .day,
            .footer-social,
            .footer-social .item ul li,
            .footer-col h3:after,
            .scrollup i,
            .news-v1 .owl-controls .owl-prev:hover,
            .news-v1 .owl-controls .owl-next:hover,
            .doctor-v1 .owl-controls .owl-prev:hover,
            .doctor-v1 .owl-controls .owl-next:hover,
            .doctor-detail .doctor-single .social ul li a,
            .doctor-detail .contact .icon,
            .doctor-v2 .owl-controls .owl-prev:hover,
            .doctor-v2 .owl-controls .owl-next:hover,
            .doctor-v2 .social-icons ul li a {
                background: <?= $color; ?>!important;
            }

            .department-v2 .department-tab .nav-tabs > li.active > a,
            .department-v2 .department-tab .nav-tabs > li.active > a:hover,
            .department-v2 .department-tab .nav-tabs > li.active > a:focus,
            .department-v2 .department-tab .nav-tabs > li a:hover,
            .department-v2 .department-tab .nav-tabs > li a:focus,
            .footer-social .item ul li a {
                border-color: <?= $color; ?>!important;
            }

            .pricing-v1 .pricing-item .price .hexa:before,
            .widget h4,
            .heading-normal h2 {
                border-bottom-color: <?= $color; ?>!important;
            }
            .pricing-v1 .pricing-item .price .hexa:after {
                border-top-color: <?= $color; ?>!important;
            }

            .slicknav_nav a {
                background: #85b5b9;
            }

            .slicknav_nav .slicknav_row:hover {
                background: #0a6b74;
                color: #fff;
            }

            .slicknav_nav a:hover {
                color: #fff;
                background: #0a6b74;
                border-radius: 0;
                -webkit-border-radius: 0;
                -moz-border-radius: 0;
            }

            .slicknav_nav .slicknav_row:hover a {
                /* background: #1AABDD; */
            }

            .slicknav_btn {
                width: 46px;
                text-indent: -999px;
                background: #85b5b9;
                padding: 10px 10px 10px 7px !important;
            }

            .panel-default>.panel-heading {
                color: #0a6b74;
                background-color: #f1f6f7;
                border-color: #0a6b74;
            }


            /* Demo only */

            .table-wrap {
                background: #fff;
            }

            .table tbody tr:nth-child(odd) {
                background: #f1f6f7;
            }

            @media (min-width: 768px) {
                .table thead tr th,
                .table tbody tr td {
                    /**padding: 15px 20px;**/
                }
            }
            .table>thead>tr>th {
                vertical-align: bottom;
                border-bottom: 2px solid #076774;
            }
            .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
                padding: 8px;
                line-height: 1.42857143;
                vertical-align: top;
                border-top: 1px solid #076774;
            }
            table.dataTable th,
            table.dataTable td {
                white-space: nowrap;
            }
            .acordo {
                margin-bottom: 1px;
                margin-top:1px;
            }
            /** FIM DA TABELA RESPONSIVA **/
        </style>

        <style>
            .dropbtn {
                background-color:#85b5ba;
                border-radius:3px;
                color: white;
                padding: 6px;
                font-size: 13px;
                border: none;
            }

            .dropdown {
                position: relative;
                display: inline-block;
            }

            .dropdown-content {
                display: none;
                position: absolute;
                background-color: #85b5ba;
                min-width: 160px;
                box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
                z-index: 1;
            }

            .dropdown-content a {
                color: black;
                padding: 12px 16px;
                text-decoration: none;
                display: block;
            }

            .dropdown-content a:hover {
                background-color: #0a6b74;
            }

            .dropdown:hover .dropdown-content {
                display: block;
            }

            .dropdown:hover .dropbtn {
                background-color: #87cca4;
            }
        </style>

        <script type="text/javascript" src="//platform-api.sharethis.com/js/sharethis.js#property=5993ef01e2587a001253a261&product=inline-share-buttons"></script>
       
       
       
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
         
        /* Cards de Serviços, Planos e Produtos Personalizáveis */
        .service-card-box {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .service-card-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(10, 107, 116, 0.16);
        }
        .service-card-price-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(10, 107, 116, 0.92);
            color: #ffffff;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            letter-spacing: 0.3px;
            backdrop-filter: blur(4px);
            z-index: 2;
        }
        .service-card-price-badge small {
            font-size: 10px;
            text-transform: uppercase;
            display: inline-block;
            opacity: 0.9;
            font-weight: 600;
            margin-right: 2px;
        }
        .service-card-benefits {
            list-style: none !important;
            padding-left: 0 !important;
            margin: 12px 0 16px 0 !important;
        }
        .service-card-benefits li {
            font-size: 12px !important;
            color: #475569 !important;
            margin-bottom: 6px !important;
            line-height: 1.4 !important;
            display: flex !important;
            align-items: center !important;
        }
        .service-card-benefits li i {
            color: #10b981 !important;
            margin-right: 6px !important;
            font-size: 13px !important;
            flex-shrink: 0 !important;
        }
        .pricing-popular {
            border: 2px solid #0a6b74 !important;
            transform: scale(1.02);
            box-shadow: 0 14px 30px rgba(10, 107, 116, 0.18) !important;
            z-index: 2;
        }
        .pricing-badge-flag {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #ffc107;
            color: #1e293b;
            font-weight: 700;
            font-size: 11px;
            padding: 4px 12px;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            z-index: 3;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .plan-img-container {
            margin: 12px auto;
            max-width: 220px;
            height: 95px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
	</style>
    </head>
    <body>

        <?php
        // Getting Facebook comment code from the database
        $statement = $pdo->prepare("SELECT * FROM tbl_comment WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            echo $row['code_body'];
        }
        ?>

        <?php if($preloader == 1){?>
        <div id="preloader">
            <div id="status"></div>
        </div>
        <?php }?>

        <div class="page-wrapper">

            <!-- Top Bar Start -->
            <div class="top-bar">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 top-contact">
                            <div class="list">
                                <ul>
                                    <li>
                                        <i class="fa fa-envelope-o" style="color:#fff;"></i> 
                                        <a href="mailto:<?= $contact_email; ?>">
                                            <?= $contact_email; ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="list">
                                <i class="fa fa-phone" style="color:#fff;"></i> 
                                <?php
                                $tel = preg_replace("/[^0-9]/", "", $contact_phone);
                                echo "<a href='tel:+55" . $tel . "'>  $contact_phone </a>";
                                ?>
                            </div>


                            <div class="">
                                <div class="list">
                                    <div class="dropdown">
                                        <a href="#" class="dropbtn text-decoration-none text-uppercase">
                                            Contato
                                        </a>
                                        <div class="dropdown-content">
                                            <a href="page/contato">
                                                <i class="fa fa-envelope-o mr-2"></i>
                                                Fale Conosco
                                            </a>
                                            <a href="https://wa.me/+5521985831464?&text=Solicitação%20de%20Agendamento%20feito%20através%20do%20site%20Pronto%20Saúde">
                                                <i class="fa fa-whatsapp mr-2"></i>
                                                WhatsApp
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 top-social">

                            <ul>

                                <?php
                                // Getting and showing all the social media icon URL from the database
                                $statement = $pdo->prepare("SELECT * FROM tbl_social");
                                $statement->execute();
                                $social_data = $statement->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($social_data as $social_row) {
                                    if ($social_row['social_url'] != '') {
                                        echo '<li><a href="' . $social_row['social_url'] . '"><i class="' . $social_row['social_icon'] . '"></i></a></li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Top Bar End -->

            <!-- Backdrop do Menu Deslizante -->
            <div class="ps-drawer-backdrop" id="psDrawerBackdrop"></div>

            <?php
            // Carregamento otimizado de menus
            $statement = $pdo->prepare("SELECT * FROM tbl_menu ORDER BY menu_order ASC");
            $statement->execute();
            $all_menus = $statement->fetchAll(PDO::FETCH_ASSOC);

            $parent_menus = [];
            $children_menus = [];
            foreach ($all_menus as $m) {
                if ($m['menu_parent'] == 0) {
                    $parent_menus[] = $m;
                } else {
                    $children_menus[$m['menu_parent']][] = $m;
                }
            }
            ?>

            <!-- Drawer Deslizante da Esquerda para a Direita -->
            <aside class="ps-offcanvas-drawer" id="psOffcanvasDrawer" role="dialog" aria-modal="true" aria-label="Menu Principal">
                <div class="ps-drawer-header">
                    <div class="ps-drawer-brand">
                        <?php if(!empty($logo)): ?>
                            <img src="<?= BASE_URL; ?>assets/uploads/<?= $logo; ?>" alt="Pronto Saúde" class="ps-drawer-logo">
                        <?php else: ?>
                            <span class="ps-drawer-title"><i class="fa fa-hospital-o me-2"></i> Pronto Saúde</span>
                        <?php endif; ?>
                    </div>
                    <button class="ps-drawer-close-btn" id="btnCloseMobileDrawer" type="button" aria-label="Fechar Menu">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <div class="ps-drawer-quick-cta">
                    <a href="https://wa.me/+5521985831464?text=Olá,%20gostaria%20de%20agendar%20uma%20consulta" target="_blank" rel="noopener noreferrer" class="ps-drawer-cta-btn">
                        <i class="fa fa-whatsapp"></i> Agendamento via WhatsApp
                    </a>
                </div>

                <nav class="ps-drawer-body">
                    <ul class="ps-drawer-menu">
                        <?php foreach ($parent_menus as $p_item): 
                            $has_subs = !empty($children_menus[$p_item['menu_id']]);
                            $p_url = '#';
                            if ($p_item['menu_type'] == 'Category') {
                                $p_url = BASE_URL . 'category/' . $p_item['category_or_page_slug'];
                            } elseif ($p_item['menu_type'] == 'Page') {
                                $p_url = BASE_URL . 'page/' . $p_item['category_or_page_slug'];
                            } elseif ($p_item['menu_type'] == 'Other') {
                                $p_url = $p_item['menu_url'];
                            }
                        ?>
                        <li class="ps-drawer-item <?= $has_subs ? 'has-sub' : ''; ?>">
                            <div class="ps-drawer-link-row">
                                <a href="<?= $p_url; ?>" class="ps-drawer-link <?= $has_subs ? 'has-children' : ''; ?>">
                                    <?= htmlspecialchars($p_item['menu_name']); ?>
                                </a>
                                <?php if($has_subs): ?>
                                <button class="ps-drawer-toggle-sub" type="button" aria-label="Expandir submenu">
                                    <i class="fa fa-chevron-down"></i>
                                </button>
                                <?php endif; ?>
                            </div>

                            <?php if($has_subs): ?>
                            <ul class="ps-drawer-submenu">
                                <?php foreach ($children_menus[$p_item['menu_id']] as $sub_item): 
                                    $s_url = '#';
                                    if ($sub_item['menu_type'] == 'Category') {
                                        $s_url = BASE_URL . 'category/' . $sub_item['category_or_page_slug'];
                                    } elseif ($sub_item['menu_type'] == 'Page') {
                                        $s_url = BASE_URL . 'page/' . $sub_item['category_or_page_slug'];
                                    } elseif ($sub_item['menu_type'] == 'Other') {
                                        $s_url = $sub_item['menu_url'];
                                    }
                                ?>
                                <li>
                                    <a href="<?= $s_url; ?>" class="ps-drawer-sublink">
                                        <i class="fa fa-angle-right"></i>
                                        <span><?= htmlspecialchars($sub_item['menu_name']); ?></span>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <div class="ps-drawer-footer">
                    <div class="ps-drawer-contact-list">
                        <?php if(!empty($contact_phone)): ?>
                        <div class="ps-drawer-contact-item">
                            <i class="fa fa-phone"></i>
                            <a href="tel:+55<?= preg_replace('/[^0-9]/', '', $contact_phone); ?>"><?= htmlspecialchars($contact_phone); ?></a>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($contact_email)): ?>
                        <div class="ps-drawer-contact-item">
                            <i class="fa fa-envelope-o"></i>
                            <a href="mailto:<?= htmlspecialchars($contact_email); ?>"><?= htmlspecialchars($contact_email); ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="ps-drawer-socials">
                        <?php
                        if (isset($social_data)) {
                            foreach ($social_data as $s_row) {
                                if (!empty($s_row['social_url'])) {
                                    echo '<a href="' . $s_row['social_url'] . '" target="_blank" rel="noopener noreferrer"><i class="' . $s_row['social_icon'] . '"></i></a>';
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </aside>

            <!-- Header Start -->
            <header>
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-xs-9 col-sm-9 col-md-4 logo">
                            <a href="<?= BASE_URL; ?>">
                                <img src="<?= BASE_URL; ?>assets/uploads/<?= $logo; ?>" alt="Pronto Saúde">
                            </a>
                        </div>
                        <div class="col-xs-3 col-sm-3 visible-xs visible-sm text-right" style="padding-top: 15px;">
                            <button class="ps-mobile-toggle-btn" id="btnOpenMobileDrawer" type="button" aria-label="Abrir Menu">
                                <span class="ps-hamburger-bar"></span>
                                <span class="ps-hamburger-bar"></span>
                                <span class="ps-hamburger-bar"></span>
                            </button>
                        </div>
                        <div class="col-md-8 nav-wrapper hidden-xs hidden-sm">

                            <!-- Nav Start -->
                            <div class="nav">
                                <ul class="sf-menu">
                                    <?php foreach ($parent_menus as $row): 
                                        $has_sub = !empty($children_menus[$row['menu_id']]);
                                        $m_url = '#';
                                        if ($row['menu_type'] == 'Category') {
                                            $m_url = BASE_URL . 'category/' . $row['category_or_page_slug'];
                                        } elseif ($row['menu_type'] == 'Page') {
                                            $m_url = BASE_URL . 'page/' . $row['category_or_page_slug'];
                                        } elseif ($row['menu_type'] == 'Other') {
                                            $m_url = $row['menu_url'];
                                        }
                                    ?>
                                    <li>
                                        <a href="<?= $m_url; ?>"><span class="menu-title"><?= htmlspecialchars($row['menu_name']); ?></span></a>
                                        <?php if($has_sub): ?>
                                        <ul>
                                            <?php foreach ($children_menus[$row['menu_id']] as $row1): 
                                                $s_url = '#';
                                                if ($row1['menu_type'] == 'Category') {
                                                    $s_url = BASE_URL . 'category/' . $row1['category_or_page_slug'];
                                                } elseif ($row1['menu_type'] == 'Page') {
                                                    $s_url = BASE_URL . 'page/' . $row1['category_or_page_slug'];
                                                } elseif ($row1['menu_type'] == 'Other') {
                                                    $s_url = $row1['menu_url'];
                                                }
                                            ?>
                                            <li>
                                                <a href="<?= $s_url; ?>"><?= htmlspecialchars($row1['menu_name']); ?></a>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <!-- Nav End -->

                        </div>
                    </div>
                </div>
            </header>
            <!-- Header End -->