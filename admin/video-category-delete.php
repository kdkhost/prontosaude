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
    header('location: video-category.php');
    exit;
}

// Verifica se a categoria existe
$statement = $pdo->prepare("SELECT * FROM tbl_category_video WHERE v_category_id = ?");
$statement->execute([$id]);
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Categoria de vídeo não encontrada.']);
        exit;
    }
    $_SESSION['erro'] = 'Categoria não encontrada.';
    header('location: video-category.php');
    exit;
}

// Remove os vídeos vinculados à categoria
$stmt_vid = $pdo->prepare("DELETE FROM tbl_video WHERE v_category_id = ?");
$stmt_vid->execute([$id]);

// Remove a categoria de vídeo
$stmt_cat = $pdo->prepare("DELETE FROM tbl_category_video WHERE v_category_id = ?");
$stmt_cat->execute([$id]);

if ($is_ajax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'success',
        'message' => 'A categoria de vídeo e seus vídeos vinculados foram removidos com sucesso!'
    ]);
    exit;
}

$_SESSION['sucesso'] = 'A categoria de vídeo e seus conteúdos foram removidos com sucesso!';
header('location: video-category.php');
exit;
