<?php
/**
 * Pronto Saúde - Validador e Removedor Automático de UTF-8 BOM
 * Uso: php tools/check_no_bom.php [--fix]
 */

$rootDir = dirname(__DIR__);
$dir = new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS);
$iter = new RecursiveIteratorIterator($dir);

$autoFix = in_array('--fix', $argv, true) || true; // Por padrão já corrige
$bomFiles = [];

foreach ($iter as $file) {
    if ($file->isDir()) continue;
    $path = str_replace('\\', '/', $file->getPathname());
    
    // Ignora pastas internas do Git e vendor
    if (strpos($path, '/.git/') !== false || strpos($path, '/vendor/') !== false) continue;
    
    $handle = @fopen($path, 'r');
    if ($handle) {
        $bytes = fread($handle, 3);
        fclose($handle);
        
        if ($bytes === "\xEF\xBB\xBF") {
            $bomFiles[] = $path;
            
            if ($autoFix) {
                $content = file_get_contents($path);
                file_put_contents($path, substr($content, 3));
                echo "[CORRIGIDO] BOM removido com sucesso: " . str_replace($rootDir . '/', '', $path) . PHP_EOL;
            } else {
                echo "[ALERTA] Arquivo contém BOM: " . str_replace($rootDir . '/', '', $path) . PHP_EOL;
            }
        }
    }
}

if (empty($bomFiles)) {
    echo "✓ [SUCESSO] Nenhum arquivo com BOM encontrado. Todo o repositório está em UTF-8 puro (sem BOM)!" . PHP_EOL;
    exit(0);
} else {
    echo "Total de arquivos com BOM processados: " . count($bomFiles) . PHP_EOL;
    exit(0);
}
