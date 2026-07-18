<?php
ob_start();
session_start();
include("admin/config.php");
include("admin/functions.php");
$error_message = '';
$success_message = '';
?>
<?php
// Delete all subscribers who did not confirm email within 1 day / 24 hours
$statement = $pdo->prepare("SELECT * FROM tbl_subscriber WHERE subs_active=0");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $subs_date_time = $row['subs_date_time'];
    $current_date_time = date('Y-m-d H:i:s');
    $t1 = strtotime($subs_date_time);
    $t2 = strtotime($current_date_time);
    $diff = $t2 - $t1;
    $res = floor($diff / (60));
    if ($res > 1440) {
        $statement1 = $pdo->prepare("DELETE FROM tbl_subscriber WHERE subs_id=?");
        $statement1->execute(array($row['subs_id']));
    }
}
// Getting the basic data for the website from database
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
$settings_data = $result[0] ?? [];
foreach ($result as $row) {
    $logo = $row['logo'];
    $favicon = $row['favicon'];
    $contact_email = $row['contact_email'];
    $contact_phone = $row['contact_phone'];
    $color = $row['color'];
    $preloader  =   $row['preloader'];
    $footer_about                = $row['footer_about'];
    $footer_copyright            = $row['footer_copyright'];
    $contact_address             = $row['contact_address'];
    $contact_fax                 = $row['contact_fax'];
    $total_recent_news_footer    = $row['total_recent_news_footer'];
    $total_popular_news_footer   = $row['total_popular_news_footer'];
    $total_recent_news_sidebar   = $row['total_recent_news_sidebar'];
    $total_popular_news_sidebar  = $row['total_popular_news_sidebar'];
    $total_recent_news_home_page = $row['total_recent_news_home_page'];
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
}

