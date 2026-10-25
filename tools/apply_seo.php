<?php
/**
 * Script de Otimização e Sincronização de Metadados de SEO e Banners Open Graph (1200x630)
 * Sistema Pronto Saúde
 * Codificação: UTF-8 No BOM
 */

if (php_sapi_name() !== 'cli') {
    // Se acessado via web, exige autenticação de administrador
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user'])) {
        http_response_code(403);
        die('Acesso negado.');
    }
}

require_once(__DIR__ . '/../admin/config.php');

// 1. Garante que a coluna og_image existe em tbl_settings
try {
    $pdo->query("SELECT og_image FROM tbl_settings LIMIT 1");
} catch (Exception $e) {
    try {
        $pdo->exec("ALTER TABLE tbl_settings ADD COLUMN og_image VARCHAR(255) NULL");
    } catch (Exception $e2) {}
}

// 2. Metadados Otimizados da Página Inicial (Home)
$homeTitle = 'Pronto Saúde | Centro Médico, Odontologia e Exames no RJ';
$homeDesc = 'Atendimento médico humanizado com mais de 30 especialidades, exames rápidos, odontologia completa e cartão fidelidade com descontos. Agende agora!';
$homeKeywords = "pronto saude\nclinica medica rj\ncentro medico rio de janeiro\nconsulta medica rio de janeiro\nexames laboratoriais\nodontologia rj\ncartao fidelidade saude\natendimento humanizado\nagendamento de consultas\nclinica popular rj";
$homeOg = 'og_home.jpg';

$stmtHome = $pdo->prepare("UPDATE tbl_settings SET meta_title_home = ?, meta_description_home = ?, meta_keyword_home = ?, og_image = ? WHERE id = 1");
$stmtHome->execute([$homeTitle, $homeDesc, $homeKeywords, $homeOg]);
echo "Home atualizada com sucesso!\n";

