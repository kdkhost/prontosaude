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
    $pdo = new PDO("mysql:host={$dbhost};dbname={$dbname};charset=utf8mb4", $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '-03:00'"
    ]);
} catch (PDOException $exception) {
    error_log("Erro de Conexão PDO: " . $exception->getMessage());
    echo "Erro ao conectar com o banco de dados. Por favor, tente novamente mais tarde.";
    exit();
}