# Changelog

Todas as mudanças notáveis deste projeto serão documentadas neste arquivo.

O formato baseia-se em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/), e este projeto adere ao [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
