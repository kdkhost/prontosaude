<?php
/**
 * Migração Automática de Colunas para o Banner PWA
 * UTF-8 No BOM
 */
require_once __DIR__ . '/../admin/config.php';

$cols = [
    'pwa_banner_status'    => 'TINYINT(1) DEFAULT 1',
    'pwa_banner_delay'     => 'INT DEFAULT 3',
    'pwa_banner_cooldown'  => 'INT DEFAULT 7',
    'pwa_banner_title'     => 'VARCHAR(255) NULL',
    'pwa_banner_desc'      => 'TEXT NULL',
    'pwa_banner_btn_text'  => 'VARCHAR(100) NULL',
    'pwa_banner_position'  => 'VARCHAR(50) NULL',
    'pwa_banner_ios_guide' => 'TINYINT(1) DEFAULT 1'
];

foreach ($cols as $c => $d) {
    try {
        $pdo->query("SELECT {$c} FROM tbl_settings LIMIT 1");
        echo "Coluna {$c} já existe.\n";
    } catch (Exception $e) {
        try {
            $pdo->exec("ALTER TABLE tbl_settings ADD COLUMN {$c} {$d}");
            echo "Coluna {$c} criada com sucesso!\n";
        } catch (Exception $ex) {
            echo "Erro ao criar {$c}: " . $ex->getMessage() . "\n";
        }
    }
}

// Configura valores padrão se estiverem vazios
try {
    $row = $pdo->query("SELECT * FROM tbl_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $updates = [];
        $params = [];
        if (empty($row['pwa_banner_title'])) {
            $updates[] = "pwa_banner_title = ?";
            $params[] = 'Instale o App Pronto Saúde';
        }
        if (empty($row['pwa_banner_desc'])) {
            $updates[] = "pwa_banner_desc = ?";
            $params[] = 'Agendamento rápido em 1 minuto, carteirinha digital, resultados de exames e lembretes direto no seu celular.';
        }
        if (empty($row['pwa_banner_btn_text'])) {
            $updates[] = "pwa_banner_btn_text = ?";
            $params[] = 'Instalar Aplicativo Grátis';
        }
        if (empty($row['pwa_banner_position'])) {
            $updates[] = "pwa_banner_position = ?";
            $params[] = 'bottom-right';
        }
        if (!empty($updates)) {
            $sql = "UPDATE tbl_settings SET " . implode(', ', $updates) . " WHERE id = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo "Valores padrão do Banner PWA atualizados.\n";
        }
    }
} catch (Exception $e) {
    echo "Erro ao preencher defaults: " . $e->getMessage() . "\n";
}

