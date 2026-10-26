<?php
require_once __DIR__ . '/../admin/config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== ATUALIZANDO CATÁLOGO DE ESPECIALIDADES E EXAMES (PRONTO SAÚDE) ===\n\n";

// Lista oficial das 16 especialidades e exames solicitados
$especialidades = [
    [
        'key' => 'toxicologico',
        'name' => 'Exame Toxicológico',
        'slug' => 'toxicologico',
        'price' => 'Consulte',
        'short_description' => '<p>Exame toxicológico de larga janela de detecção para CNH (renovação C, D e E), admissão e demissão CLT, concursos públicos e particulares. Coleta ágil e laudo oficial credenciado no RENACH.</p>',
        'description' => '<p>O <strong>Exame Toxicológico de Larga Janela de Detecção</strong> da <strong>Pronto Saúde</strong> é realizado com elevado rigor analítico, confidencialidade e rapidez. É um procedimento obrigatório para motoristas profissionais (categorias C, D e E) na primeira habilitação, renovação periódica ou mudança de categoria, além de admissões e demissões corporativas pela CLT e certames públicos.</p>
<h4>Como Funciona a Coleta</h4>
<p>O teste é realizado através de uma pequena amostra de pelos corporais ou mecha de cabelo coletada na raiz por profissionais habilitados. O procedimento é 100% indolor, rápido e não invasivo, sem necessidade de preparação prévia ou jejum.</p>
<h4>Janela de Detecção e Substâncias Pesquisadas</h4>
<p>Capaz de identificar substâncias psicoativas consumidas em uma janela média de 90 a 180 dias antecedentes à coleta, incluindo anfetaminas, cocaína e derivados, canabinoides (maconha), opiáceos e outras substâncias controladas com protocolo de cadeia de custódia rastreado.</p>
<h4>Vantagens na Pronto Saúde</h4>
<ul>
    <li>Coleta expressa com atendimento acolhedor e humanizado.</li>
    <li>Integração direta e homologada com o RENACH e Detran.</li>
    <li>Laudo pericial com certificado de autenticidade emitido com agilidade.</li>
    <li>Tabela de valores acessíveis e desconto especial no Cartão Fidelidade.</li>
</ul>',
        'benefits' => "Coleta rápida, indolor e segura\nHomologado no RENACH e Detran\nCadeia de custódia certificada\nPreço acessível e parcelamento",
        'button_text' => 'Agendar Toxicológico',
        'button_url' => '',
        'photo' => 'service-toxicologico.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'oftalmologia',
        'name' => 'Oftalmologia',
        'slug' => 'oftalmologia',
        'price' => 'Consulte',
        'short_description' => '<p>Consultas oftalmológicas completas para adultos e crianças, avaliação da acuidade visual, refração com grau para óculos, biomicroscopia, pressão ocular e prevenção de doenças da visão.</p>',
        'description' => '<p>A especialidade de <strong>Oftalmologia</strong> da <strong>Pronto Saúde</strong> é dedicada ao cuidado preventivo e terapêutico da visão de toda a família. Nossos médicos oftalmologistas realizam diagnósticos minuciosos com equipamentos modernos para garantir conforto e clareza visual.</p>
<h4>Exames Realizados na Consulta</h4>
<ul>
    <li><strong>Refração Computadorizada:</strong> Avaliação do grau exato para prescrição de óculos e lentes de contato corretivas para miopia, hipermetropia e astigmatismo.</li>
    <li><strong>Biomicroscopia (Lâmpada de Fenda):</strong> Inspeção minuciosa das estruturas anteriores e posteriores do olho, incluindo córnea, íris, cristalino e conjuntiva.</li>
    <li><strong>Tonometria Ocular:</strong> Medição precisa da pressão intraocular, fundamental para rastreio e controle do glaucoma.</li>
    <li><strong>Avaliação do Fundo de Olho:</strong> Rastreio de lesões na retina provocadas por diabetes, hipertensão e degenerações maculares.</li>
</ul>
<h4>Quando Consultar o Oftalmologista?</h4>
<p>Recomenda-se consulta anual preventiva para todas as idades, ou imediatamente em casos de visão embaçada, dores de cabeça frequentes, fadiga ocular ao ler, sensibilidade excessiva à luz ou sensação de areia nos olhos.</p>',
        'benefits' => "Médicos oftalmologistas experientes\nPrescrição precisa para óculos e lentes\nEquipamentos modernos de diagnóstico\nAtendimento para adultos e crianças",
        'button_text' => 'Agendar Oftalmologista',
        'button_url' => '',
        'photo' => 'service-oftalmologia.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'teste-de-alergia',
        'name' => 'Teste de Alergia',
        'slug' => 'teste-de-alergia',
        'price' => 'Consulte',
        'short_description' => '<p>Diagnóstico preciso de alergias respiratórias e alimentares através do Prick Test (teste de puntura cutânea), com aplicação indolor no antebraço e resultado imediato em 20 minutos.</p>',
        'description' => '<p>O <strong>Teste de Alergia (Prick Test ou Teste de Puntura)</strong> da <strong>Pronto Saúde</strong> é o padrão-ouro internacional para a identificação célere e confiável de alérgenos responsáveis por crises de rinite, asma, conjuntivite alérgica e reações dermatológicas.</p>
<h4>Como o Teste é Feito</h4>
<p>Pequenas gotas contendo extratos de alérgenos padronizados são dispostas sobre a pele do antebraço do paciente. Uma microagulha descartável estéril faz uma puntura superficial indolor na epiderme. Em cerca de 15 a 20 minutos, o especialista afere a formação de pápulas e confirma as substâncias desencadeantes da sensibilidade alérgica.</p>
<h4>Painel de Alérgenos Pesquisados</h4>
<ul>
    <li><strong>Alérgenos Inalantes:</strong> Ácaros da poeira domiciliar (Dermatophagoides), fungos do ar, pólen e epitélio de cão e gato.</li>
    <li><strong>Alérgenos Alimentares:</strong> Leite de vaca, ovo, trigo, frutos do mar, soja e oleaginosas.</li>
</ul>
<h4>Resultados e Tratamento</h4>
<p>O resultado é entregue durante o próprio atendimento, permitindo que o médico institua condutas de controle ambiental, medicamentos de alívio e terapias de imunoterapia específica com eficácia comprovada.</p>',
        'benefits' => "Resultado rápido em apenas 20 minutos\nProcedimento minimamente invasivo e indolor\nPainel amplo de inalantes e alimentos\nConduta terapêutica personalizada",
        'button_text' => 'Agendar Teste Alérgico',
        'button_url' => '',
        'photo' => 'service-alergia.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'dna',
        'name' => 'Teste de Paternidade (DNA)',
        'slug' => 'dna',
        'price' => 'Consulte',
        'short_description' => '<p>Exame de DNA com precisão superior a 99,99%, sigilo absoluto, coleta simples e indolor por swab bucal ou sangue e laudo pericial detalhado com total validade técnica e jurídica.</p>',
        'description' => '<p>O <strong>Teste de Paternidade por DNA</strong> da <strong>Pronto Saúde</strong> oferece a máxima segurança e clareza genética para esclarecer dúvidas de vínculo biológico. Utilizamos tecnologias avançadas de análise multiplex de marcadores STR (Short Tandem Repeats), garantindo índices de acerto superiores a 99,999% na confirmação e 100% na exclusão de paternidade.</p>
<h4>Tipos de Exame Disponíveis</h4>
<ul>
    <li><strong>Trio:</strong> Coleta com a presença do suposto pai, da mãe biológica e do filho(a).</li>
    <li><strong>Duo:</strong> Coleta na presença apenas do suposto pai e do filho(a), mantendo alta precisão técnica.</li>
</ul>
<h4>Coleta Confortável e Sigilosa</h4>
<p>A coleta pode ser realizada através de suave esfregaço na mucosa interna da bochecha (swab bucal), técnica totalmente indolor e perfeita para recém-nascidos e crianças pequenas, sem a necessidade de picada de agulha ou jejum.</p>
<h4>Privacidade Garantida</h4>
<p>Todo o processo ocorre sob sigilo profissional irrestrito, garantindo anonimato, proteção de dados e entrega de laudo pericial lacrado e codificado.</p>',
        'benefits' => "Precisão superior a 99,99% comprovada\nColeta por swab bucal indolor (ideal para bebês)\nSigilo ético e confidencialidade total\nLaudo pericial com suporte técnico completo",
        'button_text' => 'Agendar Teste de DNA',
        'button_url' => '',
        'photo' => 'service-paternidade.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'exames-laboratoriais',
        'name' => 'Exames Laboratoriais',
        'slug' => 'exames-laboratoriais',
        'price' => 'A partir de R$ 15,00',
        'short_description' => '<p>Mais de 1.500 exames de sangue, urina, fezes, hormonais, imunológicos e bioquímicos com coletas humanizadas, resultados rápidos online e preços populares no Cartão Fidelidade.</p>',
        'description' => '<p>O setor de <strong>Exames Laboratoriais</strong> da <strong>Pronto Saúde</strong> alia tecnologia automatizada de ponta a um acolhimento caloroso em cada coleta. Atendemos todas as demandas de check-up preventivo, acompanhamento pré-operatório e investigação médica em parceria com os centros diagnósticos mais conceituados do país.</p>
<h4>Exames de Rotina e Especializados</h4>
<ul>
    <li><strong>Bioquímica e Sangue:</strong> Hemograma completo, glicemia de jejum, hemoglobina glicada, perfil lipídico completo (colesterol e frações, triglicerídeos).</li>
    <li><strong>Função Renal e Hepática:</strong> Creatinina, ureia, TGO, TGP, Gama GT, fosfatase alcalina e bilirrubinas.</li>
    <li><strong>Hormônios e Tireoide:</strong> TSH ultra-sensível, T4 Livre, testosterona total e livre, estradiol, prolactina e cortisol.</li>
    <li><strong>Vitaminas e Minerais:</strong> Vitamina D (25-hidroxi), Vitamina B12, ferro sérico, ferritina e ácido fólico.</li>
    <li><strong>Urina e Fezes:</strong> EAS (Elementos Anormais e Sedimento), urocultura com antibiograma e protoparasitológico.</li>
</ul>
<h4>Comodidade e Rapidez</h4>
<p>Resultados liberados com agilidade pela internet, garantindo que você e seu médico tenham as informações essenciais para a melhor conduta clínica no menor tempo possível.</p>',
        'benefits' => "Mais de 1.500 tipos de exames disponíveis\nResultados rápidos com acesso online seguro\nColeta humanizada e acolhedora\nPreços populares e desconto Fidelidade",
        'button_text' => 'Agendar Exames Laboratoriais',
        'button_url' => '',
        'photo' => 'service-4.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'usg',
        'name' => 'Ultrassonografia (Ultra)',
        'slug' => 'usg',
        'price' => 'Consulte',
        'short_description' => '<p>Exames de ultrassom geral, obstétrico, transvaginal, articular e com Doppler colorido. Imagens de alta definição com laudos rápidos e médicos ultrassonografistas dedicados.</p>',
        'description' => '<p>A <strong>Ultrassonografia (Ultra)</strong> da <strong>Pronto Saúde</strong> utiliza aparelhos digitais modernos com transdutores multifrequenciais para gerar imagens anatômicas nítidas e detalhadas de tecidos moles, articulações e fluxo vascular em tempo real.</p>
<p>Por ser um método diagnóstico baseado em ondas sonoras, é totalmente seguro, indolor e livre de qualquer radiação ionizante, sendo o exame padrão para o acompanhamento gestacional e avaliações preventivas.</p>
<h4>Principais Modalidades Realizadas</h4>
<ul>
    <li><strong>Ultrassom de Abdome Total e Superior:</strong> Avaliação de fígado, vesícula biliar, pâncreas, baço, rins e bexiga.</li>
    <li><strong>Ultrassom Pélvico e Transvaginal:</strong> Estudo minucioso do útero, endométrio e ovários para investigação ginecológica.</li>
    <li><strong>Ultrassom de Mamas e Tireoide:</strong> Rastreio de nódulos, cistos e alterações teciduais com modo Doppler.</li>
    <li><strong>Ultrassom Obstétrico e Morfológico:</strong> Acompanhamento seguro e emocionante de cada trimestre da gestação.</li>
    <li><strong>Ultrassom Articular e Muscular:</strong> Ombros, joelhos, tornozelos, punhos e cotovelos com foco ortopédico.</li>
</ul>',
        'benefits' => "Aparelhos modernos de alta resolução diagnóstica\nSem radiação: seguro para gestantes e bebês\nEntrega rápida de laudos e imagens digitais\nAtendimento pontual e confortável",
        'button_text' => 'Agendar Ultrassonografia',
        'button_url' => '',
        'photo' => 'service-ultrassonografia.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'mapa',
        'name' => 'MAPA 24 Horas',
        'slug' => 'mapa',
        'price' => 'Consulte',
        'short_description' => '<p>Monitorização Ambulatorial da Pressão Arterial durante 24 horas contínuas. Avaliação precisa de picos hipertensivos, efeito do avental branco e eficácia medicamentosa com laudo cardiológico.</p>',
        'description' => '<p>O <strong>MAPA (Monitorização Ambulatorial da Pressão Arterial 24h)</strong> é um exame cardiológico dinâmico que afere e registra as variações da pressão arterial durante o dia e a noite, enquanto o paciente segue suas atividades habituais de trabalho, descanso e sono.</p>
<h4>Para Que Serve o MAPA</h4>
<ul>
    <li>Identificar a hipertensão arterial mascarada ou picos esporádicos.</li>
    <li>Diferenciar a hipertensão verdadeira do efeito do "avental branco" (ansiedade no consultório).</li>
    <li>Avaliar o descenso pressórico durante o sono (descenso noturno fisiológico).</li>
    <li>Ajustar dosagens e horários de medicações anti-hipertensivas de forma personalizada.</li>
</ul>
<h4>Como Funciona a Instalação</h4>
<p>Uma braçadeira confortável é posicionada no braço do paciente e acoplada por um fino tubo a um gravador digital compacto e silencioso fixado à cintura. Após 24 horas de registro, os dados são compilados e analisados minuciosamente por cardiologistas experientes.</p>',
        'benefits' => "Aparelhos digitais leves, compactos e silenciosos\nAnálise completa das pressões diurnas e noturnas\nLaudo detalhado com gráficos e parecer cardiológico\nInstalação rápida e orientações completas da equipe",
        'button_text' => 'Agendar MAPA 24h',
        'button_url' => '',
        'photo' => 'service-mapa.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'holter',
        'name' => 'Holter 24 Horas',
        'slug' => 'holter',
        'price' => 'Consulte',
        'short_description' => '<p>Registro contínuo do ritmo cardíaco por 24 horas. Fundamental para identificar arritmias, taquicardias, palpitações, síncopes (desmaios) e isquemia silenciosa com monitor digital moderno.</p>',
        'description' => '<p>O <strong>Holter 24 Horas</strong> é uma monitorização contínua do eletrocardiograma durante as 24 horas normais do dia a dia do paciente. É o método ideal para capturar alterações elétricas transitórias no coração que não ocorrem durante o breve período de um eletrocardiograma convencional.</p>
<h4>Principais Indicações</h4>
<ul>
    <li>Investigação de palpitações, palpitações irregulares ("batedeira") e extrassístoles.</li>
    <li>Avaliação de tonturas, episódios de pré-desmaio e desmaios sem causa evidente (síncopes).</li>
    <li>Acompanhamento de arritmias cardíacas diagnosticadas e avaliação da resposta a remédios.</li>
    <li>Verificação do funcionamento de marca-passos cardíacos implantados.</li>
</ul>
<h4>Instalação e Orientações</h4>
<p>Eletrodos autoadesivos descartáveis são colocados suavemente no tórax e ligados ao gravador digital leve preso a um cinto. O paciente preenche um diário com suas atividades e horários de eventuais sintomas, permitindo correlação clínica perfeita no laudo.</p>',
        'benefits' => "Equipamento moderno digital, leve e confortável\nDetecção precisa de arritmias transitórias e silenciosas\nCorrelação exata de sintomas com traçado eletrocardiográfico\nLaudo assinado por cardiologista especialista",
        'button_text' => 'Agendar Holter 24h',
        'button_url' => '',
        'photo' => 'service-11.jpeg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'eletro-cardiograma',
        'name' => 'Eletrocardiograma (ECG)',
        'slug' => 'eletro-cardiograma',
        'price' => 'A partir de R$ 40,00',
        'short_description' => '<p>Exame cardiológico rápido e indolor que avalia a atividade elétrica do coração, ritmo cardíaco, sobrecargas e circulação coronariana com laudo médico emitido com agilidade.</p>',
        'description' => '<p>O <strong>Eletrocardiograma em Repouso (ECG)</strong> é o exame fundamental de triagem cardiológica. Em poucos minutos, ele mapeia os impulsos elétricos gerados a cada batimento cardíaco, avaliando o ritmo, a frequência e a condução elétrica nas câmaras do coração.</p>
<h4>Aplicações e Importância</h4>
<ul>
    <li><strong>Risco Cirúrgico Pré-Operatório:</strong> Exame indispensável para a liberação segura de procedimentos cirúrgicos e anestésicos.</li>
    <li><strong>Atestado Esportivo:</strong> Avaliação para início ou retomada segura de treinos e práticas desportivas em academias.</li>
    <li><strong>Rastreio Cardiológico:</strong> Detecção precoce de arritmias, hipertrofias ventriculares (coração aumentado) e sequelas de infarto.</li>
</ul>
<h4>Conforto no Atendimento</h4>
<p>O exame é realizado deitado, sem desconforto ou preparação especial, com entrega de traçado nítido e laudo emitido por cardiologista habilitado.</p>',
        'benefits' => "Exame rápido, totalmente indolor e não invasivo\nLaudo emitido por médico cardiologista\nEssencial para risco cirúrgico e atestados esportivos\nPreço popular e atendimento sem filas",
        'button_text' => 'Agendar Eletrocardiograma',
        'button_url' => '',
        'photo' => 'service-13.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'psiquiatria',
        'name' => 'Psiquiatria',
        'slug' => 'psiquiatria',
        'price' => 'Consulte',
        'short_description' => '<p>Cuidado médico humanizado e especializado para ansiedade, depressão, insônia, burnout, transtorno bipolar, TDAH e bem-estar emocional e mental com acolhimento profissional.</p>',
        'description' => '<p>A especialidade de <strong>Psiquiatria</strong> da <strong>Pronto Saúde</strong> proporciona um atendimento ético, acolhedor e despido de estigmas para cuidar da saúde mental e emocional. Nossos psiquiatras realizam avaliações clínicas detalhadas, integrando fatores biológicos, psicológicos e contextuais de cada paciente.</p>
<h4>Transtornos e Queixas Atendidas</h4>
<ul>
    <li>Transtornos de ansiedade (TAG, crises de pânico, fobias e estresse agudo).</li>
    <li>Depressão em diferentes estágios, distimia e transtorno afetivo bipolar.</li>
    <li>Insônia persistente, alterações do ritmo circadiano e fadiga mental crônica.</li>
    <li>Transtorno de Déficit de Atenção e Hiperatividade (TDAH) em adultos e jovens.</li>
    <li>Esgotamento profissional (Síndrome de Burnout) e transtorno obsessivo-compulsivo (TOC).</li>
</ul>
<h4>Abordagem Terapêutica Segura</h4>
<p>A conduta do psiquiatra visa sempre o equilíbrio biopsicossocial, com prescrição racional e segura de medicações quando estritamente necessárias, associada a orientações de rotina, higiene do sono e psicoterapia.</p>',
        'benefits' => "Médicos psiquiatras acolhedores, sem julgamentos\nAbordagem moderna com farmacoterapia responsável\nPrescrição e renovação segura de receitas controladas\nAmbiente clínico calmo, discreto e confidencial",
        'button_text' => 'Agendar Psiquiatra',
        'button_url' => '',
        'photo' => 'service-psiquiatria.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'fonoaudiologia',
        'name' => 'Fonoaudiologia',
        'slug' => 'fonoaudiologia',
        'price' => 'Consulte',
        'short_description' => '<p>Prevenção, avaliação e reabilitação dos distúrbios da comunicação humana: fala, linguagem infantil e adulta, voz profissional, deglutição (disfagia) e audição com fonoaudiólogas dedicadas.</p>',
        'description' => '<p>A <strong>Fonoaudiologia</strong> na <strong>Pronto Saúde</strong> cuida de funções essenciais para a qualidade de vida e a conexão humana: falar, ouvir, mastigar e engolir com harmonia e segurança. Nossas profissionais atuam tanto na reabilitação clínica quanto no aperfeiçoamento da comunicação.</p>
<h4>Principais Linhas de Cuidado</h4>
<ul>
    <li><strong>Desenvolvimento da Fala Infantil:</strong> Auxílio especializado para crianças com atraso na fala, trocas ou omissões de letras (ceceio, dislalia).</li>
    <li><strong>Gagueira e Fluência:</strong> Estratégias terapêuticas para controle de bloqueios e fluência verbal em qualquer faixa etária.</li>
    <li><strong>Aprimoramento e Terapia Vocal:</strong> Tratamento de rouquidão, cansaço vocal, nódulos ou pólipos nas cordas vocais para professores, cantores e comunicadores.</li>
    <li><strong>Disfagia e Deglutição:</strong> Reabilitação de dificuldades de engasgo e deglutição decorrentes de pós-AVC, doenças neurológicas ou envelhecimento.</li>
</ul>',
        'benefits' => "Sessões personalizadas e lúdicas para crianças\nExercícios vocais práticos para profissionais da voz\nReabilitação segura para disfagia e pós-AVC\nAtendimento individualizado com fonoaudiólogas experientes",
        'button_text' => 'Agendar Fonoaudióloga',
        'button_url' => '',
        'photo' => 'service-fonoaudiologia.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'psicologia',
        'name' => 'Psicologia',
        'slug' => 'psicologia',
        'price' => 'Consulte',
        'short_description' => '<p>Psicoterapia acolhedora individual para crianças, adolescentes, adultos e idosos. Espaço seguro para autoconhecimento, inteligência emocional, superação de traumas e ansiedade.</p>',
        'description' => '<p>O serviço de <strong>Psicologia</strong> da <strong>Pronto Saúde</strong> acolhe o sofrimento humano com escuta atenta, respeito e rigor ético. Por meio de encontros regulares de psicoterapia, auxiliamos nossos pacientes a compreender padrões comportamentais, processar sentimentos complexos e construir novos caminhos para uma vida plena.</p>
<h4>Motivos para Procurar um Psicólogo</h4>
<ul>
    <li>Dificuldade no manejo de ansiedade, angústia, fobias e estresse diário.</li>
    <li>Processamento de lutos, perdas afetivas, rupturas de relacionamento e mudanças de vida.</li>
    <li>Transtornos depressivos, sentimentos de vazio e desmotivação.</li>
    <li>Fortalecimento da autoestima, assertividade e comunicação nos relacionamentos.</li>
    <li>Dificuldades de aprendizagem, limites e regulação emocional na infância.</li>
</ul>
<h4>Metodologias e Acolhimento</h4>
<p>Trabalhamos com abordagens comprovadas da ciência psicológica (como Terapia Cognitivo-Comportamental e Psicanálise), sempre focando nas singularidades e objetivos de cada indivíduo.</p>',
        'benefits' => "Espaço seguro com sigilo ético absoluto\nPsicólogos graduados com abordagens terapêuticas modernas\nPlanos de atendimento individualizados e flexíveis\nIntegração multiprofissional com psiquiatria e clínica médica",
        'button_text' => 'Agendar Psicólogo',
        'button_url' => '',
        'photo' => 'service-14.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'ginecologia',
        'name' => 'Ginecologia & Obstetrícia',
        'slug' => 'ginecologia',
        'price' => 'Consulte',
        'short_description' => '<p>Cuidado integral e humanizado para a saúde da mulher em todas as idades: consultas preventivas, preventivo (Papanicolau), pré-natal, métodos contraceptivos e climatério.</p>',
        'description' => '<p>A <strong>Ginecologia e Obstetrícia</strong> da <strong>Pronto Saúde</strong> é planejada para oferecer um ambiente calmo, seguro e acolhedor para as mulheres cuidarem de sua saúde física e reprodutiva com máxima tranquilidade e respeito.</p>
<h4>Acompanhamento Preventivo Completo</h4>
<ul>
    <li><strong>Preventivo / Papanicolau:</strong> Rastreio fundamental para prevenção do câncer do colo uterino e detecção de infecções ginecológicas.</li>
    <li><strong>Planejamento Familiar e Contracepção:</strong> Orientação detalhada sobre métodos orais, injetáveis, implantes e inserção de DIU (hormonal e não hormonal).</li>
    <li><strong>Pré-Natal Acolhedor:</strong> Assistência completa à gestante e ao bebê durante toda a gravidez, orientando cada fase do parto com carinho.</li>
    <li><strong>Climatério e Menopausa:</strong> Manejo dos sintomas como ondas de calor, alterações do sono e do humor, com terapias individualizadas.</li>
    <li><strong>Tratamento Ginecológico:</strong> Investigação e alívio de cólicas intensas, endometriose, candidíase de repetição e síndrome dos ovários policísticos (SOP).</li>
</ul>',
        'benefits' => "Médicas ginecologistas atenciosas e acolhedoras\nExame preventivo e ultrassons no mesmo local\nOrientação personalizada sobre contracepção e fertilidade\nAmbiente confortável com privacidade total",
        'button_text' => 'Agendar Ginecologista',
        'button_url' => '',
        'photo' => 'service-ginecologia.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'gastroenterologia',
        'name' => 'Gastroenterologia (Gastro)',
        'slug' => 'gastroenterologia',
        'price' => 'Consulte',
        'short_description' => '<p>Diagnóstico e tratamento de doenças do estômago, esôfago, intestino, fígado e pâncreas: refluxo, gastrite, dores abdominais, intolerâncias alimentares e esteatose hepática.</p>',
        'description' => '<p>A <strong>Gastroenterologia (Gastro)</strong> da <strong>Pronto Saúde</strong> trata com precisão os distúrbios de todo o trato digestório, promovendo o bem-estar e o alívio de sintomas que afetam a rotina e a alimentação de milhares de pacientes.</p>
<h4>Principais Queixas Tratadas</h4>
<ul>
    <li><strong>Refluxo Gastroesofágico e Azia:</strong> Tratamento da sensação de queimação no peito, regurgitação ácida e tosse seca crônica.</li>
    <li><strong>Gastrite e Úlceras:</strong> Manejo da inflamação da mucosa gástrica e pesquisa/erradicação da bactéria <em>Helicobacter pylori</em>.</li>
    <li><strong>Saúde Intestinal:</strong> Investigação de constipação crônica (prisão de ventre), diarreias, gases excessivos e Síndrome do Intestino Irritável.</li>
    <li><strong>Hepatologia Clínica:</strong> Diagnóstico e controle de gordura no fígado (esteatose hepática), hepatites virais e cálculos biliares.</li>
</ul>
<h4>Diagnóstico Integrado</h4>
<p>Nossos gastroenterologistas correlacionam a avaliação clínica com exames laboratoriais e ultrassonografia abdominal realizados diretamente na clínica, trazendo respostas rápidas para o seu tratamento.</p>',
        'benefits' => "Médicos gastroenterologistas experientes\nInvestigação completa de refluxo, gastrite e dores abdominais\nIntegração direta com exames laboratoriais e ultrassom\nOrientações dietéticas e prescrições terapêuticas precisas",
        'button_text' => 'Agendar Gastroenterologista',
        'button_url' => '',
        'photo' => 'service-gastroenterologia.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'pediatria',
        'name' => 'Pediatria',
        'slug' => 'pediatria',
        'price' => 'Consulte',
        'short_description' => '<p>Acompanhamento médico carinhoso e dedicado do desenvolvimento, crescimento e saúde de bebês, crianças e adolescentes. Puericultura e suporte seguro para a família.</p>',
        'description' => '<p>A <strong>Pediatria</strong> da <strong>Pronto Saúde</strong> é o porto seguro para os pais cuidarem do desenvolvimento saudável de seus filhos. Com profissionais que aliam sólida experiência clínica a uma abordagem lúdica e calorosa, transformamos a ida ao médico em uma experiência positiva para a criança.</p>
<h4>O Que É Feito na Consulta Pediátrica</h4>
<ul>
    <li><strong>Puericultura Mensal e Periódica:</strong> Acompanhamento minucioso de peso, comprimento/estatura, perímetro cefálico e curvas de crescimento da OMS.</li>
    <li><strong>Desenvolvimento Neuropsicomotor:</strong> Avaliação dos marcos do desenvolvimento infantil como sustentar a cabeça, sentar, engatinhar, andar e falar.</li>
    <li><strong>Alimentação e Nutrição:</strong> Orientação sobre amamentação exclusiva, introdução alimentar nutritiva e combate à seletividade alimentar.</li>
    <li><strong>Prevenção e Vacinas:</strong> Checagem criteriosa do cartão vacinal e orientação sobre o calendário da Sociedade Brasileira de Pediatria (SBP).</li>
    <li><strong>Doenças Comuns da Infância:</strong> Diagnóstico ágil de bronquiolite, crises de asma, infecções de ouvido (otites), viroses e alergias.</li>
</ul>',
        'benefits' => "Consultório lúdico, aconchegante e seguro para os pequenos\nPediatras carinhosos que escutam as dúvidas dos pais\nMonitoramento atento dos marcos de desenvolvimento\nPontualidade e agendamento rápido no WhatsApp",
        'button_text' => 'Agendar Pediatra',
        'button_url' => '',
        'photo' => 'service-pediatria.jpg',
        'banner' => 'department-1.png'
    ],
    [
        'key' => 'contraprova',
        'name' => 'Contraprova Toxicológica & Laboratorial',
        'slug' => 'contraprova',
        'price' => 'Consulte',
        'short_description' => '<p>Serviço especializado de custódia e reanálise pericial de contraprova para exames toxicológicos (CNH e CLT), concursos públicos e laudos laboratoriais com validade jurídica.</p>',
        'description' => '<p>A <strong>Contraprova Toxicológica &amp; Laboratorial</strong> da <strong>Pronto Saúde</strong> é o serviço técnico especializado destinado à reanálise pericial e confirmação diagnóstica de amostras biológicas previamente colhidas, resguardando os direitos do periciado perante empresas, órgãos públicos ou exigências do Código de Trânsito Brasileiro (CTB).</p>
<h4>Como Funciona a Cadeia de Custódia da Contraprova</h4>
<ul>
    <li><strong>Amostra Testemunha (Amostra B):</strong> No momento da coleta original, uma segunda porção da amostra é lacrada com selo de segurança holográfico e inviolável na presença do periciado.</li>
    <li><strong>Armazenamento Sob Condições Ideais:</strong> A contraprova permanece sob custódia controlada em temperatura e ambiente certificados durante todo o prazo legal de validade.</li>
    <li><strong>Abertura e Reanálise:</strong> Mediante requerimento formal do periciado, a contraprova é deslacrada conforme protocolo pericial, procedendo-se a nova análise laboratorial independente.</li>
</ul>
<h4>Garantia de Ampla Defesa e Transparência</h4>
<p>O laudo resultante da contraprova possui validade técnica e jurídica formal, sendo um instrumento essencial para contestações administrativas perante o RENACH/Detran, bancas de concursos públicos e rotinas da Justiça do Trabalho.</p>',
        'benefits' => "Segurança jurídica e garantia do direito de ampla defesa\nCadeia de custódia inviolável com lacres rastreados\nLaboratórios de referência acreditados internacionalmente\nAtendimento sigiloso, transparente e com assessoria técnica",
        'button_text' => 'Solicitar Contraprova',
        'button_url' => '',
        'photo' => 'service-contraprova.jpg',
        'banner' => 'department-1.png'
    ],
];

