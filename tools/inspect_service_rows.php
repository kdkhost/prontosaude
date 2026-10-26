<?php
require_once __DIR__ . '/../admin/config.php';

$stmt = $pdo->query("SELECT id, name, slug, photo, banner, status, price, short_description FROM tbl_service ORDER BY id ASC");
while ($r = $stmt->fetch()) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Slug: {$r['slug']} | Photo: {$r['photo']} | Status: {$r['status']} | Price: {$r['price']}\n";
    echo "  Short: " . mb_substr($r['short_description'], 0, 80) . "...\n";
}
