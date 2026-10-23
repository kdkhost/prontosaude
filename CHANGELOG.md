# Changelog

Todas as mudanças notáveis deste projeto serão documentadas neste arquivo.

O formato baseia-se em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/), e este projeto adere ao [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.20.0] - 2026-10-23

### Padronizado & Aprimorado
- **Arquitetura Multipage & Respeito Rigoroso ao Banco de Dados**:
  - **Remoção do Formulário de Contato da Home (`index.php`):** O formulário de contato foi removido da primeira página, preservando a arquitetura multipage tradicional onde o contato é realizado exclusivamente em sua página dedicada (`/page/contato` ou `page.php?slug=contato`), com submissão assíncrona via AJAX e diálogos interativos do **SweetAlert2**.
  - **Respeito Estrito às Configurações do Painel Admin:** Cada seção da Home agora é estritamente condicionada aos switches gravados no banco de dados em `tbl_settings` e `tbl_page`:
    - `home_status_pricing` (Planos & Preços)
    - `home_status_service` (Nossos Serviços)
    - `home_status_doctor` (Corpo Clínico - respeitado como inativo conforme gravação no banco)
    - `home_status_testimonial` (Depoimentos de Pacientes)
    - `home_status_news` (Notícias e Informações com limite configurável)
    - `home_status_partner` (Parceiros Institucionais - respeitado conforme banco)
    - Títulos (`$home_title_*`) e subtítulos (`$home_subtitle_*`) renderizados dinamicamente a partir dos campos do banco de dados.
  - **Links Multipage Reais no Hero e Faixa de Agendamento:** Direcionamento direto para as páginas multipage correspondentes (`servicos.php`, `fidelidade.php`, `odontologia.php`, `page/sobre` e `page/contato`).
  - **Padronização das Demais Páginas Multipage:** 
    - `page.php`: layout de contato com formulário integrado via AJAX e SweetAlert2, cards de canais diretos e mapa responsivo; layouts de FAQ com acordeões modernos, galeria, blog e corpo clínico harmonizados.
    - `servicos.php` & `service.php`: cards modernos com benefícios, preços e ação direta.
    - `fidelidade.php`: planos de fidelidade com detalhes, preços e adesão.
    - `odontologia.php`: procedimentos com fotos, valores e modais detalhados em Teal Pronto Saúde.
    - `convenio.php` & `trabalhe-conosco.php`: visual padronizado, sem classes vermelhas ou elementos destoantes.
  - **Identidade Visual Oficial Pronto Saúde:** Paleta baseada em Teal (`#0a6b74`), Dark Teal (`#07474d`), Verde Esmeralda (`#10b981`) e fundo suave (`#f8fafc`).

## [2.19.0] - 2026-10-22

### Adicionado & Aprimorado
- **Botão Flutuante do WhatsApp & Pop-up Multicanais (Estilo Foco 7 Oficial)**:
  - **Fidelidade Integral ao Site de Referência:** Implementação da mesma mecânica de atendimento multicanais da plataforma Foco 7, harmonizada com a identidade visual médica da Pronto Saúde (`#0a6b74`).
  - **Botão Flutuante com Pulso e Status Online:** Botão pulsante com ondas suaves (`pulseRingTeal`), badge verde de status em tempo real (`.whatsapp-badge-pulse`) e tooltip contextual.
  - **Box Pop-up Multicanais (`#popupSupportWhatsapp`):** Janela suspensa com 5 canais categorizados de atendimento humanizado:
    - *Consultas Médicas & Especialidades*
    - *Exames Laboratoriais & Imagem*
    - *Centro Odontológico*
    - *Cartão Fidelidade & Benefícios*
    - *Recepção & Informações Gerais*
    Todos com textos pré-preenchidos contextualizados e disparo instantâneo para o WhatsApp oficial.
  - **Controle de Fechamento Amigável:** Abertura via botão flutuante e fechamento facilitado por botão `X`, clique externo (`outside click`) ou tecla `Escape`.
  - **Botão Voltar ao Topo (`#btnVoltarAoTopo`):** Exibição dinâmica com scroll > 300px e rolagem suave ao início da página.

