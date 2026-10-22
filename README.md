# Pronto Saúde

Sistema web completo para gestão e apresentação institucional de clínicas médicas, laboratórios e redes de saúde. O projeto é composto por um portal público (frontend) e um painel administrativo moderno de alta performance baseado em **AdminLTE 4** (backend), permitindo o gerenciamento dinâmico de todos os conteúdos.

## 🚀 Funcionalidades Principais

### Portal Público (Frontend Moderno & Responsivo - Estilo Foco7 com Identidade Pronto Saúde)
- **Design System Premium (`assets/css/pronto-modern-frontend.css`):**
  - Inspirado na arquitetura visual moderna e dinâmica da plataforma de referência Foco7, preservando integralmente a paleta e identidade visual médica da Pronto Saúde: Azul Petróleo/Teal (`#0a6b74`), Verde Esmeralda (`#10b981`), Dark Slate (`#071822` / `#0b1c24`) e Cyan (`#00b4d8`).
  - Tipografia moderna **Plus Jakarta Sans** (Google Fonts) em todos os títulos, textos e elementos de navegação.
  - Micro-animações e hover interativo em cards de serviços, médicos, planos e depoimentos.
- **Topbar Ultra Clean & Navbar Sticky:**
  - Barra superior sutil com dados de atendimento (horários, telefone, WhatsApp oficial) e botão pílula de Área do Paciente.
  - Barra de navegação branca flutuante/sticky (`.ps-foco-navbar`) com logotipo de alta definição, links centrados com indicadores dinâmicos e botões de chamada de ação (*Especialidades* e *Agendar Consulta*).
- **Hero Section de Alto Impacto com Card de Agendamento Rápido em 1 Minuto:**
  - Hero com fundo escuro hospitalar premium (`#071822` / `#0e2b38`), badge brilhante de boas-vindas, chamada persuasiva e bullet points de diferenciais de atendimento.
  - **Card Flutuante de Agendamento em 1 Minuto (`.ps-quick-card`):** Grid interativo com seleção de Especialidade, Unidade Médica, Turno preferencial e direcionamento automático para confirmação via WhatsApp.
- **Seção de Cuidado Preventivo & Diferenciais:**
  - Grid de 3 colunas (`.ps-benefit-card`) com ícones iluminados destacando Atendimento Humanizado, Resultados Online Rápidos e Tecnologia de Ponta.
- **Sobre Nós & Estatísticas de Impacto:**
  - Apresentação institucional com bullet points de diferenciais e cards estatísticos com números em tempo real (+50 Especialistas, 98% Satisfação, +15 Anos de Tradição).
- **Grid Moderno de Serviços & Especialidades (`.ps-service-card-modern`):**
  - Cards com cantos generosos arredondados (`20px`), badges flutuantes de valores "A partir de", tópicos de benefícios com checks verdes e botões de ação com gradiente.
- **Planos e Cartão Fidelidade (`.ps-plan-card`):**
  - Selos dourados de "Mais Popular", valores mensais em destaque com tipografia de impacto, lista de coberturas e contratação instantânea.
- **Corpo Clínico & Médicos:**
  - Cards médicos modernos com fotos redondas, titulação de especialidade, CRM e botão direto de agendamento de consulta com o especialista.
- **Perguntas Frequentes Interativas (FAQ):**
  - Acordeões modernos com círculos de interrogação `?` à esquerda e chevrons rotativos à direita.
- **Rodapé Premium Escuro com Google Maps Embutido (`.ps-foco-footer`):**
  - 4 colunas perfeitamente alinhadas (Sobre a clínica, Especialidades, Links rápidos e Localização/Contato), ícones sociais circulares com hover animado e mapa interativo do Google Maps responsivo embutido com segurança.
- **Menu Deslizante Offcanvas Mobile (Drawer Lateral da Esquerda para a Direita):**
  - Abertura e fechamento suaves acelerados por hardware (`translate3d`), suporte a gestos swipe, backdrop com desfoque de vidro (*backdrop-filter: blur*), acordeões de navegação e acesso rápido aos canais de atendimento.
- **Botão Flutuante de WhatsApp Oficial:**
  - Botão fixo no canto inferior direito (`.ps-floating-whatsapp`) com animação contínua de pulso suave, tooltip interativo "Agende sua Consulta" e redirecionamento direto com mensagens contextualizadas.
