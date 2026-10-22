# Changelog

Todas as mudanças notáveis deste projeto serão documentadas neste arquivo.

O formato baseia-se em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/), e este projeto adere ao [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.9.0] - 2026-10-21

### Adicionado & Aprimorado
- **Reestruturação e Agrupamento da Sidebar em Menus Treeview Estruturados**:
  - Eliminação de itens excessivos soltos que transbordavam e cortavam o menu no rodapé da tela.
  - Agrupamento inteligente e lógico em 10 categorias temáticas principais com ícones dedicados:
    1. *Painel Principal* (`index.php`)
    2. *Administração & Configurações* (Configurações Gerais, Segurança WAF, Controle de Seções, Gestor de Páginas, Menus do Site, Usuários)
    3. *Portal de Notícias & Blog* (Artigos, Categorias do Blog, Moderação de Comentários)
    4. *Corpo Clínico & Atendimento* (Médicos Cadastrados, Especialidades Médicas, Horários & Atendimentos)
    5. *Serviços & Clínicas* (Serviços Médicos, Odontologia, Clínicas/Departamentos, Convênios Atendidos)
    6. *Planos & Preços* (Planos de Saúde, Itens & Benefícios)
    7. *Galeria & Mídias* (Galeria de Fotos, Categorias de Fotos, Galeria de Vídeos, Categorias de Vídeos, Upload de Arquivos)
    8. *Elementos do Portal* (Sliders/Destaques, Depoimentos, Parceiros, Vagas de Emprego, FAQ, Categorias de FAQ, Privacidade, Termos)
    9. *Banners Publicitários* (Banner Inicial, Banner Barra Lateral, Categorias de Anúncios)
    10. *Engajamento & Comunicação* (Redes Sociais, Inscritos na Newsletter)
    11. *Minha Conta & Sessão* (Editar Meus Dados, Encerrar Sessão)
- **Comportamento Treeview com Modo Acordeão Estrito (Accordion)**:
  - Implementação de lógica de acordeão tanto no PHP quanto no motor SPA JavaScript (`admin/js/pronto-spa.js`).
  - Ao expandir qualquer menu pai, todos os outros menus treeview de primeiro nível abertos se fecham automaticamente, prevenindo expansão excessiva e mantendo o menu sempre compacto e 100% visível.
  - Seta indicadora (`.nav-arrow`) com rotação suave de 90 graus via CSS (`transition: transform 0.25s`).
- **Marcação e Destaque Visual da Página Ativa**:
  - Destaque refinado para o link filho ativo com cor de destaque (`--ps-primary`), ícone contrastado e sombra elegante.
  - Indicação no menu pai correspondente com borda lateral esquerda e fundo translúcido para fácil localização hierárquica.
  - Mapeamento universal automático das rotas de adição (`-add.php`) e edição (`-edit.php`) para suas páginas-mãe.
  - Chamada `scrollIntoView({ behavior: 'smooth', block: 'nearest' })` garantindo que o item ativo seja mantido confortavelmente na área visível da barra lateral.
- **Scroll Nativo e Suave na Barra Lateral (`sidebar-wrapper`)**:
  - Configuração de `overflow-y: auto !important` com scrollbar estilizada e fina (`scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.25) transparent`).
  - `padding-bottom: 3.5rem` garantindo que o botão "Encerrar Sessão" e os itens inferiores nunca sejam cortados ou fiquem inacessíveis.

---

## [2.8.0] - 2026-10-21

### Adicionado & Aprimorado
- **DataTables Responsive Integrado no Padrão Oficial AdminLTE 3 / 4**:
  - Inclusão dos módulos oficiais `dataTables.responsive.min.js`, `responsive.bootstrap4.min.js` e folhas de estilo CSS correspondentes diretamente na base local do projeto.
  - Implementação do controle circular expansível com **ícone verde de sinal de mais (`+`)** na primeira coluna ao abrir em telas menores ou dispositivos móveis.
  - Alternância interativa para **sinal de menos (`-`)** com fundo vermelho ao abrir a linha filha detalhada (`child row`).
  - Renderização organizada de colunas ocultas em lista estruturada com títulos em destaque (`dtr-title`) e dados correspondentes (`dtr-data`) com quebra automática inteligente de linha (`word-break: break-word`).
  - Regras de `white-space: nowrap !important` aplicadas nas colunas da tabela e remoção de `overflow-x` impeditivo em containers `.table-responsive` para acionamento imediato e dinâmico dos breakpoints em tempo real via SPA.
  - Priorização semântica das colunas mantendo a coluna de identificação/controle e a coluna de ações sempre visíveis, colapsando as intermediárias no acordeão.