## [2.18.0] - 2026-10-22

### Padronizado & Aprimorado
- **Padronização Extrema de Menus e Submenus (Desktop & Mobile)**:
  - **Dropdowns Desktop Sofisticados e Fluidos:** Menus pais com filhos (`Quem Somos`, `Especialidades`, `Outros`) agora possuem dropdowns suspensos com micro-animação suave (fade-in + elevação), chevrons que rotacionam 180°, ponte de hover invisível e marcadores visuais elegantes.
  - **Sincronização Inteligente de Rotas Ativas:** Destaque ativo simultâneo no item pai e no submenu específico quando o paciente navega por páginas internas como FAQ, Galeria, Médicos, Serviços ou Contato.
  - **Acordeão Mobile com Usabilidade Total:** Suporte a toque tanto no chevron quanto no texto de menus agregadores (`#`), abertura pré-expandida automática na página em que o usuário está e fechamento suave ao navegar para links finais.
  - **Normalização e Blindagem de URLs:** Resolução robusta de links relativos (`../`), páginas institucionais, categorias e URLs absolutas sem risco de quebra em subpastas.

## [2.17.0] - 2026-10-22

### Adicionado & Reformulado
- **Reformulação Visual e Estrutural do Portal Público no Estilo Foco7 com Identidade Pronto Saúde**:
  - **Eliminação da Barra Duplicada & Topbar Unificada:** Remoção da barra legada antiga duplicada, mantendo uma única Topbar ultra clean com horários, telefone, e-mail, WhatsApp oficial, redes sociais e botão da Área do Paciente/Painel perfeitamente alinhados acima da Navbar branca sticky.
  - **Preservação Integral das Imagens e Sliders no Modelo Novo:** Reintegração harmônica de 100% das imagens do banco de dados no design moderno:
    - **Slider / Carrossel Principal (`tbl_slider`):** Carrossel dinâmico com fotos reais dos banners, overlay hospitalar suave e botão de chamada, combinado com a faixa flutuante de **Agendamento em 1 Minuto** (`.ps-quick-strip-section`).
    - **Departamentos & Clínicas (`tbl_department`):** Abas com fotos reais de cada departamento e agendamento direto.
    - **Serviços (`tbl_service`):** Cards modernos com as fotos reais dos procedimentos, badges flutuantes de preço e lista de benefícios.
    - **Sobre Nós:** Apresentação institucional enriquecida com a imagem real das instalações e números de impacto.
    - **Planos e Cartão Fidelidade (`tbl_pricing_plan`):** Cards com as fotos/mockups reais dos cartões e selo mais popular.
    - **Corpo Clínico (`tbl_doctor`), Depoimentos (`tbl_testimonial`), Notícias (`tbl_news`) e Convênios (`tbl_partner`):** Todos com suas respectivas fotos e logos originais integrados em carrosséis responsivos.
  - **Páginas Internas Padronizadas:** Atualização de `servicos.php` e `fidelidade.php` para utilizarem os novos grids modernos.
- **Blindagem e Saneamento Definitivo contra UTF-8 com BOM:**
  - Criação de `.editorconfig`, `.gitattributes`, regras estritas em `.agents/rules/utf8_no_bom.md`, `AGENTS.md` e ferramenta CLI `tools/check_no_bom.php`.
  - Validação de 0 arquivos com BOM em todo o repositório local e remoto.

## [2.16.0] - 2026-10-22

### Padronizado & Aprimorado
- **Universalização do Editor Summernote BS5 em Todos os Formulários do Painel**:
  - **Summernote por Padrão em Todos os Textareas:** Todos os campos `<textarea>` do painel administrativo agora são automaticamente convertidos no editor rico Summernote oficial (Bootstrap 5 v0.8.20), dispensando classes ou seletores manuais específicos.
  - **Exceções Técnicas Automatizadas:** Campos de código de incorporação (`iframe`), listas de IP (`.font-monospace`), chaves e campos com classes/atributos de escape (`.no-summernote`, `.no-editor`, `data-no-summernote`) continuam mantendo a formatação bruta sem rich text.
  - **Localização em Português do Brasil:** Integração do pacote oficial `summernote-pt-BR.min.js` para menus, botões e dicas do editor totalmente traduzidos.
  - **Sincronização Bidirecional Pré-Envio:** Atualização instantânea do valor HTML gerado para o textarea original antes de qualquer submissão (tanto em formulários tradicionais quanto requisições AJAX/SPA).
  - **Design System & Dark Mode:** Adaptação completa de bordas arredondadas, foco com anel translúcido e paleta de alto contraste para o tema escuro.

