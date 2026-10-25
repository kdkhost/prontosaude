<?php
require_once __DIR__ . '/../admin/config.php';
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
echo "Driver: {$driver}\n";
if ($driver === 'sqlite') {
    $stmt = $pdo->query('PRAGMA table_info(tbl_settings)');
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $r['name'] . ' (' . $r['type'] . ")\n";
    }
} else {
    $stmt = $pdo->query('DESCRIBE tbl_settings');
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $r['Field'] . ' (' . $r['Type'] . ")\n";
    }
    echo "\n=== tbl_page ===\n";
    $stmt2 = $pdo->query('DESCRIBE tbl_page');
    while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        echo $r['Field'] . ' (' . $r['Type'] . ")\n";
    }
    echo "\n=== tbl_page rows ===\n";
    $stmt3 = $pdo->query('SELECT page_id, page_name, page_slug, status, banner, meta_title FROM tbl_page');
    while ($r = $stmt3->fetch(PDO::FETCH_ASSOC)) {
        echo "#{$r['page_id']} | {$r['page_slug']} | {$r['page_name']} | Status: {$r['status']} | Banner: {$r['banner']}\n";
    }
}

