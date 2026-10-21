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
- **Design de Última Geração e Modo Escuro/Claro:** Construído sobre o **AdminLTE 4** e **Bootstrap 5.3**, com alternador dinâmico de tema (Claro / Escuro / Automático) na barra superior, tipografia nativa **Source Sans 3** e espaçamentos padronizados sem estilos legados.
- **Cabeçalho Fixo e Sidebar com Menu Treeview:**
  - Barra de navegação superior fixa (`sticky-top`) com efeito de desfoque de fundo (`backdrop-filter`).
  - Menu lateral dinâmico com abertura automática de seções treeview e marcação de página ativa inclusive em rotas filhas de adição e edição.
- **Sistema Universal de Máscaras de Entrada (`admin/js/pronto-masks.js`):**
  - Formatação em tempo real para Telefone/WhatsApp, CEP, CPF, CNPJ, Documento Dinâmico, Data, Hora e Moeda Brasileira (R$).
- **Preenchimento Automático via CEP com Auto Focus no Número:**
  - Integração assíncrona automática com a API pública ViaCEP durante a digitação de 8 dígitos de CEP.
  - Posicionamento automático com seleção imediata no campo de Número (`#numero_endereco`) assim que os dados do endereço são preenchidos.
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
