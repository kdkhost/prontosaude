<?php
// tools/audit_links.php
// Auditoria completa de rotas, links, menus e páginas do portal Pronto Saúde

require_once __DIR__ . '/../admin/config.php';

echo "=== AUDITORIA DE ROTAS E PÁGINAS PRONTO SAÚDE ===\n\n";

// 1. Páginas em tbl_page
echo "--- 1. Páginas em tbl_page ---\n";
$stmt = $pdo->query("SELECT page_id, page_name, page_slug, page_layout, status FROM tbl_page ORDER BY page_id ASC");
$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($pages as $p) {
    echo sprintf("[%d] %-25s | slug: %-20s | layout: %-22s | status: %s\n", 
        $p['page_id'], $p['page_name'], $p['page_slug'], $p['page_layout'], $p['status']);
}

// 2. Menus em tbl_menu
echo "\n--- 2. Menus em tbl_menu ---\n";
$stmt = $pdo->query("SELECT menu_id, menu_name, menu_type, menu_url, category_or_page_slug, menu_parent, menu_order FROM tbl_menu ORDER BY menu_order ASC");
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($menus as $m) {
    echo sprintf("[%d] %-20s | tipo: %-10s | slug: %-15s | url: %-25s | parent: %d\n", 
        $m['menu_id'], $m['menu_name'], $m['menu_type'], $m['category_or_page_slug'], $m['menu_url'], $m['menu_parent']);
}

// 3. Serviços em tbl_service
echo "\n--- 3. Total de Serviços em tbl_service ---\n";
$stmt = $pdo->query("SELECT id, name, slug FROM tbl_service LIMIT 5");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($services as $s) {
    echo sprintf("[%d] %-30s | slug: %s\n", $s['id'], $s['name'], $s['slug']);
}

// 4. Categorias do Blog em tbl_category
echo "\n--- 4. Categorias em tbl_category ---\n";
$stmt = $pdo->query("SELECT category_id, category_name, category_slug FROM tbl_category");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($categories as $c) {
    // Contagem de notícias nessa categoria
    $st_count = $pdo->prepare("SELECT COUNT(*) FROM tbl_news WHERE category_id=?");
    $st_count->execute([$c['category_id']]);
    $count = $st_count->fetchColumn();
    echo sprintf("[%d] %-25s | slug: %-20s | %d posts\n", $c['category_id'], $c['category_name'], $c['category_slug'], $count);
}

// 5. Notícias em tbl_news
echo "\n--- 5. Notícias em tbl_news ---\n";
$stmt = $pdo->query("SELECT news_id, news_title, news_slug, category_id, total_view FROM tbl_news ORDER BY news_id DESC LIMIT 5");
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($news as $n) {
    echo sprintf("[%d] %-40s | slug: %-25s | views: %d\n", $n['news_id'], mb_strimwidth($n['news_title'], 0, 38, '..'), $n['news_slug'], $n['total_view']);
}

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
