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
$home_status_partner         = $row['home_status_partner'] ?? 0;

// Parâmetros PWA & Popup Promocional
$pwa_status      = (int)($row['pwa_status'] ?? 1);
$pwa_app_name    = $row['pwa_app_name'] ?? 'Pronto Saúde | Centro Médico & Odontológico';
$pwa_short_name  = $row['pwa_short_name'] ?? 'Pronto Saúde';
$pwa_theme_color = $row['pwa_theme_color'] ?? '#0a6b74';
$pwa_icon_192    = !empty($row['pwa_icon_192']) ? BASE_URL . 'assets/uploads/' . $row['pwa_icon_192'] : BASE_URL . 'assets/uploads/' . ($favicon ?: 'favicon.png');

$popup_status    = (int)($row['popup_status'] ?? 0);
$popup_title     = $row['popup_title'] ?? '';
$popup_text      = $row['popup_text'] ?? '';
$popup_image     = !empty($row['popup_image']) ? BASE_URL . 'assets/uploads/' . $row['popup_image'] : '';
$popup_button_text = $row['popup_button_text'] ?? 'Saiba Mais';
$popup_button_url = $row['popup_button_url'] ?? '';
$popup_delay     = max(500, (int)($row['popup_delay'] ?? 2000));
$popup_expire_hours = max(1, (int)($row['popup_expire_hours'] ?? 24));

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

        // Sincroniza páginas nativas do sistema com tbl_page
        if (function_exists('pronto_sync_system_pages')) {
            pronto_sync_system_pages($pdo);
        }

        // Bloqueio de rota segura caso a página tenha sido desativada pelo administrador
        $cur_page_clean = strtolower(str_replace('.php', '', $cur_page));
        if ($cur_page !== 'index.php' && $cur_page_clean !== 'page' && !empty($cur_page_clean) && function_exists('pronto_is_page_active')) {
            if (!pronto_is_page_active($cur_page_clean, $pdo)) {
                header("Location: " . BASE_URL . "?pagina_indisponivel=" . urlencode($cur_page_clean));
                exit;
            }
        }
        if ($cur_page === 'page.php' && !empty($req_slug) && function_exists('pronto_is_page_active')) {
            if (!pronto_is_page_active($req_slug, $pdo)) {
                header("Location: " . BASE_URL . "?pagina_indisponivel=" . urlencode($req_slug));
                exit;
            }
        }

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


        <!-- Progressive Web App (PWA) Tags Configuráveis pelo Painel Admin -->
        <?php if ($pwa_status == 1): ?>
        <link rel="manifest" href="<?= BASE_URL; ?>manifest.webmanifest.php">
        <meta name="theme-color" content="<?= htmlspecialchars($pwa_theme_color, ENT_QUOTES, 'UTF-8'); ?>">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($pwa_short_name, ENT_QUOTES, 'UTF-8'); ?>">
        <link rel="apple-touch-icon" href="<?= htmlspecialchars($pwa_icon_192, ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>

        <!-- Favicon -->
        <link href="<?= BASE_URL; ?>assets/uploads/<?= $favicon; ?>" rel="shortcut icon" type="image/png">

        <!-- Google Fonts: Plus Jakarta Sans & Outfit (Tipografia Médica de Alta Conversão) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        
        <!-- Bootstrap 5.3.3, FontAwesome 6, Flaticon UIcons & Bootstrap Icons (Universal) -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/v4-shims.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
        <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-rounded/css/uicons-solid-rounded.css">

        <!-- Swiper 11 Slider (Carrosséis Touch e Drag) -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

        <!-- Fancybox 5 (Lightbox Imersivo para Fotos e Vídeos) -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">

        <!-- Toastr & SweetAlert2 (Notificações Globais) -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

        <!-- Estilos Oficiais Pronto Saúde -->
        <link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/pronto-modern-frontend.css?v=<?= time(); ?>">
    </head>
    <body class="ps-foco-body" id="inicio">

        <?php
        // Facebook comment code from database
        try {
            $statement = $pdo->prepare("SELECT * FROM tbl_comment WHERE id=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                echo $row['code_body'] ?? '';
            }
        } catch (Exception $e) {}
        ?>

        <?php if($preloader == 1): ?>
        <!-- Preloader Oficial Pronto Saúde (Configurável no Painel Admin) -->
        <div id="preloader" aria-hidden="true">
            <div id="status">
                <div class="ps-preloader-spinner-wrap">
                    <div class="ps-preloader-ring"></div>
                    <div class="ps-preloader-center">
                        <i class="fas fa-heartbeat ps-preloader-icon"></i>
                    </div>
                </div>
                <div class="ps-preloader-brand">
                    <span class="ps-preloader-title">Pronto Saúde</span>
                    <span class="ps-preloader-sub">Cuidando de você com excelência...</span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php
        // Carregamento de mídias sociais
        $social_data = [];
        try {
            $statement = $pdo->prepare("SELECT * FROM tbl_social WHERE social_url != ''");
            $statement->execute();
            $social_data = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}

        // Limpeza do número de WhatsApp
        $whats_raw = !empty($contact_phone) ? $contact_phone : '21985831464';
        $whats_clean = preg_replace('/\D/', '', $whats_raw);
        if (strlen($whats_clean) <= 11) {
            $whats_clean = '55' . $whats_clean;
        }

        // Carregamento otimizado de menus
        $all_menus = [];
        try {
            $statement = $pdo->prepare("SELECT * FROM tbl_menu ORDER BY menu_order ASC");
            $statement->execute();
            $all_menus = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}

        $parent_menus = [];
        $children_menus = [];
        foreach ($all_menus as $m) {
            if (function_exists('pronto_is_menu_active') && !pronto_is_menu_active($m, $pdo)) {
                continue;
            }
            if ($m['menu_parent'] == 0) {
                $parent_menus[] = $m;
            } else {
                $children_menus[$m['menu_parent']][] = $m;
            }
        }

        $parent_menus = array_filter($parent_menus, function($p) use ($children_menus) {
            $url = trim($p['menu_url'] ?? '');
            $type = $p['menu_type'] ?? '';
            if ($type === 'Other' && ($url === '#' || empty($url))) {
                return !empty($children_menus[$p['menu_id']]);
            }
            return true;
        });

        if (!function_exists('pronto_resolve_menu_url')) {
            function pronto_resolve_menu_url($item) {
                $type = $item['menu_type'] ?? '';
                $slug = trim($item['category_or_page_slug'] ?? '');
                $raw_url = trim($item['menu_url'] ?? '');

                if ($type === 'Category') {
                    return BASE_URL . 'category/' . $slug;
                }
                if ($type === 'Page') {
                    return BASE_URL . 'page/' . $slug;
                }
                if ($type === 'Other') {
                    if ($raw_url === '#' || empty($raw_url)) {
                        return '#';
                    }
                    if (preg_match('/^https?:\/\//i', $raw_url)) {
                        return $raw_url;
                    }
                    if (strpos($raw_url, '../') === 0) {
                        return BASE_URL . substr($raw_url, 3);
                    }
                    if (strpos($raw_url, '/') === 0) {
                        return BASE_URL . ltrim($raw_url, '/');
                    }
                    return BASE_URL . $raw_url;
                }
                return BASE_URL;
            }
        }

        if (!function_exists('pronto_is_menu_item_active')) {
            function pronto_is_menu_item_active($item, $current_uri, $script_name) {
                $type = $item['menu_type'] ?? '';
                $slug = trim($item['category_or_page_slug'] ?? '');
                $raw_url = trim($item['menu_url'] ?? '');

                $is_home = ($current_uri === '/' || $current_uri === '' || $script_name === 'index.php');
                if ($is_home) {
                    if ($slug === 'home' || $raw_url === '../' || $raw_url === './' || $raw_url === BASE_URL) {
                        return true;
                    }
                }

                if (!empty($slug)) {
                    if (strpos($current_uri, '/' . $slug) !== false || $script_name === ($slug . '.php')) {
                        return true;
                    }
                }

                if ($type === 'Other' && !empty($raw_url) && $raw_url !== '#') {
                    $clean_file = basename(parse_url($raw_url, PHP_URL_PATH));
                    if (!empty($clean_file) && $clean_file !== '..' && $clean_file !== '.') {
                        if ($script_name === $clean_file || strpos($current_uri, '/' . $clean_file) !== false) {
                            return true;
                        }
                    }
                }

                return false;
            }
        }

        $current_req_uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $current_script_file = basename($_SERVER['SCRIPT_NAME'] ?? '');
        ?>

        <!-- 1. CABEÇALHO GLOBAL (TOPBAR + NAVBAR IDENTIDADE VISUAL PRONTO SAÚDE) -->
        <header class="foco-header-fixed" id="focoHeader">
            <!-- TOPBAR DE CONTATO E HORÁRIOS -->
            <div class="foco-topbar d-none d-md-block">
                <div class="container d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-4">
                        <span><i class="fas fa-clock text-white-50 me-1"></i> Seg a Sex: 07h às 19h | Sáb: 07h às 13h</span>
                        <?php if(!empty($contact_phone)): ?>
                            <span><i class="fas fa-phone-alt text-white-50 me-1"></i> <?= htmlspecialchars($contact_phone); ?></span>
                        <?php endif; ?>
                        <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de falar com o atendimento Pronto Saúde'); ?>" target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp Oficial
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <?php if(!empty($social_data)): ?>
                            <?php foreach ($social_data as $s_row): ?>
                                <?php if(!empty($s_row['social_url'])): 
                                    $s_ico = $s_row['social_icon'] ?? '';
                                    if (strpos($s_ico, 'fa-') !== false && strpos($s_ico, 'fab ') === false && strpos($s_ico, 'fas ') === false) {
                                        $s_ico = 'fab ' . preg_replace('/^fa\s+fa-/', 'fa-', $s_ico);
                                        if ($s_ico === 'fab fa-facebook') $s_ico = 'fab fa-facebook-f';
                                    }
                                ?>
                                    <a href="<?= $s_row['social_url']; ?>" target="_blank" rel="noopener noreferrer" title="Rede Social" data-bs-toggle="tooltip" data-bs-placement="bottom">
                                        <i class="<?= $s_ico; ?>"></i>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <a href="<?= BASE_URL; ?>admin/login.php" class="btn-cliente-topo">
                            <i class="fas fa-user-lock me-1"></i> Área do Paciente / Painel
                        </a>
                    </div>
                </div>
            </div>

            <!-- NAVBAR NAVEGAÇÃO PRONTO SAÚDE (Fundo Teal #0a6b74 Oficial) -->
            <nav class="navbar navbar-expand-lg foco-navbar" id="mainNavbar">
                <div class="container">
                    <!-- Brand: Logo Clara sobre o fundo Teal da Navbar -->
                    <a class="navbar-brand d-flex align-items-center logo-on-dark" href="<?= BASE_URL; ?>">
                        <?php if(!empty($logo)): ?>
                            <!-- Logo Clara para partes escuras -->
                            <img src="<?= BASE_URL; ?>assets/uploads/<?= $logo; ?>" alt="Pronto Saúde" height="42" class="ps-logo ps-logo-light">
                            <!-- Logo Escura para partes claras -->
                            <img src="<?= BASE_URL; ?>assets/uploads/logo-dark.png" alt="Pronto Saúde" height="42" class="ps-logo ps-logo-dark">
                        <?php else: ?>
                            <span class="fw-bold fs-4 text-white"><i class="fas fa-hospital-user text-white-50 me-2"></i> Pronto Saúde</span>
                        <?php endif; ?>
                    </a>

                    <!-- BOTÃO HAMBÚRGUER ESTILO ANDROID (Mobile) -->
                    <button class="navbar-toggler d-lg-none btn-android-burger" type="button" id="btnOpenMenuDrawer" data-bs-toggle="offcanvas" data-bs-target="#menuDrawerAndroid" aria-controls="menuDrawerAndroid" aria-label="Abrir Menu de Navegação">
                        <i class="fas fa-bars"></i>
                    </button>

                    <!-- MENU HORIZONTAL DESKTOP (>= 992px) -->
                    <div class="collapse navbar-collapse d-none d-lg-flex" id="navDesktopFoco">
                        <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-lg-center">
                            <?php foreach ($parent_menus as $p_item): 
                                $has_subs = !empty($children_menus[$p_item['menu_id']]);
                                $p_url = pronto_resolve_menu_url($p_item);
                                $p_active = pronto_is_menu_item_active($p_item, $current_req_uri, $current_script_file);
                                if (!$p_active && $has_subs) {
                                    foreach ($children_menus[$p_item['menu_id']] as $sub_chk) {
                                        if (pronto_is_menu_item_active($sub_chk, $current_req_uri, $current_script_file)) {
                                            $p_active = true;
                                            break;
                                        }
                                    }
                                }
                            ?>
                                <?php if($has_subs): ?>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle <?= $p_active ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                            <?= htmlspecialchars($p_item['menu_name']); ?>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <?php foreach ($children_menus[$p_item['menu_id']] as $sub_item): 
                                                $s_url = pronto_resolve_menu_url($sub_item);
                                                $s_active = pronto_is_menu_item_active($sub_item, $current_req_uri, $current_script_file);
                                            ?>
                                                <li>
                                                    <a class="dropdown-item <?= $s_active ? 'active' : ''; ?>" href="<?= $s_url; ?>">
                                                        <i class="fas fa-angle-right me-2"></i><?= htmlspecialchars($sub_item['menu_name']); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                <?php else: ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $p_active ? 'active' : ''; ?>" href="<?= $p_url; ?>">
                                            <?= htmlspecialchars($p_item['menu_name']); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                        <div class="d-flex flex-column flex-lg-row gap-2 align-items-stretch align-items-lg-center mt-3 mt-lg-0">
                            <a href="<?= BASE_URL; ?>servicos.php" class="btn-nav-outline d-none d-xl-inline-flex align-items-center gap-1 text-decoration-none">
                                <i class="fas fa-stethoscope"></i> Especialidades
                            </a>
                            <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar uma consulta na Pronto Saúde'); ?>" target="_blank" rel="noopener" class="btn-nav-agendar text-decoration-none">
                                <i class="fas fa-calendar-check me-1"></i> Agendar Consulta
                            </a>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <!-- MENU GAVETA DESLIZANTE NATIVO ANDROID (Mobile & Tablet) -->
        <div class="offcanvas offcanvas-start foco-android-drawer" tabindex="-1" id="menuDrawerAndroid" aria-labelledby="menuDrawerLabel">
            <!-- 1. Header do Side Menu: Logo e Botão de Fechar alinhado à extrema direita -->
            <div class="offcanvas-header android-drawer-header">
                <div class="d-flex align-items-center gap-2 logo-on-dark">
                    <?php if(!empty($logo)): ?>
                        <img src="<?= BASE_URL; ?>assets/uploads/<?= $logo; ?>" alt="Pronto Saúde" height="34" class="ps-logo ps-logo-light">
                    <?php else: ?>
                        <span class="fw-bold text-white"><i class="fas fa-hospital-user text-white-50 me-2"></i> Pronto Saúde</span>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-android-close ms-auto" data-bs-dismiss="offcanvas" aria-label="Fechar menu" title="Fechar Menu">
                    <i class="fas fa-arrow-left"></i>
                </button>
            </div>

            <!-- 2. Corpo do Drawer com Flexbox e Rodapé Estático -->
            <div class="offcanvas-body android-drawer-body p-0 d-flex flex-column h-100">
                <!-- Cartão de Status do Atendimento (Fixo no Topo) -->
                <div class="android-drawer-card-status flex-shrink-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="android-pulse-dot"></span>
                            <span class="small fw-bold text-dark">Central de Atendimento Ativa</span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 0.72rem;">Online</span>
                    </div>
                    <p class="small text-muted mb-0 mt-1" style="font-size: 0.75rem;">Consultas, Exames Laboratoriais &amp; Odontologia</p>
                </div>

                <!-- Lista de Navegação com Rolagem Independente (O menu rola internamente sem empurrar o rodapé) -->
                <div class="android-drawer-nav-scroll flex-grow-1 overflow-y-auto">
                    <nav class="android-nav-list">
                        <?php foreach ($parent_menus as $p_item): 
                            $has_subs = !empty($children_menus[$p_item['menu_id']]);
                            $p_url = pronto_resolve_menu_url($p_item);
                            $p_active = pronto_is_menu_item_active($p_item, $current_req_uri, $current_script_file);
                        ?>
                            <?php if($has_subs): ?>
                                <div class="android-nav-group">
                                    <button type="button" class="android-nav-item android-nav-toggle d-flex justify-content-between align-items-center w-100 border-0 bg-transparent text-start" data-bs-toggle="collapse" data-bs-target="#drawerSub<?= $p_item['menu_id']; ?>" aria-expanded="false" aria-controls="drawerSub<?= $p_item['menu_id']; ?>">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="android-item-icon"><i class="fas fa-folder"></i></div>
                                            <span class="android-item-text"><?= htmlspecialchars($p_item['menu_name']); ?></span>
                                        </div>
                                        <i class="fas fa-chevron-down android-item-arrow"></i>
                                    </button>
                                    <div class="collapse" id="drawerSub<?= $p_item['menu_id']; ?>">
                                        <div class="ps-3 py-1 bg-light">
                                            <?php foreach ($children_menus[$p_item['menu_id']] as $sub_item): 
                                                $s_url = pronto_resolve_menu_url($sub_item);
                                            ?>
                                                <a href="<?= $s_url; ?>" class="android-nav-item py-2">
                                                    <div class="android-item-icon"><i class="fas fa-angle-right"></i></div>
                                                    <span class="android-item-text small"><?= htmlspecialchars($sub_item['menu_name']); ?></span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a href="<?= $p_url; ?>" class="android-nav-item <?= $p_active ? 'active' : ''; ?>">
                                    <div class="android-item-icon"><i class="fas fa-chevron-right"></i></div>
                                    <span class="android-item-text"><?= htmlspecialchars($p_item['menu_name']); ?></span>
                                    <i class="fas fa-chevron-right android-item-arrow"></i>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                </div>

                <!-- 3. Informações Estáticas Fixadas no Rodapé do Side Menu (NUNCA saem do campo de visão) -->
                <div class="android-drawer-footer flex-shrink-0 border-top bg-white p-3 shadow-sm mt-auto">
                    <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar uma consulta médica na Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="btn-foco-primary w-100 justify-content-center mb-2">
                        <i class="fab fa-whatsapp me-2"></i> Agendamento via WhatsApp
                    </a>
                    <?php if(!empty($contact_phone)): ?>
                        <div class="small text-secondary mb-1 d-flex align-items-center">
                            <i class="fas fa-phone-alt me-2" style="color: var(--ps-primary);"></i> <?= htmlspecialchars($contact_phone); ?>
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($contact_email)): ?>
                        <div class="small text-secondary d-flex align-items-center">
                            <i class="fas fa-envelope me-2" style="color: var(--ps-primary);"></i> <?= htmlspecialchars($contact_email); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