- **Padronização Visual Completa de Todas as Páginas e Tipografia do Sistema**:
  - Escala de fontes padronizada estritamente em **0.875rem (14px)** para todo o painel, tabelas, formulários e inputs, alinhada à especificação oficial do AdminLTE 4 e Bootstrap 5.3.
  - Cabeçalhos de página (`.content-header`) refinados com alinhamento flexível, espaçamento proporcional e títulos sem sobreposições.
  - Cards (`.card` e `.box`) padronizados com bordas sutis, cantos arredondados (`0.75rem`), sombra moderna e padding uniforme.
- **Padronização e Alinhamento Preciso de Todos os Botões e Formulários**:
  - Botões de cabeçalho ("Adicionar Novo", "Voltar") estilizados com altura consistente, tipografia seminegrito e ícones automáticos (`bi-plus-lg`, `bi-list-ul`).
  - Botões de ação em tabelas ("Editar", "Deletar", "Visualizar") alinhados lado a lado sem quebra feia (`white-space: nowrap`), tamanho `btn-xs` / `btn-sm` (28px de altura) e ícones padronizados (`bi-pencil-square`, `bi-trash3`).
  - Formulários horizontais legados modernizados com `display: flex` e alinhamento perfeito entre rótulos (`control-label`) e botões de submissão (`.btn-success`, `.btn-primary`).
- **Compatibilidade e Estabilidade Global de Scripts**:
  - Implementação de shim de compatibilidade de tooltips do Summernote para Bootstrap 5, eliminando exceções no console.
  - Aplicação de parâmetros de versão dinâmicos (`cache-busting`) nos links de CSS e scripts JS, garantindo atualização instantânea para os usuários.
  - Sanitização de entidades HTML e integridade de acentuação na exibição do perfil do usuário logado na barra de navegação.

---

## [2.7.0] - 2026-10-21

### Corrigido & Aprimorado
- **Correção Integral e Profunda de Acentuação Ortográfica no Sistema e Banco de Dados MariaDB**:
  - Varredura exaustiva e sanitização gramatical completa de todas as tabelas textuais de produção (`tbl_settings`, `tbl_service`, `tbl_privacidade`, `tbl_termos`, `tbl_department`, `tbl_designation`, etc.).
  - Eliminação definitiva de interrogações anômalas (`?`) residuais de migrações anteriores que substituíam vogais acentuadas e artigos/preposições (ex: conversão de sentenças quebradas para `... Pronto Saúde é reconhecida...`, `... nosso corpo clínico é criteriosamente selecionado...`, `... Conformidade à Privacidade...`, `... destinados à prevenção...`).
  - Restauração de termos médicos e institucionais sem acento para suas grafias oficiais na língua portuguesa: `Clínica Médica`, `Clínica Odontológica`, `Gardênia`, `Pronto Saúde`, `Serviço`, `Diagnóstico`, `Endereço`, `Número`, `Página`, `Usuário`, `Módulo`, `Configuração`, `Relatório`, `Histórico`, `Código`.
  - Correção pontual no cabeçalho de tabelas do painel administrativo (`admin/video.php: Código (iframe)`).
  - Preservação estrita de pontuação de interrogações legítimas em títulos e parágrafos de perguntas frequentes e termos médicos.
  - Alinhamento total com `SET NAMES utf8mb4` e integridade semântica em todo o portal público e painel administrativo.

---

## [2.6.0] - 2026-10-21

