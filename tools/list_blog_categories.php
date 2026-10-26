<?php
require_once __DIR__ . '/../admin/config.php';

$st = $pdo->query("SELECT category_id, category_name, category_slug FROM tbl_category ORDER BY category_id ASC");
while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['category_id']} | Nome: {$r['category_name']} | Slug: {$r['category_slug']}\n";
}
