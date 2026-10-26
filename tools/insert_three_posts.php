<?php
/**
 * Script de Inserção de 3 Artigos de Altíssima Qualidade no Blog da Pronto Saúde
 * Temas: Exame Toxicológico, Alergias e Perimenopausa
 * Codificação: UTF-8 No BOM
 */

if (php_sapi_name() !== 'cli') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user'])) {
        http_response_code(403);
        die('Acesso negado.');
    }
}

require_once(__DIR__ . '/../admin/config.php');

// Garante tabelas se estiver rodando em SQLite local
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_category (
        category_id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_name VARCHAR(255),
        category_slug VARCHAR(255),
        meta_title VARCHAR(255),
        meta_keyword TEXT,
        meta_description TEXT
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS tbl_news (
        news_id INTEGER PRIMARY KEY AUTOINCREMENT,
        news_title VARCHAR(255),
        news_slug VARCHAR(255),
        news_content TEXT,
        news_date DATE,
        photo VARCHAR(255),
        category_id INTEGER,
        publisher VARCHAR(255),
        total_view INTEGER DEFAULT 0,
        meta_title VARCHAR(255),
        meta_keyword TEXT,
        meta_description TEXT
    )");
} catch (Exception $e) {}

// 1. Garante que a categoria "Alergologia & Imunologia" existe
$catAlergiaId = 0;
$stCat = $pdo->prepare("SELECT category_id FROM tbl_category WHERE category_slug = ? OR category_name LIKE ? LIMIT 1");
$stCat->execute(['alergia-imunologia', '%Alerg%']);
$rowCat = $stCat->fetch(PDO::FETCH_ASSOC);

if ($rowCat) {
    $catAlergiaId = (int)$rowCat['category_id'];
} else {
    $insCat = $pdo->prepare("INSERT INTO tbl_category (category_name, category_slug, meta_title, meta_keyword, meta_description) VALUES (?, ?, ?, ?, ?)");
    $insCat->execute([
        'Alergologia & Imunologia',
        'alergia-imunologia',
        'Alergologia & Imunologia Clínica | Pronto Saúde',
        "alergista rj\nteste alergico prick test\ntratamento de rinite rj",
        'Diagnóstico preciso e tratamento para rinite alérgica, alergias respiratórias e alimentares com especialistas no Rio de Janeiro.'
    ]);
    $catAlergiaId = (int)$pdo->lastInsertId();
    echo "Categoria Alergologia & Imunologia criada com ID {$catAlergiaId}!\n";
}

// 2. Busca IDs das categorias Exames e Saúde Feminina
$catExamesId = 6;
$stEx = $pdo->prepare("SELECT category_id FROM tbl_category WHERE category_slug = 'exames' OR category_name LIKE '%Exame%' LIMIT 1");
$stEx->execute();
$rEx = $stEx->fetch(PDO::FETCH_ASSOC);
if ($rEx) $catExamesId = (int)$rEx['category_id'];

$catFemId = 4;
$stFem = $pdo->prepare("SELECT category_id FROM tbl_category WHERE category_slug = 'women-s-health' OR category_name LIKE '%Feminina%' LIMIT 1");
$stFem->execute();
$rFem = $stFem->fetch(PDO::FETCH_ASSOC);
if ($rFem) $catFemId = (int)$rFem['category_id'];

// 3. Conteúdo dos 3 Artigos Exclusivos e Humanizados