## [2.15.1] - 2026-10-22

### Reformulado & Otimizado
- **Reformulação Completa da Página de Configurações (`admin/settings.php`)**:
  - **Barra de Abas com Scroll Horizontal Suave (`.ps-tabs-scrollable`)**:
    - Navegação ergonômica e responsiva com 10 abas categorizadas por ícones (Logo, Favicon, Conteúdo Geral, Contato & E-mail, Notícias, SEO, Seções da Home, Cores, Preloader, reCAPTCHA).
    - Suporte bidirecional e imediato a hash na URL (`#tab_1` a `#tab_10`) e persistência automática de aba ativa (`active_tab`) em todos os formulários.
  - **Identidade Visual & Mídias**:
    - Visualização da logo atual em moldura transparente e upload Drag & Drop moderno com suporte a PNG, WEBP e SVG.
    - Mockup de aba do navegador em tempo real para prévia do Favicon.
  - **Localização Inteligente & Busca por CEP**:
    - Integração assíncrona automática com a API ViaCEP com foco automático imediato no campo de Número (`#numero_endereco`).
    - Campo de código de incorporação do Google Maps com prévia interativa do mapa.
  - **Simulador SERP do Google & SEO em Tempo Real**:
    - Prévia visual dinâmica de como a página inicial aparece nos resultados de busca do Google, com contadores de caracteres para título e descrição.
  - **Controle de Seções da Home com Switches Visuais**:
    - Cards modernos para Serviços, Departamentos, Médicos, Preços, Depoimentos, Notícias e Parceiros.
  - **Seletor de Cores com Paletas Recomendadas**:
    - Integração de clique rápido para Teal Hospitalar (`#0a6b74`), Verde Saúde (`#10b981`), Azul Clínico (`#0284c7`), Ciano Moderno (`#06b6d4`) e Púrpura Médica (`#7c3aed`) com preview ao vivo.
  - **Segurança & reCAPTCHA**:
    - Card refinado com toggle de ativação e botão de alternar visibilidade da chave secreta.

---

## [2.15.0] - 2026-10-22

### Reformulado & Aprimorado
- **Coerência e Consistência Global do Tema Dark / Light**:
  - **Suíte Completa para Dark Mode (`admin/css/adminlte4-compat.css`)**:
    - Equalização cromática de alto contraste para `[data-bs-theme="dark"]` com `--bs-body-bg: #0b1320`, cards `.card`, `.box` e `.card-outline` (`#131f33`), bordas (`#24354f`), tabelas thead/tbody (`#19273f`), inputs `.form-control` e `.form-select` (`#152238`), abas `.nav-tabs` e `.nav-link.active` com verde esmeralda (`#10b981`), modais, dropdowns e paginação DataTables.
    - Suporte nativo completo ao Dark Mode para plugins de terceiros: **Select2** (container, dropdown e search field), **Datepicker** (células, dias e meses), **Summernote** (toolbar, editable area e statusbar), **SweetAlert2** e **Toastr**.
    - Eliminação de todos os fundos fixos (`background: #fff` / `white`) no painel administrativo (`admin/convenio.php`, `admin/extra-add.php`), substituindo por variáveis dinâmicas de tema `var(--bs-card-bg)`.
