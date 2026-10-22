<?php
/**
 * Pronto Saúde - Endpoint Universal de Atalhos Rápidos (Switches Toggle AJAX)
 * Gerencia o status de qualquer entidade do sistema em tempo real sem emitir HTML.
 */
ob_start();
session_start();
require_once('config.php');

// Limpa qualquer saída anterior acumulada no buffer
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

// Verificação de autenticação no painel
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sessão expirada. Por favor, faça login novamente para continuar.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método de requisição inválido.'
    ]);
    exit;
}

$entity = trim($_POST['entity'] ?? '');
$id     = (int)($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');

if (empty($entity)) {
    echo json_encode(['success' => false, 'message' => 'Entidade não especificada.']);
    exit;
}

// Mapeamento seguro de entidades para tabelas e colunas
$entityMap = [
    'page' => [
        'table' => 'tbl_page',
        'id_col' => 'page_id',
        'status_col' => 'status',
        'type' => 'string', // 'Active' / 'Inactive'
        'name_col' => 'page_name',
        'singular' => 'Página'
    ],
    'doctor' => [
        'table' => 'tbl_doctor',
        'id_col' => 'id',
        'status_col' => 'status',
        'type' => 'string',
        'name_col' => 'name',
        'singular' => 'Médico(a)'
    ],
    'service' => [
        'table' => 'tbl_service',
        'id_col' => 'id',
        'status_col' => 'status',
        'type' => 'string',
        'name_col' => 'name',
        'singular' => 'Serviço Médico'
    ],
    'slider' => [
        'table' => 'tbl_slider',
        'id_col' => 'id',
        'status_col' => 'status',
        'type' => 'string',
        'name_col' => 'heading',
        'singular' => 'Slide da Home'
    ],
    'pricing_plan' => [
        'table' => 'tbl_pricing_plan',
        'id_col' => 'pricing_plan_id',
        'status_col' => 'status',
        'type' => 'int', // 1 / 0
        'name_col' => 'title',
        'singular' => 'Plano / Cartão Fidelidade'
    ],
    'testimonial' => [
        'table' => 'tbl_testimonial',
        'id_col' => 'id',
        'status_col' => 'status',
        'type' => 'string',
        'name_col' => 'name',
        'singular' => 'Depoimento'
    ],
    'news' => [
        'table' => 'tbl_news',
        'id_col' => 'news_id',
        'status_col' => 'status',
        'type' => 'string',
        'name_col' => 'news_title',
        'singular' => 'Notícia'
    ],
    'photo' => [
        'table' => 'tbl_photo',
        'id_col' => 'photo_id',
        'status_col' => 'status',
        'type' => 'string',
        'name_col' => 'photo_caption',
        'singular' => 'Foto da Galeria'
    ],
    'video' => [
        'table' => 'tbl_video',
        'id_col' => 'video_id',
        'status_col' => 'status',
        'type' => 'string',
        'name_col' => 'video_title',
        'singular' => 'Vídeo da Galeria'
    ],
    'faq' => [
        'table' => 'tbl_faq',
        'id_col' => 'faq_id',
        'status_col' => 'status',
        'type' => 'string',
        'name_col' => 'faq_title',
        'singular' => 'Item de FAQ'
    ],
    'popup' => [
        'table' => 'tbl_settings',
        'id_col' => 'id',
        'status_col' => 'popup_status',
        'type' => 'int',
        'name_col' => 'popup_title',
        'singular' => 'Banner de Popup'
    ],
    'pwa' => [
        'table' => 'tbl_settings',
        'id_col' => 'id',
        'status_col' => 'pwa_status',
        'type' => 'int',
        'name_col' => 'pwa_app_name',
        'singular' => 'Aplicativo PWA'
    ]
];

if (!isset($entityMap[$entity])) {
    echo json_encode(['success' => false, 'message' => "Entidade '{$entity}' não suportada para toggle."]);
    exit;
}

$cfg = $entityMap[$entity];

// Para popup e pwa, o id sempre é 1 em tbl_settings
if ($entity === 'popup' || $entity === 'pwa') {
    $id = 1;
}

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Identificador inválido fornecido.']);
    exit;
}

// Normaliza o valor de status baseado no tipo
if ($cfg['type'] === 'int') {
    $newStatusVal = ($status === '1' || $status === 'Active' || $status === 'true' || $status === 1) ? 1 : 0;
    $isActive = ($newStatusVal === 1);
} else {
    $newStatusVal = ($status === 'Active' || $status === '1' || $status === 'true') ? 'Active' : 'Inactive';
    $isActive = ($newStatusVal === 'Active');
}

try {
    // 1. Busca nome da entidade para retorno humanizado
    $nameCol = $cfg['name_col'];
    $idCol = $cfg['id_col'];
    $table = $cfg['table'];
    $statusCol = $cfg['status_col'];

    $stmt_name = $pdo->prepare("SELECT {$nameCol} FROM {$table} WHERE {$idCol} = ? LIMIT 1");
    $stmt_name->execute([$id]);
    $item_name = $stmt_name->fetchColumn() ?: $cfg['singular'];

    // 2. Executa a atualização
    $stmt_up = $pdo->prepare("UPDATE {$table} SET {$statusCol} = ? WHERE {$idCol} = ?");
    $stmt_up->execute([$newStatusVal, $id]);

    $actionText = $isActive ? 'ativado(a) com sucesso' : 'desativado(a) com sucesso';
    $message = "{$cfg['singular']} \"{$item_name}\" foi {$actionText}!";

    echo json_encode([
        'success'    => true,
        'entity'     => $entity,
        'id'         => $id,
        'status'     => $newStatusVal,
        'is_active'  => $isActive,
        'item_name'  => $item_name,
        'message'    => $message
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no banco de dados: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;
