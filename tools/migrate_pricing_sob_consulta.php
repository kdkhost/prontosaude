<?php
/**
 * Migração Automática: Adicionar coluna sob_consulta em tbl_pricing_plan
 * UTF-8 No BOM
 */
require_once __DIR__ . '/../admin/config.php';

// Cria a tabela tbl_pricing_plan se não existir (para compatibilidade de ambiente local)
try {
    $pdo->query("SELECT 1 FROM tbl_pricing_plan LIMIT 1");
} catch (Exception $e) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_pricing_plan (
            pricing_plan_id INTEGER PRIMARY KEY AUTOINCREMENT,
            pricing_plan_name VARCHAR(255) NOT NULL,
            pricing_plan_badge VARCHAR(255) NULL,
            pricing_plan_popular INTEGER DEFAULT 0,
            pricing_plan_photo VARCHAR(255) NULL,
            pricing_plan_price VARCHAR(100) NULL,
            pricing_plan_period VARCHAR(100) DEFAULT '/mês',
            sob_consulta INTEGER DEFAULT 0,
            pricing_plan_button_text VARCHAR(100) DEFAULT 'Quero Este Plano',
            pricing_plan_button_url VARCHAR(255) NULL,
            status INTEGER DEFAULT 1
        )");
        echo "Tabela tbl_pricing_plan criada com sucesso!\n";
    } catch (Exception $ex) {
        echo "Aviso ao criar tabela: " . $ex->getMessage() . "\n";
    }
}

// Cria a tabela tbl_pricing_item se não existir
try {
    $pdo->query("SELECT 1 FROM tbl_pricing_item LIMIT 1");
} catch (Exception $e) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_pricing_item (
            pricing_item_id INTEGER PRIMARY KEY AUTOINCREMENT,
            pricing_item_name VARCHAR(255) NOT NULL,
            pricing_plan_id INTEGER NOT NULL
        )");
        echo "Tabela tbl_pricing_item criada com sucesso!\n";
    } catch (Exception $ex) {
        echo "Aviso ao criar itens: " . $ex->getMessage() . "\n";
    }
}

// Garante a existência da coluna sob_consulta
try {
    $pdo->query("SELECT sob_consulta FROM tbl_pricing_plan LIMIT 1");
    echo "Coluna sob_consulta já existe em tbl_pricing_plan.\n";
} catch (Exception $e) {
    try {
        $pdo->exec("ALTER TABLE tbl_pricing_plan ADD COLUMN sob_consulta TINYINT(1) DEFAULT 0");
        echo "Coluna sob_consulta criada com sucesso em tbl_pricing_plan!\n";
    } catch (Exception $ex) {
        echo "Erro ao criar coluna sob_consulta: " . $ex->getMessage() . "\n";
    }
}

// Atualizar planos cujo preço seja '0', '0.00', '0,00', 'R$ 0,00' ou vazio para sob_consulta = 1
try {
    $stmt = $pdo->query("SELECT pricing_plan_id, pricing_plan_name, pricing_plan_price, sob_consulta FROM tbl_pricing_plan");
    $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($planos as $p) {
        $price = trim($p['pricing_plan_price'] ?? '');
        $clean = preg_replace('/^R\$\s*/i', '', $price);
        $clean = trim(str_replace(',', '.', $clean));
        if (empty($price) || $clean === '0' || $clean === '0.00' || $clean === '0,00' || stripos($price, 'sob') !== false || stripos($price, 'consultar') !== false) {
            $up = $pdo->prepare("UPDATE tbl_pricing_plan SET sob_consulta = 1 WHERE pricing_plan_id = ?");
            $up->execute([$p['pricing_plan_id']]);
            echo "Plano '{$p['pricing_plan_name']}' atualizado para sob_consulta = 1.\n";
        }
    }
} catch (Exception $e) {
    echo "Aviso ao verificar planos: " . $e->getMessage() . "\n";
}
