# Pronto Saúde

Sistema web completo para gestão e apresentação institucional de clínicas médicas, laboratórios e redes de saúde. O projeto é composto por um portal público (frontend) e um painel administrativo (backend) robusto, permitindo o gerenciamento dinâmico de todos os conteúdos.

## 🚀 Funcionalidades Principais

### Portal Público (Frontend)
- **Home Dinâmica:** Exibição de banners (sliders), serviços, clínicas/departamentos, equipe médica, planos e preços, depoimentos e notícias mais recentes.
- **Departamentos:** Seção detalhada das especialidades e infraestrutura das clínicas.
- **Corpo Clínico:** Busca e perfil detalhado dos médicos, com suporte a mídias sociais.
- **Trabalhe Conosco:** Divulgação de vagas de emprego, requisitos, salários e formulário integrado ao WhatsApp.
- **Planos e Preços:** Sistema de fidelidade, planos corporativos e tabelas de preços dos serviços dentários/médicos.
- **Blog/Notícias:** Sistema de categorias, artigos, contagem de visualizações (notícias populares) e barra lateral com anúncios.
- **Seção Institucional:** Páginas de FAQ, parceiros institucionais e páginas estáticas (ex.: Política de Privacidade e Termos de Uso).

### Painel Administrativo
- **Dashboard:** Contadores estatísticos em tempo real de cadastros, notícias, médicos (ativos/inativos), etc.
- **Gestão de Usuários:** Múltiplos níveis de permissão (Super Admin, Admin, Publisher).
- **Gestão de Conteúdo (CMS):** Criação/Edição/Exclusão de categorias, notícias, banners, depoimentos, fotos e vídeos.
- **Configurações do Site:** Atualização de logotipo, favicon, paleta de cores primárias, contatos (e-mail, endereço, redes sociais) e configurações de SEO.
- **Controle Dinâmico da Home:** Liga/desliga seções (ex.: mostrar ou ocultar depoimentos) diretamente no painel.

## 🛠️ Tecnologias e Linguagens

- **Backend:** PHP 7+ (PDO)
- **Banco de Dados:** MySQL/MariaDB
- **Frontend (Público):** HTML5, CSS3, JavaScript (jQuery), Bootstrap, Owl Carousel, bxSlider
- **Frontend (Admin):** AdminLTE, Bootstrap, FontAwesome, CKEditor/Summernote
- **Integração:** API de compartilhamento social (ShareThis), WhatsApp dinâmico.

## ⚙️ Configuração e Instalação

1. Clone o repositório ou faça o upload dos arquivos para o diretório web (`public_html` ou similar).
2. Configure o banco de dados:
   - Importe o arquivo `.sql` (disponível no diretório `backup` se aplicável).
3. Conecte o banco de dados:
   - Edite o arquivo `admin/config.php` informando `$dbhost`, `$dbname`, `$dbuser` e `$dbpass`.
4. Ajuste de fuso horário:
   - O projeto está configurado para `America/Sao_Paulo`.
5. Ajuste de charset:
   - O banco foi ajustado para rodar nativamente com `utf8mb4`.

## 🔒 Segurança

- Senhas geradas/armazenadas em Hash MD5.
- Conexão PDO segura (evita ataques tradicionais de SQL Injection).
- Proteção XSS básica nas páginas de busca pública com `htmlspecialchars()`.

## 👨‍💻 Boas Práticas (Desenvolvimento)

- Sempre utilizar `charset=utf8mb4` para o MySQL evitar problemas com acentuação nas postagens do blog ( emojis e afins ).
- Evite criar páginas `.php` separadas caso a funcionalidade se aplique perfeitamente aos módulos pré-existentes no painel.
- Respeitar a sintaxe PSR básica no PHP e a hierarquia do CSS para componentes do frontend.
