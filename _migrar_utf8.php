<?php
/**
 * Script de Migração: Correção de Dupla Codificação (Double Encoding) para UTF-8
 * 
 * Este script:
 * 1. Converte todas as tabelas e colunas do banco para utf8mb4
 * 2. Corrige dados com dupla codificação (latin1 -> utf8)
 * 
 * IMPORTANTE: Execute este script APENAS UMA VEZ no servidor.
 * Após rodar com sucesso, DELETE este arquivo por segurança.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Credenciais do banco (mesmo do config.php)
$dbhost = 'localhost';
$dbname = 'prontosaude_garden';
$dbuser = 'prontosaude_garden';
$dbpass = 'prontosaude_garden';

echo "<pre>\n";
echo "=== MIGRAÇÃO DE CHARSET PARA UTF-8 ===\n";
echo "Data: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Conecta usando latin1 para ler os dados corretamente
    $pdo = new PDO(
        "mysql:host={$dbhost};dbname={$dbname}",
        $dbuser,
        $dbpass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // 1. Primeiro, alterar o charset do banco de dados
    echo ">> Alterando charset do banco de dados para utf8mb4...\n";
    $pdo->exec("ALTER DATABASE `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   OK\n\n";
    
    // 2. Listar todas as tabelas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo ">> Tabelas encontradas: " . count($tables) . "\n\n";
    
    foreach ($tables as $table) {
        echo "--- Processando tabela: {$table} ---\n";
        
        // 3. Buscar colunas de texto (VARCHAR, TEXT, MEDIUMTEXT, LONGTEXT, CHAR, TINYTEXT, ENUM)
        $cols = $pdo->query("
            SELECT COLUMN_NAME, COLUMN_TYPE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_DEFAULT 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = '{$dbname}' 
            AND TABLE_NAME = '{$table}' 
            AND DATA_TYPE IN ('varchar', 'text', 'mediumtext', 'longtext', 'char', 'tinytext', 'enum', 'set')
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($cols)) {
            echo "   Nenhuma coluna de texto encontrada. Pulando...\n\n";
            // Mesmo assim, converte a tabela para utf8mb4
            $pdo->exec("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            continue;
        }
        
        echo "   Colunas de texto: " . count($cols) . "\n";
        
        // 4. Para cada coluna de texto, corrigir a dupla codificação
        foreach ($cols as $col) {
            $colName = $col['COLUMN_NAME'];
            $dataType = $col['DATA_TYPE'];
            
            // Pular ENUMs e SETs (não podem ter dupla codificação)
            if (in_array($dataType, ['enum', 'set'])) {
                echo "   Pulando coluna ENUM/SET: {$colName}\n";
                continue;
            }
            
            // Usar a técnica CONVERT(BINARY CONVERT(col USING latin1) USING utf8mb4)
            // Isso corrige a dupla codificação: os bytes armazenados como latin1 são re-interpretados como UTF-8
            try {
                $sql = "UPDATE `{$table}` SET `{$colName}` = CONVERT(BINARY CONVERT(`{$colName}` USING latin1) USING utf8mb4) WHERE `{$colName}` IS NOT NULL AND `{$colName}` != ''";
                $affected = $pdo->exec($sql);
                echo "   Coluna {$colName}: {$affected} registro(s) corrigido(s)\n";
            } catch (PDOException $e) {
                echo "   AVISO na coluna {$colName}: " . $e->getMessage() . "\n";
            }
        }
        
        // 5. Converter a tabela inteira para utf8mb4
        try {
            $pdo->exec("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "   Tabela convertida para utf8mb4 com sucesso.\n\n";
        } catch (PDOException $e) {
            echo "   AVISO ao converter tabela: " . $e->getMessage() . "\n\n";
        }
    }
    
    echo "\n=== MIGRAÇÃO CONCLUÍDA COM SUCESSO ===\n";
    echo "Agora o sistema pode usar charset=utf8mb4 na conexão PDO.\n";
    echo "\n⚠️  DELETE ESTE ARQUIVO DO SERVIDOR APÓS CONFIRMAR QUE TUDO ESTÁ OK!\n";
    
} catch (PDOException $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
}

echo "</pre>\n";
