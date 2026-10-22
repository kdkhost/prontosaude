<?php
/**
 * Pronto Saúde - Web App Manifest Dinâmico (PWA)
 * Lê parâmetros configuráveis em tbl_settings e emite o manifesto JSON padronizado W3C.
 */
header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: public, max-age=3600');

require_once('admin/config.php');

$st = $pdo->query("SELECT * FROM tbl_settings WHERE id = 1 LIMIT 1");
$cfg = $st->fetch(PDO::FETCH_ASSOC) ?: [];

$appName       = !empty($cfg['pwa_app_name']) ? $cfg['pwa_app_name'] : 'Pronto Saúde | Centro Médico & Odontológico';
$shortName     = !empty($cfg['pwa_short_name']) ? $cfg['pwa_short_name'] : 'Pronto Saúde';
$description   = !empty($cfg['pwa_description']) ? $cfg['pwa_description'] : 'Aplicativo oficial do Centro Médico e Odontológico Pronto Saúde - Agendamento, Especialidades e Exames.';
$themeColor    = !empty($cfg['pwa_theme_color']) ? $cfg['pwa_theme_color'] : '#0a6b74';
$bgColor       = !empty($cfg['pwa_bg_color']) ? $cfg['pwa_bg_color'] : '#ffffff';

$icon192 = !empty($cfg['pwa_icon_192']) ? BASE_URL . 'assets/uploads/' . $cfg['pwa_icon_192'] : BASE_URL . 'assets/uploads/' . ($cfg['favicon'] ?: 'favicon.png');
$icon512 = !empty($cfg['pwa_icon_512']) ? BASE_URL . 'assets/uploads/' . $cfg['pwa_icon_512'] : $icon192;

$manifest = [
    'name'             => $appName,
    'short_name'        => $shortName,
    'description'       => $description,
    'start_url'         => BASE_URL . '?source=pwa',
    'scope'             => BASE_URL,
    'display'           => 'standalone',
    'orientation'       => 'portrait',
    'theme_color'       => $themeColor,
    'background_color'  => $bgColor,
    'lang'              => 'pt-BR',
    'categories'        => ['medical', 'health', 'lifestyle'],
    'icons'             => [
        [
            'src'     => $icon192,
            'sizes'   => '192x192',
            'type'    => 'image/png',
            'purpose' => 'any maskable'
        ],
        [
            'src'     => $icon512,
            'sizes'   => '512x512',
            'type'    => 'image/png',
            'purpose' => 'any maskable'
        ]
    ],
    'shortcuts' => [
        [
            'name'        => 'Agendar Consulta',
            'short_name'  => 'Agendamento',
            'description' => 'Agende sua consulta diretamente pelo WhatsApp',
            'url'         => BASE_URL . '#whatsapp-agendamento',
            'icons'       => [['src' => $icon192, 'sizes' => '192x192']]
        ],
        [
            'name'        => 'Corpo Clínico',
            'short_name'  => 'Médicos',
            'description' => 'Conheça nossos médicos especialistas',
            'url'         => BASE_URL . 'doctor.php',
            'icons'       => [['src' => $icon192, 'sizes' => '192x192']]
        ],
        [
            'name'        => 'Planos Fidelidade',
            'short_name'  => 'Fidelidade',
            'description' => 'Planos com desconto em consultas e exames',
            'url'         => BASE_URL . 'fidelidade.php',
            'icons'       => [['src' => $icon192, 'sizes' => '192x192']]
        ]
    ]
];

echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
