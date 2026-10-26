<?php
/**
 * Script para inspecionar e atualizar FAQs
 * UTF-8 No BOM
 */
require_once __DIR__ . '/../admin/config.php';

echo "=== COLUNAS DE tbl_faq ===\n";
$stmtDesc = $pdo->query("DESCRIBE tbl_faq");
print_r($stmtDesc->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== CATEGORIAS DE FAQ (tbl_faq_category) ===\n";
$stmtCat = $pdo->query("SELECT * FROM tbl_faq_category ORDER BY faq_category_id ASC");
print_r($stmtCat->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== FAQS (tbl_faq) ===\n";
$stmtFaq = $pdo->query("SELECT * FROM tbl_faq ORDER BY faq_id ASC");
print_r($stmtFaq->fetchAll(PDO::FETCH_ASSOC));