- **Trabalhe Conosco:** Divulgação de vagas de emprego ativas com modal hospitalar de detalhes, badges de requisitos e candidatura direta via WhatsApp ou e-mail.
- **Blog/Notícias & Busca:** Sistema de categorias, artigos completos, paginação moderna, contagem atômica de visualizações e barra lateral com anúncios e busca rápida.
- **Interface e Feedback 100% Interativos:** Formulários públicos integrados com AJAX assíncrono (sem reload), notificações flutuantes com **Toastr** e caixas de diálogo elegantes com **SweetAlert2** substituindo qualquer alerta nativo de navegador.

### Painel Administrativo Moderno e Desacoplado (AdminLTE 4 + SPA)
- **Painel Totalmente Desacoplado via AJAX (SPA):**
  - Navegação fluida entre rotas sem refresh de página e sem cintilação (`admin/js/pronto-spa.js`).
  - Cabeçalho, menu lateral (sidebar), navbar e rodapé desacoplados, com transições instantâneas e barra de progresso no topo.
  - Submissão de formulários e uploads via `FormData` e `fetch` assíncrono.
  - **Ciclo de Vida Unificado e Blindagem Anti-Duplicação:** Inicialização centralizada de componentes (`reinitAllComponents`) com guardas atômicas e restritivas (`ps-summernote-init`, `ps-datatable-init`, `ps-select2-init`, `ps-datepicker-init`), eliminando por completo ferramentas duplicadas, toolbars sobrepostas ou conflitos entre abas.
- **Upload Drag & Drop com Preview em Tempo Real:**
  - Utilitário universal (`admin/js/pronto-uploader.js`) que transforma todos os campos de imagem em zonas modernas de arrastar e soltar com preview visual instantâneo e informações do arquivo.
- **Editor Rico Summernote Global Idempotente:**
  - Integrado globalmente em todas as áreas de texto elegíveis do painel administrativo, com isolamento contra textareas internos de codeview (`.note-codable`), sincronização bidirecional em tempo real e antes do envio de formulários.
- **Geração Automática de Slugs em Tempo Real:**
  - Digitação automática de URLs amigáveis conforme títulos e nomes são digitados nos módulos de Clínicas, Páginas, Notícias, Categorias, Serviços e Médicos.
  - Bloqueio de ajuste manual (`readonly`) para prevenir inconsistências, acentos inválidos e quebra de rotas no sistema.
- **Controle Individual de Seções via AJAX (`admin/secoes.php`):**
  - Painel exclusivo com switches interativos para ativar ou desativar em tempo real qualquer seção da página inicial (Serviços, Clínicas, Médicos, Preços, Depoimentos, Notícias, Parceiros) sem recarregar a tela.
- **Páginas Personalizadas de Manutenção e Erros:**
  - `manutencao.php`: Página de manutenção programada (HTTP 503) com canais de emergência, ativável diretamente pelo painel.
  - `404.php`, `500.php`, `403.php`: Páginas de erro humanizadas e estilizadas no padrão hospitalar Pronto Saúde.
- **Segurança de Padrão Militar & Firewall WAF Integrado (`admin/seguranca.php`):**
  - **Firewall de Aplicação Web (WAF):** Inspeção profunda e em tempo real de requisições contra injeção SQL (SQLi), Cross-Site Scripting (XSS), travessia de diretórios (Path Traversal/LFI) e execução remota de código (RCE).
  - **Defesa Ativa contra Força Bruta:** Bloqueio temporário (lockout) automático de IPs e contas após sucessivas tentativas incorretas de login, com tempo de suspensão ajustável e regeneração de sessão contra fixação.
  - **Armadilha Honeypot:** Campos invisíveis anti-robô que detectam e bloqueiam preenchimentos automatizados de formulários por scripts hostis.
  - **Headers de Alta Segurança & Anti-Vazamento:** Políticas estritas de HSTS, X-Content-Type-Options (nosniff), X-Frame-Options (anti-clickjacking), Referrer-Policy e ocultação total da assinatura do PHP.
  - **Controle de Acesso por IP & Quarentena Dinâmica:** Gestão integrada de Lista Branca (Whitelist), Lista Negra (Blacklist) e desbloqueio imediato de IPs bloqueados via painel administrativo.
  - **Auditoria e Logs em Tempo Real:** Registro detalhado de todos os incidentes com identificação de IP, endpoint atacado, severidade da ameaça e payload interceptado.