- **Reconstrução Completa das Páginas Internas do Site Público**:
  - `department.php`: Reconstruída com layout hospitalar de padrão internacional, badges de localização, telefone, whatsapp e e-mail, horários de atendimento com badges de status, FAQ dinâmico em acordeão e botão direto de agendamento.
  - `doctor.php`: Perfil médico ultra moderno com card do especialista, badge de especialidade com ícone, grau acadêmico/registro, botões de redes sociais e abas modernas (Apresentação Profissional, Contato & Localização, Horários e Especialidades Atendidas) e carrossel da equipe médica.
  - `page.php`: Reconstrução dos layouts de Conteúdo Completo (`Full Width`), Formulário de Contato (`Contact Us`), FAQ estruturado por categorias, Galeria de Fotos (com legendas e zoom suave) e Galeria de Vídeos (com player responsivo e títulos dos vídeos).
  - `news.php`: Artigo de notícia com card elevado, moldura de imagem, badges de categoria e data, tipografia aprimorada, compartilhamento social e seção de comentários moderna.
  - `privacidade.php` & `rsocial.php`: Envolvidas em card elevado com sombra suave e tipografia legível.
  - `sidebar.php`: Widgets modernizados com ícones temáticos, input de pesquisa arredondado e links com hover elegante.

---

## [2.14.0] - 2026-10-22

### Reformulado & Modernizado
- **Modernização e Refatoração Completa do Frontend Público**:
  - **Design System do Portal (`assets/css/pronto-modern-frontend.css`)**:
    - Tipografia de alto padrão com **Plus Jakarta Sans** (Google Fonts) carregada globalmente.
    - Paleta de cores oficial refinada: Teal (`#0a6b74`), Verde Esmeralda (`#10b981`), Cyan Glow e Dark Slate (`#0f172a`), com design tokens CSS, sombras suaves e bordas arredondadas.
    - Cards modernos com elevação 3D ao passar o mouse (`translateY(-4px)`), badges com blur translúcido e micro-animações fluidas.
    - Estilização completa e refinada para perfis médicos (`.doctor-detail`, `.doctor-single`), abas de especialidade (`.doctor-detail-tab .nav-tabs`), barra lateral (`.sidebar .widget`), paginação moderna (`.pagination`), accordions (`.panel-group`) e banner de cookies consent (`.cc-window`).
  - **Menu Deslizante Offcanvas Mobile (Drawer Lateral)**:
    - Menu deslizante suave da esquerda para a direita acelerado por hardware (`translate3d`), com animação elástica, suporte a gestos swipe, backdrop com desfoque de vidro e canais de atendimento rápido.
  - **Botão Flutuante Oficial do WhatsApp**:
    - Substituição de links e tags órfãs por botão fixo e responsivo (`.ps-floating-whatsapp`) com animação de pulso contínua, tooltip "Agende sua Consulta" e número oficial de atendimento.
  - **Interatividade & AJAX Global (`assets/js/pronto-global.js`)**:
    - Interceptador global de `window.alert` e `window.confirm` com **SweetAlert2**.
    - Notificações flutuantes assíncronas com **Toastr**.
    - Submissão via AJAX com `fetch()` e `FormData` nos formulários de contato público e páginas institucionais com bloqueio de clique duplo e spinner de carregamento.
    - Máscaras automáticas e dinâmicas para campos de telefone/WhatsApp.
  - **Páginas Institucionais Refatoradas**:
    - `index.php`: Seção de serviços, depoimentos com estrelas e imagem de fundo local de alta qualidade.
    - `trabalhe-conosco.php`: Vagas de emprego ativas com modal hospitalar de detalhes, badges de requisitos e candidatura direta via WhatsApp ou e-mail.
    - `convenio.php`: Apresentação moderna com cards responsivos e contato direto para parcerias.
    - `odontologia.php`: Banner institucional, cards de procedimentos e modal moderno com foto em moldura suave, detalhes e agendamento via WhatsApp.
    - `fidelidade.php`: Banner padrão moderno, cards de planos com destaque de popularidade e badges.
    - `service.php`: Detalhe do serviço com imagem em moldura, botão de agendamento e barra lateral interativa destacando o serviço ativo.
    - `doctor.php`: Perfil do médico com card lateral, badges de formação, links de redes sociais e agendamento direto com o profissional.
    - `news.php`, `category.php`, `search.php`: Banners padronizados com imagem e overlay gradiente, eliminando cabeçalhos vazios (`background: none`), e campo de busca modernizado.
    - `page.php`: Fallback elegante para banners, suporte a resposta JSON em AJAX no formulário de contato e remoção de scripts jQuery duplicados.
    - `cookie.php`: Harmonização de cores do consentimento de cookies com o tema do Pronto Saúde.

