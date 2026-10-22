<?php
/**
 * Migração Idempotente para Suporte Completo à Customização de Cards
 * Pronto Saúde - Serviços, Produtos e Planos
 */
require_once __DIR__ . '/../config.php';

echo "=== Iniciando Migração de Schema para Cards de Serviços/Planos ===\n";

function addColumnIfNotExists($pdo, $table, $column, $definition) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $column]);
        $exists = $stmt->fetchColumn();
        if (!$exists) {
            $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
            $pdo->exec($sql);
            echo "[+] Coluna `$column` adicionada com sucesso em `$table`.\n";
        } else {
            echo "[=] Coluna `$column` já existe em `$table`.\n";
        }
    } catch (PDOException $e) {
        echo "[!] Erro ao adicionar `$column` em `$table`: " . $e->getMessage() . "\n";
    }
}

// 1. Tabela tbl_pricing_plan
addColumnIfNotExists($pdo, 'tbl_pricing_plan', 'pricing_plan_photo', 'VARCHAR(255) NULL DEFAULT "" AFTER `pricing_plan_name`');
addColumnIfNotExists($pdo, 'tbl_pricing_plan', 'pricing_plan_period', 'VARCHAR(50) NULL DEFAULT "/mês" AFTER `pricing_plan_price`');
addColumnIfNotExists($pdo, 'tbl_pricing_plan', 'pricing_plan_badge', 'VARCHAR(100) NULL DEFAULT "" AFTER `pricing_plan_name`');
addColumnIfNotExists($pdo, 'tbl_pricing_plan', 'pricing_plan_popular', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `pricing_plan_badge`');
addColumnIfNotExists($pdo, 'tbl_pricing_plan', 'status', 'TINYINT(1) NOT NULL DEFAULT 1 AFTER `pricing_plan_popular`');

// 2. Tabela tbl_service
addColumnIfNotExists($pdo, 'tbl_service', 'price', 'VARCHAR(100) NULL DEFAULT "" AFTER `name`');
addColumnIfNotExists($pdo, 'tbl_service', 'benefits', 'TEXT NULL DEFAULT NULL AFTER `short_description`');
addColumnIfNotExists($pdo, 'tbl_service', 'button_text', 'VARCHAR(100) NULL DEFAULT "Saiba Mais" AFTER `short_description`');
addColumnIfNotExists($pdo, 'tbl_service', 'button_url', 'VARCHAR(255) NULL DEFAULT "" AFTER `button_text`');

// 3. Tabela tbl_dental
addColumnIfNotExists($pdo, 'tbl_dental', 'beneficios', 'TEXT NULL DEFAULT NULL AFTER `descricao`');
addColumnIfNotExists($pdo, 'tbl_dental', 'button_text', 'VARCHAR(100) NULL DEFAULT "Agendar Avaliação" AFTER `link`');

// 4. Tabela tbl_settings (reCAPTCHA)
addColumnIfNotExists($pdo, 'tbl_settings', 'recaptcha_status', 'TINYINT(1) NOT NULL DEFAULT 0');
addColumnIfNotExists($pdo, 'tbl_settings', 'recaptcha_site_key', 'VARCHAR(255) NULL DEFAULT ""');
addColumnIfNotExists($pdo, 'tbl_settings', 'recaptcha_secret_key', 'VARCHAR(255) NULL DEFAULT ""');

// Atualiza planos existentes com imagens iniciais se estiverem vazios
try {
    $stmt = $pdo->query("SELECT pricing_plan_id, pricing_plan_name, pricing_plan_photo FROM tbl_pricing_plan");
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($plans as $p) {
        if (empty($p['pricing_plan_photo'])) {
            $name = $p['pricing_plan_name'];
            $photo = '';
            if (stripos($name, 'Med Odonto') !== false || stripos($name, 'Odonto') !== false) {
                $photo = 'odonto.png';
            } elseif (stripos($name, 'Empresa') !== false) {
                $photo = 'empresarial.png';
            } elseif (stripos($name, 'Top saúde') !== false || stripos($name, 'Top') !== false) {
                $photo = 'top-saude.png';
            }
            if (!empty($photo)) {
                $up = $pdo->prepare("UPDATE tbl_pricing_plan SET pricing_plan_photo = ? WHERE pricing_plan_id = ?");
                $up->execute([$photo, $p['pricing_plan_id']]);
                echo "[+] Plano '{$name}' associado com foto padrão '{$photo}'.\n";
            }
        }
    }
} catch (Exception $e) {
    echo "[!] Erro na atualização inicial de fotos dos planos: " . $e->getMessage() . "\n";
}

// Copia imagens padrão de planos para assets/uploads caso não existam lá
$uploadsDir = __DIR__ . '/../../assets/uploads';
$planosDir = __DIR__ . '/../../assets/images/planos';
if (is_dir($uploadsDir) && is_dir($planosDir)) {
    foreach (['top-saude.png', 'odonto.png', 'empresarial.png'] as $img) {
        $src = $planosDir . '/' . $img;
        $dst = $uploadsDir . '/' . $img;
        if (file_exists($src) && !file_exists($dst)) {
            copy($src, $dst);
            echo "[+] Imagem '{$img}' copiada para assets/uploads.\n";
        }
    }
}

echo "=== Migração Concluída com Sucesso ===\n";