- **Design de Última Geração e Coerência Absoluta Dark / Light Mode:**
  - Construído sobre o **AdminLTE 4** e **Bootstrap 5.3**, com alternador dinâmico de tema (Claro / Escuro / Automático) na barra superior, tipografia nativa **Source Sans 3** e espaçamentos padronizados sem divergências de cores.
  - **Suíte de Alto Contraste para Modo Escuro:** Correção de tabelas, cartões, abas, modais, formulários, inputs, paginações DataTables, e integração total com componentes externos como **Select2**, **Datepicker**, **Summernote**, **SweetAlert2** e **Toastr**.
  - **Reconstrução das Páginas Internas do Site:** Modernização das páginas de especialidades (`department.php`), corpo clínico (`doctor.php`), notícias (`news.php`), termos institucionais e todos os layouts do gerenciador de páginas (`page.php`), mantendo 100% das funções e tabelas do banco de dados intactas.
- **Cabeçalho Fixo e Sidebar com Menus Treeview Agrupados & Modo Acordeão:**
  - Barra de navegação superior fixa (`sticky-top`) com efeito de desfoque de fundo (`backdrop-filter`).
  - Menu lateral 100% categorizado em grupos lógicos compactos (Administração, Notícias, Corpo Clínico, Serviços, Planos, Mídias, Elementos do Portal, Banners, Engajamento e Minha Conta).
  - **Modo Acordeão (Treeview Accordion):** Ao abrir qualquer menu pai, os outros menus abertos se fecham automaticamente, prevenindo menus extensos demais.
  - **Marcação Dinâmica de Página Ativa:** Destaque visual colorido no item filho e indicador no menu pai correspondente, sincronizados em PHP e no motor SPA via AJAX.
  - **Scroll Nativo e Suave (`sidebar-wrapper`):** Rolagem interna independente com scrollbar fina, impedindo cortes de menus ou botões no rodapé da página.
- **Sistema Universal de Máscaras de Entrada (`admin/js/pronto-masks.js`):**
  - Formatação em tempo real para Telefone/WhatsApp, CEP, CPF, CNPJ, Documento Dinâmico, Data, Hora e Moeda Brasileira (R$).
- **Preenchimento Automático via CEP com Auto Focus no Número:**
  - Integração assíncrona automática com a API pública ViaCEP durante a digitação de 8 dígitos de CEP.
  - Posicionamento automático com seleção imediata no campo de Número (`#numero_endereco`) assim que os dados do endereço são preenchidos.
- **DataTables Responsive no Padrão AdminLTE 3 / 4:**
  - Tabelas totalmente responsivas com recolhimento inteligente de colunas em telas menores ou dispositivos móveis.
  - Botão circular expansível com ícone verde de sinal de mais (`+`) e sinal de menos (`-`) em vermelho ao expandir a linha filha com todos os dados e detalhes estruturados.
- **Padronização Visual Completa, Reformulação de Modelos de Páginas e Alinhamento de Botões:**
  - Modernização integral de mais de 90 páginas administrativas (`*-add.php`, `*-edit.php`, listagens e configurações) convertidas para os padrões oficiais do **AdminLTE 4** e **Bootstrap 5.3**.
  - Erradicação definitiva de classes legadas (`.box`, `.box-header`, `.box-body`, `<section class="content">`), adotando containers `.app-content-header`, `.app-content`, `.card card-outline`, cabeçalhos com breadcrumbs com ícones e rodapé padronizado.
  - Tipografia unificada e confortável (`0.95rem` no corpo, `0.875rem` em cabeçalhos de tabela, `0.935rem` em dados e `0.875rem` em `.btn-sm`), eliminando fontes minúsculas de 12px e botões desproporcionais.
  - Alinhamento automático de botões de ação nas tabelas (Editar, Deletar, Visualizar) lado a lado sem quebra feia, cabeçalhos de página proporcionais e grid de formulários perfeitamente alinhado.
  - Tela de recuperação de senha (`admin/recover.php`) completamente modernizada com Glassmorphism, Floating Labels e confirmações via SweetAlert2 no mesmo padrão do login.
- **Arquitetura Oficial de Abas (Tabs) e Resolução Definitiva de Navegação:**
  - Migração completa de todas as páginas com abas (`admin/settings.php`, `admin/menu-add.php`, `admin/advertisement-home.php`, `admin/profile-edit.php` e `admin/user-edit.php`) para a estrutura oficial `.card.card-primary.card-outline.card-outline-tabs`.
  - **Página de Configurações Geral (`admin/settings.php`) Totalmente Reformulada:** Barra de abas com scroll suave horizontal (`.ps-tabs-scrollable`), 10 seções categorizadas por ícones, upload Drag & Drop para Logo e Favicon com mockup de navegador, busca inteligente por CEP com auto focus no número, prévia de mapa Google Maps, simulador de busca Google SERP com contadores de caracteres, seletor de cores com paletas rápidas do Pronto Saúde e controle de seções da Home com switches visuais.
  - Persistência e restauração automática da aba ativa (`$active_tab`) em todos os formulários submetidos via POST e sincronização bidirecional com a URL hash (`#tab_1` a `#tab_10`).
  - Interceptação global de `window.alert` e `window.confirm` com SweetAlert2 e Toastr em 100% dos fluxos do sistema.
