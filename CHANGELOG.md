# Changelog

Todas as mudanças notáveis deste projeto serão documentadas neste arquivo.

O formato baseia-se em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/), e este projeto adere ao [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