---

## [2.13.1] - 2026-10-22

### Corrigido & Aprimorado
- **Correção Definitiva e Universal do Sistema de Abas (Tabs) em Todo o Painel Administrativo**:
  - **Eliminação de Conflito Assíncrono com Bootstrap 5**: Substituídas as tentativas assíncronas do Bootstrap `Tab.show()` (que entravam em corrida de transição `.fade` deixando os painéis com `opacity: 0` ou `display: none`) por um motor síncrono, imediato e universal (`window.switchProntoTab`).
  - **Interceptador Prioritário de Fase de Captura (`useCapture: true`)**: Garante que o clique em qualquer aba (`button` ou `a`, com `data-bs-toggle="pill"`, `data-bs-toggle="tab"`, `data-toggle="tab"` ou classes `.nav-tabs .nav-link`) seja processado antes de qualquer outro listener concorrente, cancelando comportamentos indesejados.
  - **Regras CSS com Força Máxima (`admin/css/adminlte4-compat.css`)**: Implementadas regras com `!important` para `.tab-pane.active` (`display: block !important; opacity: 1 !important; visibility: visible !important; height: auto !important`) e `.tab-pane:not(.active)` (`display: none !important; opacity: 0 !important; visibility: hidden !important; height: 0 !important`), assegurando visibilidade instantânea sem depender de animações instáveis.
  - **Sincronização Bidirecional**: Sincronização automática da aba ativa a partir do hash da URL (`#tab_1` a `#tab_10`) e do estado inicial gerado pelo PHP (`$active_tab`) tanto no carregamento inicial (`DOMContentLoaded` e `window.load`) quanto nas navegações SPA (`reinitAllComponents`).
  - **Camada de Redundância Global (`admin/js/pronto-global.js`)**: Adicionado listener global de fail-safe em `pronto-global.js` para garantir funcionamento imediato mesmo sob condições adversas de rede ou carregamento assíncrono.
  - **Recálculo Automático de Tabelas e Editores**: Ao trocar de aba, colunas responsivas do DataTables e dimensões de componentes internos são automaticamente recalculadas.

## [2.13.0] - 2026-10-22

### Reformulado & Corrigido
- **Reformulação Integral de Todas as Páginas com Abas (Tabs) do Painel Administrativo**:
  - **Configurações Gerais do Sistema (`admin/settings.php`)**:
    - Reestruturação das 10 abas (Logo, Favicon, Conteúdo Geral, E-mail, Notícias, SEO/Meta, Seções da Home, Cores, Preloader, reCAPTCHA) com a arquitetura oficial `.card.card-primary.card-outline.card-outline-tabs` e botões semânticos do Bootstrap 5.3.
    - Persistência e restauração automática da aba ativa (`$active_tab = 'tab_1'` até `'tab_10'`) em cada formulário submetido.
    - Modernização do acordeão de seções da Home com `.accordion.accordion-flush` nativo do Bootstrap 5 e switches dinâmicos.
    - Unificação do container `.app-content` eliminando wrappers duplicados.
  - **Adição de Menus (`admin/menu-add.php`)**:
    - Migração das 3 abas (Categoria como Menu, Página como Menu, Link Personalizado) para `.card-outline-tabs` com persistência de `$active_tab`.
    - Tradução completa de textos, rótulos e mensagens de retorno para o português brasileiro.
  - **Banners da Home (`admin/advertisement-home.php`)**:
    - Migração das 2 abas (No Cabeçalho e Abaixo das Notícias) para abas oficiais AdminLTE 4 com suporte a WEBP, visualização em cards elegantes e persistência da aba ativa.
  - **Gestão de Menus (`admin/menu.php`) e Categorias (`admin/category.php`)**:
    - Modernização da listagem com badges de identificação de tipo (`bg-primary`, `bg-info`, `bg-secondary`), botões de ação padronizados e exclusão com SweetAlert2.
    - Remoção de modais legados aninhados irregularmente dentro de tags `<tbody>`.
