# Pronto Saúde

Sistema web completo para gestão e apresentação institucional de clínicas médicas, laboratórios e redes de saúde. O projeto é composto por um portal público (frontend) e um painel administrativo moderno de alta performance baseado em **AdminLTE 4** (backend), permitindo o gerenciamento dinâmico de todos os conteúdos.

## 🚀 Funcionalidades Principais

### Portal Público (Frontend)
- **Home Dinâmica:** Exibição de banners (sliders), serviços, clínicas/departamentos, equipe médica, planos e preços, depoimentos e notícias mais recentes.
- **Departamentos:** Seção detalhada das especialidades e infraestrutura das clínicas.
- **Corpo Clínico:** Busca e perfil detalhado dos médicos, com suporte a mídias sociais.
- **Trabalhe Conosco:** Divulgação de vagas de emprego, requisitos, salários e formulário integrado ao WhatsApp.
- **Planos e Preços:** Sistema de fidelidade, planos corporativos e tabelas de preços dos serviços dentários/médicos.
- **Blog/Notícias:** Sistema de categorias, artigos, contagem atômica de visualizações e barra lateral com anúncios.
- **Seção Institucional:** Páginas de FAQ, parceiros institucionais e páginas estáticas (Política de Privacidade e Termos de Uso).
- **Interface e Feedback:** Notificações flutuantes com **Toastr** e caixas de diálogo elegantes com **SweetAlert2** substituindo qualquer alerta nativo de navegador.

### Painel Administrativo Moderno e Desacoplado (AdminLTE 4 + SPA)
- **Painel Totalmente Desacoplado via AJAX (SPA):**
  - Navegação fluida entre rotas sem refresh de página e sem cintilação (`admin/js/pronto-spa.js`).
  - Cabeçalho, menu lateral (sidebar), navbar e rodapé desacoplados, com transições instantâneas e barra de progresso no topo.
  - Submissão de formulários e uploads via `FormData` e `fetch` assíncrono.
  - Re-inicialização automática de tooltips, tabelas DataTables, Summernote, seletores e gráficos.
- **Upload Drag & Drop com Preview em Tempo Real:**
  - Utilitário universal (`admin/js/pronto-uploader.js`) que transforma todos os campos de imagem em zonas modernas de arrastar e soltar com preview visual instantâneo e informações do arquivo.
- **Editor Rico Summernote Global:**
  - Integrado globalmente em todas as áreas de texto do painel administrativo, com sincronização automática pré-envio.
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
- **Design de Última Geração e Modo Escuro/Claro:** Construído sobre o **AdminLTE 4** e **Bootstrap 5.3**, com alternador dinâmico de tema (Claro / Escuro / Automático) na barra superior, tipografia nativa **Source Sans 3** e espaçamentos padronizados sem estilos legados.
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
- **Padronização Visual Completa e Alinhamento de Botões:**
  - Tipografia unificada estritamente em 14px (`Source Sans 3`), alinhamento automático de botões de ação nas tabelas (Editar, Deletar, Visualizar) lado a lado sem quebra feia, cabeçalhos de página proporcionais e grid de formulários perfeitamente alinhado.
- **Gestão e Customização Total de Cards (Serviços, Planos e Produtos):**
  - **100% Configurável pelo Painel Admin:** Fim de imagens, links e textos fixos no código-fonte. Tudo é editável em tempo real nos módulos de Serviços (`admin/service.php`), Planos (`admin/pricing-plan.php`) e Odontologia (`admin/dental.php`).
  - **Upload Dinâmico com Preview:** Upload por arrastar e soltar (Drag & Drop) de fotos e ilustrações sem dependência de caminhos estáticos.
  - **Preços e Valores Flexíveis:** Definição livre de valores monetários (`R$ 49,90`), prazos (`/mês`, `/ano`) ou modalidades especiais (`Sob Consulta`).
  - **Benefícios Inclusos:** Edição simplificada de tópicos/benefícios (um por linha no painel) com renderização automática de ícones de checkmark nos cards do portal.
  - **Botões e Links Customizáveis:** Personalização do texto da ação (ex: *Contratar Agora*, *Agendar Consulta*, *Saiba Mais*) e URL de destino flexível (WhatsApp direto, link de checkout ou página de detalhes).
  - **Destaque e Selos (Badges):** Opção de marcar planos recomendados com efeito de elevação e selos dinâmicos (*Mais Popular*, *Recomendado*).
- **Central Completa de Auditoria & Otimização de SEO (`admin/seo.php`):**
  - Auditoria técnica em tempo real com cálculo de pontuação (0 a 100) para páginas fixas e dinâmicas.
  - Simuladores ao vivo do Google SERP (Desktop e Mobile) e de Cartões de Redes Sociais / Open Graph (WhatsApp, Facebook, LinkedIn).
  - Salvamento assíncrono via AJAX sem recarregar a tela.
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

## 👨‍💻 Boas Práticas (Desenvolvimento)

- Sempre utilizar `charset=utf8mb4` no MySQL para suportar caracteres especiais e acentuação da língua portuguesa.
- Nunca utilizar o `alert()` ou `confirm()` padrão do navegador; utilizar o utilitário global `window.notificar` (Toastr) ou `Swal.fire` (SweetAlert2).
- Toda comunicação, commits e documentação devem ser mantidos em português brasileiro.
- Manter o arquivo `CHANGELOG.md` atualizado com o histórico de versões mais recente no topo.