- **Gestão e Customização Total de Cards (Serviços, Planos e Produtos):**
  - **100% Configurável pelo Painel Admin:** Fim de imagens, links e textos fixos no código-fonte. Tudo é editável em tempo real nos módulos de Serviços (`admin/service.php`), Planos (`admin/pricing-plan.php`) e Odontologia (`admin/dental.php`).
  - **Upload Dinâmico com Preview:** Upload por arrastar e soltar (Drag & Drop) de fotos e ilustrações sem dependência de caminhos estáticos.
  - **Preços e Valores Flexíveis:** Definição livre de valores monetários (`R$ 49,90`), prazos (`/mês`, `/ano`) ou modalidades especiais (`Sob Consulta`).
  - **Benefícios Inclusos:** Edição simplificada de tópicos/benefícios (um por linha no painel) com renderização automática de ícones de checkmark nos cards do portal.
  - **Botões e Links Customizáveis:** Personalização do texto da ação (ex: *Contratar Agora*, *Agendar Consulta*, *Saiba Mais*) e URL de destino flexível (WhatsApp direto, link de checkout ou página de detalhes).
  - **Destaque e Selos (Badges):** Opção de marcar planos recomendados com efeito de elevação e selos dinâmicos (*Mais Popular*, *Recomendado*).
- **Padronização Universal de Atalhos Rápidos (Switches Toggle AJAX) no Painel:**
  - **Correção Definitiva de Comunicação:** Resolução completa da falha de buffer em endpoints AJAX que gerava mensagens de "Falha na comunicação com o servidor". Esvaziamento proativo de buffers (`ob_end_clean()`) e criação do controlador universal `admin/toggle-ajax.php`.
  - **Switches de Status em Todo o Sistema:** Implementação padronizada de switches toggle instantâneos (`.form-switch`) em **100% das tabelas do painel**, permitindo ativar e desativar qualquer registro com 1 clique sem recarregar a tela:
    - **Páginas do Portal (`admin/page.php`):** Ativação/desativação e remoção automática de menus.
    - **Corpo Clínico & Médicos (`admin/doctor.php`):** Ativação instantânea de médicos no site.
    - **Serviços Médicos (`admin/service.php`):** Controle em tempo real da exibição de especialidades e cards.
    - **Sliders da Home (`admin/slider.php`):** Ligar/desligar slides do carrossel principal.
    - **Planos e Cartão Fidelidade (`admin/pricing-plan.php`):** Controle de exibição de planos e preços.
    - **Depoimentos de Pacientes (`admin/testimonial.php`):** Gestão rápida de avaliações aprovadas.
    - **Notícias & Blog (`admin/news.php`):** Alternância entre Publicada e Rascunho.
    - **Galeria de Fotos (`admin/photo.php`) & Vídeos (`admin/video.php`):** Visibilidade em tempo real.
    - **Perguntas Frequentes (`admin/faq.php`):** Controle de exibição de dúvidas frequentes.
  - **Script Global `admin/js/pronto-switches.js`:** Gerenciador universal com suporte a DataTables (mesmo com busca e paginação ativa), atualização dinâmica de badges, feedback por Toastr / SweetAlert2 e reversão automática em caso de instabilidade.
- **Aplicativo PWA (Progressive Web App) 100% Configurável (`admin/pwa.php`):**
  - **Painel Administrativo Próprio:** Personalização completa de nome do app, nome curto, descrição, cores do tema (`theme_color`), cor de fundo da splash (`background_color`) e upload de ícones em alta resolução (192x192 e 512x512) com mockup em tempo real de smartphone.
  - **Manifesto Web Dinâmico (`manifest.webmanifest.php`):** Emissão automática e em tempo real dos metadados W3C PWA direto do banco de dados com atalhos de aplicativo integrados.
  - **Service Worker Inteligente (`sw.js`):** Cache avançado com estratégias *Network First* para páginas dinâmicas e *Stale-While-Revalidate* para assets estáticos (CSS, JS, imagens, fontes), garantindo menor tempo de abertura e navegação acelerada mesmo com oscilações de sinal.
  - **Instalação Amigável no Frontend:** Toast flutuante moderno convidando o visitante a instalar o aplicativo no celular ou desktop, com controle de intervalo para não ser invasivo.