- **Correção Geral de Tipografia, Fontes Pequenas e Botões Fora de Padrão (`admin/css/adminlte4-compat.css`)**:
  - **Aumento e Padronização de Fontes**:
    - `body`: Ajustado para `0.95rem` (15.2px) com contraste otimizado.
    - `.table thead th`: Elevado de `0.8125rem` para `0.875rem` (14px) com padding aprimorado.
    - `.table tbody td`: Elevado de `0.875rem` para `0.935rem` (15px) para leitura nítida de dados.
    - Formulários e labels (`.form-control`, `.form-select`, `.control-label`): Elevados para `0.925rem` (14.8px).
    - DataTables (Busca, paginação e info): Aumentados para `0.875rem` - `0.925rem`.
  - **Padronização Universal de Botões**:
    - Substituição das regras restritivas antigas que forçavam `font-size: 0.8125rem !important` e `0.8rem` em `.btn-sm` e `.btn-xs`.
    - `.btn-sm`: Padronizado para `0.875rem` (14px) com padding confortável de `0.35rem 0.75rem`.
    - `.btn` geral: Padronizado para `0.925rem` (14.8px) com padding de `0.5rem 1.15rem`.
    - `.btn-lg`: Padronizado para `1.05rem` com padding de `0.75rem 1.5rem`.
- **Eliminação Definitiva de `confirm()` Nativo e Aplicação Global do SweetAlert2**:
  - Remoção de todas as chamadas remanescentes a `confirm()` em `admin/service.php`, `admin/pricing-plan.php` e `admin/dental.php`.
  - Adição de interceptador global de `window.confirm` em `admin/js/pronto-global.js`, garantindo que nenhuma rotina legada invoque diálogos padrão do navegador.
- **Validação Completa**:
  - 100% dos arquivos PHP do painel verificados sem nenhum erro de sintaxe.

---

## [2.12.0] - 2026-10-22

### Reformulado & Modernizado
- **Reformulação Integral de Todos os Modelos de Páginas Administrativas (AdminLTE 4 + Bootstrap 5.3)**:
  - **Modernização de mais de 90 Arquivos de Interface**: Refatoração e alinhamento de todas as páginas de listagem, cadastro, edição e configuração (`*-add.php`, `*-edit.php`, tabelas e formulários).
  - **Eliminação Total de Estruturas Legadas**: Substituição de `<section class="content-header">`, `<section class="content">`, `.box`, `.box-header`, `.box-body`, `.box-footer`, botões desalinhados e ícones obsoletos por componentes oficiais do AdminLTE 4:
    - Cabeçalhos de página com `.app-content-header`, `.container-fluid`, tipografia moderna e breadcrumbs semânticos com ícones Bootstrap.
    - Área de conteúdo com `.app-content` e `.container-fluid`.
    - Cards padronizados com `.card`, `.card-outline`, `.card-primary`, `.shadow-sm`, `.card-header` e `.card-body`.
    - Tabelas responsivas com `.table-hover`, `.table-striped`, `.align-middle` e alinhamento horizontal consistente dos botões de ação (Editar, Excluir, Ativar/Inativar).
    - Formulários reestruturados com grid nativo do Bootstrap 5 (`.row`, `.col-md-*`, `.form-label fw-bold`, `.form-control`, `.form-select`).
  - **Redesenho Completo da Tela de Recuperação de Senha (`admin/recover.php`)**:
    - Substituição da interface legada do AdminLTE 2 pelo padrão contemporâneo em Glassmorphism, Bootstrap 5.3 e AdminLTE 4 idêntico à tela de login.
    - Suporte a floating labels (`.form-floating`), notificações integradas via SweetAlert2 (sem alerts nativos) e correção de bug na validação de confirmação de senha.
  - **Correção Definitiva do Bug de Alternância de Abas (Tabs) no Painel Administrativo**:
    - **Prevenção de Interceptação SPA (`admin/js/pronto-spa.js`)**: Ajustado o interceptador global de links para ignorar links com hash (`href.startsWith('#')`), toggles de abas (`[data-bs-toggle="tab"]`, `[data-bs-toggle="pill"]`) e elementos dentro de `.nav-tabs` e `.nav-pills`, eliminando o recarregamento indevido de página durante a troca de abas.
    - **Padronização Oficial AdminLTE 4 nas Telas de Perfil e Usuários (`admin/profile-edit.php` e `admin/user-edit.php`)**:
      - Migração para a estrutura oficial `.card.card-primary.card-outline.card-outline-tabs` com botões semânticos `<button class="nav-link" data-bs-toggle="tab" ...>`.
      - Persistência e restauração automática da aba ativa após submissão de formulários específicos (Informações, Foto, Senha).
      - Inicialização e exibição correta de mensagens de erro e sucesso em português com integração ao SweetAlert2 e Toastr.
  - **Validação e Integridade de Código**:
    - 100% dos arquivos PHP do painel verificados com zero erros de sintaxe no PHP 8.5/8.4 (`php -l`).
    - Validação de exclusão segura de fotos e banners em cadastros garantindo existência prévia de arquivo em disco.

