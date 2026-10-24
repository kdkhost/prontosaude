<?php
$files = [
    'admin/slider.php',
    'admin/slider-edit.php',
    'admin/slider-add.php',
    'admin/menu.php',
    'admin/menu-edit.php',
    'admin/comment.php',
    'admin/settings.php',
    'index.php',
    'header.php',
    'news.php'
];

$errors = 0;
foreach ($files as $file) {
    $path = __DIR__ . '/../' . $file;
    $output = [];
    $returnVar = 0;
    exec("php -l \"$path\"", $output, $returnVar);
    if ($returnVar !== 0) {
        echo "[ERRO] $file: " . implode(" ", $output) . "\n";
        $errors++;
    } else {
        echo "[OK] $file\n";
    }
}

if ($errors === 0) {
    echo "\nTodos os arquivos possuem sintaxe PHP 100% válida!\n";
} else {
    echo "\nForam encontrados $errors erros de sintaxe.\n";
}
