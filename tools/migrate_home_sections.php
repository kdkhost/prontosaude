<?php
/**
 * Script de migração para adicionar colunas de configuração das seções da Home
 * em tbl_settings (Diferenciais Clínicos, Sobre Nós / Centro Médico Moderno e Hero Box)
 * Usa TEXT para evitar 'Row size too large (65535)' no MySQL InnoDB.
 * UTF-8 No BOM
 */
require_once(__DIR__ . '/../admin/config.php');

$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

$columns = [
    // Hero Box
    'hero_box_status' => "INT(1) DEFAULT 1",
    'hero_box_badge_left' => "TEXT",
    'hero_box_badge_right' => "TEXT",
    'hero_box_title' => "TEXT",
    'hero_box_desc' => "TEXT",
    'hero_box_item1_icon' => "TEXT",
    'hero_box_item1_title' => "TEXT",
    'hero_box_item1_subtitle' => "TEXT",
    'hero_box_item1_url' => "TEXT",
    'hero_box_item2_icon' => "TEXT",
    'hero_box_item2_title' => "TEXT",
    'hero_box_item2_subtitle' => "TEXT",
    'hero_box_item2_url' => "TEXT",
    'hero_box_item3_icon' => "TEXT",
    'hero_box_item3_title' => "TEXT",
    'hero_box_item3_subtitle' => "TEXT",
    'hero_box_item3_url' => "TEXT",
    'hero_box_item4_icon' => "TEXT",
    'hero_box_item4_title' => "TEXT",
    'hero_box_item4_subtitle' => "TEXT",
    'hero_box_item4_url' => "TEXT",
    'hero_box_btn_text' => "TEXT",
    'hero_box_btn_url' => "TEXT",

    // Diferenciais Clínicos & Saúde Preventiva
    'home_risco_status' => "INT(1) DEFAULT 1",
    'home_risco_badge' => "TEXT",
    'home_risco_title' => "TEXT",
    'home_risco_subtitle' => "TEXT",
    'home_risco_item1_icon' => "TEXT",
    'home_risco_item1_title' => "TEXT",
    'home_risco_item1_desc' => "TEXT",
    'home_risco_item1_highlight' => "TEXT",
    'home_risco_item2_icon' => "TEXT",
    'home_risco_item2_title' => "TEXT",
    'home_risco_item2_desc' => "TEXT",
    'home_risco_item2_highlight' => "TEXT",
    'home_risco_item3_icon' => "TEXT",
    'home_risco_item3_title' => "TEXT",
    'home_risco_item3_desc' => "TEXT",
    'home_risco_item3_highlight' => "TEXT",

    // Quem Somos & Autoridade Médica
    'home_about_status' => "INT(1) DEFAULT 1",
    'home_about_badge' => "TEXT",
    'home_about_title' => "TEXT",
    'home_about_desc' => "TEXT",
    'home_about_stat1_icon' => "TEXT",
    'home_about_stat1_val' => "TEXT",
    'home_about_stat1_lbl' => "TEXT",
    'home_about_stat2_icon' => "TEXT",
    'home_about_stat2_val' => "TEXT",
    'home_about_stat2_lbl' => "TEXT",
    'home_about_stat3_icon' => "TEXT",
    'home_about_stat3_val' => "TEXT",
    'home_about_stat3_lbl' => "TEXT",
    'home_about_stat4_icon' => "TEXT",
    'home_about_stat4_val' => "TEXT",
    'home_about_stat4_lbl' => "TEXT",
    'home_about_card_icon' => "TEXT",
    'home_about_card_title' => "TEXT",
    'home_about_card_subtitle' => "TEXT",
    'home_about_card_item1' => "TEXT",
    'home_about_card_item2' => "TEXT",
    'home_about_card_item3' => "TEXT",
    'home_about_card_item4' => "TEXT",
    'home_about_btn1_text' => "TEXT",
    'home_about_btn1_url' => "TEXT",
    'home_about_btn2_text' => "TEXT",
    'home_about_btn2_url' => "TEXT"
];

$existingColumns = [];
if ($driver === 'sqlite') {
    $res = $pdo->query("PRAGMA table_info(tbl_settings)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($res as $r) {
        $existingColumns[] = strtolower($r['name']);
    }
} else {
    $res = $pdo->query("SHOW COLUMNS FROM tbl_settings")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($res as $r) {
        $existingColumns[] = strtolower($r['Field']);
    }
}

// Se for MySQL, converte colunas existentes que foram adicionadas como VARCHAR para TEXT para liberar espaço na linha
if ($driver === 'mysql') {
    foreach ($columns as $col => $definition) {
        if (in_array(strtolower($col), $existingColumns) && $definition === "TEXT") {
            try {
                $pdo->exec("ALTER TABLE tbl_settings MODIFY COLUMN `{$col}` TEXT NULL");
            } catch (Exception $e) {}
        }
    }
}

$added = 0;
foreach ($columns as $col => $definition) {
    if (!in_array(strtolower($col), $existingColumns)) {
        try {
            $pdo->exec("ALTER TABLE tbl_settings ADD COLUMN `{$col}` {$definition}");
            echo "Coluna adicionada com sucesso: {$col}\n";
            $added++;
        } catch (Exception $e) {
            echo "Erro ao adicionar {$col}: " . $e->getMessage() . "\n";
        }
    }
}

echo "Migração de estrutura finalizada. Driver: {$driver}. Total adicionadas: {$added}\n";
