<?php
// tools/activate_medicos.php
// Ativa a página de médicos em tbl_page para que o menu /page/medicos funcione perfeitamente

require_once __DIR__ . '/../admin/config.php';

try {
    $stmt = $pdo->prepare("UPDATE tbl_page SET status='Active' WHERE page_slug='medicos'");
    $stmt->execute();
    echo "Sucesso: página 'medicos' ativada em tbl_page (" . $stmt->rowCount() . " linha(s) afetada(s)).\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
