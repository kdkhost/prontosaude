<?php
require_once __DIR__ . '/../admin/config.php';

echo "--- TBL_PAGE ---\n";
$st = $pdo->query("SELECT page_id, page_name, page_slug, page_layout, status FROM tbl_page");
while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['page_id']} | Nome: {$r['page_name']} | Slug: {$r['page_slug']} | Layout: {$r['page_layout']} | Status: {$r['status']}\n";
}

echo "\n--- TBL_SETTINGS STATUS ---\n";
$st2 = $pdo->query("SELECT home_title_service, home_status_service, home_title_pricing, home_status_pricing, home_title_doctor, home_status_doctor, home_title_testimonial, home_status_testimonial, home_title_news, home_status_news, home_title_partner, home_status_partner, total_recent_news_home_page FROM tbl_settings WHERE id = 1");
$s = $st2->fetch(PDO::FETCH_ASSOC);
print_r($s);

echo "\n--- CONTAGEM DE ITENS ATIVOS ---\n";
echo "Noticias: " . $pdo->query("SELECT COUNT(*) FROM tbl_news")->fetchColumn() . "\n";
echo "Servicos: " . $pdo->query("SELECT COUNT(*) FROM tbl_service WHERE status='Active'")->fetchColumn() . "\n";
echo "Medicos: " . $pdo->query("SELECT COUNT(*) FROM tbl_doctor WHERE status='Active'")->fetchColumn() . "\n";
echo "Planos: " . $pdo->query("SELECT COUNT(*) FROM tbl_pricing_plan WHERE status=1")->fetchColumn() . "\n";
echo "Depoimentos: " . $pdo->query("SELECT COUNT(*) FROM tbl_testimonial")->fetchColumn() . "\n";
echo "Parceiros: " . $pdo->query("SELECT COUNT(*) FROM tbl_partner")->fetchColumn() . "\n";
echo "FAQ: " . $pdo->query("SELECT COUNT(*) FROM tbl_faq")->fetchColumn() . "\n";