// 1. Mapeamento dos serviços existentes no banco por slug / id
$stmt = $pdo->query("SELECT id, slug, name FROM tbl_service");
$existing_by_slug = [];
while ($r = $stmt->fetch()) {
    $existing_by_slug[$r['slug']] = $r;
}

// Slugs equivalentes históricos
$slug_map = [
    'dna' => 'dna',
    'exames-laboratoriais' => 'exames-laboratoriais',
    'usg' => 'usg',
    'mapa' => 'mapa',
    'holter' => 'holter',
    'eletro-cardiograma' => 'eletro-cardiograma',
    'psicologia' => 'psicologia',
];

$active_ids = [];

foreach ($especialidades as $item) {
    $target_slug = $item['slug'];
    $found = null;

    if (isset($existing_by_slug[$target_slug])) {
        $found = $existing_by_slug[$target_slug];
    } else {
        // Tenta achar pelo slug alternativo
        foreach ($slug_map as $alt_key => $orig_slug) {
            if ($item['key'] === $alt_key && isset($existing_by_slug[$orig_slug])) {
                $found = $existing_by_slug[$orig_slug];
                break;
            }
        }
    }

    if ($found) {
        $id = $found['id'];
        $upd = $pdo->prepare("UPDATE tbl_service SET 
            name = ?,
            price = ?,
            description = ?,
            short_description = ?,
            button_text = ?,
            benefits = ?,
            photo = ?,
            banner = ?,
            status = 'Active'
            WHERE id = ?");
        $upd->execute([
            $item['name'],
            $item['price'],
            $item['description'],
            $item['short_description'],
            $item['button_text'],
            $item['benefits'],
            $item['photo'],
            $item['banner'],
            $id
        ]);
        $active_ids[] = $id;
        echo "[ATUALIZADO] ID {$id} -> {$item['name']} (slug: {$found['slug']})\n";
    } else {
        // Insere novo
        $ins = $pdo->prepare("INSERT INTO tbl_service 
            (name, price, slug, description, short_description, button_text, button_url, benefits, photo, banner, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
        $ins->execute([
            $item['name'],
            $item['price'],
            $item['slug'],
            $item['description'],
            $item['short_description'],
            $item['button_text'],
            $item['button_url'],
            $item['benefits'],
            $item['photo'],
            $item['banner']
        ]);
        $new_id = $pdo->lastInsertId();
        $active_ids[] = $new_id;
        echo "[INSERIDO NOVO] ID {$new_id} -> {$item['name']} (slug: {$item['slug']})\n";
    }
}

// 2. Colocar itens não listados em Inactive (como Biópsias, PAAF, Doppler, Endoscopia, Dentistas)
$in_clause = implode(',', array_map('intval', $active_ids));
$deact = $pdo->query("UPDATE tbl_service SET status = 'Inactive' WHERE id NOT IN ({$in_clause})");
echo "\n[INFO] Especialidades antigas/não solicitadas mantidas salvas no banco com status 'Inactive'.\n";

// 3. Atualizar SEO das especialidades em tbl_page para as rotas /service/<slug>
echo "\n=== CONFERÊNCIA FINAL DOS 16 ITENS ATIVOS ===\n";
$stmt = $pdo->query("SELECT id, name, slug, photo, status, price FROM tbl_service WHERE status = 'Active' ORDER BY id ASC");
$count = 0;
while ($r = $stmt->fetch()) {
    $count++;
    echo "{$count}. ID: {$r['id']} | {$r['name']} | Foto: {$r['photo']} | Preço: {$r['price']} | Link: /service/{$r['slug']}\n";
}

echo "\nTotal de especialidades ativas: {$count}/16\n";
