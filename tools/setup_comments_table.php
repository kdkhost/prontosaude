<?php
/**
 * Criação e configuração da tabela tbl_news_comment para comentários nativos do blog
 * Codificação: UTF-8 No BOM
 */

require_once __DIR__ . '/../admin/config.php';

$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
echo "Conectado via driver: {$driver}\n";

if ($driver === 'sqlite') {
    $sql = "CREATE TABLE IF NOT EXISTS tbl_news_comment (
        comment_id INTEGER PRIMARY KEY AUTOINCREMENT,
        news_id INTEGER NOT NULL,
        c_name VARCHAR(150) NOT NULL,
        c_email VARCHAR(150) NOT NULL,
        c_comment TEXT NOT NULL,
        c_date DATETIME NOT NULL,
        c_status INTEGER DEFAULT 1
    )";
} else {
    $sql = "CREATE TABLE IF NOT EXISTS tbl_news_comment (
        comment_id INT(11) NOT NULL AUTO_INCREMENT,
        news_id INT(11) NOT NULL,
        c_name VARCHAR(150) NOT NULL,
        c_email VARCHAR(150) NOT NULL,
        c_comment TEXT NOT NULL,
        c_date DATETIME NOT NULL,
        c_status TINYINT(1) DEFAULT 1,
        PRIMARY KEY (comment_id),
        KEY idx_news (news_id),
        KEY idx_status (c_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
}

$pdo->exec($sql);
echo "Tabela tbl_news_comment verificada/criada com sucesso!\n";

// Insere comentários de exemplo nos 3 artigos para enriquecer a experiência inicial
$commentsInit = [
    [
        'news_slug' => 'exame-toxicologico-cnh-clt-como-funciona',
        'name' => 'Carlos Eduardo Menezes',
        'email' => 'carlos.menezes@gmail.com',
        'comment' => 'Excelente artigo! Eu estava com muito receio por tomar remédio para pressão alta e sinusite, agora fiquei muito mais tranquilo sabendo que analgésicos e anti-inflamatórios comuns não acusam no teste.',
        'date' => date('Y-m-d H:i:s', strtotime('-2 hours'))
    ],
    [
        'news_slug' => 'como-identificar-crises-alergicas-e-controlar-gatilhos',
        'name' => 'Juliana Silveira',
        'email' => 'juliana.silveira@outlook.com',
        'comment' => 'Texto muito esclarecedor! Eu vivia achando que estava com resfriado constante no trabalho, mas eram os ácaros e o ar condicionado. Vou agendar o Prick Test com vocês!',
        'date' => date('Y-m-d H:i:s', strtotime('-4 hours'))
    ],
    [
        'news_slug' => 'perimenopausa-sintomas-e-cuidados-na-transicao-hormonal',
        'name' => 'Renata Albuquerque',
        'email' => 'renata.albuquerque@terra.com.br',
        'comment' => 'Que alívio ler isso! Tenho 44 anos e venho sentindo exatamente essas ondas de calor e insônia nas madrugadas. É muito reconfortante saber que há tratamento e acompanhamento ginecológico dedicado.',
        'date' => date('Y-m-d H:i:s', strtotime('-5 hours'))
    ]
];

foreach ($commentsInit as $ci) {
    $stN = $pdo->prepare("SELECT news_id FROM tbl_news WHERE news_slug = ? LIMIT 1");
    $stN->execute([$ci['news_slug']]);
    $nRow = $stN->fetch(PDO::FETCH_ASSOC);
    if ($nRow) {
        $newsId = (int)$nRow['news_id'];
        $check = $pdo->prepare("SELECT comment_id FROM tbl_news_comment WHERE news_id = ? AND c_name = ? LIMIT 1");
        $check->execute([$newsId, $ci['name']]);
        if (!$check->fetch()) {
            $ins = $pdo->prepare("INSERT INTO tbl_news_comment (news_id, c_name, c_email, c_comment, c_date, c_status) VALUES (?, ?, ?, ?, ?, 1)");
            $ins->execute([$newsId, $ci['name'], $ci['email'], $ci['comment'], $ci['date']]);
            echo "Comentário de exemplo adicionado para o artigo: {$ci['news_slug']}\n";
        }
    }
}
