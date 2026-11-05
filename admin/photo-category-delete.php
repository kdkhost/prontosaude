<?php
ob_start();
session_start();

$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    || (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1');

require_once('inc/security_guard.php');
require_once('config.php');

// Verifica autenticação
if (!isset($_SESSION['user'])) {
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Sessão expirada. Por favor, refaça o login.']);
        exit;
    }
    header('location: login.php');
    exit;
}

$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
if ($id <= 0) {
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Identificador de categoria inválido.']);
        exit;
    }
    header('location: photo-category.php');
    exit;
}

// Verifica se a categoria existe
$statement = $pdo->prepare("SELECT * FROM tbl_category_photo WHERE p_category_id = ?");
$statement->execute([$id]);
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Categoria de foto não encontrada.']);
        exit;
    }
    $_SESSION['erro'] = 'Categoria de foto não encontrada.';
    header('location: photo-category.php');
    exit;
}

// Remove os arquivos físicos das fotos vinculadas
$statement = $pdo->prepare("SELECT photo_name FROM tbl_photo WHERE p_category_id = ?");
$statement->execute([$id]);
$photos = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($photos as $p) {
    $arq = trim($p['photo_name'] ?? '');
    if (!empty($arq)) {
        $caminhos = [
            __DIR__ . '/../assets/uploads/' . $arq,
            __DIR__ . '/../assets/uploads/photos/' . $arq
        ];
        foreach ($caminhos as $path) {
            if (file_exists($path) && is_file($path)) {
                @unlink($path);
            }
        }
    }
}

// Remove as fotos da categoria
$stmt_photos = $pdo->prepare("DELETE FROM tbl_photo WHERE p_category_id = ?");
$stmt_photos->execute([$id]);

// Remove a categoria de foto
$stmt_cat = $pdo->prepare("DELETE FROM tbl_category_photo WHERE p_category_id = ?");
$stmt_cat->execute([$id]);

if ($is_ajax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'success',
        'message' => 'A categoria de fotos e suas imagens vinculadas foram removidas com sucesso!'
    ]);
    exit;
}

$_SESSION['sucesso'] = 'A categoria de fotos e suas imagens foram removidas com sucesso!';
header('location: photo-category.php');
exit;
