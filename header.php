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
$og_image_home               = $row['og_image'] ?? '';
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
        <!-- ========================================================
             SISTEMA UNIFICADO DE SEO, OPEN GRAPH & REDES SOCIAIS
             Padrão Oficial: 1200 x 630 pixels (Proporção 1.91:1)
             Compatível com: WhatsApp, Facebook, LinkedIn, Twitter/X
             ======================================================== -->
        <?php
        $cur_page = basename($_SERVER['SCRIPT_NAME'] ?? '');
        $req_slug = trim($_REQUEST['slug'] ?? '');

        // Determina URL Canônica
        $page_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        $page_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $page_uri = $_SERVER['REQUEST_URI'] ?? '/';
        $page_canonical = $page_protocol . $page_host . $page_uri;

        // Metadados Padrão (Fallback da Home)
        $seo_title = !empty($meta_title_home) ? $meta_title_home : 'Pronto Saúde • Cuidados Médicos e Especialidades';
        $seo_desc = !empty($meta_description_home) ? $meta_description_home : 'Clínica médica moderna com diversas especialidades integradas, agendamento de consultas e exames de ponta.';
        $seo_keywords = !empty($meta_keyword_home) ? $meta_keyword_home : 'saúde, clínica médica, exames, consultas, prontosaude';
        $seo_og_type = 'website';
        $seo_image = !empty($og_image_home) ? $og_image_home : 'department-1.png';

        // Lógica por tipo de página
        if ($cur_page == 'news.php') {
            if (!empty($req_slug)) {
                $statement = $pdo->prepare("SELECT * FROM tbl_news WHERE news_slug=? LIMIT 1");
                $statement->execute([$req_slug]);
                $post = $statement->fetch(PDO::FETCH_ASSOC);
                if ($post) {
                    $seo_title = !empty($post['meta_title']) ? $post['meta_title'] : $post['news_title'] . ' | Pronto Saúde';
                    $seo_desc = !empty($post['meta_description']) ? $post['meta_description'] : substr(strip_tags($post['news_content'] ?? ''), 0, 160) . '...';
                    $seo_keywords = !empty($post['meta_keyword']) ? $post['meta_keyword'] : $seo_keywords;
                    $seo_og_type = 'article';
                    if (!empty($post['photo'])) {
                        $seo_image = $post['photo'];
                    }
                }
            } else {
                $seo_title = 'Notícias & Artigos de Saúde | ' . (!empty($meta_title_home) ? $meta_title_home : 'Pronto Saúde');
                $seo_desc = 'Fique por dentro das novidades, dicas de prevenção, campanhas de vacinação e artigos dos nossos especialistas.';
                $st_news = $pdo->prepare("SELECT banner, meta_title, meta_description, meta_keyword FROM tbl_page WHERE page_slug='news' LIMIT 1");
                $st_news->execute();
                $p_news = $st_news->fetch(PDO::FETCH_ASSOC);
                if ($p_news) {
                    if (!empty($p_news['meta_title'])) $seo_title = $p_news['meta_title'];
                    if (!empty($p_news['meta_description'])) $seo_desc = $p_news['meta_description'];
                    if (!empty($p_news['meta_keyword'])) $seo_keywords = $p_news['meta_keyword'];
                    if (!empty($p_news['banner'])) $seo_image = $p_news['banner'];
                }
            }
        } elseif ($cur_page == 'page.php') {
            if (!empty($req_slug)) {
                $statement = $pdo->prepare("SELECT * FROM tbl_page WHERE page_slug=? LIMIT 1");
                $statement->execute([$req_slug]);
                $p_dyn = $statement->fetch(PDO::FETCH_ASSOC);
                if ($p_dyn) {
                    $seo_title = !empty($p_dyn['meta_title']) ? $p_dyn['meta_title'] : $p_dyn['page_name'] . ' | ' . (!empty($meta_title_home) ? $meta_title_home : 'Pronto Saúde');
                    $seo_desc = !empty($p_dyn['meta_description']) ? $p_dyn['meta_description'] : substr(strip_tags($p_dyn['page_content'] ?? ''), 0, 160) . '...';
                    $seo_keywords = !empty($p_dyn['meta_keyword']) ? $p_dyn['meta_keyword'] : $seo_keywords;
                    if (!empty($p_dyn['banner'])) {
                        $seo_image = $p_dyn['banner'];
                    }
                }
            }
        } elseif ($cur_page == 'service.php') {
            if (!empty($req_slug)) {
                $statement = $pdo->prepare("SELECT * FROM tbl_service WHERE slug=? LIMIT 1");
                $statement->execute([$req_slug]);
                $srv = $statement->fetch(PDO::FETCH_ASSOC);
                if ($srv) {
                    $seo_title = $srv['name'] . ' | Pronto Saúde Especialidades';
                    $seo_desc = !empty($srv['short_description']) ? strip_tags($srv['short_description']) : substr(strip_tags($srv['description'] ?? ''), 0, 160) . '...';
                    if (!empty($srv['photo'])) {
                        $seo_image = $srv['photo'];
                    }
                }
            } else {
                $seo_title = 'Serviços & Especialidades Médicas | Pronto Saúde';
            }
        } elseif ($cur_page == 'category.php') {
            if (!empty($req_slug)) {
                $statement = $pdo->prepare("SELECT * FROM tbl_category WHERE category_slug=? LIMIT 1");
                $statement->execute([$req_slug]);
                $cat = $statement->fetch(PDO::FETCH_ASSOC);
                if ($cat) {
                    $seo_title = (!empty($cat['meta_title']) ? $cat['meta_title'] : $cat['category_name']) . ' | Pronto Saúde';
                    if (!empty($cat['meta_description'])) $seo_desc = $cat['meta_description'];
                    if (!empty($cat['meta_keyword'])) $seo_keywords = $cat['meta_keyword'];
                }
            }
        } elseif ($cur_page == 'doctor.php') {
            if (!empty($req_slug)) {
                $statement = $pdo->prepare("SELECT d.*, dg.designation_name FROM tbl_doctor d LEFT JOIN tbl_designation dg ON d.designation_id = dg.designation_id WHERE d.slug=? LIMIT 1");
                $statement->execute([$req_slug]);
                $doc = $statement->fetch(PDO::FETCH_ASSOC);
                if ($doc) {
                    $doc_role = !empty($doc['designation_name']) ? ' - ' . $doc['designation_name'] : '';
                    $seo_title = !empty($doc['meta_title']) ? $doc['meta_title'] : $doc['name'] . $doc_role . ' | Pronto Saúde';
                    $seo_desc = !empty($doc['meta_description']) ? $doc['meta_description'] : 'Conheça o perfil, especialidade e agende sua consulta com ' . $doc['name'] . $doc_role . ' na Pronto Saúde.';
                    $seo_keywords = !empty($doc['meta_keyword']) ? $doc['meta_keyword'] : ($doc['name'] . ', ' . ($doc['designation_name'] ?? '') . ', medico rj, consulta');
                    if (!empty($doc['photo'])) {
                        $seo_image = $doc['photo'];
                    }
                }
            } else {
                $seo_title = 'Corpo Clínico & Médicos Especialistas | Pronto Saúde';
                $seo_desc = 'Conheça nossa equipe de médicos e especialistas dedicados a cuidar da sua saúde integral com atendimento humanizado.';
            }
        } elseif ($cur_page != 'index.php' && !empty($cur_page)) {
            // Páginas fixas do portal (odontologia.php, fidelidade.php, servicos.php, trabalhe-conosco.php, convenio.php, privacidade.php, rsocial.php, doctor.php, etc.)
            $fixed_slug = str_replace('.php', '', $cur_page);
            $st_fixed = $pdo->prepare("SELECT * FROM tbl_page WHERE page_slug=? LIMIT 1");
            $st_fixed->execute([$fixed_slug]);
            $p_fixed = $st_fixed->fetch(PDO::FETCH_ASSOC);

            if ($p_fixed) {
                if (!empty($p_fixed['meta_title'])) $seo_title = $p_fixed['meta_title'];
                else $seo_title = $p_fixed['page_name'] . ' | ' . (!empty($meta_title_home) ? $meta_title_home : 'Pronto Saúde');

                if (!empty($p_fixed['meta_description'])) $seo_desc = $p_fixed['meta_description'];
                elseif (!empty($p_fixed['page_content'])) $seo_desc = substr(strip_tags($p_fixed['page_content']), 0, 160) . '...';

                if (!empty($p_fixed['meta_keyword'])) $seo_keywords = $p_fixed['meta_keyword'];
                if (!empty($p_fixed['banner'])) $seo_image = $p_fixed['banner'];
            } else {
                // Fallbacks padrão caso ainda não haja registro salvo em tbl_page
                switch ($cur_page) {
                    case 'odontologia.php':
                        $seo_title = 'Odontologia Avançada | Pronto Saúde';
                        $seo_desc = 'Tratamentos ortodônticos avançados, implantes, profilaxia e estética dental com profissionais altamente qualificados.';
                        break;
                    case 'fidelidade.php':
                        $seo_title = 'Cartão Fidelidade | Pronto Saúde';
                        $seo_desc = 'Conheça nossos Planos Fidelidade e aproveite descontos e benefícios exclusivos em consultas e exames.';
                        break;
                    case 'servicos.php':
                        $seo_title = 'Serviços Médicos Integrados | Pronto Saúde';
                        $seo_desc = 'Consultas especializadas, exames laboratoriais e diagnósticos com precisão, agilidade e carinho.';
                        break;
                    case 'trabalhe-conosco.php':
                        $seo_title = 'Trabalhe Conosco | Pronto Saúde';
                        $seo_desc = 'Venha fazer parte da equipe Pronto Saúde. Oportunidades para médicos, enfermagem e setor administrativo.';
                        break;
                    case 'convenio.php':
                        $seo_title = 'Convênio Livre Escolha | Pronto Saúde';
                        $seo_desc = 'Atendimento por sistema de livre escolha com reembolso e facilidades para conveniados de diversos planos.';
                        break;
                    case 'privacidade.php':
                        $seo_title = 'Política de Privacidade | Pronto Saúde';
                        $seo_desc = 'Conheça como tratamos e protegemos seus dados pessoais de acordo com a LGPD.';
                        break;
                    case 'rsocial.php':
                        $seo_title = 'Termos de Uso | Pronto Saúde';
                        $seo_desc = 'Termos e condições gerais de uso dos canais e serviços do Pronto Saúde.';
                        break;
                    case 'doctor.php':
                        $seo_title = 'Corpo Clínico & Médicos | Pronto Saúde';
                        $seo_desc = 'Conheça nossa equipe de médicos e especialistas dedicados a cuidar de você e da sua família.';
                        break;
                }
            }
        }

        // Montagem da URL Completa da Imagem para Redes Sociais (WhatsApp, Facebook, Twitter, LinkedIn)
        // Proporção ideal: 1.91:1 (1200 x 630 pixels)
        if (!empty($seo_image) && (strpos($seo_image, 'http://') === 0 || strpos($seo_image, 'https://') === 0)) {
            $og_image_url = $seo_image;
        } elseif (!empty($seo_image) && file_exists(__DIR__ . '/assets/uploads/' . $seo_image)) {
            $og_image_url = rtrim(BASE_URL, '/') . '/assets/uploads/' . ltrim($seo_image, '/');
        } elseif (!empty($og_image_home) && file_exists(__DIR__ . '/assets/uploads/' . $og_image_home)) {
            $og_image_url = rtrim(BASE_URL, '/') . '/assets/uploads/' . ltrim($og_image_home, '/');
        } else {
            $og_image_url = rtrim(BASE_URL, '/') . '/assets/uploads/department-1.png';
        }

        $image_ext = strtolower(pathinfo($og_image_url, PATHINFO_EXTENSION));
        $og_mime = 'image/jpeg';
        if ($image_ext === 'png') $og_mime = 'image/png';
        elseif ($image_ext === 'webp') $og_mime = 'image/webp';
        elseif ($image_ext === 'gif') $og_mime = 'image/gif';
        if (!function_exists('pronto_clean_meta_string')) {
            function pronto_clean_meta_string($text, $max_len = 0) {
                if (empty($text) && !is_numeric($text)) return '';
                $text = preg_replace('/<(script|style)[^>]*?>.*?<\/\\1>/si', '', (string)$text);
                $text = preg_replace('/<br\s*\/?>/i', ' ', $text);
                $text = preg_replace('/<\/(p|div|h[1-6]|li)>/i', ' ', $text);
                $text = strip_tags($text);
                $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $text = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $text);
                $text = preg_replace('/[\r\n\t]+/', ' ', $text);
                $text = preg_replace('/\s+/', ' ', $text);
                $text = trim($text);
                if ($max_len > 0 && mb_strlen($text, 'UTF-8') > $max_len) {
                    $text = mb_substr($text, 0, $max_len - 3, 'UTF-8') . '...';
                }
                return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            }
        }
        ?>

        <!-- Título e Metadados Básicos (Totalmente Sanitizados sem HTML) -->
        <title><?= pronto_clean_meta_string($seo_title, 70); ?></title>
        <meta name="description" content="<?= pronto_clean_meta_string($seo_desc, 160); ?>">
        <meta name="keywords" content="<?= pronto_clean_meta_string($seo_keywords, 255); ?>">
        <link rel="canonical" href="<?= htmlspecialchars($page_canonical, ENT_QUOTES, 'UTF-8'); ?>">

        <!-- Metadados Open Graph (WhatsApp, Facebook, LinkedIn - Proporção Ideal 1.91:1 / 1200x630 px) -->
        <meta property="og:locale" content="pt_BR">
        <meta property="og:type" content="<?= htmlspecialchars($seo_og_type, ENT_QUOTES, 'UTF-8'); ?>">
        <meta property="og:title" content="<?= pronto_clean_meta_string($seo_title, 70); ?>">
        <meta property="og:description" content="<?= pronto_clean_meta_string($seo_desc, 200); ?>">
        <meta property="og:url" content="<?= htmlspecialchars($page_canonical, ENT_QUOTES, 'UTF-8'); ?>">
        <meta property="og:site_name" content="Pronto Saúde">
        <meta property="og:image" content="<?= htmlspecialchars($og_image_url, ENT_QUOTES, 'UTF-8'); ?>">
        <meta property="og:image:secure_url" content="<?= htmlspecialchars($og_image_url, ENT_QUOTES, 'UTF-8'); ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:type" content="<?= $og_mime; ?>">
        <meta property="og:image:alt" content="<?= pronto_clean_meta_string($seo_title, 70); ?>">

        <!-- Metadados Twitter / X Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?= pronto_clean_meta_string($seo_title, 70); ?>">
        <meta name="twitter:description" content="<?= pronto_clean_meta_string($seo_desc, 200); ?>">
        <meta name="twitter:image" content="<?= htmlspecialchars($og_image_url, ENT_QUOTES, 'UTF-8'); ?>">
        <meta name="twitter:image:alt" content="<?= pronto_clean_meta_string($seo_title, 70); ?>">


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