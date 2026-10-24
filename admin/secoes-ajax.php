<?php
ob_start();
session_start();
require_once('config.php');

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$page_id = isset($_POST['page_id']) ? (int)$_POST['page_id'] : 0;
$tipo    = $_POST['tipo'] ?? '';
$secao   = $_POST['secao'] ?? '';
$status  = isset($_POST['status']) && ($_POST['status'] == '1' || $_POST['status'] === 'Active') ? 1 : 0;

// Caso 1: Controle de Páginas de tbl_page
if ($page_id > 0 || $tipo === 'page') {
    if ($page_id <= 0 && is_numeric($secao)) {
        $page_id = (int)$secao;
    }

    if ($page_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Identificador da página não informado.']);
        exit;
    }

    try {
        $stmt_chk = $pdo->prepare("SELECT page_name, page_slug FROM tbl_page WHERE page_id = ?");
        $stmt_chk->execute([$page_id]);
        $page_info = $stmt_chk->fetch(PDO::FETCH_ASSOC);

        if (!$page_info) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Página não encontrada no sistema.']);
            exit;
        }

        $new_status = ($status === 1) ? 'Active' : 'Inactive';
        $stmt = $pdo->prepare("UPDATE tbl_page SET status = ? WHERE page_id = ?");
        $stmt->execute([$new_status, $page_id]);

        $acaoTexto = ($new_status === 'Active') ? 'ativada e visível' : 'desativada e oculta';

        echo json_encode([
            'success'     => true,
            'tipo'        => 'page',
            'page_id'     => $page_id,
            'status'      => $status,
            'status_text' => $new_status,
            'page_name'   => $page_info['page_name'],
            'message'     => "A página \"{$page_info['page_name']}\" foi {$acaoTexto} no site com sucesso!"
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar página: ' . $e->getMessage()]);
        exit;
    }
}

// Caso 2: Controle de Seções da Home e Manutenção
$secoes_validas = [
    'hero_box_status'         => 'Atendimento Rápido na Hero (Card 1 Minuto)',
    'home_risco_status'       => 'Diferenciais Clínicos & Saúde Preventiva',
    'home_about_status'       => 'Quem Somos & Centro Médico Moderno',
    'home_status_service'     => 'Serviços Médicos',
    'home_status_department'  => 'Clínicas e Departamentos',
    'home_status_doctor'      => 'Corpo Clínico (Médicos)',
    'home_status_pricing'     => 'Planos e Preços',
    'home_status_testimonial' => 'Depoimentos de Pacientes',
    'home_status_news'        => 'Notícias e Blog',
    'home_status_partner'     => 'Parceiros Institucionais',
    'maintenance_mode'        => 'Modo de Manutenção'
];

if (!array_key_exists($secao, $secoes_validas)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Seção inválida informada.']);
    exit;
}

try {
    if ($secao === 'maintenance_mode') {
        try {
            $pdo->query("SELECT maintenance_mode FROM tbl_settings LIMIT 1");
        } catch (Exception $e) {
            $pdo->query("ALTER TABLE tbl_settings ADD COLUMN maintenance_mode TINYINT(1) DEFAULT 0");
        }

        $stmt = $pdo->prepare("UPDATE tbl_settings SET maintenance_mode = ? WHERE id = 1");
        $stmt->execute([$status]);

        // Arquivo flag de sincronização
        $flagFile = __DIR__ . '/../.maintenance';
        if ($status == 1) {
            file_put_contents($flagFile, json_encode([
                'active' => true,
                'updated_at' => date('Y-m-d H:i:s'),
                'by' => $_SESSION['user']['full_name'] ?? 'Admin'
            ]));
        } else {
            if (file_exists($flagFile)) {
                @unlink($flagFile);
            }
        }
    } else {
        $stmt = $pdo->prepare("UPDATE tbl_settings SET {$secao} = ? WHERE id = 1");
        $stmt->execute([$status]);
    }

    $nomeSecao = $secoes_validas[$secao];
    $acaoTexto = $status == 1 ? 'ativada' : 'desativada';

    echo json_encode([
        'success' => true,
        'tipo'    => 'section',
        'secao'   => $secao,
        'status'  => $status,
        'message' => "Seção '{$nomeSecao}' {$acaoTexto} com sucesso!"
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar no banco de dados: ' . $e->getMessage()
    ]);
}
