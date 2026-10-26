<?php
require_once __DIR__ . '/../admin/config.php';

function scorePage($title, $desc, $kw) {
    $score = 30;
    $tLen = mb_strlen(trim((string)$title));
    if ($tLen >= 40 && $tLen <= 65) $score += 30;
    elseif ($tLen >= 25) $score += 15;
    elseif ($tLen > 0) $score += 5;

    $dLen = mb_strlen(trim((string)$desc));
    if ($dLen >= 100 && $dLen <= 165) $score += 30;
    elseif ($dLen >= 60) $score += 15;
    elseif ($dLen > 0) $score += 5;

    $lines = preg_split('/[\r\n]+/', trim((string)$kw));
    $kwCount = 0;
    foreach ($lines as $l) {
        if (trim($l) !== '') $kwCount++;
    }
    if ($kwCount >= 5) $score += 10;
    elseif ($kwCount >= 2) $score += 5;

    return min(100, $score);
}

// 1. Home
$stmtHome = $pdo->query("SELECT meta_title_home, meta_description_home, meta_keyword_home, og_image FROM tbl_settings WHERE id = 1");
$home = $stmtHome->fetch(PDO::FETCH_ASSOC);
$homeScore = scorePage($home['meta_title_home'] ?? '', $home['meta_description_home'] ?? '', $home['meta_keyword_home'] ?? '');
echo "HOME | Score: {$homeScore} | Title (" . mb_strlen($home['meta_title_home'] ?? '') . "): " . ($home['meta_title_home'] ?? '') . " | Banner: " . ($home['og_image'] ?? 'none') . "\n\n";

// 2. Todas as páginas em tbl_page
$stmtPages = $pdo->query("SELECT page_id, page_name, page_slug, status, meta_title, meta_description, meta_keyword, banner FROM tbl_page ORDER BY page_id ASC");
while ($r = $stmtPages->fetch(PDO::FETCH_ASSOC)) {
    $sc = scorePage($r['meta_title'] ?? '', $r['meta_description'] ?? '', $r['meta_keyword'] ?? '');
    $tLen = mb_strlen(trim((string)$r['meta_title']));
    $dLen = mb_strlen(trim((string)$r['meta_description']));
    $lines = array_filter(preg_split('/[\r\n]+/', trim((string)$r['meta_keyword'])));
    $kwCount = count($lines);
    $bFile = $r['banner'] ?: 'none';
    $bExists = file_exists(__DIR__ . '/../assets/uploads/' . $r['banner']) ? 'OK' : 'MISSING';
    echo sprintf(
        "ID: %2d | %-25s | Slug: %-20s | Status: %-8s | Score: %3d | T: %2d | D: %3d | KW: %2d | Banner: %-25s (%s)\n",
        $r['page_id'],
        $r['page_name'],
        $r['page_slug'],
        $r['status'],
        $sc,
        $tLen,
        $dLen,
        $kwCount,
        $bFile,
        $bExists
    );
}
