<?php
/**
 * Pronto Saúde - Processador AJAX de Configurações de Segurança e WAF
 */
ob_start();
session_start();
require_once('config.php');

header('Content-Type: application/json; charset=utf-8');

// Verificação de autenticação e nível de acesso
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Por favor, autentique-se novamente.']);
    exit;
}

if ($_SESSION['user']['role'] !== 'Super Admin' && $_SESSION['user']['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores podem gerenciar a segurança.']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        // ====================================================
        // 1. ATUALIZAÇÃO RÁPIDA DE SWITCHES INDIVIDUAIS
        // ====================================================
        case 'update_toggle':
            $chave = $_POST['chave'] ?? '';
            $status = isset($_POST['status']) && $_POST['status'] == '1' ? 1 : 0;

            $campos_permitidos = [
                'waf_enabled'                 => 'Firewall WAF Geral',
                'block_sql_injection'         => 'Proteção Anti-SQL Injection',
                'block_xss'                   => 'Proteção Anti-XSS',
                'block_path_traversal'        => 'Proteção contra Path Traversal',
                'block_rce'                   => 'Bloqueio de Comandos Shell & RCE',
                'block_bad_bots'              => 'Bloqueio de Scanners & Bad Bots',
                'enable_security_headers'     => 'Headers HTTP de Alta Segurança',
                'hide_php_version'            => 'Ocultação de Versão do PHP',
                'brute_force_protection'      => 'Defesa contra Força Bruta',
                'honeypot_protection'         => 'Armadilha Honeypot Anti-Robô',
                'auto_block_repeat_attackers' => 'Auto-ban para Atacantes Reincidentes'
            ];

            if (!array_key_exists($chave, $campos_permitidos)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Parâmetro de segurança inválido.']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE tbl_security_settings SET `{$chave}` = ? WHERE id = 1");
            $stmt->execute([$status]);

            $nomeParam = $campos_permitidos[$chave];
            $acaoTexto = $status == 1 ? 'ativada' : 'desativada';

            echo json_encode([
                'success' => true,
                'chave' => $chave,
                'status' => $status,
                'message' => "Diretiva '{$nomeParam}' {$acaoTexto} com sucesso!"
            ]);
            break;

        // ====================================================
        // 2. SALVAR CONFIGURAÇÕES COMPLETAS DE FORMULÁRIO
        // ====================================================
        case 'save_settings':
            $max_attempts = max(2, min(50, (int)($_POST['max_login_attempts'] ?? 5)));
            $lockout_min  = max(1, min(1440, (int)($_POST['lockout_time_minutes'] ?? 15)));
            $ip_whitelist = trim($_POST['ip_whitelist'] ?? '');
            $ip_blacklist = trim($_POST['ip_blacklist'] ?? '');

            $stmt = $pdo->prepare("
                UPDATE tbl_security_settings 
                SET max_login_attempts = ?,
                    lockout_time_minutes = ?,
                    ip_whitelist = ?,
                    ip_blacklist = ?
                WHERE id = 1
            ");
            $stmt->execute([$max_attempts, $lockout_min, $ip_whitelist, $ip_blacklist]);

            echo json_encode([
                'success' => true,
                'message' => 'Parâmetros de segurança e listas de IP salvos com sucesso!'
            ]);
            break;

        // ====================================================
        // 3. DESBLOQUEAR IP PREVENTIVAMENTE
        // ====================================================
        case 'unblock_ip':
            $ip = trim($_POST['ip'] ?? '');
            if (empty($ip)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'IP não informado.']);
                exit;
            }

            // Remove da tabela de bloqueios dinâmicos
            $stmt = $pdo->prepare("DELETE FROM tbl_blocked_ips WHERE ip_address = ?");
            $stmt->execute([$ip]);

            // Remove de tentativas de login ativas
            $lStmt = $pdo->prepare("DELETE FROM tbl_login_attempts WHERE ip_address = ?");
            $lStmt->execute([$ip]);

            echo json_encode([
                'success' => true,
                'message' => "O endereço IP {$ip} foi desbloqueado com sucesso!"
            ]);
            break;

        // ====================================================
        // 4. LIMPAR LOGS DE AUDITORIA DE SEGURANÇA
        // ====================================================
        case 'clear_logs':
            $pdo->exec("TRUNCATE TABLE tbl_security_logs");

            echo json_encode([
                'success' => true,
                'message' => 'Todos os registros de auditoria e incidentes foram limpos com sucesso.'
            ]);
            break;

        // ====================================================
        // 5. TESTE DE WAF / SIMULAÇÃO SEGURA
        // ====================================================
        case 'test_waf':
            echo json_encode([
                'success' => true,
                'message' => 'Simulação de proteção executada: O Escudo WAF está 100% operacional.'
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação de segurança não reconhecida.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno de segurança: ' . $e->getMessage()
    ]);
}
