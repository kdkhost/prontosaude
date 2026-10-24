<?php
require_once __DIR__ . '/../admin/config.php';

echo "Iniciando migração de banco de dados...\n";
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
echo "Driver PDO: {$driver}\n";

// Helper para verificar coluna
function hasColumn($pdo, $driver, $table, $column) {
    if ($driver === 'sqlite') {
        $stmt = $pdo->query("PRAGMA table_info({$table})");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($r['name'] === $column) return true;
        }
        return false;
    } else {
        $stmt = $pdo->query("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// 1. Migração tbl_slider: garantir content TEXT
if ($driver !== 'sqlite') {
    try {
        $pdo->exec("ALTER TABLE tbl_slider MODIFY COLUMN content TEXT NULL");
        echo "[tbl_slider] Coluna content alterada para TEXT com sucesso.\n";
    } catch (Exception $e) {
        echo "[tbl_slider] Aviso ao alterar content: " . $e->getMessage() . "\n";
    }
}

// 2. Migração tbl_comment
$commentCols = [
    'fb_comment_status' => $driver === 'sqlite' ? 'INTEGER DEFAULT 1' : 'TINYINT(1) DEFAULT 1',
    'fb_comment_numposts' => $driver === 'sqlite' ? 'INTEGER DEFAULT 5' : 'INT(11) DEFAULT 5',
    'fb_app_id' => $driver === 'sqlite' ? "TEXT DEFAULT ''" : "VARCHAR(100) DEFAULT ''"
];

foreach ($commentCols as $col => $type) {
    if (!hasColumn($pdo, $driver, 'tbl_comment', $col)) {
        try {
            $pdo->exec("ALTER TABLE tbl_comment ADD COLUMN {$col} {$type}");
            echo "[tbl_comment] Coluna {$col} adicionada.\n";
        } catch (Exception $e) {
            echo "[tbl_comment] Erro ao adicionar {$col}: " . $e->getMessage() . "\n";
        }
    } else {
        echo "[tbl_comment] Coluna {$col} já existe.\n";
    }
}

// Garante registro ID 1 em tbl_comment
$stc = $pdo->query("SELECT COUNT(*) FROM tbl_comment WHERE id=1");
if ($stc->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO tbl_comment (id, code_body, code_main, fb_comment_status, fb_comment_numposts, fb_app_id) VALUES (1, '', '', 1, 5, '')");
    echo "[tbl_comment] Registro padrão ID 1 criado.\n";
}

// 3. Migração tbl_settings: Box de Atendimento Rápido da Hero
$boxCols = [
    'hero_box_status' => $driver === 'sqlite' ? 'INTEGER DEFAULT 1' : 'TINYINT(1) DEFAULT 1',
    'hero_box_badge_left' => $driver === 'sqlite' ? "TEXT DEFAULT 'ATENDIMENTO ÁGIL'" : "VARCHAR(100) DEFAULT 'ATENDIMENTO ÁGIL'",
    'hero_box_badge_right' => $driver === 'sqlite' ? "TEXT DEFAULT 'Retorno Rápido'" : "VARCHAR(100) DEFAULT 'Retorno Rápido'",
    'hero_box_title' => $driver === 'sqlite' ? "TEXT DEFAULT 'Agendamento em 1 Minuto'" : "VARCHAR(255) DEFAULT 'Agendamento em 1 Minuto'",
    'hero_box_desc' => $driver === 'sqlite' ? "TEXT DEFAULT 'Escolha o serviço desejado e fale diretamente com a nossa recepção:'" : "TEXT NULL",
    
    'hero_box_item1_icon' => $driver === 'sqlite' ? "TEXT DEFAULT 'fas fa-user-md'" : "VARCHAR(100) DEFAULT 'fas fa-user-md'",
    'hero_box_item1_title' => $driver === 'sqlite' ? "TEXT DEFAULT 'Consultas'" : "VARCHAR(100) DEFAULT 'Consultas'",
    'hero_box_item1_subtitle' => $driver === 'sqlite' ? "TEXT DEFAULT 'Diversas Áreas'" : "VARCHAR(100) DEFAULT 'Diversas Áreas'",
    'hero_box_item1_url' => $driver === 'sqlite' ? "TEXT DEFAULT 'servicos.php'" : "VARCHAR(255) DEFAULT 'servicos.php'",

    'hero_box_item2_icon' => $driver === 'sqlite' ? "TEXT DEFAULT 'fas fa-flask'" : "VARCHAR(100) DEFAULT 'fas fa-flask'",
    'hero_box_item2_title' => $driver === 'sqlite' ? "TEXT DEFAULT 'Exames'" : "VARCHAR(100) DEFAULT 'Exames'",
    'hero_box_item2_subtitle' => $driver === 'sqlite' ? "TEXT DEFAULT 'Laudos Precisos'" : "VARCHAR(100) DEFAULT 'Laudos Precisos'",
    'hero_box_item2_url' => $driver === 'sqlite' ? "TEXT DEFAULT 'servicos.php'" : "VARCHAR(255) DEFAULT 'servicos.php'",

    'hero_box_item3_icon' => $driver === 'sqlite' ? "TEXT DEFAULT 'fas fa-tooth'" : "VARCHAR(100) DEFAULT 'fas fa-tooth'",
    'hero_box_item3_title' => $driver === 'sqlite' ? "TEXT DEFAULT 'Odontologia'" : "VARCHAR(100) DEFAULT 'Odontologia'",
    'hero_box_item3_subtitle' => $driver === 'sqlite' ? "TEXT DEFAULT 'Tratamento Dental'" : "VARCHAR(100) DEFAULT 'Tratamento Dental'",
    'hero_box_item3_url' => $driver === 'sqlite' ? "TEXT DEFAULT 'odontologia.php'" : "VARCHAR(255) DEFAULT 'odontologia.php'",

    'hero_box_item4_icon' => $driver === 'sqlite' ? "TEXT DEFAULT 'fas fa-id-card'" : "VARCHAR(100) DEFAULT 'fas fa-id-card'",
    'hero_box_item4_title' => $driver === 'sqlite' ? "TEXT DEFAULT 'Fidelidade'" : "VARCHAR(100) DEFAULT 'Fidelidade'",
    'hero_box_item4_subtitle' => $driver === 'sqlite' ? "TEXT DEFAULT 'Até 50% Desconto'" : "VARCHAR(100) DEFAULT 'Até 50% Desconto'",
    'hero_box_item4_url' => $driver === 'sqlite' ? "TEXT DEFAULT 'fidelidade.php'" : "VARCHAR(255) DEFAULT 'fidelidade.php'",

    'hero_box_btn_text' => $driver === 'sqlite' ? "TEXT DEFAULT 'CONHEÇA O CARTÃO FIDELIDADE'" : "VARCHAR(255) DEFAULT 'CONHEÇA O CARTÃO FIDELIDADE'",
    'hero_box_btn_url' => $driver === 'sqlite' ? "TEXT DEFAULT 'fidelidade.php'" : "VARCHAR(255) DEFAULT 'fidelidade.php'"
];

foreach ($boxCols as $col => $type) {
    if (!hasColumn($pdo, $driver, 'tbl_settings', $col)) {
        try {
            $pdo->exec("ALTER TABLE tbl_settings ADD COLUMN {$col} {$type}");
            echo "[tbl_settings] Coluna {$col} adicionada.\n";
        } catch (Exception $e) {
            echo "[tbl_settings] Erro ao adicionar {$col}: " . $e->getMessage() . "\n";
        }
    } else {
        echo "[tbl_settings] Coluna {$col} já existe.\n";
    }
}

// Inicializa valores padrão caso nulos
$pdo->exec("UPDATE tbl_settings SET 
    hero_box_status = COALESCE(hero_box_status, 1),
    hero_box_badge_left = COALESCE(hero_box_badge_left, 'ATENDIMENTO ÁGIL'),
    hero_box_badge_right = COALESCE(hero_box_badge_right, 'Retorno Rápido'),
    hero_box_title = COALESCE(hero_box_title, 'Agendamento em 1 Minuto'),
    hero_box_desc = COALESCE(hero_box_desc, 'Escolha o serviço desejado e fale diretamente com a nossa recepção:'),
    hero_box_item1_icon = COALESCE(hero_box_item1_icon, 'fas fa-user-md'),
    hero_box_item1_title = COALESCE(hero_box_item1_title, 'Consultas'),
    hero_box_item1_subtitle = COALESCE(hero_box_item1_subtitle, 'Diversas Áreas'),
    hero_box_item1_url = COALESCE(hero_box_item1_url, 'servicos.php'),
    hero_box_item2_icon = COALESCE(hero_box_item2_icon, 'fas fa-flask'),
    hero_box_item2_title = COALESCE(hero_box_item2_title, 'Exames'),
    hero_box_item2_subtitle = COALESCE(hero_box_item2_subtitle, 'Laudos Precisos'),
    hero_box_item2_url = COALESCE(hero_box_item2_url, 'servicos.php'),
    hero_box_item3_icon = COALESCE(hero_box_item3_icon, 'fas fa-tooth'),
    hero_box_item3_title = COALESCE(hero_box_item3_title, 'Odontologia'),
    hero_box_item3_subtitle = COALESCE(hero_box_item3_subtitle, 'Tratamento Dental'),
    hero_box_item3_url = COALESCE(hero_box_item3_url, 'odontologia.php'),
    hero_box_item4_icon = COALESCE(hero_box_item4_icon, 'fas fa-id-card'),
    hero_box_item4_title = COALESCE(hero_box_item4_title, 'Fidelidade'),
    hero_box_item4_subtitle = COALESCE(hero_box_item4_subtitle, 'Até 50% Desconto'),
    hero_box_item4_url = COALESCE(hero_box_item4_url, 'fidelidade.php'),
    hero_box_btn_text = COALESCE(hero_box_btn_text, 'CONHEÇA O CARTÃO FIDELIDADE'),
    hero_box_btn_url = COALESCE(hero_box_btn_url, 'fidelidade.php')
WHERE id=1");

echo "Migração finalizada com sucesso!\n";
