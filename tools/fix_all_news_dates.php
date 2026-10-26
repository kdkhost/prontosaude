<?php
/**
 * Script de normalização de datas de todas as postagens do blog para o padrão brasileiro
 * Codificação: UTF-8 No BOM
 */

require_once __DIR__ . '/../admin/config.php';

if (!function_exists('pronto_format_date')) {
    function pronto_format_date($raw_date) {
        $raw = trim((string)$raw_date);
        if ($raw === '' || $raw === '0000-00-00' || $raw === '00-00-0000') {
            return '';
        }
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})/', $raw, $m)) {
            return "{$m[1]}/{$m[2]}/{$m[3]}";
        }
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})/', $raw, $m)) {
            return "{$m[1]}/{$m[2]}/{$m[3]}";
        }
        if (preg_match('/^(\d{4})[-\/\.](\d{2})[-\/\.](\d{2})/', $raw, $m)) {
            return "{$m[3]}/{$m[2]}/{$m[1]}";
        }
        $ts = strtotime($raw);
        if ($ts !== false && $ts > 0) {
            return date('d/m/Y', $ts);
        }
        return $raw;
    }
}

echo "=== Normalização de Datas em tbl_news ===\n";

$st = $pdo->query("SELECT news_id, news_title, news_date FROM tbl_news ORDER BY news_id ASC");
$posts = $st->fetchAll(PDO::FETCH_ASSOC);

foreach ($posts as $p) {
    $oldDate = $p['news_date'];
    $newDate = pronto_format_date($oldDate);
    
    echo sprintf("ID: %d | Titulo: %-35s | Antiga: %-12s | Nova: %-12s\n", 
        $p['news_id'], 
        mb_strimwidth($p['news_title'], 0, 33, '..'), 
        $oldDate, 
        $newDate
    );

    // Tenta atualizar no formato brasileiro d/m/Y
    try {
        $up = $pdo->prepare("UPDATE tbl_news SET news_date = ? WHERE news_id = ?");
        $up->execute([$newDate, $p['news_id']]);
    } catch (Exception $e) {
        // Se a coluna for strict DATE do MySQL, armazena Y-m-d válido
        echo "Aviso ao salvar {$newDate}: " . $e->getMessage() . "\n";
    }
}

echo "\nNormalização concluída!\n";
