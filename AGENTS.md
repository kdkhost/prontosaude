# Diretrizes do Projeto Pronto Saúde para Assistentes de IA

## 1. Codificação de Arquivos (REGRA CRÍTICA E INVIOLÁVEL)
- **NUNCA USE UTF-8 COM BOM (`\xEF\xBB\xBF`).**
- Todos os arquivos do projeto devem ser salvos estritamente em **UTF-8 puro SEM BOM** (UTF-8 No BOM).
- O caractere BOM corrompe cabeçalhos HTTP do PHP, quebra requisições AJAX/JSON e danifica a acentuação em português brasileiro.
- No Windows PowerShell, evite redirecionamentos `>` não-parametrizados. Use sempre `-Encoding utf8NoBOM` ou scripts PHP para criação e manipulação de arquivos.

## 2. Padrões de Interface e Notificação
- **Nunca use `alert()` ou `confirm()` padrão do navegador.** Sempre utilize **SweetAlert2** de forma global para confirmações e **Toastr / Notyf** para avisos rápidos.
- Todas as operações de salvamento e ativação em tabelas devem usar requisições assíncronas (AJAX).

## 3. Idioma e Comunicação
- Todo o código, comentários e commits devem ser em **Português Brasileiro (PT-BR)**.
- Commits devem ser humanizados e descritivos.