- **Banner de Popup Promocional & Avisos da Home (`admin/popup.php`):**
  - Gestão de campanhas sazonais, avisos de feriados, vacinação e promoções do cartão fidelidade.
  - Personalização de título, mensagem, banner ilustrativo, botão com link personalizado, tempo de espera para abertura (delay em ms) e frequência de exibição (controle de expiração via cookies).
  - Preview ao vivo em modal simulado direto no painel administrativo.
- **Sistema LGPD Moderno com Glassmorphism (`footer.php`):**
  - Banner de consentimento com efeito de vidro fosco (`backdrop-filter: blur(16px)`), bordas suaves e paleta médica turquesa.
  - Modal intuitivo de preferências para cookies essenciais e analíticos em estrita conformidade com a Lei Geral de Proteção de Dados (LGPD).
  - Armazenamento persistente e seguro das preferências no navegador (`localStorage`).
- **Reformulação Visual, Performance & Carregamento Assíncrono do Site Público:**
  - **Design System Inovador de 2026 (`assets/css/pronto-modern-frontend.css`):** Cores tailored (`#0a6b74` e `#00b4d8`), tipografia contemporânea Plus Jakarta Sans, sombras volumétricas suaves, bordas arredondadas e micro-animações interativas nos cards.
  - **Carregamento Assíncrono Universal (Lazy Loading):** Atributos nativos `loading="lazy"` e `decoding="async"` aliados a `IntersectionObserver` progressivo com transições suaves de fade-in em fotos e vídeos.
  - **Otimização de Performance:** Menor tempo de abertura de páginas, eliminação de scripts bloqueantes legados, pré-conexão com repositórios de fontes e aceleração de hardware em carrosséis e no menu drawer offcanvas.
- **Gestor de Páginas & Controle de Visibilidade Individual (`admin/page.php`):**
  - **Ativação e Desativação Instantânea em Tempo Real:** Chaves switch AJAX (`.form-switch`) para cada página do portal (página de médicos e todas as demais páginas do sistema e personalizadas), permitindo ligar e desligar a visibilidade de qualquer página a qualquer momento sem recarregar a tela.
  - **Ocultação Automática dos Menus de Navegação:** Ao desativar uma página (ex: *Corpo Clínico & Médicos*), o link é automaticamente removido do menu desktop principal e do menu móvel deslizante (drawer offcanvas), evitando links quebrados.
  - **Ocultação em Seções da Home e Rodapé:** A página desativada também é ocultada dinamicamente das seções da página inicial e dos links institucionais do rodapé (`footer.php`).
  - **Bloqueio de Rota Direta com Proteção Ativa:** Qualquer tentativa de acesso direto à URL de uma página desativada é interceptada de forma segura e redirecionada para a página inicial com aviso informativo Toastr amigável (*"A página solicitada está temporariamente desativada pelo administrador."*).
  - **Painel com Indicadores Rápidos & Filtros:** Cards no topo com contadores de páginas totais, ativas, desativadas e nativas do sistema, além de busca instantânea e filtros rápidos por status.
  - **Sincronização Automática das Páginas Nativas:** Catálogo canônico que garante a existência de todas as páginas institucionais em `tbl_page`, preservando o status individual escolhido pelo administrador.
- **Menu Deslizante Offcanvas do Site Público (Slide da Esquerda para a Direita):**
  - Menu mobile/tablet deslizante de alta performance acelerado por hardware (`translate3d`), com animação elástica suave.
  - Suporte completo a gestos de toque (*swipe* para a esquerda para fechar), tecla `ESC`, botão `X` e backdrop blur com desfoque de vidro.
  - Submenus em formato acordeão com chevrons rotativos e canais de contato rápido integrados.
- **Gestão Integral do Cartão Fidelidade (`admin/pricing-plan.php`):**
  - Criação, edição, exclusão e chave de ativação/desativação instantânea com `.form-switch` via AJAX com impacto imediato no portal público (`fidelidade.php` e `index.php`).
- **Defesa Global Anti-Robô com Google reCAPTCHA e Honeypot:**
  - Configuração no painel (`admin/settings.php`), integração com a API oficial do Google via backend (cURL) e honeypot invisível para descarte automático de spambots.