// Getting the basic data for the website from database
$statement = $pdo->prepare("SELECT * FROM tbl_department WHERE dep_id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row){
    $clinica = $row['dep_name'];
    $email   = $row['dep_email'];
}

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

        if ($cur_page == 'news.php') {
            $statement = $pdo->prepare("SELECT * FROM tbl_news WHERE news_slug=?");
            $statement->execute(array($_REQUEST['slug']));
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                $og_photo = $row['photo'];
                $og_title = $row['news_title'];
                $og_slug = $row['news_slug'];
                $og_description = substr(strip_tags($row['news_content']), 0, 200) . '...';
                echo '<meta name="description" content="' . $row['meta_description'] . '">';
                echo '<meta name="keywords" content="' . $row['meta_keyword'] . '">';
                echo '<title>' . $row['meta_title'] . '</title>';
                echo '<meta property="og:type" content="article" />
                      <meta property="og:image" content="" />
                      <meta property="og:image:alt" content="'.$og_title.'" />
                      <meta property="og:url" content="" />';
            }
        }

        if ($cur_page == 'page.php') {
            $statement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_slug=?");
            $statement->execute(array($_REQUEST['slug']));
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                echo '<meta name="description" content="' . $row['meta_description'] . '">';
                echo '<meta name="keywords" content="' . $row['meta_keyword'] . '">';
                echo '<title>' . $row['meta_title'] . '</title>';
            }
        }

        if ($cur_page == 'category.php') {
            $statement = $pdo->prepare("SELECT * FROM tbl_category WHERE category_slug=?");
            $statement->execute(array($_REQUEST['slug']));
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                echo '<meta name="description" content="' . $row['meta_description'] . '">';
                echo '<meta name="keywords" content="' . $row['meta_keyword'] . '">';
                echo '<title>' . $row['meta_title'] . '</title>';
                echo '<meta property="og:type" content="website" />
                      <meta property="og:image" content="" />
                      <meta property="og:image" content="" />
                      <meta property="og:image" content="" />
                      <meta property="og:image:alt" content="" />
                      <meta property="og:url" content="" />';
            }
        }

        if ($cur_page == 'index.php') {
                echo '<meta name="description" content="' . $meta_description_home . '">';
                echo '<meta name="keywords" content="' . $meta_keyword_home . '">';
                echo '<title>' . $meta_title_home . '</title>';
                echo '<meta property="og:type" content="website" />
                      <meta property="og:image" content="../assets/uploads/department-1.png" />
                      <meta property="og:url" content=" " />
                      <meta property="og:title" content="' . htmlspecialchars($meta_title_home) . '" />
                      <meta property="og:description" content="' . htmlspecialchars($meta_description_home) . '" />';
                $titulo = $meta_title_home;
        }
        if ($cur_page == "service.php") {
            $statement = $pdo->prepare("SELECT * FROM tbl_service WHERE slug=?");
            $statement->execute(array($_REQUEST['slug']));
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                echo '<meta name="description" content="">';
                echo '<title>' . $row['name'] . '</title>';
                echo '<meta property="og:type" content="website" />
                      <meta property="og:image" content="'. BASE_URL . 'assets/uploads/'. $row['photo'] .'" />
                      <meta property="og:url" content=" " />
                      <meta property="og:title" content="' . $row['name'] . '" />
                      <meta property="og:description" content="" />';
            }
        }
        if ($cur_page == "odontologia.php") {
            echo "<title>Odontologia | Pronto Saúde</title>";
            echo "<meta name='description' content='Oferecemos tratamentos ortodônticos avançados, que corrigem a dentição, alinham a mordida e protegem os dentes. 
            Temos aparelhos que se encaixam perfeitamente ao seu estilo: colorido para quem quer causar ou estético para quem é mais discreto!'>";
            echo '<meta property="og:type" content="website" />
                      <meta property="og:image" content="" />
                      <meta property="og:image" content="" />
                      <meta property="og:image" content="" />
                      <meta property="og:image:alt" content="" />
                      <meta property="og:url" content="" />';
             }
        if($cur_page == "privacidade.php"){
            // Getting the basic data for the website from database
            $statement = $pdo->prepare("SELECT * FROM tbl_privacidade WHERE status=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row){
                $titulo         = $row['titulo'];
                $detalhes       = $row['detalhes'];
                $atualizado     = $row['modificado'];
                $banner         = $row['banner'];
            }
            
            echo '<title>' . $titulo . '</title>';
            echo '<meta property="og:type" content="website" />
                      <meta property="og:image" content="" />
                      <meta property="og:image:alt" content="' . $titulo . '" />
                      <meta property="og:url" content="" />
                      <meta property="og:title" content="' . $titulo . '" />
                      <meta property="og:description" content="" />';
        }
        if($cur_page == "rsocial.php"){
            // Getting the basic data for the website from database
            $statement = $pdo->prepare("SELECT * FROM tbl_termos WHERE status=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row){
                $titulo         = $row['titulo'];
                $detalhes       = $row['detalhes'];
                $atualizado     = $row['modificado'];
                $banner         = $row['banner'];
            }
            
            echo '<title>' . $titulo . '</title>';
        }
        
        if($cur_page == "trabalhe-conosco.php"){
            // Getting the basic data for the website from database
            $statement = $pdo->prepare("SELECT * FROM tbl_trabalhe WHERE status=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row){
                $titulo         = $row['titulo'];
                $detalhes       = $row['detalhes'];
                $atualizado     = $row['modificado'];
            }
            //var_dump($row);
            echo '<title>Trabalhe Conosco | Pronto Saúde</title>';
            echo "<meta name='description' content='Oferecemos vagas para diversos segmentos dentro da rede Pronto Saúde; 
            Vagas que podem mudar sua vida e ajudar no seu crescimento profissional e melhorias no seu estágio ...'>";
            echo '<meta property="og:type" content="website" />
                      <meta property="og:image" content="" />
                      <meta property="og:image" content="" />
                      <meta property="og:image" content="" />
                      <meta property="og:image:alt" content="" />
                      <meta property="og:url" content="" />
                      <meta property="og:description" content="" />';
        }
        
        if($cur_page == "convenio.php"){
            // Getting the basic data for the website from database
            $statement = $pdo->prepare("SELECT * FROM tbl_convenio WHERE status=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row){
                $titulo         = $row['titulo'];
                $detalhes       = $row['detalhes'];
                $atualizado     = $row['modificado'];
            }
            //var_dump($row);
            echo '<title>Convênio Livre Escolha | Pronto Saúde</title>';
            echo "<meta name='description' content='Sistema de Livre escolha utilizado nos planos de saude. Neste tipo de contratação o plano de saude oferece ao consumidor a liberdade de escolher os profissionais ou os serviços que não pertençam à rede de prestadores de serviços da operadora.
                A opção pela livre escolha é obrigatória em caso de seguros de saúde, mas não nos planos de saude.  ...'>";
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
        
        <!-- Stylesheets -->
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/slicknav.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/superfish.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/animate.css">

        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/jquery.bxslider.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/hover.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/magnific-popup.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/style.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/responsive.css">
        <script src="<?= BASE_URL; ?>assets/js/modernizr.min.js"></script>

        <?php if ($cur_page == 'news.php'): ?>
            <meta property="og:title" content="<?= $og_title; ?>">
            <meta property="og:type" content="website">
            <meta property="og:url" content="<?= BASE_URL; ?>news/<?= $og_slug; ?>">
            <meta property="og:description" content="<?= $og_description; ?>">
            <meta property="og:image" content="<?= BASE_URL; ?>assets/uploads/<?= $og_photo; ?>">
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
         
        ::-webkit-scrollbar-thumb {
            border-radius: 10px;
            -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.5);
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

            <!-- Header Start -->
            <header>
                <div class="container">
                    <div class="row">
                        <div class="col-md-4 logo">
                            <a href="<?= BASE_URL; ?>">
                                <img src="<?= BASE_URL; ?>assets/uploads/<?= $logo; ?>" alt="">
                            </a>
                        </div>
                        <div class="col-md-8 nav-wrapper">

                            <!-- Nav Start -->
                            <div class="nav">
                                <ul class="sf-menu">

                                    <?php
                                    // Showing the menu dynamically from the database
                                    $statement = $pdo->prepare("SELECT * FROM tbl_menu ORDER BY menu_order ASC");
                                    $statement->execute();
                                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($result as $row) {
                                        echo '<li>';
                                        if ($row['menu_parent'] == 0) {
                                            if ($row['menu_type'] == 'Category') {
                                                echo '<a href="' . BASE_URL . 'category/' . $row['category_or_page_slug'] . '"> 
                                                <span class="menu-title">' . $row['menu_name'] . '</span></a>';
                                            }
                                            if ($row['menu_type'] == 'Page') {
                                                echo '<a href="' . BASE_URL . 'page/' . $row['category_or_page_slug'] . '">
                                                <span class="menu-title">' . $row['menu_name'] . '</span></a>';
                                            }
                                            if ($row['menu_type'] == 'Other') {
                                                echo '<a href="' . $row['menu_url'] . '"><span class="menu-title">' . $row['menu_name'] . '</span></a>';
                                            }
                                        }

                                        $statement1 = $pdo->prepare("SELECT * FROM tbl_menu WHERE menu_parent=?");
                                        $statement1->execute(array($row['menu_id']));
                                        $total = $statement1->rowCount();
                                        if ($total) {
                                            echo '<ul>';
                                            $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($result1 as $row1) {
                                                echo '<li>';
                                                if ($row1['menu_type'] == 'Category') {
                                                    echo '<a href="' . BASE_URL . 'category/' . $row1['category_or_page_slug'] . '">';
                                                }
                                                if ($row1['menu_type'] == 'Page') {
                                                    echo '<a href="' . BASE_URL . 'page/' . $row1['category_or_page_slug'] . '">';
                                                }
                                                if ($row1['menu_type'] == 'Other') {
                                                    echo '<a href="' . $row1['menu_url'] . '">';
                                                }
                                                echo $row1['menu_name'];
                                                echo '</a>';
                                                echo '</li>';
                                            }
                                            echo '</ul>';
                                        }
                                        echo '</li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                            <!-- Nav End -->

                        </div>
                    </div>
                </div>
            </header>
            <!-- Header End -->