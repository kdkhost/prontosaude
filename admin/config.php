<?php
// Padronização do fuso horário para América/São Paulo
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR', 'Portuguese_Brazil.1252');

// Credenciais de Banco de Dados
$dbhost = 'localhost';
$dbname = 'prontosaude_garden';
$dbuser = 'prontosaude_garden';
$dbpass = 'prontosaude_garden';

// Detecção segura de protocolo HTTPS
$is_https = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

$protocol = $is_https ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'prontosaude.kdkhost.com.br';
$dominio = $protocol . $host . '/';
$url = $dominio;

// Definição de URLs base
if (!defined('BASE_URL')) {
    define('BASE_URL', $url);
}

if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', BASE_URL . 'admin/');
}

// Conexão PDO com suporte total a UTF-8 mb4 e timezone sincronizado
try {
    $mysql_init_cmd = defined('Pdo\Mysql::ATTR_INIT_COMMAND') ? \Pdo\Mysql::ATTR_INIT_COMMAND : (defined('PDO::MYSQL_ATTR_INIT_COMMAND') ? PDO::MYSQL_ATTR_INIT_COMMAND : 1002);
    $pdo = new PDO("mysql:host={$dbhost};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        $mysql_init_cmd => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '-03:00'"
    ]);
} catch (PDOException $exception) {
    // Fallback inteligente para ambiente de desenvolvimento local caso o MySQL não esteja ativo
    $sqlite_local = __DIR__ . '/../pronto_local.sqlite';
    if (($dbhost === 'localhost' || $dbhost === '127.0.0.1') && file_exists($sqlite_local)) {
        try {
            $pdo = new PDO("sqlite:" . $sqlite_local, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $sqle) {
            error_log("Erro de Conexão PDO SQLite: " . $sqle->getMessage());
            echo "Erro ao conectar com o banco de dados. Por favor, tente novamente mais tarde.";
            exit();
        }
    } else {
        error_log("Erro de Conexão PDO: " . $exception->getMessage());
        echo "Erro ao conectar com o banco de dados. Por favor, tente novamente mais tarde.";
        exit();
    }
}

// Inicialização do Escudo de Segurança de Padrão Militar e WAF
$security_guard_file = __DIR__ . '/inc/security_guard.php';
if (file_exists($security_guard_file)) {
    require_once($security_guard_file);
    pronto_security_inspect_request($pdo);
}

// Função Utilitária Global para Padronização de Datas no Padrão Brasileiro (DD/MM/YYYY)
if (!function_exists('pronto_format_date')) {
    function pronto_format_date($raw_date) {
        $raw = trim((string)$raw_date);
        if ($raw === '' || $raw === '0000-00-00' || $raw === '00-00-0000') {
            return '';
        }
        // Se já estiver no formato d/m/Y (ex: 25/10/2026)
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})/', $raw, $m)) {
            return "{$m[1]}/{$m[2]}/{$m[3]}";
        }
        // Se estiver no formato d-m-Y (ex: 10-10-2022)
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})/', $raw, $m)) {
            return "{$m[1]}/{$m[2]}/{$m[3]}";
        }
        // Se estiver no formato Y-m-d ou Y/m/d (ex: 2026-10-25 ou 2022-10-10)
        if (preg_match('/^(\d{4})[-\/\.](\d{2})[-\/\.](\d{2})/', $raw, $m)) {
            return "{$m[3]}/{$m[2]}/{$m[1]}";
        }
        // Fallback via timestamp
        $timestamp = strtotime($raw);
        if ($timestamp !== false && $timestamp > 0) {
            return date('d/m/Y', $timestamp);
        }
        return $raw;
    }
}