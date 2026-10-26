<?php
/**
 * Endpoint AJAX para Envio de Comentários no Blog da Pronto Saúde
 * Codificação: UTF-8 No BOM
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once(__DIR__ . '/admin/config.php');

// 1. Verificação Honeypot anti-spam
if (!empty($_POST['website_hp'])) {
    // Bot preencheu campo oculto
    echo json_encode(['status' => 'error', 'message' => 'Requisição inválida.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. Extração e sanitização dos campos
$news_id   = isset($_POST['news_id']) ? (int)$_POST['news_id'] : 0;
$c_name    = trim($_POST['c_name'] ?? '');
$c_email   = trim($_POST['c_email'] ?? '');
$c_comment = trim($_POST['c_comment'] ?? '');

if ($news_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Artigo inválido ou não especificado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($c_name) < 2 || mb_strlen($c_name) > 120) {
    echo json_encode(['status' => 'error', 'message' => 'Por favor, informe seu nome completo (mínimo 2 caracteres).'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!filter_var($c_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Por favor, informe um endereço de e-mail válido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($c_comment) < 4 || mb_strlen($c_comment) > 2500) {
    echo json_encode(['status' => 'error', 'message' => 'O comentário deve conter entre 4 e 2500 caracteres.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 3. Garante que a tabela tbl_news_comment existe
try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_news_comment (
            comment_id INTEGER PRIMARY KEY AUTOINCREMENT,
            news_id INTEGER NOT NULL,
            c_name VARCHAR(150) NOT NULL,
            c_email VARCHAR(150) NOT NULL,
            c_comment TEXT NOT NULL,
            c_date DATETIME NOT NULL,
            c_status INTEGER DEFAULT 1
        )");
    } else {
        $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_news_comment (
            comment_id INT(11) NOT NULL AUTO_INCREMENT,
            news_id INT(11) NOT NULL,
            c_name VARCHAR(150) NOT NULL,
            c_email VARCHAR(150) NOT NULL,
            c_comment TEXT NOT NULL,
            c_date DATETIME NOT NULL,
            c_status TINYINT(1) DEFAULT 1,
            PRIMARY KEY (comment_id),
            KEY idx_news (news_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
} catch (Exception $e) {}

// 4. Salva o comentário
try {
    $now = date('Y-m-d H:i:s');
    $clean_comment = strip_tags($c_comment);

    $stmt = $pdo->prepare("INSERT INTO tbl_news_comment (news_id, c_name, c_email, c_comment, c_date, c_status) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->execute([
        $news_id,
        $c_name,
        $c_email,
        $clean_comment,
        $now
    ]);
    $new_id = (int)$pdo->lastInsertId();

    // Extrai iniciais para o avatar
    $parts = explode(' ', trim($c_name));
    $initials = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1));
    if (count($parts) > 1) {
        $initials .= mb_strtoupper(mb_substr(end($parts) ?? '', 0, 1));
    }

    echo json_encode([
        'status'  => 'success',
        'message' => 'Comentário publicado com sucesso! Obrigado pela sua participação.',
        'comment' => [
            'id'       => $new_id,
            'name'     => htmlspecialchars($c_name, ENT_QUOTES, 'UTF-8'),
            'initials' => $initials,
            'comment'  => nl2br(htmlspecialchars($clean_comment, ENT_QUOTES, 'UTF-8')),
            'date'     => date('d/m/Y \à\s H:i', strtotime($now))
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro interno ao salvar seu comentário. Tente novamente mais tarde.'], JSON_UNESCAPED_UNICODE);
    exit;
}
