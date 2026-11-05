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
        echo json_encode(['status' => 'error', 'message' => 'Identificador de vídeo inválido.']);
        exit;
    }
    header('location: video.php');
    exit;
}

// Verifica se o vídeo existe
$statement = $pdo->prepare("SELECT * FROM tbl_video WHERE video_id = ?");
$statement->execute([$id]);
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Vídeo não encontrado no sistema.']);
        exit;
    }
    $_SESSION['erro'] = 'Vídeo não encontrado.';
    header('location: video.php');
    exit;
}

// Se for arquivo de vídeo físico em uploads, remove o arquivo
$v_code = trim($row['video_iframe'] ?? '');
if (!empty($v_code) && !preg_match('/<iframe|youtube|vimeo/i', $v_code)) {
    // Normaliza caminho relativo
    $caminho_limpo = ltrim(str_replace(['../', './'], '', $v_code), '/');
    $arquivo_fisico = __DIR__ . '/../' . $caminho_limpo;
    if (file_exists($arquivo_fisico) && is_file($arquivo_fisico)) {
        @unlink($arquivo_fisico);
    }
}

// Remove o registro do banco
$statement = $pdo->prepare("DELETE FROM tbl_video WHERE video_id = ?");
$statement->execute([$id]);

if ($is_ajax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'success',
        'message' => 'O vídeo foi removido com sucesso da galeria!'
    ]);
    exit;
}

$_SESSION['sucesso'] = 'O vídeo foi removido com sucesso da galeria!';
header('location: video.php');
exit;
