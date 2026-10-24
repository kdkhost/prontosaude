<?php
/**
 * Script de migração para adicionar colunas de configuração das seções da Home
 * em tbl_settings (Diferenciais Clínicos, Sobre Nós / Centro Médico Moderno e Hero Box)
 * Compatível com MySQL e SQLite
 * UTF-8 No BOM
 */
require_once(__DIR__ . '/../admin/config.php');

$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

$columns = [
    // Hero Box
    'hero_box_status' => "INT(1) DEFAULT 1",
    'hero_box_badge_left' => "VARCHAR(255) DEFAULT 'ATENDIMENTO ÁGIL'",
    'hero_box_badge_right' => "VARCHAR(255) DEFAULT 'Retorno Rápido'",
    'hero_box_title' => "VARCHAR(255) DEFAULT 'Agendamento em 1 Minuto'",
    'hero_box_desc' => "TEXT",
    'hero_box_item1_icon' => "VARCHAR(100) DEFAULT 'fas fa-user-md'",
    'hero_box_item1_title' => "VARCHAR(255) DEFAULT 'Consultas'",
    'hero_box_item1_subtitle' => "VARCHAR(255) DEFAULT 'Diversas Áreas'",
    'hero_box_item1_url' => "VARCHAR(255) DEFAULT 'servicos.php'",
    'hero_box_item2_icon' => "VARCHAR(100) DEFAULT 'fas fa-flask'",
    'hero_box_item2_title' => "VARCHAR(255) DEFAULT 'Exames'",
    'hero_box_item2_subtitle' => "VARCHAR(255) DEFAULT 'Laudos Precisos'",
    'hero_box_item2_url' => "VARCHAR(255) DEFAULT 'servicos.php'",
    'hero_box_item3_icon' => "VARCHAR(100) DEFAULT 'fas fa-tooth'",
    'hero_box_item3_title' => "VARCHAR(255) DEFAULT 'Odontologia'",
    'hero_box_item3_subtitle' => "VARCHAR(255) DEFAULT 'Tratamento Dental'",
    'hero_box_item3_url' => "VARCHAR(255) DEFAULT 'odontologia.php'",
    'hero_box_item4_icon' => "VARCHAR(100) DEFAULT 'fas fa-id-card'",
    'hero_box_item4_title' => "VARCHAR(255) DEFAULT 'Fidelidade'",
    'hero_box_item4_subtitle' => "VARCHAR(255) DEFAULT 'Até 50% Desconto'",
    'hero_box_item4_url' => "VARCHAR(255) DEFAULT 'fidelidade.php'",
    'hero_box_btn_text' => "VARCHAR(255) DEFAULT 'CONHEÇA O CARTÃO FIDELIDADE'",
    'hero_box_btn_url' => "VARCHAR(255) DEFAULT 'fidelidade.php'",

    // Diferenciais Clínicos & Saúde Preventiva
    'home_risco_status' => "INT(1) DEFAULT 1",
    'home_risco_badge' => "VARCHAR(255) DEFAULT 'Saúde Preventiva & Cuidado Integrado'",
    'home_risco_title' => "VARCHAR(255) DEFAULT 'O cuidado com sua saúde não pode esperar'",
    'home_risco_subtitle' => "TEXT",
    'home_risco_item1_icon' => "VARCHAR(100) DEFAULT 'fas fa-stethoscope'",
    'home_risco_item1_title' => "VARCHAR(255) DEFAULT 'Diagnóstico Ágil e Preciso'",
    'home_risco_item1_desc' => "TEXT",
    'home_risco_item1_highlight' => "VARCHAR(255) DEFAULT 'Laudos rápidos e confiáveis.'",
    'home_risco_item2_icon' => "VARCHAR(100) DEFAULT 'fas fa-user-doctor'",
    'home_risco_item2_title' => "VARCHAR(255) DEFAULT 'Corpo Clínico Humanizado'",
    'home_risco_item2_desc' => "TEXT",
    'home_risco_item2_highlight' => "VARCHAR(255) DEFAULT 'Atendimento com hora marcada.'",
    'home_risco_item3_icon' => "VARCHAR(100) DEFAULT 'fas fa-wallet'",
    'home_risco_item3_title' => "VARCHAR(255) DEFAULT 'Economia Sem Carências'",
    'home_risco_item3_desc' => "TEXT",
    'home_risco_item3_highlight' => "VARCHAR(255) DEFAULT 'Descontos reais de até 50%.'",

    // Quem Somos & Autoridade Médica
    'home_about_status' => "INT(1) DEFAULT 1",
    'home_about_badge' => "VARCHAR(255) DEFAULT 'Centro Médico Moderno'",
    'home_about_title' => "VARCHAR(255) DEFAULT 'Referência em Cuidados Médicos e Odontológicos'",
    'home_about_desc' => "TEXT",
    'home_about_stat1_icon' => "VARCHAR(100) DEFAULT 'fas fa-user-md'",
    'home_about_stat1_val' => "VARCHAR(100) DEFAULT '+15 Áreas'",
    'home_about_stat1_lbl' => "VARCHAR(255) DEFAULT 'Especialidades Médicas'",
    'home_about_stat2_icon' => "VARCHAR(100) DEFAULT 'fas fa-hospital-user'",
    'home_about_stat2_val' => "VARCHAR(100) DEFAULT '+10.000'",
    'home_about_stat2_lbl' => "VARCHAR(255) DEFAULT 'Pacientes Atendidos'",
    'home_about_stat3_icon' => "VARCHAR(100) DEFAULT 'fas fa-star'",
    'home_about_stat3_val' => "VARCHAR(100) DEFAULT '98.6%'",
    'home_about_stat3_lbl' => "VARCHAR(255) DEFAULT 'Satisfação Comprovada'",
    'home_about_stat4_icon' => "VARCHAR(100) DEFAULT 'fas fa-file-medical'",
    'home_about_stat4_val' => "VARCHAR(100) DEFAULT '100%'",
    'home_about_stat4_lbl' => "VARCHAR(255) DEFAULT 'Laudos Digitais Ágeis'",
    'home_about_card_icon' => "VARCHAR(100) DEFAULT 'fas fa-clinic-medical'",
    'home_about_card_title' => "VARCHAR(255) DEFAULT 'Centro Integrado Pronto Saúde'",
    'home_about_card_subtitle' => "VARCHAR(255) DEFAULT 'Gardênia Azul • Jacarepaguá • RJ'",
    'home_about_card_item1' => "VARCHAR(255) DEFAULT 'Consultórios médicos climatizados, acessíveis e confortáveis'",
    'home_about_card_item2' => "VARCHAR(255) DEFAULT 'Laboratório de análises clínicas com coleta humanizada'",
    'home_about_card_item3' => "VARCHAR(255) DEFAULT 'Odontologia moderna com raio-x e procedimentos integrados'",
    'home_about_card_item4' => "VARCHAR(255) DEFAULT 'Agendamento ágil pelo WhatsApp em menos de 1 minuto'",
    'home_about_btn1_text' => "VARCHAR(255) DEFAULT 'Conheça Nossa História'",
    'home_about_btn1_url' => "VARCHAR(255) DEFAULT 'page/sobre'",
    'home_about_btn2_text' => "VARCHAR(255) DEFAULT 'Falar com Atendente'",
    'home_about_btn2_url' => "VARCHAR(255) DEFAULT ''"
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

$added = 0;
foreach ($columns as $col => $definition) {
    if (!in_array(strtolower($col), $existingColumns)) {
        try {
            $pdo->exec("ALTER TABLE tbl_settings ADD COLUMN `{$col}` {$definition}");
            echo "Coluna adicionada: {$col}\n";
            $added++;
        } catch (Exception $e) {
            echo "Erro ao adicionar {$col}: " . $e->getMessage() . "\n";
        }
    }
}

echo "Migração de estrutura concluída. Driver: {$driver}. Total adicionadas: {$added}\n";
