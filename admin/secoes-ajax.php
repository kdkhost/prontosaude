<?php
ob_start();
session_start();
require_once('config.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$secao = $_POST['secao'] ?? '';
$status = isset($_POST['status']) && $_POST['status'] == '1' ? 1 : 0;

$secoes_validas = [
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
        // Tenta garantir que a coluna existe
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
        'secao' => $secao,
        'status' => $status,
        'message' => "Seção '{$nomeSecao}' {$acaoTexto} com sucesso!"
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar no banco de dados: ' . $e->getMessage()
    ]);
}
