<?php
require_once __DIR__ . '/../admin/config.php';

echo "=== TBL_ESPECIALIDADE COLUMNS ===\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_especialidade");
    while ($col = $stmt->fetch()) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Erro tbl_especialidade columns: " . $e->getMessage() . "\n";
}

echo "\n=== TBL_ESPECIALIDADE ROWS ===\n";
try {
    $stmt = $pdo->query("SELECT * FROM tbl_especialidade");
    while ($r = $stmt->fetch()) {
        print_r($r);
    }
} catch (Exception $e) {
    echo "Erro tbl_especialidade rows: " . $e->getMessage() . "\n";
}

echo "\n=== TBL_MENU CHILDREN OF 4 (Especialidades) ===\n";
try {
    $stmt = $pdo->query("SELECT * FROM tbl_menu WHERE menu_parent = 4 ORDER BY menu_order ASC");
    while ($r = $stmt->fetch()) {
        print_r($r);
    }
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}
