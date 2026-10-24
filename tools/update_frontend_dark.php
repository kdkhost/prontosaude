<?php
// Script para harmonizar dark/light em todos os arquivos de template e no CSS
$cssFile = __DIR__ . '/../assets/css/pronto-modern-frontend.css';
$css = file_get_contents($cssFile);

$additionalDarkCss = <<<CSS

/* ==============================================================================
   15. SUPORTE UNIVERSAL A PÁGINAS INTERNAS E COMPONENTES MULTIPAGE (DARK MODE)
   ============================================================================== */
.ps-page-content-box {
    background: #ffffff;
    border-radius: 16px;
    padding: 36px 32px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    font-size: 15.5px;
    line-height: 1.85;
    color: #334155;
    transition: var(--ps-transition);
}

[data-bs-theme="dark"] .ps-page-content-box {
    background: #131f33 !important;
    border-color: #24354f !important;
    color: #cbd5e1 !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45) !important;
}

[data-bs-theme="dark"] .ps-page-content-box h1,
[data-bs-theme="dark"] .ps-page-content-box h2,
[data-bs-theme="dark"] .ps-page-content-box h3,
[data-bs-theme="dark"] .ps-page-content-box h4,
[data-bs-theme="dark"] .ps-page-content-box h5,
[data-bs-theme="dark"] .ps-page-content-box h6,
[data-bs-theme="dark"] .ps-page-content-box strong,
[data-bs-theme="dark"] .ps-page-content-box b {
    color: #f8fafc !important;
}

/* Seções Internas (Sobre, Contato, FAQ, Blog, Médicos, Especialidades, Trabalhe Conosco) */
[data-bs-theme="dark"] .about-v2,
[data-bs-theme="dark"] .contact-v1,
[data-bs-theme="dark"] .faq,
[data-bs-theme="dark"] .blog,
[data-bs-theme="dark"] .doctor-v3,
[data-bs-theme="dark"] .doctor-detail,
[data-bs-theme="dark"] .service-v1,
[data-bs-theme="dark"] .ps-foco-services,
[data-bs-theme="dark"] .ps-foco-plans,
[data-bs-theme="dark"] .ps-service-detail-section {
    background-color: #0b1320 !important;
    background: #0b1320 !important;
    color: #cbd5e1 !important;
}

/* Regras Universais para Elementos com Estilos Inline no Dark Mode */
[data-bs-theme="dark"] [style*="background: #f8fafc"],
[data-bs-theme="dark"] [style*="background:#f8fafc"] {
    background-color: #0b1320 !important;
    background: #0b1320 !important;
}

[data-bs-theme="dark"] [style*="background: #ffffff"],
[data-bs-theme="dark"] [style*="background:#ffffff"],
[data-bs-theme="dark"] [style*="background: #fff"],
[data-bs-theme="dark"] [style*="background:#fff"] {
    background-color: #131f33 !important;
    background: #131f33 !important;
    border-color: #24354f !important;
}

[data-bs-theme="dark"] [style*="color: #334155"],
[data-bs-theme="dark"] [style*="color: #475569"],
[data-bs-theme="dark"] [style*="color: #64748b"] {
    color: #cbd5e1 !important;
}

[data-bs-theme="dark"] [style*="border: 1px solid #e2e8f0"],
[data-bs-theme="dark"] [style*="border-top: 1px solid #e2e8f0"],
[data-bs-theme="dark"] [style*="border-bottom: 1px solid #e2e8f0"],
[data-bs-theme="dark"] [style*="border-top: 1px solid #f1f5f9"] {
    border-color: #24354f !important;
}

[data-bs-theme="dark"] .accordion-item {
    background-color: #131f33 !important;
    border-color: #24354f !important;
}

[data-bs-theme="dark"] .accordion-button {
    background-color: #131f33 !important;
    color: #f8fafc !important;
    border-color: #24354f !important;
}

[data-bs-theme="dark"] .accordion-button:not(.collapsed) {
    background-color: #16243a !important;
    color: #0dcaf0 !important;
}

[data-bs-theme="dark"] .accordion-body {
    background-color: #0b1320 !important;
    color: #cbd5e1 !important;
}

[data-bs-theme="dark"] .post-item,
[data-bs-theme="dark"] .widget {
    background-color: #131f33 !important;
    border-color: #24354f !important;
    color: #cbd5e1 !important;
}

CSS;

if (strpos($css, 'ps-page-content-box') === false) {
    $css .= "\n" . $additionalDarkCss;
    file_put_contents($cssFile, $css);
    file_put_contents(__DIR__ . '/convert_foco_css.php', file_get_contents(__DIR__ . '/convert_foco_css.php') . "\n" . $additionalDarkCss);
    echo "CSS atualizado com suporte universal para páginas internas e componentes!\n";
} else {
    echo "CSS já continha ps-page-content-box.\n";
}

// 2. Atualizar page.php para remover inline backgrounds e adotar ps-page-content-box
$pagePhp = file_get_contents(__DIR__ . '/../page.php');
$pagePhp = str_replace(
    'style="padding: 60px 0; background: #f8fafc;"',
    'style="padding: 60px 0;"',
    $pagePhp
);
$pagePhp = str_replace(
    '<div style="background: #ffffff; border-radius: 16px; padding: 36px 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; font-size: 15.5px; line-height: 1.85; color: #334155;">',
    '<div class="ps-page-content-box">',
    $pagePhp
);
file_put_contents(__DIR__ . '/../page.php', $pagePhp);
echo "page.php atualizado com sucesso!\n";

// 3. Atualizar trabalhe-conosco.php
$tcPhp = file_get_contents(__DIR__ . '/../trabalhe-conosco.php');
$tcPhp = str_replace('style="padding: 70px 0; background: #f8fafc;"', 'style="padding: 70px 0;"', $tcPhp);
$tcPhp = str_replace(
    '<div class="p-5" style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">',
    '<div class="p-5 ps-page-content-box">',
    $tcPhp
);
file_put_contents(__DIR__ . '/../trabalhe-conosco.php', $tcPhp);
echo "trabalhe-conosco.php atualizado com sucesso!\n";

// 4. Atualizar doctor.php
$docPhp = file_get_contents(__DIR__ . '/../doctor.php');
$docPhp = str_replace('style="padding: 60px 0 80px 0; background: #f8fafc;"', 'style="padding: 60px 0 80px 0;"', $docPhp);
file_put_contents(__DIR__ . '/../doctor.php', $docPhp);
echo "doctor.php atualizado com sucesso!\n";

// 5. Atualizar department.php
$depPhp = file_get_contents(__DIR__ . '/../department.php');
$depPhp = str_replace('style="padding: 60px 0; background: #f8fafc;"', 'style="padding: 60px 0;"', $depPhp);
file_put_contents(__DIR__ . '/../department.php', $depPhp);
echo "department.php atualizado com sucesso!\n";

// 6. Atualizar service.php
$srvPhp = file_get_contents(__DIR__ . '/../service.php');
$srvPhp = str_replace('style="padding: 60px 0; background: #f8fafc;"', 'style="padding: 60px 0;"', $srvPhp);
file_put_contents(__DIR__ . '/../service.php', $srvPhp);
echo "service.php atualizado com sucesso!\n";