---

## [2.11.0] - 2026-10-21

### Adicionado & Aprimorado
- **Menu Deslizante Offcanvas do Site Público (Slide da Esquerda para a Direita)**:
  - **Experiência Mobile Premium:** Menu deslizante suave e moderno substituindo menus tradicionais, com aceleração de hardware (`translate3d`), curva cúbica elástica (`cubic-bezier(0.16, 1, 0.3, 1)`) e backdrop translúcido com desfoque de vidro (`backdrop-filter: blur(5px)`).
  - **Interatividade & Gestos:** Suporte nativo a gestos de toque móvel (*touch swipe* para fechar deslizando para a esquerda), tecla `ESC`, botão `X` rotativo e clique fora.
  - **Submenus Retráteis com Acordeão:** Navegação hierárquica fluida com chevrons animados e fechamento automático de seções irmãs.
  - **Acesso Rápido a Contatos:** Botão de agendamento via WhatsApp em destaque, telefones com discagem em 1 clique e ícones das redes sociais no rodapé do drawer.
  - **Botão Hamburger Responsivo:** Ativador de 3 barras no cabeçalho com feedback visual de clique e elevação.
- **Central Completa de Auditoria & Otimização de SEO (`admin/seo.php`)**:
  - **Painel de Controle SEO Inteligente:** Métricas consolidadas (Média de pontuação, total de páginas auditadas, status de indexação e cobertura Open Graph).
  - **Pontuação de Saúde Técnica em Tempo Real (0 a 100):** Cálculo ponderado baseado em tamanho de título, densidade e tamanho de meta descrição, presença de palavras-chave, imagem Open Graph e canonical URL.
  - **Pré-Visualização ao Vivo do Google SERP:** Simulador visual fiel aos padrões atuais do Google para dispositivos Desktop e Celulares/Smartphones.
  - **Pré-Visualização de Redes Sociais (Open Graph):** Visualização em tempo real do cartão compartilhado no WhatsApp, Facebook e LinkedIn.
  - **Salvar sem Recarregar (AJAX):** Atualização instantânea dos metadados com feedback via notificações Toastr.
- **Gestão Integral do Cartão Fidelidade no Painel Admin (`admin/pricing-plan.php`)**:
  - **Ativação e Desativação Instantânea via Switch:** Coluna de status em tempo real com alternador `.form-switch` que atualiza via AJAX e reflete imediatamente no portal público (`fidelidade.php` e `index.php`).
  - **CRUD Completo:** Criação, edição, exclusão segura e listagem detalhada de planos com miniaturas, valores e tópicos de benefícios.
- **Proteção contra Robôs & Spam com Google reCAPTCHA e Armadilha Honeypot**:
  - **Configuração Centralizada em `admin/settings.php`:** Nova aba dedicada com chave do site, chave secreta e switch de ativação rápida.
  - **Integração no Formulário de Contato (`page.php`):** Widget oficial reCAPTCHA renderizado quando ativo e validação no servidor via cURL com a API do Google.
  - **Defesa Dupla com Honeypot:** Campo invisível para captura e bloqueio silencioso de spambots automatizados.
