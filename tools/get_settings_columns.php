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
}
