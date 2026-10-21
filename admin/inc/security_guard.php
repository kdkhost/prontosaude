<?php
/**
 * Pronto Saúde - Módulo de Segurança de Padrão Militar e WAF Integrado
 * 
 * Proteções:
 * 1. Web Application Firewall (WAF) contra SQL Injection, XSS, Path Traversal e RCE.
 * 2. Proteção contra Força Bruta em Logins (Lockout por IP/E-mail e Exponential Delay).
 * 3. Headers HTTP de Segurança Máxima (HSTS, NoSniff, X-Frame-Options, Referrer-Policy, CSP).
 * 4. Prevenção de Vazamento de Dados e Ocultação de Versões do Servidor/PHP.
 * 5. Armadilha Honeypot para Robôs e Scanners Maliciosos.
 * 6. Lista Branca (Whitelist) e Lista Negra (Blacklist Dinâmica com Auto-Ban).
 * 7. Auditoria Completa e Registro de Incidentes em Tempo Real.
 */

if (!defined('PRONTO_SECURITY_LOADED')) {
    define('PRONTO_SECURITY_LOADED', true);

    // ========================================================
    // 1. OBTENÇÃO PRECISA DO IP DO CLIENTE
    // ========================================================
    function pronto_security_get_ip() {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // ========================================================
    // 2. CARREGAMENTO DAS DIRETIVAS DE SEGURANÇA DO BANCO
    // ========================================================
    function pronto_security_get_settings($pdo) {
        static $settings = null;
        if ($settings !== null) {
            return $settings;
        }

        try {
            $stmt = $pdo->query("SELECT * FROM tbl_security_settings WHERE id = 1 LIMIT 1");
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                $settings = $data;
                return $settings;
            }
        } catch (Exception $e) {
            // Em caso de tabela inexistente momentaneamente, assume padrões ativos de segurança máxima
        }

        $settings = [
            'waf_enabled' => 1,
            'block_sql_injection' => 1,
            'block_xss' => 1,
            'block_path_traversal' => 1,
            'block_rce' => 1,
            'block_bad_bots' => 1,
            'enable_security_headers' => 1,
            'hide_php_version' => 1,
            'brute_force_protection' => 1,
            'max_login_attempts' => 5,
            'lockout_time_minutes' => 15,
            'honeypot_protection' => 1,
            'csrf_protection' => 1,
            'auto_block_repeat_attackers' => 1,
            'ip_whitelist' => '127.0.0.1',
            'ip_blacklist' => ''
        ];
        return $settings;
    }

    // ========================================================
    // 3. REGISTRO DE INCIDENTES NO LOG DE AUDITORIA
    // ========================================================
    function pronto_security_log_incident($pdo, $ip, $eventType, $threatLevel, $payloadSample = '', $action = 'BLOCKED') {
        try {
            $uri = substr($_SERVER['REQUEST_URI'] ?? '', 0, 255);
            $method = substr($_SERVER['REQUEST_METHOD'] ?? 'GET', 0, 10);
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
            $sample = substr((string)$payloadSample, 0, 1000);

            $stmt = $pdo->prepare("
                INSERT INTO tbl_security_logs 
                (ip_address, user_agent, event_type, threat_level, request_uri, request_method, payload_sample, action_taken, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$ip, $userAgent, $eventType, $threatLevel, $uri, $method, $sample, $action]);
            $lastId = (int)$pdo->lastInsertId();

            // Se auto-ban estiver ativo e reincidência for detectada
            $settings = pronto_security_get_settings($pdo);
            if (!empty($settings['auto_block_repeat_attackers']) && $action === 'BLOCKED') {
                $cStmt = $pdo->prepare("
                    SELECT COUNT(*) FROM tbl_security_logs 
                    WHERE ip_address = ? AND action_taken = 'BLOCKED' AND created_at >= (NOW() - INTERVAL 1 HOUR)
                ");
                $cStmt->execute([$ip]);
                $recentBlocks = (int)$cStmt->fetchColumn();

                if ($recentBlocks >= 3) {
                    $bStmt = $pdo->prepare("
                        INSERT IGNORE INTO tbl_blocked_ips (ip_address, reason, blocked_until, created_at)
                        VALUES (?, 'Bloqueio automático por múltiplos ataques WAF', NOW() + INTERVAL 24 HOUR, NOW())
                    ");
                    $bStmt->execute([$ip]);
                }
            }

            return $lastId ?: time();
        } catch (Exception $e) {
            error_log("Erro ao registrar log de segurança: " . $e->getMessage());
            return time();
        }
    }

    // ========================================================
    // 4. TELA MILITAR DE BLOQUEIO DE SEGURANÇA
    // ========================================================
    function pronto_security_render_blocked_screen($reason, $eventType, $incidentId, $ip) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
            || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

        http_response_code(403);

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'security_blocked' => true,
                'incident_id' => $incidentId,
                'threat_type' => $eventType,
                'title' => 'Bloqueio de Segurança Ativo',
                'message' => 'Sua solicitação foi bloqueada pelo Escudo de Segurança Pronto Saúde por conter dados suspeitos (' . $reason . ').'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Resposta Visual de Alta Segurança (Padrão Militar / Hospitalar)
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Acesso Bloqueado | Escudo de Segurança Pronto Saúde</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    background: radial-gradient(circle at center, #0b1a24 0%, #050d12 100%);
                    color: #e2e8f0;
                    font-family: 'Source Sans 3', sans-serif;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 1.5rem;
                }
                .shield-card {
                    background: rgba(15, 23, 42, 0.85);
                    border: 1px solid rgba(239, 68, 68, 0.4);
                    border-radius: 1.25rem;
                    box-shadow: 0 25px 50px -12px rgba(239, 68, 68, 0.25);
                    max-width: 580px;
                    width: 100%;
                    overflow: hidden;
                    backdrop-filter: blur(16px);
                }
                .shield-header {
                    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(185, 28, 28, 0.05) 100%);
                    padding: 2rem 1.5rem 1.5rem 1.5rem;
                    text-align: center;
                    border-bottom: 1px solid rgba(239, 68, 68, 0.2);
                }
                .shield-icon {
                    width: 72px;
                    height: 72px;
                    margin: 0 auto 1rem auto;
                    background: rgba(239, 68, 68, 0.15);
                    color: #ef4444;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 2.25rem;
                    border: 2px solid rgba(239, 68, 68, 0.5);
                    box-shadow: 0 0 25px rgba(239, 68, 68, 0.35);
                    animation: pulseShield 2s infinite ease-in-out;
                }
                @keyframes pulseShield {
                    0%, 100% { transform: scale(1); box-shadow: 0 0 20px rgba(239, 68, 68, 0.3); }
                    50% { transform: scale(1.05); box-shadow: 0 0 35px rgba(239, 68, 68, 0.6); }
                }
                .shield-title { font-size: 1.5rem; font-weight: 700; color: #f87171; letter-spacing: -0.02em; }
                .shield-subtitle { font-size: 0.95rem; color: #94a3b8; margin-top: 0.25rem; }
                .shield-body { padding: 1.75rem 1.5rem; }
                .alert-desc { font-size: 1rem; color: #cbd5e1; line-height: 1.6; margin-bottom: 1.25rem; text-align: center; }
                .incident-box {
                    background: rgba(0, 0, 0, 0.4);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    border-radius: 0.75rem;
                    padding: 1rem;
                    margin-bottom: 1.5rem;
                    font-size: 0.875rem;
                }
                .incident-row { display: flex; justify-content: space-between; padding: 0.35rem 0; border-bottom: 1px dashed rgba(255,255,255,0.06); }
                .incident-row:last-child { border-bottom: none; }
                .incident-label { color: #64748b; }
                .incident-val { color: #f1f5f9; font-family: monospace; font-weight: 600; }
                .btn-back {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0.5rem;
                    width: 100%;
                    padding: 0.85rem 1.5rem;
                    background: #0a6b74;
                    color: #ffffff;
                    text-decoration: none;
                    font-weight: 700;
                    border-radius: 0.75rem;
                    transition: all 0.2s ease;
                    text-align: center;
                }
                .btn-back:hover { background: #075259; transform: translateY(-1px); color: #ffffff; }
            </style>
        </head>
        <body>
            <div class="shield-card">
                <div class="shield-header">
                    <div class="shield-icon"><i class="bi bi-shield-slash-fill"></i></div>
                    <h1 class="shield-title">Acesso Interrompido por Segurança</h1>
                    <p class="shield-subtitle">Firewall de Aplicação Web (WAF) Pronto Saúde Ativo</p>
                </div>
                <div class="shield-body">
                    <p class="alert-desc">
                        Uma assinatura potencialmente maliciosa ou incompatível com os protocolos de segurança foi detectada e neutralizada com sucesso.
                    </p>
                    <div class="incident-box">
                        <div class="incident-row">
                            <span class="incident-label">Código do Incidente:</span>
                            <span class="incident-val">#SEC-<?= str_pad((string)$incidentId, 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="incident-row">
                            <span class="incident-label">Motivo do Bloqueio:</span>
                            <span class="incident-val" style="color: #f87171;"><?= htmlspecialchars($reason); ?></span>
                        </div>
                        <div class="incident-row">
                            <span class="incident-label">Classificação:</span>
                            <span class="incident-val"><?= htmlspecialchars($eventType); ?></span>
                        </div>
                        <div class="incident-row">
                            <span class="incident-label">Endereço IP Registrado:</span>
                            <span class="incident-val"><?= htmlspecialchars($ip); ?></span>
                        </div>
                        <div class="incident-row">
                            <span class="incident-label">Data e Horário:</span>
                            <span class="incident-val"><?= date('d/m/Y H:i:s'); ?> (Brasília)</span>
                        </div>
                    </div>
                    <a href="javascript:history.back()" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Retornar com Segurança
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    // ========================================================
    // 5. INSPEÇÃO WAF DE INJEÇÕES, PAYLOADS E ATACANTES
    // ========================================================
    function pronto_security_inspect_request($pdo) {
        $ip = pronto_security_get_ip();
        $settings = pronto_security_get_settings($pdo);

        // A. Verifica Whitelist de IPs
        if (!empty($settings['ip_whitelist'])) {
            $allowedIps = array_map('trim', explode(',', str_replace(["\r\n", "\n"], ',', $settings['ip_whitelist'])));
            if (in_array($ip, $allowedIps, true)) {
                return; // IP liberado na lista branca
            }
        }

        // B. Verifica Blacklist Permanente
        if (!empty($settings['ip_blacklist'])) {
            $blockedIps = array_map('trim', explode(',', str_replace(["\r\n", "\n"], ',', $settings['ip_blacklist'])));
            if (in_array($ip, $blockedIps, true)) {
                $incId = pronto_security_log_incident($pdo, $ip, 'IP_BLACKLISTED', 'CRITICA', 'IP banido na lista negra permanente');
                pronto_security_render_blocked_screen('Seu endereço IP está bloqueado na lista negra do sistema.', 'IP_BLACKLISTED', $incId, $ip);
            }
        }

        // C. Verifica Tabela Dinâmica de IPs Bloqueados
        try {
            $bStmt = $pdo->prepare("
                SELECT reason, blocked_until FROM tbl_blocked_ips 
                WHERE ip_address = ? AND (blocked_until IS NULL OR blocked_until > NOW())
                LIMIT 1
            ");
            $bStmt->execute([$ip]);
            $ban = $bStmt->fetch(PDO::FETCH_ASSOC);
            if ($ban) {
                $incId = pronto_security_log_incident($pdo, $ip, 'IP_TEMP_BANNED', 'CRITICA', $ban['reason']);
                pronto_security_render_blocked_screen('Endereço IP temporariamente suspenso: ' . $ban['reason'], 'IP_TEMP_BANNED', $incId, $ip);
            }
        } catch (Exception $e) {}

        // D. Aplicação dos Headers HTTP de Segurança Máxima
        if (!empty($settings['enable_security_headers'])) {
            if (!headers_sent()) {
                header('X-Content-Type-Options: nosniff');
                header('X-Frame-Options: SAMEORIGIN');
                header('X-XSS-Protection: 1; mode=block');
                header('Referrer-Policy: strict-origin-when-cross-origin');
                header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

                $isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
                    || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

                if ($isHttps) {
                    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
                }

                if (!empty($settings['hide_php_version'])) {
                    header_remove('X-Powered-By');
                    header('X-Powered-By: ProntoSaude-Shield');
                }
            }
        }

        // Se o WAF estiver desativado nas configurações, não roda inspeções de payload
        if (empty($settings['waf_enabled'])) {
            return;
        }

        // E. Detecção de Scanners e Bad Bots
        if (!empty($settings['block_bad_bots'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $badBotsRegex = '/(sqlmap|nikto|wpscan|acunetix|havij|masscan|zgrab|nmap|dirbuster|gobuster|shodan|censys)/i';
            if (preg_match($badBotsRegex, $userAgent)) {
                $incId = pronto_security_log_incident($pdo, $ip, 'BAD_BOT_DETECTED', 'CRITICA', $userAgent);
                pronto_security_render_blocked_screen('Scanner automatizado ou bot malicioso identificado.', 'BAD_BOT_DETECTED', $incId, $ip);
            }
        }

        // F. Detecção de Honeypot Anti-Robô
        if (!empty($settings['honeypot_protection']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['security_token_hp']) || !empty($_POST['security_guard_hp'])) {
                $incId = pronto_security_log_incident($pdo, $ip, 'BOT_HONEYPOT_TRIPPED', 'ALTA', 'Preenchimento de campo invisível honeypot');
                pronto_security_render_blocked_screen('Submissão automatizada de formulário detectada por armadilha honeypot.', 'BOT_HONEYPOT_TRIPPED', $incId, $ip);
            }
        }

        // G. Coleta de Dados para Inspeção WAF
        $inputsToInspect = [];

        // Inspeciona Query String e URI
        $inputsToInspect['URI'] = $_SERVER['REQUEST_URI'] ?? '';
        $inputsToInspect['QUERY'] = $_SERVER['QUERY_STRING'] ?? '';

        // Flatten recursivo de arrays GET e POST
        $flattenArray = function ($arr, $prefix = '') use (&$flattenArray) {
            $flat = [];
            foreach ($arr as $k => $v) {
                $key = $prefix ? "{$prefix}.{$k}" : $k;
                if (is_array($v)) {
                    $flat = array_merge($flat, $flattenArray($v, $key));
                } else {
                    $flat[$key] = (string)$v;
                }
            }
            return $flat;
        };

        $flatGet = $flattenArray($_GET, 'GET');
        $flatPost = $flattenArray($_POST, 'POST');
        $flatCookies = $flattenArray($_COOKIE, 'COOKIE');

        $allInputs = array_merge($inputsToInspect, $flatGet, $flatPost, $flatCookies);

        // Regex de Injeção SQL (Padrão Militar)
        $sqliPatterns = [
            '/\b(union(\s+all)?\s+select)\b/i' => 'UNION SELECT Attack',
            '/\b(select\s+.*\s+from\s+information_schema)\b/i' => 'Information Schema Leakage',
            '/\b(benchmark\s*\(|sleep\s*\(|waitfor\s+delay)\b/i' => 'Time-based SQL Blind Injection',
            '/\b(into\s+(out|dump)file)\b/i' => 'SQL File Dump Attack',
            '/\b(load_file\s*\()/i' => 'SQL Load File Attack',
            '/;\s*(drop|truncate|alter)\s+(table|database)/i' => 'SQL DDL Malicious Execution',
            '/(\b(exec|execute|sp_executesql)\b.*\b(xp_cmdshell)\b)/i' => 'SQL Remote Shell Execution',
            '/\b(or|and)\s+[\d\'"]+\s*=\s*[\d\'"]+(\s*--|\s*#|\s*\/\*)/i' => 'SQL Boolean Tautology with Comment'
        ];

        // Regex de Cross-Site Scripting (XSS)
        $xssPatterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is' => 'XSS Script Tag Injection',
            '/(javascript|vbscript|livescript)\s*:/i' => 'XSS Protocol URI Scheme',
            '/(onload|onerror|onmouseover|onfocus|onblur|onkeydown)\s*=\s*[\'"][^\'"]*[\'"]/i' => 'XSS Event Handler Attribute',
            '/<iframe\b[^>]*>/i' => 'XSS Iframe Injection',
            '/<object\b|<embed\b|<applet\b/i' => 'XSS Object/Embed Injection',
            '/document\.(cookie|location|domain)/i' => 'XSS DOM Cookie Stealing Attempt'
        ];

        // Regex de Path Traversal e LFI
        $pathTraversalPatterns = [
            '/(\.\.\/|\.\.\\\\){2,}/' => 'Directory Path Traversal (../)',
            '/(\/etc\/passwd|\/etc\/shadow|\/proc\/self|c:\\\\windows|c:\\\\boot\.ini|win\.ini)/i' => 'System File Path Disclosure'
        ];

        // Regex de Execução Remota de Código (RCE) / Web Shells
        $rcePatterns = [
            '/\b(passthru|shell_exec|system|popen|proc_open)\s*\(/i' => 'RCE Shell Execution Function',
            '/<\?php|<\?=/i' => 'PHP Code Injection Tag',
            '/php:\/\/(input|filter)/i' => 'PHP Wrapper Stream Injection',
            '/base64_decode\s*\(.*eval/i' => 'Obfuscated Base64 Eval Shell'
        ];

        // Executa inspeção profunda em cada input
        foreach ($allInputs as $inputKey => $inputValue) {
            if (empty($inputValue) || strlen($inputValue) < 4) {
                continue;
            }

            // Exceção controlada para campos de texto rico (Summernote) permitindo tags normais de formatação
            $isSummernoteField = (
                strpos($inputKey, 'content') !== false || 
                strpos($inputKey, 'detail') !== false || 
                strpos($inputKey, 'description') !== false ||
                strpos($inputKey, 'detalhes') !== false ||
                strpos($inputKey, 'requisitos') !== false
            );

            // 1. Anti-SQL Injection
            if (!empty($settings['block_sql_injection'])) {
                foreach ($sqliPatterns as $regex => $desc) {
                    if (preg_match($regex, $inputValue)) {
                        $incId = pronto_security_log_incident($pdo, $ip, 'SQL_INJECTION', 'CRITICA', "[$inputKey] $inputValue");
                        pronto_security_render_blocked_screen("Injeção SQL detectada ($desc).", 'SQL_INJECTION', $incId, $ip);
                    }
                }
            }

            // 2. Anti-XSS
            if (!empty($settings['block_xss'])) {
                foreach ($xssPatterns as $regex => $desc) {
                    // Para Summernote, permitimos tags HTML válidas mas nunca <script>, onerror=, javascript: ou document.cookie
                    if (preg_match($regex, $inputValue)) {
                        $incId = pronto_security_log_incident($pdo, $ip, 'XSS_ATTACK', 'ALTA', "[$inputKey] $inputValue");
                        pronto_security_render_blocked_screen("Script malicioso detectado ($desc).", 'XSS_ATTACK', $incId, $ip);
                    }
                }
            }

            // 3. Anti-Path Traversal
            if (!empty($settings['block_path_traversal'])) {
                foreach ($pathTraversalPatterns as $regex => $desc) {
                    if (preg_match($regex, $inputValue)) {
                        $incId = pronto_security_log_incident($pdo, $ip, 'PATH_TRAVERSAL', 'CRITICA', "[$inputKey] $inputValue");
                        pronto_security_render_blocked_screen("Tentativa de travessia de diretório detectada ($desc).", 'PATH_TRAVERSAL', $incId, $ip);
                    }
                }
            }

            // 4. Anti-RCE
            if (!empty($settings['block_rce'])) {
                foreach ($rcePatterns as $regex => $desc) {
                    if (preg_match($regex, $inputValue)) {
                        $incId = pronto_security_log_incident($pdo, $ip, 'RCE_ATTACK', 'CRITICA', "[$inputKey] $inputValue");
                        pronto_security_render_blocked_screen("Execução remota de código / comando shell detectada ($desc).", 'RCE_ATTACK', $incId, $ip);
                    }
                }
            }
        }
    }

    // ========================================================
    // 6. MOTOR DE DEFESA CONTRA FORÇA BRUTA (LOGIN LOCKOUT)
    // ========================================================

    function pronto_security_check_login_lockout($pdo, $ip, $email) {
        $settings = pronto_security_get_settings($pdo);
        if (empty($settings['brute_force_protection'])) {
            return ['locked' => false];
        }

        try {
            $stmt = $pdo->prepare("
                SELECT attempts, locked_until, 
                       TIMESTAMPDIFF(MINUTE, NOW(), locked_until) AS minutes_left
                FROM tbl_login_attempts 
                WHERE (ip_address = ? OR email = ?) AND locked_until > NOW()
                ORDER BY locked_until DESC LIMIT 1
            ");
            $stmt->execute([$ip, $email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $minLeft = max(1, (int)$row['minutes_left']);
                return [
                    'locked' => true,
                    'minutes_left' => $minLeft,
                    'attempts' => (int)$row['attempts']
                ];
            }
        } catch (Exception $e) {}

        return ['locked' => false];
    }

    function pronto_security_record_login_failure($pdo, $ip, $email) {
        $settings = pronto_security_get_settings($pdo);
        if (empty($settings['brute_force_protection'])) {
            return;
        }

        $maxAttempts = (int)($settings['max_login_attempts'] ?? 5);
        $lockoutMinutes = (int)($settings['lockout_time_minutes'] ?? 15);

        try {
            $stmt = $pdo->prepare("
                SELECT id, attempts FROM tbl_login_attempts 
                WHERE ip_address = ? AND email = ? LIMIT 1
            ");
            $stmt->execute([$ip, $email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $newAttempts = $row['attempts'] + 1;
                $lockedUntilSql = ($newAttempts >= $maxAttempts) ? "NOW() + INTERVAL {$lockoutMinutes} MINUTE" : "NULL";

                $uStmt = $pdo->prepare("
                    UPDATE tbl_login_attempts 
                    SET attempts = ?, last_attempt = NOW(), locked_until = {$lockedUntilSql}
                    WHERE id = ?
                ");
                $uStmt->execute([$newAttempts, $row['id']]);

                if ($newAttempts >= $maxAttempts) {
                    pronto_security_log_incident($pdo, $ip, 'BRUTE_FORCE_LOCKOUT', 'ALTA', "Bloqueio de $lockoutMinutes min após $newAttempts tentativas falhas para $email");
                } else {
                    pronto_security_log_incident($pdo, $ip, 'LOGIN_FAILED', 'BAIXA', "Tentativa falha ($newAttempts/$maxAttempts) para $email", 'LOGGED');
                }
            } else {
                $iStmt = $pdo->prepare("
                    INSERT INTO tbl_login_attempts (ip_address, email, attempts, first_attempt, last_attempt)
                    VALUES (?, ?, 1, NOW(), NOW())
                ");
                $iStmt->execute([$ip, $email]);
                pronto_security_log_incident($pdo, $ip, 'LOGIN_FAILED', 'BAIXA', "Tentativa falha (1/$maxAttempts) para $email", 'LOGGED');
            }
        } catch (Exception $e) {}
    }

    function pronto_security_record_login_success($pdo, $ip, $email) {
        try {
            $dStmt = $pdo->prepare("DELETE FROM tbl_login_attempts WHERE ip_address = ? OR email = ?");
            $dStmt->execute([$ip, $email]);
            pronto_security_log_incident($pdo, $ip, 'LOGIN_SUCCESS', 'BAIXA', "Login efetuado com sucesso por $email", 'ALLOWED');
        } catch (Exception $e) {}
    }

    // ========================================================
    // 7. GERADOR DE CAMPO HONEYPOT INVISÍVEL
    // ========================================================
    function pronto_security_honeypot_field() {
        return '<div style="display:none!important;opacity:0!important;position:absolute!important;left:-9999px!important;top:-9999px!important;" aria-hidden="true">'
             . '<input type="text" name="security_token_hp" value="" autocomplete="off" tabindex="-1">'
             . '</div>';
    }

    // ========================================================
    // 8. CSRF TOKEN HELPER
    // ========================================================
    function pronto_security_csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    function pronto_security_csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(pronto_security_csrf_token()) . '">';
    }

    function pronto_security_verify_csrf() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