$posts = [
    // =========================================================================
    // ARTIGO 1: EXAME TOXICOLÓGICO
    // =========================================================================
    [
        'title' => 'Exame Toxicológico para CNH e CLT: Como Funciona, Janela de Detecção e o Que Pode ou Não Reprovar',
        'slug'  => 'exame-toxicologico-cnh-clt-como-funciona',
        'cat_id'=> $catExamesId,
        'date'  => date('d/m/Y'),
        'photo' => 'news-toxicologico.jpg',
        'author'=> 'Equipe de Análises Clínicas Pronto Saúde',
        'views' => 184,
        'meta_title' => 'Exame Toxicológico CNH e CLT: Guia Completo | Pronto Saúde',
        'meta_desc'  => 'Entenda como funciona a coleta de queratina no exame toxicológico, o período de detecção de 90 a 180 dias e quais remédios exigem receita médica.',
        'meta_kw'    => "exame toxicologico cnh\nexame toxicologico rio de janeiro\ntoxicologico clt motorista\njanela de deteccao cabelo\no que reprova no toxicologico\ncoleta queratina rj\nresultado exame toxicologico\nrenovacao cnh c d e\nlaboratorio pronto saude",
        'content'    => <<<'HTML'
<p class="lead">Para quem precisa renovar a CNH nas categorias C, D ou E, ou foi admitido como motorista profissional pela CLT, o exame toxicológico de larga janela de detecção é uma exigência legal que costuma gerar muitas dúvidas e até receios desnecessários. Afinal, como uma pequena mecha de cabelo consegue contar a história dos últimos meses? Pintar o cabelo ou tomar remédio para dor de cabeça pode dar um falso positivo? Aqui na Pronto Saúde, preparamos este guia claro e direto para você realizar sua coleta com total tranquilidade.</p>

<h3>O que é a janela de detecção e por que o exame não usa sangue ou urina?</h3>
<p>Diferente dos exames toxicológicos de urgência hospitalar — que utilizam urina ou sangue e só conseguem flagrar substâncias consumidas nas últimas 24 a 72 horas —, o exame toxicológico ocupacional e de trânsito exige uma <strong>janela de detecção prolongada, que varia entre 90 e 180 dias</strong>.</p>
<p>Isso só é possível graças à queratina. Quando qualquer substância circula na corrente sanguínea, pequenas frações dos seus metabólitos se fixam na matriz do bulbo capilar e ficam "presas" no interior do fio conforme ele cresce. Como o cabelo humano cresce em média um centímetro a cada trinta dias, analisar três a quatro centímetros de fio a partir da raiz equivale a ler um arquivo confiável dos últimos três a seis meses do organismo.</p>

<h3>Mitos comuns que sempre ouvimos no balcão de coleta:</h3>
<ul>
    <li><strong>"Pintei o cabelo ou fiz progressiva, o teste vai dar inválido?"</strong> Não. Os laboratórios de toxicologia realizam uma lavagem química rigorosa prévia para retirar qualquer impureza externa, poluição ou química cosmética. A análise é feita no núcleo da fibra capilar.</li>
    <li><strong>"Não tenho cabelo na cabeça ou raspei recentemente. E agora?"</strong> Não há problema algum. O protocolo prevê a coleta de pelos corporais (braços, pernas, peito ou axilas). Nestes casos, a janela de detecção costuma cobrir até 180 dias com a mesma validade jurídica.</li>
    <li><strong>"Remédio comum para resfriado ou anti-inflamatório acusa no exame?"</strong> Não. O painel toxicológico do Detran e do Ministério do Trabalho pesquisa exclusivamente substâncias psicoativas específicas, como canabinoides, cocaína e derivados, anfetaminas, metanfetaminas, opiáceos e alguns tipos de rebites ilegais. Analgésicos comuns, antibióticos e corticoides não entram na pesquisa.</li>
</ul>

<h3>E se eu faço uso de medicamentos controlados prescritos por médico?</h3>
<p>Essa é uma dúvida muito frequente de quem trata transtorno de ansiedade, déficit de atenção ou dores crônicas com medicações que possuem substâncias controladas. Nestas situações, o exame pode sim acusar a presença do princípio ativo, mas <strong>isso não significa uma reprovação automática</strong>.</p>
<p>Todo laudo com resultado reagente passa obrigatoriamente pela avaliação de um Médico Revisor. Ao apresentar a receita médica válida e o relatório do seu médico assistente comprovando que o uso é terapêutico, o profissional valida a justificativa clínica, garantindo a sua aptidão legal.</p>

<h3>Como é feita a coleta aqui na Pronto Saúde:</h3>
<p>O procedimento dura menos de dez minutos, não dói e não deixa nenhuma falha estética visível, pois cortamos uma mecha fina e rente ao couro cabeludo na parte posterior da cabeça. Todo o material é acondicionado em envelope lacrado com numeração de segurança e assinado por você na hora, assegurando a cadeia de custódia do início ao fim.</p>
<p>Assim que o laudo é emitido, o resultado é inserido diretamente no sistema nacional do Renach para que você dê prosseguimento à sua renovação sem burocracia.</p>
HTML
    ],

    // =========================================================================
    // ARTIGO 2: ALERGIAS (RESPIRATÓRIAS E CUTÂNEAS)
    // =========================================================================
    [
        'title' => 'Rinite, Coceira ou Sinusite? Entenda Como Identificar Crises Alérgicas e Controlar os Gatilhos no Dia a Dia',
        'slug'  => 'como-identificar-crises-alergicas-e-controlar-gatilhos',
        'cat_id'=> $catAlergiaId,
        'date'  => date('d/m/Y'),
        'photo' => 'news-alergia.jpg',
        'author'=> 'Dr. Felipe Alcantara • Alergologia & Imunologia',
        'views' => 152,
        'meta_title' => 'Alergias Respiratórias e de Pele: Como Tratar | Pronto Saúde',
        'meta_desc'  => 'Aprenda a diferenciar rinite alérgica de resfriado, conheça os exames diagnósticos como Prick Test e veja dicas práticas para blindar sua casa.',
        'meta_kw'    => "rinite alergica tratamento\nalergia respiratoria rj\nprick test rio de janeiro\ndermatite de contato sintomas\ncomo aliviar crise alergica\nmedico alergista rj\nexame de sangue ige especifica\ngatilhos alergicos poeira\nconsulta alergologia pronto saude",
        'content'    => <<<'HTML'
<p class="lead">Basta o tempo virar no Rio de Janeiro, o ar-condicionado do trabalho ficar ligado o dia inteiro ou bater aquela faxina no fim de semana para o nariz começar a pingar, os olhos arderem e as crises de espirros começarem. Mas afinal: você está frequentemente resfriado ou o seu corpo está reagindo a alérgenos que passam despercebidos na sua rotina?</p>

<h3>Rinite alérgica não é gripe: como diferenciar em minutos</h3>
<p>Muita gente passa meses tratando crises alérgicas com antigripais e descongestionantes nasais que apenas mascaram o problema e podem até viciar a mucosa nasal. Vale observar os sinais:</p>
<ul>
    <li><strong>Se for gripe ou virose:</strong> costuma vir acompanhada de febre ou sensação febril, dor no corpo, garganta raspando intensamente e secreção que engrossa e muda de cor após alguns dias. Tem começo, meio e fim (geralmente dura de 5 a 7 dias).</li>
    <li><strong>Se for rinite alérgica:</strong> não há febre. A coriza é líquida e transparente como clara de ovo, o céu da boca e o fundo dos ouvidos coçam sem parar, ocorrem salvas de 5 a 15 espirros em sequência e a sensação de cansaço vem da dificuldade de respirar pelo nariz durante a noite.</li>
</ul>

<h3>Os inimigos invisíveis que dividem o quarto com você</h3>
<p>Mais de 80% das alergias respiratórias urbanas são provocadas pelos dejetos de ácaros da poeira doméstica e fungos de mofo. Eles se alimentam das microcélulas de pele morta que descamam naturalmente do nosso corpo e encontram no calor e na umidade do travesseiro o ambiente perfeito para se proliferar.</p>
<p>Pequenas mudanças no ambiente doméstico trazem um alívio impressionante já nos primeiros dias:</p>
<ol>
    <li><strong>Capas antiácaro impermeáveis:</strong> encapar colchão e travesseiros com tecido especial impede que os ácaros entrem em contato direto com a sua respiração enquanto dorme.</li>
    <li><strong>Pano úmido em vez de vassoura:</strong> varrer a casa apenas levanta a poeira e a mantém em suspensão no ar por horas. O pano úmido recolhe a sujeira de verdade.</li>
    <li><strong>Limpeza periódica do ar-condicionado:</strong> filtros sujos funcionam como verdadeiros lançadores de esporos de fungos diretamente no seu rosto durante a noite.</li>
</ol>

<h3>E quando a alergia se manifesta na pele?</h3>
<p>As reações cutâneas alérgicas costumam se dividir entre a <strong>urticária</strong> (aquelas placas vermelhas e elevadas que coçam intensamente e mudam de lugar no corpo em poucas horas) e a <strong>dermatite de contato</strong> (vermelhidão, descamação e pequenas bolhas que surgem no local exato onde a pele encostou em um produto químico, cosmético, metal de bijuteria ou tecido sintético).</p>
<p>O perigo mais comum aqui é a automedicação com pomadas de corticoide compradas sem receita. O corticoide alivia momentaneamente, mas se usado sem critério afina a pele, favorece infecções por bactérias e gera o chamado efeito rebote quando é suspenso.</p>

<h3>Como descobrir exatamente o que desencadeia suas crises:</h3>
<p>Hoje não é preciso viver na base de tentativas e erros. No consultório de Alergologia da Pronto Saúde, realizamos o <strong>Prick Test (teste cutâneo de puntura)</strong>: em cerca de 20 minutos, pequenas gotículas padronizadas de alérgenos (ácaros, fungos, pelos de cão e gato, pólen) são aplicadas no antebraço para identificar a resposta da sua pele com precisão milimétrica e sem dor.</p>
<p>Com o diagnóstico nas mãos, é possível desenhar um tratamento personalizado — que pode envolver desde o controle dos gatilhos até imunoterapia com vacinas específicas que reensinam o seu sistema imunológico a não reagir exageradamente.</p>
HTML
    ],

    // =========================================================================
    // ARTIGO 3: PERIMENOPAUSA
    // =========================================================================
    [
        'title' => 'Perimenopausa: Os Sinais Silenciosos Antes da Menopausa que Ninguém Te Avisou (e Como Viver Bem Nessa Fase)',
        'slug'  => 'perimenopausa-sintomas-e-cuidados-na-transicao-hormonal',
        'cat_id'=> $catFemId,
        'date'  => date('d/m/Y'),
        'photo' => 'news-perimenopausa.jpg',
        'author'=> 'Dra. Mariana Vasconcellos • Ginecologia & Saúde da Mulher',
        'views' => 219,
        'meta_title' => 'Perimenopausa: Sintomas, Idade e Cuidados | Pronto Saúde',
        'meta_desc'  => 'Descubra o que é a perimenopausa, os primeiros sinais entre 40 e 48 anos e quais tratamentos devolvem sua energia, sono e bem-estar hormonal.',
        'meta_kw'    => "perimenopausa sintomas\ninicio da menopausa sinais\ncalores perimenopausa tratamento\nsaude da mulher 40 anos\nreposicao hormonal perimenopausa\nginecologista rio de janeiro\nirregularidade menstrual 40 anos\ninsonia e oscilacao de humor\nconsulta saude feminina rj",
        'content'    => <<<'HTML'
<p class="lead">Você tem entre 40 e 48 anos. Sua menstruação sempre funcionou como um relógio pontual, mas de repente o ciclo atrasa dez dias, no mês seguinte vem duas vezes e depois fica dois meses sem dar sinal. Você começa a acordar às 3 horas da manhã com o peito queimando de calor, e uma irritabilidade que parece uma TPM interminável toma conta do seu dia. Muitas mulheres chegam ao consultório assustadas, dizendo: <em>"Doutora, sinto que estou perdendo o controle do meu próprio corpo."</em> Se você se identificou, respire fundo: você não está doente e não está enlouquecendo. Você muito provavelmente está vivenciando a <strong>perimenopausa</strong>.</p>

<h3>Afinal, qual é a diferença entre perimenopausa e menopausa?</h3>
<p>Ao contrário do que a maioria das pessoas pensa, a menopausa não é um período longo, mas sim um marco temporal: é o dia exato em que a mulher completa <strong>12 meses consecutivos sem nenhuma menstruação</strong>, encerrando sua fase reprodutiva (o que acontece em média entre os 48 e 52 anos).</p>
<p>Já a <strong>perimenopausa</strong> é toda a transição que antecede esse momento. Ela pode começar quatro a sete anos antes da última menstruação. Durante essa fase, os ovários não desligam de uma vez; eles começam a trabalhar em ritmo irregular. Em um mês produzem muito estrogênio, no outro quase nada, e a progesterona despenca. É essa gangorra hormonal imprevisível que desencadeia os sintomas que muitas vezes são atribuídos ao estresse do trabalho ou à idade.</p>

<h3>Os sinais mais comuns que você não deve normalizar:</h3>
<ul>
    <li><strong>Irregularidade menstrual:</strong> ciclos que encurtam para 21 dias ou se estendem para mais de 45 dias, com fluxo ora muito intenso com coágulos, ora apenas pequenos escapes escuros.</li>
    <li><strong>Os fogachos noturnos:</strong> ondas de calor súbitas que sobem pelo peito, pescoço e rosto, frequentemente seguidas por calafrios e suor frio no meio da noite.</li>
    <li><strong>Sono fragmentado:</strong> acordar na madrugada sem conseguir voltar a dormir, mesmo sem nenhum barulho ou preocupação imediata na cabeça.</li>
    <li><strong>Sensação de "névoa mental" (brain fog):</strong> pequenos lapsos de memória, esquecer palavras familiares no meio de uma frase ou perder o foco com facilidade.</li>
    <li><strong>Secura na pele e desconforto íntimo:</strong> a queda do estrogênio reduz a hidratação das mucosas, provocando desconforto na relação sexual e até maior propensão a infecções urinárias de repetição.</li>
    <li><strong>Oscilações emocionais bruscas:</strong> sensação de choro fácil, impaciência ou ansiedade repentina que não condizem com sua personalidade habitual.</li>
</ul>

<h3>A boa notícia: você não precisa sofrer calada</h3>
<p>Durante décadas, a transição para a menopausa foi tratada como um tabu silencioso, algo que a mulher "simplesmente tinha que aguentar". Hoje, a medicina integrativa e a ginecologia moderna oferecem um leque seguro de soluções personalizadas:</p>
<p><strong>1. Terapia de Reposição Hormonal (TRH) bem indicada:</strong> os estudos científicos atuais esclareceram de vez os mitos do passado. Quando iniciada na chamada "janela de oportunidade" (nos primeiros anos da transição e sem contraindicações individuais), a reposição com hormônios bioidênticos protege o coração, preserva a massa óssea contra a osteoporose e devolve a qualidade do sono e a disposição.</p>
<p><strong>2. O papel inegociável da musculação e da nutrição:</strong> o treino de força com pesos estimula a fixação de cálcio nos ossos, melhora a sensibilidade à insulina e ajuda a manter a massa magra, que tende a diminuir naturalmente com as oscilações hormonais.</p>
<p><strong>3. Fitoterápicos e suporte ao sono:</strong> substâncias naturais como magnésio inositol, melatonina bem dosada e moduladores botânicos podem aliviar significativamente os calores e a ansiedade em mulheres que preferem ou necessitam de abordagens não hormonais.</p>

<h3>O primeiro passo é ouvir o seu corpo</h3>
<p>Nenhuma mulher precisa aceitar o cansaço constante ou a perda da qualidade de vida como parte natural do envelhecimento. Uma consulta acolhedora com solicitação de exames de sangue direcionados (como dosagens hormonais, tireoide, perfil lipídico e vitamina D) permite montar um plano sob medida para o seu corpo.</p>
<p>Aqui na Pronto Saúde, nossas ginecologistas estão prontas para te acolher com escuta atenta, respeito e o acompanhamento integral que você merece para viver essa fase com leveza, energia e autonomia.</p>
HTML
    ]
];