### Adicionado
- **Escudo de Segurança de Padrão Militar & Firewall WAF Integrado (`admin/inc/security_guard.php`)**:
  - Motor universal de inspeção profunda de pacotes (Deep Packet Inspection) ativo globalmente em todas as requisições (`$_GET`, `$_POST`, `$_COOKIE`, `$_SERVER['REQUEST_URI']`).
  - **Anti-SQL Injection (SQLi):** Neutralização imediata de ataques UNION SELECT, blind timing injections, dumps de tabelas, injeções booleanas e comandos DDL (`DROP`, `TRUNCATE`, `ALTER`).
  - **Anti-Cross Site Scripting (XSS):** Bloqueio estrito de tags `<script>`, esquemas `javascript:`, atributos de manipulação de eventos DOM (`onerror`, `onload`) e tentativas de roubo de cookies.
  - **Anti-Path Traversal (LFI/RFI):** Bloqueio de sequências de escape de diretórios (`../`, `..\`) e acesso não autorizado a arquivos confidenciais do servidor (`/etc/passwd`, `win.ini`).
  - **Anti-Remote Code Execution (RCE) / Web Shells:** Prevenção contra execução de funções de sistema (`passthru`, `shell_exec`, `system`) e injeções de código PHP em streams.
  - **Bloqueio de Scanners e Bad Bots:** Detecção de assinaturas de ferramentas automatizadas hostis (SQLMap, Nikto, WPScan, Acunetix, Nmap, etc.).
- **Defesa Ativa contra Força Bruta & Ataques de Dicionário em Logins**:
  - Mecanismo de bloqueio temporário (Lockout) por IP e por e-mail após exceder o limite de tentativas inválidas (`tbl_login_attempts`).
  - Regeneração atômica do ID da sessão (`session_regenerate_id(true)`) no login para neutralizar ataques de fixação de sessão.
  - Armadilha invisível **Honeypot Anti-Robô** em formulários de login e contato, desmascarando bots automatizados instantaneamente.
- **Headers HTTP de Máxima Segurança & Prevenção de Vazamento**:
  - Implementação de `Strict-Transport-Security (HSTS)`, `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN` (anti-clickjacking), `X-XSS-Protection` e `Referrer-Policy`.
  - Ocultação da assinatura e versão do PHP (`X-Powered-By`) para prevenção de fingerprinting e reconhecimento por atacantes.
- **Painel Central de Gestão de Segurança (`admin/seguranca.php` e `admin/seguranca-ajax.php`)**:
  - 4 Cards de KPIs em tempo real: Status do WAF, Ameaças Bloqueadas, Invasões Neutralizadas e Força Bruta Contida.
  - Painel dividido em 5 abas interativas com switches assíncronos via AJAX (WAF, Força Bruta, Headers HTTP, Gestão de IPs e Auditoria).
  - Tabela dinâmica de IPs em quarentena com botão de desbloqueio instantâneo via SweetAlert2.
  - Log de auditoria em tempo real (`tbl_security_logs`) com classificação de severidade (`CRÍTICA`, `ALTA`, `MÉDIA`, `BAIXA`), amostras de payload e ação tomada.

---

## [2.5.0] - 2026-10-21

### Adicionado
- **Sistema Universal de Máscaras de Entrada (`admin/js/pronto-masks.js`)**:
  - Máscaras automáticas e flexíveis para Telefone fixo e Celular/WhatsApp (`(00) 00000-0000` ou `(00) 0000-0000`), CEP (`00000-000`), CPF (`000.000.000-00`), CNPJ (`00.000.000/0000-00`), Documento Dinâmico, Data (`00/00/0000`), Hora (`00:00`) e Moeda (`R$ 0,00`).
  - Aplicação universal automática por classes CSS (`.mask-phone`, `.mask-cep`, `.mask-cpf`, `.mask-cnpj`, `.mask-money`, etc.) e por detecção de atributos de nome/id em todos os formulários.
- **Preenchimento Automático via CEP com Auto Focus no Número**:
  - Consulta assíncrona automática à API pública ViaCEP durante a digitação de 8 dígitos de CEP sem necessidade de clicar em botões.
  - Preenchimento inteligente de logradouro, bairro, cidade e estado em formulários com campos separados ou consolidados.
  - Foco imediato automático com seleção no campo de Número (`#numero_endereco`) assim que a localização do endereço é concluída.
  - Sincronização em tempo real do número digitado no campo consolidado de endereço.
- **Cabeçalho Fixo (Sticky Header)**:
  - Fixação da barra de navegação superior (`.app-header.sticky-top`) com efeito translúcido `backdrop-filter: blur(8px)`, permanecendo sempre visível e acessível durante a rolagem de páginas extensas.
- **Sidebar com Menu Treeview Reativo e Marcação de Página Ativa**:
  - Organização do menu lateral com marcação de página ativa inclusive para subpáginas de cadastro e edição (`-add.php` e `-edit.php`), garantindo que o módulo pai permaneça com a classe `.active` e o submenu com a classe `.menu-open`.
  - Suporte a abertura/fechamento manual de seções treeview com transição suave.

### Alterado
- **Padronização Visual e Tipográfica Idêntica ao AdminLTE 4 Original**:
  - Eliminação de regras legadas herdadas do AdminLTE 2 (`admin/style.css` que impunham fonte `verdana 12px`).
  - Padronização em fonte `Source Sans 3` (300, 400, 600, 700), tamanhos proporcionais de `1rem` (15px/16px) e espaçamentos padronizados do Bootstrap 5.3.
- **Correção Geral de Acentuação e Codificação do Sistema**:
  - Identificação e reparo de 257 registros com acentuação corrompida (`?` literal) em tabelas do banco de dados (`tbl_settings`, `tbl_department`, `tbl_service`, `tbl_trabalhe`, `tbl_doctor`, `tbl_pricing_plan`, `tbl_slider`, etc.).
  - Padronização do portal público e administrativo sem caracteres corrompidos, exibindo acentuação ortográfica brasileira impecável.

---

## [2.4.0] - 2026-10-21

### Adicionado
- **Geração Automática de Slugs em Tempo Real (`admin/js/pronto-slug.js`)**:
  - Motor de slugificação instantânea ativado em todos os formulários de cadastro e edição (Departamentos/Clínicas, Páginas, Notícias, Categorias, Serviços e Corpo Clínico).
  - Ao digitar ou alterar títulos e nomes, o campo de slug é preenchido sozinho em tempo real com caracteres minúsculos, sem acentuação e separados por traço simples.
  - Bloqueio completo da digitação manual através do atributo `readonly`, prevenindo inserção de caracteres inválidos, acentos indevidos, espaços ou quebra de URLs amigáveis no sistema.
  - Estilização visual dos campos de slug bloqueados com borda tracejada e aviso explicativo em português.
  - Função nativa `gerar_slug()` adicionada a `admin/functions.php` para higienização e sanitização segura no backend em PHP 8.4+.

---

## [2.3.0] - 2026-10-21

### Adicionado
- **Painel Desacoplado via Motor SPA (Single Page Application)**:
  - Arquitetura desacoplada onde cabeçalho, sidebar, navbar e rodapé permanecem fixos enquanto o conteúdo é atualizado via AJAX sem refresh de página e sem piscar a tela (`admin/js/pronto-spa.js`).
  - Barra de progresso animada no topo durante requisições assíncronas.
  - Interceptação automática de cliques em links internos e envio de formulários via `FormData` assíncrono.
  - Integração com `history.pushState` e `popstate` para navegação fluida pelos botões voltar/avançar do navegador.
  - Re-inicialização automática pós-AJAX de Tooltips do Bootstrap 5, DataTables em português, Select2, Datepicker e ApexCharts.
- **Upload de Imagens Drag & Drop com Preview em Tempo Real**:
  - Componente universal `admin/js/pronto-uploader.js` que transforma todos os inputs de imagens em áreas modernas de arrastar e soltar (drag & drop).
  - Preview imediato no navegador utilizando `FileReader`, com exibição do tamanho em KB e nome do arquivo selecionado.
  - Botão de remoção rápida e sincronização total com o envio de formulários via AJAX.
- **Editor de Texto Online Summernote Global**:
  - Editor rico **Summernote** padronizado e ativo globalmente em todos os textareas de conteúdo do painel administrativo.
  - Sincronização automática do HTML gerado pelo Summernote no `FormData` do formulário antes do disparo da requisição AJAX.
- **Controle Individual de Seções da Home via AJAX**:
  - Nova tela `admin/secoes.php` e endpoint `admin/secoes-ajax.php` com switches visuais para ativar/desativar cada seção da página inicial de forma individual (Serviços, Clínicas, Médicos, Preços, Depoimentos, Notícias, Parceiros).
  - Atualização instantânea no banco de dados via AJAX com feedback flutuante Toastr, sem recarregar a página.
- **Página de Manutenção Personalizada (`manutencao.php`)**:
  - Tela responsiva moderna com resposta HTTP 503, informando manutenções programadas com horário de Brasília e canais de emergência (WhatsApp e telefone).
  - Ativação direta no painel administrativo via switch, bloqueando visitantes públicos no `header.php` enquanto permite acesso normal aos administradores autenticados.
- **Páginas de Erro Personalizadas (`404.php`, `500.php`, `403.php`)**:
  - Interfaces amigáveis com design hospitalar, ilustrações SVG, canais de suporte e retorno rápido à página inicial.
  - Configuração no `.htaccess` com diretivas `ErrorDocument 403`, `ErrorDocument 404` e `ErrorDocument 500`.

### Alterado
- **Sincronização de Handlers do Apache**:
  - Arquivo `.htaccess` local padronizado com o ambiente de produção do cPanel para `ea-php84` e `php8_module`.
  - Eliminação de qualquer chamada direta a `alert()` nativo em novos scripts, assegurando o uso exclusivo de SweetAlert2 e Toastr.

---

## [2.2.0] - 2026-10-21

### Adicionado
- **Migração Completa para o AdminLTE 4**:
  - Painel administrativo totalmente atualizado para a versão mais recente do **AdminLTE 4**, baseada em **Bootstrap 5.3**, **Bootstrap Icons**, **FontAwesome 6** e **OverlayScrollbars**.
  - Novo cabeçalho e rodapé estruturados segundo as diretrizes de design do AdminLTE 4 (`.app-wrapper`, `.app-header`, `.app-sidebar`, `.app-main`, `.app-footer`).
  - Suporte nativo a **Modo Escuro (Dark Mode)**, **Modo Claro (Light Mode)** e **Automático (Auto)** com alternador dinâmico na navbar e persistência no `localStorage`.
- **Dashboard com KPIs Modernos e Interativos (`admin/index.php`)**:
  - Nova grade de cartões de KPI com gradientes modernos (Teal, Emerald, Blue, Amber, Cyan, Indigo, Purple, Rose), ícones translúcidos e contadores de alto impacto visual.
  - Integração de gráficos interativos com **ApexCharts**:
    - Gráfico de barras moderno: Distribuição de cadastros em todos os módulos assistenciais e editoriais.
    - Gráfico Donut de saúde: Proporção dinâmica dos recursos médicos, odontológicos, clínicos e vagas de emprego.
    - Sincronização automática dos gráficos com o tema (Dark / Light) ativo.
  - Painel de Ações Rápidas (Quick Actions) para criação de médicos, notícias, vagas, clínicas e configurações.
  - Cards de suporte técnico atualizados com acionamento direto e feedback via Toastr.
- **Camada Universal de Compatibilidade CSS (`admin/css/adminlte4-compat.css`)**:
  - Mapeamento automático dos componentes clássicos (`.box`, `.box-header`, `.box-body`, `.callout`, etc.) para os novos cartões e alertas do Bootstrap 5, assegurando que todas as mais de 60 telas do painel permaneçam belas e 100% funcionais.
- **Tela de Login Modernizada (`admin/login.php`)**:
  - Interface com design em glassmorphism, floating labels, validação integrada e feedback de erro humanizado via SweetAlert2.

### Alterado
- **Padronização Global de Notificações e Confirmações**:
  - Configuração refinada do **Toastr** com barra de progresso, posição superior direita e animação suave em todo o sistema.
  - Confirmações de exclusão e logout rigorosamente padronizadas com diálogos interativos do **SweetAlert2** em português brasileiro.
  - Interceptação global de `window.alert()` nativo do navegador, convertendo para modais do SweetAlert2.

---

## [2.1.0] - 2026-10-21

### Adicionado
- **Suporte Oficial e Otimizado para PHP 8.4**: Ambiente remoto atualizado via cPanel para `ea-php84` e código totalmente adaptado para a versão mais recente do PHP.
- **Sistema Global de Notificações e Confirmações**:
  - Integração do **SweetAlert2** de forma global em todo o sistema (frontend e painel administrativo).
  - Integração do **Toastr** para notificações flutuantes dinâmicas (sucesso, aviso, erro e informação).
  - Scripts globais `assets/js/pronto-global.js` e `admin/js/pronto-global.js` que interceptam qualquer chamada nativa a `window.alert()` e redirecionam para SweetAlert2 estilizado.
  - Confirmações de exclusão de registros no painel administrativo convertidas para diálogos interativos do SweetAlert2 em português.
- **Função Auxiliar de Data `formata_mes_ano()`**: Implementada em `admin/functions.php` para substituição segura e formatada de datas em português, eliminando a dependência do `strftime()`.
- **Configuração de Chave SSH Automatizada**: Configuração de par de chaves SSH Ed25519 autorizadas no servidor remoto para operações seguras e automatizadas.
- **Arquivo `.gitignore`**: Padronização para ignorar logs temporários (`error_log`) e arquivos locais de ambiente gerados pelo cPanel.

### Alterado
- **Padronização Global de Fuso Horário**:
  - Fuso horário padronizado rigorosamente para `America/Sao_Paulo` (GMT-3) em `admin/config.php` e replicado na sessão do MySQL (`SET time_zone = '-03:00'`).
- **Remoção Total de UTF-8 com BOM**: Varredura e conversão de todos os arquivos do projeto (PHP, JavaScript e CSS) para UTF-8 sem BOM (evitando headers quebrados, espaços fantasmas e erros de sessão).
- **Otimização de Performance e Consultas MySQL**:
  - `header.php`: Substituído loop N+1 de limpeza de inscritos inativos por consulta atômica em lote único: `DELETE FROM tbl_subscriber WHERE subs_active=0 AND subs_date_time < NOW() - INTERVAL 1 DAY`.
  - `news.php`: Contador de visualizações otimizado para incremento atômico (`total_view = total_view + 1`) evitando leituras desnecessárias e race conditions sob alto tráfego.
  - `page.php`: Remoção de consultas duplicadas de categorias e FAQs.
  - `admin/config.php`: Desativação de emulação de prepared statements (`PDO::ATTR_EMULATE_PREPARES => false`) para ganho de performance e máxima segurança nativa no MySQL.

### Corrigido
- **Compatibilidade PHP 8.4 e Eliminação de Warnings**:
  - `privacidade.php` e `rsocial.php`: Removida função descontinuada `strftime()`, substituída pela função `formata_mes_ano()`.
  - `admin/login.php`: Correção de aviso de chave indefinida para `$row['empresa']` e correção da ordem de carregamento de scripts para evitar falha no jQuery.
  - `admin/header.php`: Correção de avisos ao acessar índices inexistentes de configurações e inclusão dos estilos Toastr.
  - `admin/footer.php`: Correção do carregamento do Summernote antes do jQuery e conversão de mensagens flash `.callout` em notificações Toastr.
  - `admin/doctor.php`, `admin/pricing-item.php`, `admin/termos.php`, `admin/privacidade.php` e `admin/trabalhe-conosco.php`: Substituição de verificações diretas `$_SESSION['msg']` / `$_SESSION['erro']` por `!empty()`, evitando alertas no log de erros.
  - `admin/extra-add.php`: Tratamento de valores pré-selecionados com `isset($_POST[...])` e inicialização de variáveis de mensagem.
  - `admin/extra-edit.php`: Correção de query antes da checagem do ID, parametrização segura contra SQL Injection com prepared statement e eliminação de loop duplicado da semana.
  - `assets/PHPmailer/email.php`: Inclusão de proteção e fallback na conexão PDO para requisições diretas de robôs.

---

## [2.0.0] - 2026-10-21

### Adicionado
- Metatag charset e init command do PDO no `admin/config.php` para garantir UTF-8 (utf8mb4).
- `htmlspecialchars()` na saída da busca em `search.php` para mitigar ataques XSS.
- Arquivo `CHANGELOG.md` para documentar mudanças.

### Alterado
- Otimização das consultas na Home (`index.php` e `header.php`) removendo chamadas duplicadas às tabelas `tbl_settings`, `tbl_social` e `tbl_department`.
- Otimização global de consultas com `COUNT(*)` no painel `admin/index.php` substituindo antigos `SELECT *` para acelerar o carregamento do Dashboard.
- README reescrito para incluir um guia técnico descritivo, tecnologias e instruções de configuração detalhadas.
- Padronização das strings em inglês no `admin/login.php` com tradução para português do Brasil (PT-BR).

### Corrigido
- Typo na variável `$exception` no arquivo `admin/config.php` que ocultava logs críticos de conexão de banco.
- Variável indefinida `$faqfavicon` no `header.php` corrigida para a `$favicon` real puxada do banco.
- Bug do `$row['name']` acionado indevidamente fora do loop foreach (Seções: Serviços, Fidelidade, Trabalhe Conosco).
- Falha de declaração da variável `$salario` em `trabalhe-conosco.php` (corrigido checando `$row['salario'] == ''`).
- Suprimidos os Warnings com operador null coalescing (`??`) no `search.php`.
- Erros de digitação e acentuação no Dashboard ("Video" -> "Vídeo", "Clinicas" -> "Clínicas") e no Menu de Administração ("Sessão do Médico" -> "Seção do Médico").
- Dupla injeção da biblioteca jQuery no `footer.php` e do Bootstrap em `admin/login.php`.
- CSS quebrado no controle de barra de rolagem (webkit-scrollbar-thumb) do `header.php`.