- **Padronização Oficial AdminLTE 4 no Painel Administrativo**:
  - **Correção Definitiva do Sidebar Treeview:** Resolução do conflito entre o script oficial do AdminLTE 4 e a navegação SPA, garantindo abertura e fechamento perfeitos dos submenus com modo acordeão.
  - **Padronização Universal de Abas (Tabs):** Estilização visual elegante de abas `.nav-tabs` e `.nav-tabs-custom`, alternância dinâmica compatível com Bootstrap 5 e persistência da aba ativa no hash da URL.
  - **Alinhamento & Padronização de Botões:** Grupos de botões padronizados em `.btn-sm`, espaçamento uniforme (`gap-2`) em ações de tabelas e formulários.
  - **Botão Flutuante Voltar ao Topo (`#adminBackToTop`):** Navegação vertical suave de volta ao topo no painel administrativo.

---

## [2.10.0] - 2026-10-21

### Adicionado & Aprimorado
- **Customização Total de Cards de Serviços, Planos e Produtos pelo Painel Admin**:
  - **Eliminação de Dados Hardcoded**: Fim de imagens fixas (`top-saude.png`, `odonto.png`, `empresarial.png`), valores estáticos ou botões com links fixos no frontend. Agora todo o conteúdo é gerido diretamente pelo banco de dados e editável pelo painel.
  - **Migração do Banco de Dados Idempotente (`admin/inc/migrate_cards.php`)**:
    - `tbl_pricing_plan`: Adicionadas colunas `pricing_plan_photo` (VARCHAR 255), `pricing_plan_period` (VARCHAR 50), `pricing_plan_badge` (VARCHAR 100) e `pricing_plan_popular` (TINYINT 1).
    - `tbl_service`: Adicionadas colunas `price` (VARCHAR 100), `benefits` (TEXT), `button_text` (VARCHAR 100) e `button_url` (VARCHAR 255).
    - `tbl_dental`: Adicionadas colunas `beneficios` (TEXT) e `button_text` (VARCHAR 100).
    - Migração automática das imagens padrão para `assets/uploads/` e vinculação dos registros existentes.
  - **Módulo de Planos de Saúde (`admin/pricing-plan*.php`)**:
    - Upload moderno de foto do plano com Drag & Drop e visualização instantânea.
    - Campos dinâmicos de Valor, Período de Cobrança (`/mês`, `/ano`) e Badge de Destaque (*Ex: Mais Popular, Recomendado*).
    - Checkbox de Plano Destaque (`pricing_plan_popular`) que aplica contorno e elevação tridimensional no frontend.
    - Área de texto para listagem de benefícios inclusos (1 por linha) sincronizada de forma atômica com a tabela filha `tbl_pricing_item`.
    - Configuração personalizada de texto do botão de ação e URL de redirecionamento (checkout, WhatsApp ou páginas internas).
    - Listagem administrativa com exibição de miniaturas das fotos, badges, valores formatados e resumo de benefícios no DataTables.
  - **Módulo de Serviços Médicos (`admin/service*.php`)**:
    - Adicionados campos para Preço/Valor (`A partir de R$ ...`), Tópicos de Benefícios Inclusos, Texto do Botão e Link do Botão.
    - Visualização no DataTables com badges informativas.
  - **Módulo de Odontologia & Produtos (`admin/dental*.php`)**:
    - Suporte a benefícios inclusos, texto do botão configurável e link de atendimento/WhatsApp dinâmico.
  - **Estilização Refinada no Frontend (`header.php`, `index.php`, `servicos.php`, `fidelidade.php`, `odontologia.php`)**:
    - Selo flutuante de preço sobre a foto (`.service-card-price-badge`) com efeito de desfoque translúcido (`backdrop-filter`).
    - Lista visual de benefícios com marcadores verdes (`.service-card-benefits`).
    - Realce visual moderno para planos populares (`.pricing-popular` e `.pricing-badge-flag`).
    - Compatibilidade responsiva e detecção inteligente de URLs externas com abertura segura em nova aba (`target="_blank" rel="noopener"`).
  - **Estabilidade no CLI e Servidor**:
    - Ajuste preventivo em `admin/inc/security_guard.php` para execuções via CLI sem emissão de warnings de `REQUEST_METHOD`.

---

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
