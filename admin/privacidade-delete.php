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
        echo json_encode(['success' => false, 'message' => 'Sessão expirada. Por favor, refaça o login.']);
        exit;
    }
    header('location: login.php');
    exit;
}

$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
if ($id <= 0) {
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Identificador inválido.']);
        exit;
    }
    header('location: privacidade.php');
    exit;
}

// Verifica se o registro existe
$statement = $pdo->prepare("SELECT * FROM tbl_privacidade WHERE id = ?");
$statement->execute([$id]);
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Registro de Política de Privacidade não encontrado.']);
        exit;
    }
    $_SESSION['erro'] = 'Registro não encontrado.';
    header('location: privacidade.php');
    exit;
}

// Proteção do registro principal (ID 1)
$total_count = $pdo->query("SELECT COUNT(*) FROM tbl_privacidade")->fetchColumn();
if ($id === 1 && $total_count <= 1) {
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'A Política de Privacidade principal do site não pode ser excluída para não deixar o portal sem termos legais vigentes. Você pode editá-la a qualquer momento.']);
        exit;
    }
    $_SESSION['erro'] = 'A Política de Privacidade principal não pode ser excluída.';
    header('location: privacidade.php');
    exit;
}

// Remover arquivo de banner se existir
$banner = $row['banner'] ?? '';
if (!empty($banner)) {
    $caminhos = [
        __DIR__ . '/../assets/uploads/privacidade/' . $banner,
        __DIR__ . '/../assets/uploads/' . $banner
    ];
    foreach ($caminhos as $arq) {
        if (file_exists($arq) && is_file($arq)) {
            @unlink($arq);
        }
    }
}

// Excluir registro
$del = $pdo->prepare("DELETE FROM tbl_privacidade WHERE id = ?");
$del->execute([$id]);

if ($is_ajax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => 'A Política de Privacidade foi removida com sucesso!'
    ]);
    exit;
}

$_SESSION['sucesso'] = 'A Política de Privacidade foi removida com sucesso!';
header('location: privacidade.php');
exit;
