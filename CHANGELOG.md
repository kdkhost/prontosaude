# Changelog

Todas as mudanças notáveis deste projeto serão documentadas neste arquivo.

O formato baseia-se em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/), e este projeto adere ao [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
