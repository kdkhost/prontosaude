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
        echo json_encode(['status' => 'error', 'message' => 'Identificador de foto inválido.']);
        exit;
    }
    header('location: photo.php');
    exit;
}

// Verifica se a foto existe
$statement = $pdo->prepare("SELECT * FROM tbl_photo WHERE photo_id = ?");
$statement->execute([$id]);
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Foto não encontrada no sistema.']);
        exit;
    }
    $_SESSION['erro'] = 'Foto não encontrada.';
    header('location: photo.php');
    exit;
}

// Exclui o arquivo físico de imagem se existir
$photo_name = trim($row['photo_name'] ?? '');
if (!empty($photo_name)) {
    $caminhos = [
        __DIR__ . '/../assets/uploads/' . $photo_name,
        __DIR__ . '/../assets/uploads/photos/' . $photo_name
    ];
    foreach ($caminhos as $path) {
        if (file_exists($path) && is_file($path)) {
            @unlink($path);
        }
    }
}

// Remove o registro do banco
$statement = $pdo->prepare("DELETE FROM tbl_photo WHERE photo_id = ?");
$statement->execute([$id]);

if ($is_ajax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'success',
        'message' => 'A foto foi removida com sucesso da galeria!'
    ]);
    exit;
}

$_SESSION['sucesso'] = 'A foto foi removida com sucesso da galeria!';
header('location: photo.php');
exit;
