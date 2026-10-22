# REGRA OBRIGATÓRIA: NUNCA USAR UTF-8 COM BOM (BYTE ORDER MARK)

> **ATENÇÃO PARA TODOS OS AGENTES DE INTELIGÊNCIA ARTIFICIAL, DESENVOLVEDORES E SCRIPTS:**

1. **PROIBIDO O USO DE UTF-8 COM BOM (`\xEF\xBB\xBF`):**
   - Todo e qualquer arquivo (`.php`, `.js`, `.css`, `.html`, `.json`, `.md`, `.sql`, etc.) DEVE OBRIGATORIAMENTE ser gravado em **UTF-8 puro SEM BOM** (UTF-8 without BOM).
   - O caractere Byte Order Mark (`\xEF\xBB\xBF`) corrompe a interpretação do PHP, gerando saídas prematuras antes de cabeçalhos (`Cannot modify header information`), quebra APIs JSON e danifica a acentuação ortográfica da língua portuguesa (ex: "Saúde" se transformando em "Sa?de").

2. **CUIDADOS COM SHELL E POWERSHELL NO WINDOWS:**
   - Nunca use `echo "texto" > arquivo` ou comandos PowerShell com redirecionadores padrão (`>`, `Out-File`) sem especificar `-Encoding utf8NoBOM`, pois versões legadas do PowerShell inserem BOM por padrão.
   - Sempre utilize ferramentas nativas de edição que preservem UTF-8 sem BOM ou scripts PHP controlados.

3. **VERIFICAÇÃO PERMANENTE:**
   - Antes de efetuar commits ou concluir tarefas, certifique-se de que nenhum arquivo possui os 3 bytes `0xEF 0xBB 0xBF`.
   - Pode ser executado a qualquer momento o validador: `php tools/check_no_bom.php`.
