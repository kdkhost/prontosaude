<?php
// tools/update_header.php
// Script de geração segura do header.php com a Paleta Oficial da Pronto Saúde
// Suporte nativo a logo clara para fundos escuros e logo escura para fundos claros

$header_file = __DIR__ . '/../header.php';

// Lê o arquivo atual e pega a primeira parte com todas as regras de PHP e SEO
$current_header = file_get_contents($header_file);
$split_marker = '<!-- Favicon -->';
$parts = explode($split_marker, $current_header);

if (count($parts) < 2) {
    die("Erro: Marcador nao encontrado em header.php\n");
}

$first_part = $parts[0] . $split_marker . "\n        <link href=\"<?= BASE_URL; ?>assets/uploads/<?= \$favicon; ?>\" rel=\"shortcut icon\" type=\"image/png\">\n";

$second_part = '
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
                echo $row[\'code_body\'] ?? \'\';
            }
        } catch (Exception $e) {}
        ?>

        <?php if($preloader == 1): ?>
        <div id="preloader">
            <div id="status"></div>
        </div>
        <?php endif; ?>

        <?php
        // Carregamento de mídias sociais
        $social_data = [];
        try {
            $statement = $pdo->prepare("SELECT * FROM tbl_social WHERE social_url != \'\'");
            $statement->execute();
            $social_data = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}

        // Limpeza do número de WhatsApp
        $whats_raw = !empty($contact_phone) ? $contact_phone : \'21985831464\';
        $whats_clean = preg_replace(\'/\D/\', \'\', $whats_raw);
        if (strlen($whats_clean) <= 11) {
            $whats_clean = \'55\' . $whats_clean;
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
            if (function_exists(\'pronto_is_menu_active\') && !pronto_is_menu_active($m, $pdo)) {
                continue;
            }
            if ($m[\'menu_parent\'] == 0) {
                $parent_menus[] = $m;
            } else {
                $children_menus[$m[\'menu_parent\']][] = $m;
            }
        }

        $parent_menus = array_filter($parent_menus, function($p) use ($children_menus) {
            $url = trim($p[\'menu_url\'] ?? \'\');
            $type = $p[\'menu_type\'] ?? \'\';
            if ($type === \'Other\' && ($url === \'#\' || empty($url))) {
                return !empty($children_menus[$p[\'menu_id\']]);
            }
            return true;
        });

        if (!function_exists(\'pronto_resolve_menu_url\')) {
            function pronto_resolve_menu_url($item) {
                $type = $item[\'menu_type\'] ?? \'\';
                $slug = trim($item[\'category_or_page_slug\'] ?? \'\');
                $raw_url = trim($item[\'menu_url\'] ?? \'\');

                if ($type === \'Category\') {
                    return BASE_URL . \'category/\' . $slug;
                }
                if ($type === \'Page\') {
                    return BASE_URL . \'page/\' . $slug;
                }
                if ($type === \'Other\') {
                    if ($raw_url === \'#\' || empty($raw_url)) {
                        return \'#\';
                    }
                    if (preg_match(\'/^https?:\/\//i\', $raw_url)) {
                        return $raw_url;
                    }
                    if (strpos($raw_url, \'../\') === 0) {
                        return BASE_URL . substr($raw_url, 3);
                    }
                    if (strpos($raw_url, \'/\') === 0) {
                        return BASE_URL . ltrim($raw_url, \'/\');
                    }
                    return BASE_URL . $raw_url;
                }
                return BASE_URL;
            }
        }

        if (!function_exists(\'pronto_is_menu_item_active\')) {
            function pronto_is_menu_item_active($item, $current_uri, $script_name) {
                $type = $item[\'menu_type\'] ?? \'\';
                $slug = trim($item[\'category_or_page_slug\'] ?? \'\');
                $raw_url = trim($item[\'menu_url\'] ?? \'\');

                $is_home = ($current_uri === \'/\' || $current_uri === \'\' || $script_name === \'index.php\');
                if ($is_home) {
                    if ($slug === \'home\' || $raw_url === \'../\' || $raw_url === \'./\' || $raw_url === BASE_URL) {
                        return true;
                    }
                }

                if (!empty($slug)) {
                    if (strpos($current_uri, \'/\' . $slug) !== false || $script_name === ($slug . \'.php\')) {
                        return true;
                    }
                }

                if ($type === \'Other\' && !empty($raw_url) && $raw_url !== \'#\') {
                    $clean_file = basename(parse_url($raw_url, PHP_URL_PATH));
                    if (!empty($clean_file) && $clean_file !== \'..\' && $clean_file !== \'.\') {
                        if ($script_name === $clean_file || strpos($current_uri, \'/\' . $clean_file) !== false) {
                            return true;
                        }
                    }
                }

                return false;
            }
        }

        $current_req_uri = parse_url($_SERVER[\'REQUEST_URI\'] ?? \'\', PHP_URL_PATH);
        $current_script_file = basename($_SERVER[\'SCRIPT_NAME\'] ?? \'\');
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
                        <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode(\'Olá! Gostaria de falar com o atendimento Pronto Saúde\'); ?>" target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp Oficial
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <?php if(!empty($social_data)): ?>
                            <?php foreach ($social_data as $s_row): ?>
                                <?php if(!empty($s_row[\'social_url\'])): 
                                    $s_ico = $s_row[\'social_icon\'] ?? \'\';
                                    if (strpos($s_ico, \'fa-\') !== false && strpos($s_ico, \'fab \') === false && strpos($s_ico, \'fas \') === false) {
                                        $s_ico = \'fab \' . preg_replace(\'/^fa\s+fa-/\', \'fa-\', $s_ico);
                                        if ($s_ico === \'fab fa-facebook\') $s_ico = \'fab fa-facebook-f\';
                                    }
                                ?>
                                    <a href="<?= $s_row[\'social_url\']; ?>" target="_blank" rel="noopener noreferrer" title="Rede Social" data-bs-toggle="tooltip" data-bs-placement="bottom">
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
                                $has_subs = !empty($children_menus[$p_item[\'menu_id\']]);
                                $p_url = pronto_resolve_menu_url($p_item);
                                $p_active = pronto_is_menu_item_active($p_item, $current_req_uri, $current_script_file);
                                if (!$p_active && $has_subs) {
                                    foreach ($children_menus[$p_item[\'menu_id\']] as $sub_chk) {
                                        if (pronto_is_menu_item_active($sub_chk, $current_req_uri, $current_script_file)) {
                                            $p_active = true;
                                            break;
                                        }
                                    }
                                }
                            ?>
                                <?php if($has_subs): ?>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle <?= $p_active ? \'active\' : \'\'; ?>" href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                            <?= htmlspecialchars($p_item[\'menu_name\']); ?>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <?php foreach ($children_menus[$p_item[\'menu_id\']] as $sub_item): 
                                                $s_url = pronto_resolve_menu_url($sub_item);
                                                $s_active = pronto_is_menu_item_active($sub_item, $current_req_uri, $current_script_file);
                                            ?>
                                                <li>
                                                    <a class="dropdown-item <?= $s_active ? \'active\' : \'\'; ?>" href="<?= $s_url; ?>">
                                                        <i class="fas fa-angle-right me-2"></i><?= htmlspecialchars($sub_item[\'menu_name\']); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                <?php else: ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $p_active ? \'active\' : \'\'; ?>" href="<?= $p_url; ?>">
                                            <?= htmlspecialchars($p_item[\'menu_name\']); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                        <div class="d-flex flex-column flex-lg-row gap-2 align-items-stretch align-items-lg-center mt-3 mt-lg-0">
                            <a href="<?= BASE_URL; ?>servicos.php" class="btn-nav-outline d-none d-xl-inline-flex align-items-center gap-1 text-decoration-none">
                                <i class="fas fa-stethoscope"></i> Especialidades
                            </a>
                            <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode(\'Olá! Gostaria de agendar uma consulta na Pronto Saúde\'); ?>" target="_blank" rel="noopener" class="btn-nav-agendar text-decoration-none">
                                <i class="fas fa-calendar-check me-1"></i> Agendar Consulta
                            </a>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <!-- MENU GAVETA DESLIZANTE NATIVO ANDROID (Mobile) -->
        <div class="offcanvas offcanvas-start foco-android-drawer" tabindex="-1" id="menuDrawerAndroid" aria-labelledby="menuDrawerLabel">
            <div class="offcanvas-header android-drawer-header">
                <div class="d-flex align-items-center gap-2 logo-on-dark">
                    <?php if(!empty($logo)): ?>
                        <img src="<?= BASE_URL; ?>assets/uploads/<?= $logo; ?>" alt="Pronto Saúde" height="34" class="ps-logo ps-logo-light">
                    <?php else: ?>
                        <span class="fw-bold text-white"><i class="fas fa-hospital-user text-white-50 me-2"></i> Pronto Saúde</span>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-android-close" data-bs-dismiss="offcanvas" aria-label="Fechar menu">
                    <i class="fas fa-arrow-left"></i>
                </button>
            </div>

            <!-- Cartão de Status do Atendimento -->
            <div class="android-drawer-card-status">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="android-pulse-dot"></span>
                        <span class="small fw-bold text-dark">Central de Atendimento Ativa</span>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 0.72rem;">Online</span>
                </div>
                <p class="small text-muted mb-0 mt-1" style="font-size: 0.75rem;">Consultas, Exames Laboratoriais & Odontologia</p>
            </div>

            <!-- Lista de Navegação Estilo Android -->
            <div class="offcanvas-body android-drawer-body p-0">
                <nav class="android-nav-list">
                    <?php foreach ($parent_menus as $p_item): 
                        $has_subs = !empty($children_menus[$p_item[\'menu_id\']]);
                        $p_url = pronto_resolve_menu_url($p_item);
                        $p_active = pronto_is_menu_item_active($p_item, $current_req_uri, $current_script_file);
                    ?>
                        <?php if($has_subs): ?>
                            <div class="android-nav-group">
                                <button type="button" class="android-nav-item android-nav-toggle d-flex justify-content-between align-items-center w-100 border-0 bg-transparent text-start" data-bs-toggle="collapse" data-bs-target="#drawerSub<?= $p_item[\'menu_id\']; ?>" aria-expanded="false" aria-controls="drawerSub<?= $p_item[\'menu_id\']; ?>">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="android-item-icon"><i class="fas fa-folder"></i></div>
                                        <span class="android-item-text"><?= htmlspecialchars($p_item[\'menu_name\']); ?></span>
                                    </div>
                                    <i class="fas fa-chevron-down android-item-arrow"></i>
                                </button>
                                <div class="collapse" id="drawerSub<?= $p_item[\'menu_id\']; ?>">
                                    <div class="ps-3 py-1 bg-light">
                                        <?php foreach ($children_menus[$p_item[\'menu_id\']] as $sub_item): 
                                            $s_url = pronto_resolve_menu_url($sub_item);
                                        ?>
                                            <a href="<?= $s_url; ?>" class="android-nav-item py-2">
                                                <div class="android-item-icon"><i class="fas fa-angle-right"></i></div>
                                                <span class="android-item-text small"><?= htmlspecialchars($sub_item[\'menu_name\']); ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="<?= $p_url; ?>" class="android-nav-item <?= $p_active ? \'active\' : \'\'; ?>">
                                <div class="android-item-icon"><i class="fas fa-chevron-right"></i></div>
                                <span class="android-item-text"><?= htmlspecialchars($p_item[\'menu_name\']); ?></span>
                                <i class="fas fa-chevron-right android-item-arrow"></i>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>

                <div class="p-3 border-top mt-3">
                    <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode(\'Olá! Gostaria de agendar uma consulta médica na Pronto Saúde.\'); ?>" target="_blank" rel="noopener noreferrer" class="btn-foco-primary w-100 justify-content-center mb-3">
                        <i class="fab fa-whatsapp me-2"></i> Agendamento via WhatsApp
                    </a>
                    <?php if(!empty($contact_phone)): ?>
                        <div class="small text-secondary mb-1">
                            <i class="fas fa-phone-alt me-2" style="color: var(--ps-primary);"></i> <?= htmlspecialchars($contact_phone); ?>
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($contact_email)): ?>
                        <div class="small text-secondary">
                            <i class="fas fa-envelope me-2" style="color: var(--ps-primary);"></i> <?= htmlspecialchars($contact_email); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
';

$new_header = $first_part . $second_part;

// Remove BOM se houver
if (substr($new_header, 0, 3) === "\xEF\xBB\xBF") {
    $new_header = substr($new_header, 3);
}

file_put_contents($header_file, $new_header);
echo "Sucesso: header.php atualizado com paleta oficial Pronto Saúde (" . strlen($new_header) . " bytes).\n";