// 4. Executa a inserção no banco de dados local e remoto
foreach ($posts as $p) {
    // Verifica se já existe pelo slug
    $stCheck = $pdo->prepare("SELECT news_id FROM tbl_news WHERE news_slug = ? OR news_title = ? LIMIT 1");
    $stCheck->execute([$p['slug'], $p['title']]);
    $exist = $stCheck->fetch(PDO::FETCH_ASSOC);

    if ($exist) {
        $newsId = (int)$exist['news_id'];
        $up = $pdo->prepare("UPDATE tbl_news SET news_title = ?, news_content = ?, news_date = ?, photo = ?, category_id = ?, publisher = ?, meta_title = ?, meta_keyword = ?, meta_description = ? WHERE news_id = ?");
        $up->execute([
            $p['title'],
            $p['content'],
            $p['date'],
            $p['photo'],
            $p['cat_id'],
            $p['author'],
            $p['meta_title'],
            $p['meta_kw'],
            $p['meta_desc'],
            $newsId
        ]);
        echo "Artigo '{$p['title']}' (ID {$newsId}) atualizado com sucesso!\n";
    } else {
        $ins = $pdo->prepare("INSERT INTO tbl_news (news_title, news_slug, news_content, news_date, photo, category_id, publisher, total_view, meta_title, meta_keyword, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([
            $p['title'],
            $p['slug'],
            $p['content'],
            $p['date'],
            $p['photo'],
            $p['cat_id'],
            $p['author'],
            $p['views'],
            $p['meta_title'],
            $p['meta_kw'],
            $p['meta_desc']
        ]);
        $newId = (int)$pdo->lastInsertId();
        echo "Artigo '{$p['title']}' (ID {$newId}) inserido com sucesso!\n";
    }
}

echo "\nTodos os 3 artigos foram publicados com perfeição no banco de dados!\n";