- **Correção Abrangente de Acentuação e Encoding:**
  - Banco de dados e arquivos 100% saneados em UTF-8 (`utf8mb4`), eliminando caracteres corrompidos (`?`) e garantindo ortografia correta da língua portuguesa.
- **KPIs Modernos e Interativos:**
  - 8 Cards de métricas com gradientes elegantes e contadores em tempo real.
  - Gráficos analíticos interativos via **ApexCharts** sincronizados com o tema Dark/Light.
  - Painel de Ações Rápidas (Quick Actions) para acesso imediato a cadastros e suporte técnico.
- **Gestão de Usuários e Permissões:** Perfis diferenciados (Super Admin, Admin, Publisher) com menu lateral categorizado e controle de acesso.
- **CMS Integrado:** Gestão completa de notícias, banners, depoimentos, fotos e vídeos com confirmações personalizadas via **SweetAlert2**.

## 🛠️ Tecnologias e Linguagens

- **Backend:** PHP 8.4 (PDO nativo com prepared statements reais e timezone configurado)
- **Banco de Dados:** MySQL/MariaDB (`utf8mb4_unicode_ci` e timezone `-03:00`)
- **Painel Administrativo:** AdminLTE 4, Bootstrap 5.3, Bootstrap Icons, FontAwesome 6, ApexCharts, OverlayScrollbars
- **Notificações & Confirmações:** SweetAlert2 e Toastr integrados globalmente (sem alerts ou confirms nativos)
- **Frontend (Público):** HTML5 semântico, CSS3, JavaScript (jQuery), Bootstrap, Owl Carousel, bxSlider
- **Integração:** API de compartilhamento social (ShareThis), WhatsApp dinâmico.

## ⚙️ Configuração e Instalação

1. Clone o repositório ou faça o upload dos arquivos para o diretório web (`public_html` ou similar).
2. Configure o banco de dados:
   - Importe o arquivo `.sql` da estrutura do sistema.
3. Conecte o banco de dados:
   - Edite o arquivo `admin/config.php` informando `$dbhost`, `$dbname`, `$dbuser` e `$dbpass`.
4. Ajuste de fuso horário:
   - O projeto está padronizado para `America/Sao_Paulo` (GMT-3) tanto na camada PHP quanto nas sessões MySQL.
5. Ajuste de charset e encoding:
   - Todo o código-fonte é gravado em **UTF-8 sem BOM**.
   - As conexões PDO utilizam estritamente `utf8mb4`.

## 🔒 Segurança e Performance

- Senhas geradas e autenticadas com Hash seguro.
- Conexão PDO segura com desativação de emulação (`PDO::ATTR_EMULATE_PREPARES => false`), prevenindo SQL Injection.
- Proteção XSS aplicada em saídas dinâmicas com `htmlspecialchars()`.
- Consultas otimizadas com comandos atômicos e consultas indexadas, evitando loops N+1.
- Compatibilidade estrita e testada com **PHP 8.4**.

## 👨‍💻 Boas Práticas (Desenvolvimento e Agentes de IA)

- **REGRA CRÍTICA DE CODIFICAÇÃO (NUNCA USAR UTF-8 COM BOM):**
  - **É terminantemente proibido o uso de UTF-8 com BOM (`\xEF\xBB\xBF`)** em qualquer arquivo do projeto (`.php`, `.js`, `.css`, `.json`, etc.).
  - O BOM descarrega bytes invisíveis antes do cabeçalho PHP, gerando erros de `headers already sent`, quebrando payloads JSON de APIs/AJAX e corrompendo caracteres acentuados da língua portuguesa.
  - Para validação e correção automática, execute: `php tools/check_no_bom.php`.
  - Configurações obrigatórias registradas em `.editorconfig`, `.gitattributes`, `.agents/rules/utf8_no_bom.md` e `AGENTS.md`.
- **Charset do Banco:** Sempre utilizar `utf8mb4` com collation `utf8mb4_unicode_ci` no MySQL para preservar a integridade ortográfica.
- **Alertas e Confirmações:** Nunca utilizar o `alert()` ou `confirm()` padrão do navegador; utilizar o utilitário global `window.notificar` (Toastr) ou `Swal.fire` (SweetAlert2).
- **Idioma:** Toda comunicação, commits e documentação devem ser mantidos em português brasileiro.
- Manter o arquivo `CHANGELOG.md` atualizado com o histórico de versões mais recente no topo.
