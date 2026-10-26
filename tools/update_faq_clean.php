<?php
/**
 * Script para Atualização dos FAQs do Sistema Pronto Saúde
 * Remove Livre Escolha e cadastra as perguntas solicitadas
 * Codificação: UTF-8 No BOM
 */

require_once __DIR__ . '/../admin/config.php';

$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
echo "Conectado via driver: {$driver}\n\n";

// 1. Se for SQLite (ambiente local de testes), garante criação das tabelas
if ($driver === 'sqlite') {
    $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_faq_category (
        faq_category_id INTEGER PRIMARY KEY AUTOINCREMENT,
        faq_category_name TEXT NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_faq (
        faq_id INTEGER PRIMARY KEY AUTOINCREMENT,
        faq_title TEXT NOT NULL,
        faq_content TEXT NOT NULL,
        faq_category_id INTEGER NOT NULL,
        registrado TEXT,
        atualizado TEXT,
        status TEXT DEFAULT 'Active'
    )");
}

// 2. Remove da tbl_faq tudo que se refere a Livre Escolha ou reembolso
echo "--- Removendo registros antigos de Livre Escolha e reembolso ---\n";
$stmtDelFaq = $pdo->prepare("DELETE FROM tbl_faq WHERE faq_title LIKE '%Livre Escolha%' OR faq_content LIKE '%Livre Escolha%' OR faq_title LIKE '%reembolso%' OR faq_content LIKE '%reembolso%'");
$stmtDelFaq->execute();
echo "FAQs de Livre Escolha removidos: " . $stmtDelFaq->rowCount() . "\n";

// Remove categoria de Livre Escolha
$stmtDelCat = $pdo->prepare("DELETE FROM tbl_faq_category WHERE faq_category_name LIKE '%Livre Escolha%'");
$stmtDelCat->execute();
echo "Categorias de Livre Escolha removidas: " . $stmtDelCat->rowCount() . "\n";

// Remove também FAQs antigos com títulos antigos para reinserção padronizada
$stmtDelOld = $pdo->prepare("DELETE FROM tbl_faq WHERE faq_title LIKE '%agendar um exame%' OR faq_title LIKE '%Marco uma consulta%' OR faq_title LIKE '%marcar uma consulta%' OR faq_title LIKE '%retornar ao m%' OR faq_title LIKE '%exames na pronto sa%'");
$stmtDelOld->execute();
echo "FAQs legados reciclados: " . $stmtDelOld->rowCount() . "\n\n";

// 3. Garante categorias organizadas
// Categoria: Consultas Médicas
$stmtCheckCat1 = $pdo->prepare("SELECT faq_category_id FROM tbl_faq_category WHERE faq_category_name LIKE '%Consulta%' LIMIT 1");
$stmtCheckCat1->execute();
$catConsultasId = $stmtCheckCat1->fetchColumn();
if (!$catConsultasId) {
    $stmtInsCat1 = $pdo->prepare("INSERT INTO tbl_faq_category (faq_category_name) VALUES (?)");
    $stmtInsCat1->execute(['Consultas Médicas']);
    $catConsultasId = $pdo->lastInsertId();
} else {
    $stmtUpCat1 = $pdo->prepare("UPDATE tbl_faq_category SET faq_category_name = 'Consultas Médicas' WHERE faq_category_id = ?");
    $stmtUpCat1->execute([$catConsultasId]);
}

// Categoria: Exames & Diagnósticos
$stmtCheckCat2 = $pdo->prepare("SELECT faq_category_id FROM tbl_faq_category WHERE faq_category_name LIKE '%Exame%' LIMIT 1");
$stmtCheckCat2->execute();
$catExamesId = $stmtCheckCat2->fetchColumn();
if (!$catExamesId) {
    $stmtInsCat2 = $pdo->prepare("INSERT INTO tbl_faq_category (faq_category_name) VALUES (?)");
    $stmtInsCat2->execute(['Exames & Diagnósticos']);
    $catExamesId = $pdo->lastInsertId();
} else {
    $stmtUpCat2 = $pdo->prepare("UPDATE tbl_faq_category SET faq_category_name = 'Exames & Diagnósticos' WHERE faq_category_id = ?");
    $stmtUpCat2->execute([$catExamesId]);
}

echo "Categoria Consultas: ID {$catConsultasId}\n";
echo "Categoria Exames: ID {$catExamesId}\n\n";

// 4. Inserção das 4 Perguntas Solicitadas com Conteúdo Médico Humanizado
$novasFaqs = [
    [
        'title' => 'Como marcar uma consulta?',
        'category_id' => $catConsultasId,
        'content' => '<p>Marcar sua consulta na <strong>Pronto Saúde</strong> é simples, acolhedor e sem burocracia:</p><ul><li><strong>Pelo WhatsApp Oficial:</strong> Envie uma mensagem para <a href="https://wa.me/5521990882462?text=Ol%C3%A1!%20Gostaria%20de%20marcar%20uma%20consulta." target="_blank" rel="noopener noreferrer">(21) 99088-2462</a> informando a especialidade desejada.</li><li><strong>Agendamento Rápido no Site:</strong> Preencha o card de agendamento em 1 minuto na página inicial e receba a confirmação no WhatsApp.</li><li><strong>Por Telefone:</strong> Ligue diretamente para nossa recepção no <strong>(21) 99088-2462</strong>.</li></ul><p>Contamos com mais de 30 especialidades médicas (Clínica Geral, Cardiologia, Ginecologia, Pediatria, Ortopedia, Dermatologia, Odontologia e muito mais), com atendimento pontual e humanizado.</p>'
    ],
    [
        'title' => 'Posso retornar ao médico após o exame?',
        'category_id' => $catConsultasId,
        'content' => '<p><strong>Sim!</strong> Na Pronto Saúde, após a realização dos exames solicitados pelo seu médico, você conta com prazo para retorno de avaliação (geralmente em até 30 dias corridos a partir da data da consulta inicial, conforme a conduta de cada especialidade clínica).</p><p>O retorno é fundamental para que o especialista examine os laudos detalhadamente, confirme o diagnóstico, esclareça todas as suas dúvidas e prescreva o melhor tratamento ou acompanhamento preventivo para sua saúde.</p>'
    ],
    [
        'title' => 'Como faço para agendar um exame?',
        'category_id' => $catExamesId,
        'content' => '<p>O agendamento de exames laboratoriais e de imagem na <strong>Pronto Saúde</strong> pode ser feito em poucos minutos:</p><ul><li><strong>Via WhatsApp:</strong> Mande uma mensagem para <a href="https://wa.me/5521990882462?text=Ol%C3%A1!%20Gostaria%20de%20agendar%20um%20exame." target="_blank" rel="noopener noreferrer">(21) 99088-2462</a> com a foto do pedido médico ou nome do exame.</li><li><strong>Por Telefone:</strong> Entre em contato com nossa central pelo <strong>(21) 99088-2462</strong>.</li><li><strong>Presencialmente:</strong> Diretamente na nossa unidade na Av. das Lagoas, 2009 - Gardênia Azul, Jacarepaguá - RJ.</li></ul><p>Nossa equipe médica passa todas as instruções de preparo prévio (como tempo de jejum ou suspensão temporária de medicamentos) e programa o melhor dia e horário para a sua conveniência.</p>'
    ],
    [
        'title' => 'Posso fazer meus exames na Pronto Saúde?',
        'category_id' => $catExamesId,
        'content' => '<p><strong>Sim, com certeza!</strong> A Pronto Saúde possui infraestrutura completa e moderna para que você faça consultas e exames no mesmo local:</p><ul><li><strong>Exames Laboratoriais:</strong> Sangue completo, urina, fezes, preventivo ginecológico (Papanicolau), teste de DNA/paternidade, painéis hormonais, colesterol, glicemia e exame toxicológico para CNH e CLT.</li><li><strong>Exames de Imagem &amp; Cardíacos:</strong> Ultrassonografia geral e obstétrica com doppler, eletrocardiograma (ECG), ecocardiograma e risco cirúrgico.</li></ul><p>Todos os exames são conduzidos por biomédicos e técnicos experientes, com equipamentos calibrados de alta precisão e resultados disponibilizados com máxima agilidade.</p>'
    ]
];

$stmtInsert = $pdo->prepare("INSERT INTO tbl_faq (faq_title, faq_content, faq_category_id, status) VALUES (?, ?, ?, 'Active')");

foreach ($novasFaqs as $f) {
    $stmtInsert->execute([$f['title'], $f['content'], $f['category_id']]);
    echo "FAQ inserido: {$f['title']} (Cat ID: {$f['category_id']})\n";
}

echo "\n=== LISTAGEM ATUALIZADA DE FAQS ===\n";
$stmtCheck = $pdo->query("SELECT f.faq_id, f.faq_title, c.faq_category_name, f.status FROM tbl_faq f LEFT JOIN tbl_faq_category c ON f.faq_category_id = c.faq_category_id ORDER BY f.faq_id ASC");
while ($row = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['faq_id']} | Categoria: {$row['faq_category_name']} | Status: {$row['status']} | Titulo: {$row['faq_title']}\n";
}

echo "\nAtualização de FAQs concluída com 100% de sucesso!\n";
