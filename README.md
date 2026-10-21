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

### Painel Administrativo Moderno (AdminLTE 4)
- **Design de Última Geração:** Construído sobre o **AdminLTE 4** e **Bootstrap 5.3**, com layout fixo responsivo (`app-wrapper`, `app-header`, `app-sidebar`, `app-main`, `app-footer`).
- **Suporte Nativo a Modo Escuro / Claro:** Alternador dinâmico de tema (Claro / Escuro / Automático) na barra superior, persistido no navegador.
- **KPIs Modernos e Interativos:**
  - 8 Cards de métricas com gradientes elegantes e contadores em tempo real.
  - Gráficos analíticos interativos via **ApexCharts** (Distribuição de Módulos e Gráfico Donut de Serviços de Saúde), sincronizados com o modo Dark/Light.
  - Painel de Ações Rápidas (Quick Actions) para acesso imediato ao cadastro de médicos, notícias, vagas e departamentos.
  - Cards de suporte técnico direto (Remoto, Telefone e WhatsApp) com notificações via Toastr.
- **Gestão de Usuários e Permissões:** Perfis diferenciados (Super Admin, Admin, Publisher) com menu lateral categorizado e controle rigoroso de acesso.
- **CMS Integrado:** Criação, edição e exclusão de categorias, notícias, banners, depoimentos, fotos e vídeos com confirmações personalizadas via **SweetAlert2**.
- **Configurações do Site:** Atualização de logotipo, favicon, paleta de cores primárias, contatos e configurações de SEO.

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
