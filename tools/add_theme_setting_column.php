<?php
require_once __DIR__ . '/../admin/config.php';

try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "Banco de dados: {$driver}\n";

    $hasColumn = false;
    if ($driver === 'sqlite') {
        $stmt = $pdo->query('PRAGMA table_info(tbl_settings)');
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($r['name'] === 'frontend_dark_mode_status') {
                $hasColumn = true;
                break;
            }
        }
    } else {
        $stmt = $pdo->query("SHOW COLUMNS FROM tbl_settings LIKE 'frontend_dark_mode_status'");
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $hasColumn = true;
        }
    }

    if (!$hasColumn) {
        if ($driver === 'sqlite') {
            $pdo->exec("ALTER TABLE tbl_settings ADD COLUMN frontend_dark_mode_status INTEGER DEFAULT 1");
        } else {
            $pdo->exec("ALTER TABLE tbl_settings ADD COLUMN frontend_dark_mode_status TINYINT(1) DEFAULT 1 AFTER preloader");
        }
        echo "Coluna frontend_dark_mode_status adicionada com sucesso!\n";
    } else {
        echo "Coluna frontend_dark_mode_status já existe.\n";
    }

    // Garante valor 1 como padrão
    $pdo->exec("UPDATE tbl_settings SET frontend_dark_mode_status = 1 WHERE frontend_dark_mode_status IS NULL");
    echo "Valor padrão configurado.\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
