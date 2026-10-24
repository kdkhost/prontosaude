<?php
$file = __DIR__ . '/../header.php';
$content = file_get_contents($file);

$target = '// Parâmetros PWA & Popup Promocional';
$replace = "// Controle do Tema Dark/Light no Portal Público\n\$frontend_dark_mode_status = (int)(\$row['frontend_dark_mode_status'] ?? 1);\n\n// Parâmetros PWA & Popup Promocional";

$pos = strpos($content, $target);
if ($pos !== false) {
    $content = substr_replace($content, $replace, $pos, strlen($target));
    file_put_contents($file, $content);
    echo "Definição de \$frontend_dark_mode_status adicionada com sucesso no topo de header.php!\n";
} else {
    echo "Alvo não encontrado em header.php\n";
}
