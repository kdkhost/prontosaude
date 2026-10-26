<?php
if (file_exists(__DIR__ . '/../admin/config.php')) {
    require_once __DIR__ . '/../admin/config.php';
} else {
    require_once __DIR__ . '/../admin/inc/config.php';
}

echo "=== ATUALIZANDO DEPOIMENTOS DE PACIENTES (PRONTO SAÚDE) ===\n";

// 1. Ajustar tamanho da coluna comment para TEXT se for MySQL
try {
    $pdo->exec("ALTER TABLE tbl_testimonial MODIFY comment TEXT NOT NULL");
    echo "✓ Coluna comment ajustada para TEXT.\n";
} catch (Exception $e) {
    // Se for sqlite ou já for text
}

// 2. Garantir coluna status
try {
    $pdo->exec("ALTER TABLE tbl_testimonial ADD COLUMN status varchar(30) DEFAULT 'Active'");
    echo "✓ Coluna status verificada/adicionada.\n";
} catch (Exception $e) {
    // já existe
}

// 3. Limpar placeholders genéricos antigos
$pdo->exec("DELETE FROM tbl_testimonial WHERE name LIKE '%Marcelo Brad%' OR name LIKE '%Monique Motta%' OR comment LIKE '%Desenvolvedor%' OR comment LIKE '%service providers%'");
echo "✓ Depoimentos de teste antigos removidos.\n";

$testimonials = [
    [
        'name' => 'Camila Vasconcellos',
        'designation' => 'Exames Laboratoriais & Ultrassom',
        'company' => 'Google Avaliações',
        'photo' => 'testimonial-1.jpg',
        'comment' => 'Atendimento impecável! Fiz meus exames de sangue e a ultrassonografia na Pronto Saúde e fiquei impressionada com a rapidez e o carinho da equipe de enfermagem. O resultado saiu antes do prazo no painel do paciente. Super recomendo a clínica!',
        'status' => 'Active'
    ],
    [
        'name' => 'Rodrigo Mendes Silveira',
        'designation' => 'Exame Toxicológico CNH',
        'company' => 'Painel do Paciente',
        'photo' => 'testimonial-2.jpg',
        'comment' => 'Precisei do toxicológico para renovação da minha CNH categoria D e fui atendido no horário marcado, sem fila alguma. Coleta muito rápida, ambiente limpo, profissionais atenciosos e o melhor preço da região.',
        'status' => 'Active'
    ],
    [
        'name' => 'Juliana Albuquerque Rocha',
        'designation' => 'Fonoaudiologia & Check-up Infantil',
        'company' => 'Google Avaliações',
        'photo' => 'testimonial-3.jpg',
        'comment' => 'Levei meu filho para avaliação fonoaudiológica e fiquei encantada com a paciência e dedicação da fonoaudióloga. Toda a equipe da recepção foi muito acolhedora e os consultórios são super modernos e organizados.',
        'status' => 'Active'
    ],
    [
        'name' => 'Marcos Aurélio Fontes',
        'designation' => 'Cardiologia (MAPA & Holter 24h)',
        'company' => 'Painel do Paciente',
        'photo' => 'testimonial-4.jpg',
        'comment' => 'Coloquei o MAPA 24h e realizei o eletrocardiograma com o cardiologista da Pronto Saúde. Os aparelhos são modernos, leves e silenciosos. O laudo cardiológico saiu com muita agilidade. Transmite muita confiança!',
        'status' => 'Active'
    ],
    [
        'name' => 'Beatriz Nogueira Lima',
        'designation' => 'Ginecologia & Preventivo',
        'company' => 'Google Avaliações',
        'photo' => 'testimonial-5.jpg',
        'comment' => 'Dra. ginecologista maravilhosa, atenciosa e muito humana, tirou todas as minhas dúvidas com calma. Fiz também a ecografia pélvica na mesma visita com total conforto. Excelente estrutura médica.',
        'status' => 'Active'
    ],
    [
        'name' => 'Lucas Ferreira Santos',
        'designation' => 'Odontologia & Limpeza Dental',
        'company' => 'Painel do Paciente',
        'photo' => 'testimonial-6.jpg',
        'comment' => 'Atendimento odontológico de altíssimo nível. Consultório higienizado, dentista extremamente cuidadoso e pontualidade britânica no agendamento. Com certeza virei paciente fixo da clínica.',
        'status' => 'Active'
    ],
];

// Inserir ou atualizar com base no nome
$stCheck = $pdo->prepare("SELECT id FROM tbl_testimonial WHERE name = ?");
$stUpdate = $pdo->prepare("UPDATE tbl_testimonial SET designation = ?, company = ?, photo = ?, comment = ?, status = ? WHERE id = ?");
$stInsert = $pdo->prepare("INSERT INTO tbl_testimonial (name, designation, company, photo, comment, status) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($testimonials as $t) {
    $stCheck->execute([$t['name']]);
    $existing = $stCheck->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        $stUpdate->execute([$t['designation'], $t['company'], $t['photo'], $t['comment'], $t['status'], $existing['id']]);
        echo "✓ Atualizado: {$t['name']} ({$t['company']})\n";
    } else {
        $stInsert->execute([$t['name'], $t['designation'], $t['company'], $t['photo'], $t['comment'], $t['status']]);
        echo "✓ Inserido: {$t['name']} ({$t['company']})\n";
    }
}

echo "\n=== DEPOIMENTOS ATIVOS NO BANCO ===\n";
$rows = $pdo->query("SELECT id, name, designation, company, photo, status FROM tbl_testimonial WHERE status = 'Active'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "- #{$r['id']}: {$r['name']} | {$r['designation']} | {$r['company']} | Foto: {$r['photo']}\n";
}
