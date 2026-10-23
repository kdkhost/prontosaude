<?php
// tools/deactivate_medicos.php
// Restaura o status da página 'medicos' para 'Inactive' em tbl_page
// conforme solicitado pelo proprietário para uso futuro

require_once __DIR__ . '/../admin/config.php';

try {
    $stmt = $pdo->prepare("UPDATE tbl_page SET status='Inactive' WHERE page_slug='medicos'");
    $stmt->execute();
    echo "Sucesso: página 'medicos' retornada ao status 'Inactive' em tbl_page (" . $stmt->rowCount() . " linha(s) afetada(s)).\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
