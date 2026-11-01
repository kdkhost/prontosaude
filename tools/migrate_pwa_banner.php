<?php
/**
 * Migração Automática de Colunas para o Banner PWA
 * Todas as configurações de textos, tempos, posições, chips e guias iOS
 * UTF-8 No BOM
 */
require_once __DIR__ . '/../admin/config.php';

$cols = [
    // Comportamento e exibição
    'pwa_banner_status'        => 'TINYINT(1) DEFAULT 1',
    'pwa_banner_delay'         => 'INT DEFAULT 3',
    'pwa_banner_cooldown'      => 'INT DEFAULT 7',
    'pwa_banner_position'      => 'VARCHAR(50) DEFAULT "bottom-right"',
    'pwa_banner_ios_guide'     => 'TINYINT(1) DEFAULT 1',
    
    // Textos do Banner Principal
    'pwa_banner_badge'         => 'VARCHAR(100) DEFAULT "App Oficial Pronto Saúde"',
    'pwa_banner_title'         => 'VARCHAR(255) DEFAULT "Instale o App Pronto Saúde"',
    'pwa_banner_subtitle'      => 'VARCHAR(255) DEFAULT "Pronto Saúde na palma da sua mão"',
    'pwa_banner_desc'          => 'TEXT NULL',
    'pwa_banner_chip1'         => 'VARCHAR(100) DEFAULT "Acesso em 1 clique"',
    'pwa_banner_chip2'         => 'VARCHAR(100) DEFAULT "Carteirinha Digital"',
    'pwa_banner_chip3'         => 'VARCHAR(100) DEFAULT "Ultraleve (0 MB)"',
    'pwa_banner_btn_text'      => 'VARCHAR(100) DEFAULT "Instalar Aplicativo Grátis"',
    'pwa_banner_btn_later'     => 'VARCHAR(100) DEFAULT "Agora não"',
    
    // Textos do Guia Especial para iOS (iPhone / iPad)
    'pwa_banner_ios_title'     => 'VARCHAR(255) DEFAULT "Instalar no seu iPhone ou iPad"',
    'pwa_banner_ios_subtitle'  => 'VARCHAR(255) DEFAULT "Siga os 3 passos simples abaixo no navegador Safari:"',
    'pwa_banner_ios_step1'     => 'TEXT NULL',
    'pwa_banner_ios_step2'     => 'TEXT NULL',
    'pwa_banner_ios_step3'     => 'TEXT NULL',
    'pwa_banner_ios_btn_text'  => 'VARCHAR(100) DEFAULT "Entendi, fechar"',
    
    // Mensagem de Sucesso pós-instalação
    'pwa_banner_success_title' => 'VARCHAR(255) DEFAULT "Aplicativo Instalado!"',
    'pwa_banner_success_msg'   => 'TEXT NULL'
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
        $defaults = [
            'pwa_banner_badge'         => 'App Oficial Pronto Saúde',
            'pwa_banner_title'         => 'Instale o App Pronto Saúde',
            'pwa_banner_subtitle'      => 'Pronto Saúde na palma da sua mão',
            'pwa_banner_desc'          => 'Agendamento rápido em 1 minuto, carteirinha digital e histórico de consultas sem ocupar memória do aparelho.',
            'pwa_banner_chip1'         => 'Acesso em 1 clique',
            'pwa_banner_chip2'         => 'Carteirinha Digital',
            'pwa_banner_chip3'         => 'Ultraleve (0 MB)',
            'pwa_banner_btn_text'      => 'Instalar Aplicativo Grátis',
            'pwa_banner_btn_later'     => 'Agora não',
            'pwa_banner_position'      => 'bottom-right',
            'pwa_banner_ios_title'     => 'Instalar no seu iPhone ou iPad',
            'pwa_banner_ios_subtitle'  => 'Siga os 3 passos simples abaixo no navegador Safari:',
            'pwa_banner_ios_step1'     => 'Toque no ícone de Compartilhar na barra inferior do Safari.',
            'pwa_banner_ios_step2'     => 'Role a lista para baixo e toque em "Adicionar à Tela de Início".',
            'pwa_banner_ios_step3'     => 'No canto superior direito, toque em "Adicionar". Pronto!',
            'pwa_banner_ios_btn_text'  => 'Entendi, fechar',
            'pwa_banner_success_title' => 'Aplicativo Instalado!',
            'pwa_banner_success_msg'   => 'O aplicativo foi instalado com sucesso. Você já pode acessá-lo direto da sua tela inicial!'
        ];

        $updates = [];
        $params = [];
        foreach ($defaults as $col => $val) {
            if (empty($row[$col])) {
                $updates[] = "{$col} = ?";
                $params[] = $val;
            }
        }

        if (!empty($updates)) {
            $sql = "UPDATE tbl_settings SET " . implode(', ', $updates) . " WHERE id = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo "Valores padrão expandidos do Banner PWA atualizados com sucesso.\n";
        }
    }
} catch (Exception $e) {
    echo "Erro ao preencher defaults: " . $e->getMessage() . "\n";
}