// 3. Metadados Otimizados das Demais Páginas (tbl_page)
$pagesData = [
    'doctor' => [
        'name' => 'Corpo Clínico & Médicos',
        'title' => 'Corpo Clínico & Médicos Especialistas | Pronto Saúde',
        'desc' => 'Conheça nossos médicos especialistas em cardiologia, pediatria, ortopedia, ginecologia e mais. Atendimento humanizado e agendamento rápido no RJ.',
        'keywords' => "medicos especialistas rj\ncardiologista rio de janeiro\npediatra rj\nginecologista rio de janeiro\nortopedista rj\nclinico geral\ndermatologista rj\ncorpo clinico pronto saude\nconsulta com especialista",
        'og' => 'og_doctor.jpg'
    ],
    'servicos' => [
        'name' => 'Nossos Serviços Médicos',
        'title' => 'Serviços Médicos, Consultas e Exames | Pronto Saúde',
        'desc' => 'Consultas especializadas, check-up preventivo, exames laboratoriais e diagnósticos por imagem com laudos rápidos e alta precisão. Agende hoje!',
        'keywords' => "servicos medicos rj\nexames de sangue\ncheckup preventivo\nultrassonografia rj\neletrocardiograma\nexames laboratoriais\ndiagnostico por imagem\nconsultas medicas completas\nlaudos rapidos",
        'og' => 'og_servicos.jpg'
    ],
    'fidelidade' => [
        'name' => 'Planos & Cartão Fidelidade',
        'title' => 'Cartão Fidelidade & Planos de Saúde | Pronto Saúde',
        'desc' => 'Economize até 70% em consultas, exames e odontologia com o Cartão Fidelidade Pronto Saúde. Sem carência, individual e familiar. Saiba mais!',
        'keywords' => "cartao fidelidade saude\ndesconto consultas rj\nplano odontologico familiar\nsaude sem carencia\ncartao de saude barato\nbeneficios medicos rj\ndescontos em exames\nplano pronto saude",
        'og' => 'og_fidelidade.jpg'
    ],
    'odontologia' => [
        'name' => 'Odontologia Especializada',
        'title' => 'Odontologia Especializada & Estética Dental | Pronto Saúde',
        'desc' => 'Tratamentos odontológicos modernos: ortodontia, clareamento, implantes dentários, próteses e canal com dentistas experientes. Agende sua avaliação!',
        'keywords' => "dentista rj\nodontologia especializada\nclareamento dental rio de janeiro\naparelho ortodontico\nimplantes dentarios rj\nprotese dentaria\nclinica odontologica rj\nestetica dental\nlimpeza dental",
        'og' => 'og_odontologia.jpg'
    ],
    'news' => [
        'name' => 'Notícias & Blog de Saúde',
        'title' => 'Blog de Saúde, Bem-Estar e Prevenção | Pronto Saúde',
        'desc' => 'Dicas médicas, cuidados com a alimentação, saúde preventiva e novidades científicas preparadas por nossos especialistas para você viver melhor.',
        'keywords' => "blog saude\ndicas de saude preventiva\nbem estar e nutricao\nnoticias medicas\ncuidados com o coracao\nsaude da mulher\nsaude da crianca\nartigos medicos confiaveis",
        'og' => 'og_news.jpg'
    ],
    'trabalhe-conosco' => [
        'name' => 'Trabalhe Conosco',
        'title' => 'Trabalhe Conosco | Carreiras e Oportunidades Pronto Saúde',
        'desc' => 'Faça parte da nossa equipe médica, de enfermagem e administrativa. Envie seu currículo e venha transformar a saúde conosco no Rio de Janeiro.',
        'keywords' => "trabalhe conosco saude\nvagas clinica medica rj\nempregos medicos rio de janeiro\noportunidades enfermagem\ncarreiras pronto saude\nvagas recepcao clinica\nenvio de curriculo saude",
        'og' => 'og_trabalhe_conosco.jpg'
    ],
    'convenio' => [
        'name' => 'Convênio Livre Escolha',
        'title' => 'Convênio Livre Escolha & Reembolso Fácil | Pronto Saúde',
        'desc' => 'Atendimento médico por Livre Escolha com assessoria completa para reembolso do seu plano de saúde. Consulte sem complicações e com qualidade.',
        'keywords' => "livre escolha saude\nreembolso consulta medica\nconvenio livre escolha rj\nreembolso plano de saude\natendimento por reembolso\nrecibo medico para reembolso\nfacilidade plano de saude",
        'og' => 'og_convenio.jpg'
    ],
    'privacidade' => [
        'name' => 'Política de Privacidade',
        'title' => 'Política de Privacidade & Proteção de Dados | Pronto Saúde',
        'desc' => 'Entenda como a Pronto Saúde coleta, armazena e protege seus dados de saúde e cadastrais em estrita conformidade com a LGPD e o sigilo médico.',
        'keywords' => "politica de privacidade\nlgpd saude\nprotecao de dados medicos\nsigilo medico\ntermos de privacidade\nseguranca da informacao clinica\ndireitos do paciente lgpd",
        'og' => 'og_privacidade.jpg'
    ],
    'rsocial' => [
        'name' => 'Responsabilidade Social',
        'title' => 'Responsabilidade Social & Comunidade | Pronto Saúde',
        'desc' => 'Conheça nossos programas sociais, campanhas de prevenção gratuitas e ações voltadas ao acesso à saúde humanizada em comunidades do Rio de Janeiro.',
        'keywords' => "responsabilidade social saude\nacoes sociais clinica medica\nprojetos sociais rj\nsaude comunitaria\ncampanhas de prevencao\nimpacto social saude\natendimento solidario",
        'og' => 'og_rsocial.jpg'
    ]
];

foreach ($pagesData as $slug => $d) {
    $st = $pdo->prepare("SELECT page_id FROM tbl_page WHERE page_slug = ? OR page_slug = ? LIMIT 1");
    $st->execute([$slug, $slug . '.php']);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $pId = (int)$row['page_id'];
        $up = $pdo->prepare("UPDATE tbl_page SET meta_title = ?, meta_description = ?, meta_keyword = ?, banner = ? WHERE page_id = ?");
        $up->execute([$d['title'], $d['desc'], $d['keywords'], $d['og'], $pId]);
        echo "Página {$slug} (ID {$pId}) atualizada com sucesso!\n";
    } else {
        $ins = $pdo->prepare("INSERT INTO tbl_page (page_name, page_slug, meta_title, meta_description, meta_keyword, banner, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        $ins->execute([$d['name'], $slug, $d['title'], $d['desc'], $d['keywords'], $d['og']]);
        echo "Página {$slug} inserida com sucesso!\n";
    }
}

echo "Todos os metadados de SEO e banners 1200x630 foram sincronizados com sucesso!\n";
