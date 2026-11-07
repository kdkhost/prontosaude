@echo off
chcp 65001 > nul
cd /d "G:\Tudo\MEU-SISTEMA\PRONTO SAUDE"
echo ============================================================
echo   Sincronizacao Git e Deploy Automatico - Pronto Saude
echo ============================================================
echo.

echo [1/4] Adicionando arquivos modificados...
git add admin/doctor-edit.php admin/designation.php admin/extra.php admin/extra-add.php admin/extra-edit.php admin/news.php inc/popup-promocional.php footer.php README.md CHANGELOG.md

echo.
echo [2/4] Criando commit humanizado em Portugues Brasileiro...
git commit -m "feat(admin): reformula modulos de medicos, especialidades e escalas, ativa popup e corrige capas"

echo.
echo [3/4] Enviando alteracoes para o GitHub (main)...
git push origin main

echo.
echo [4/4] Atualizando servidor de producao via SSH...
ssh -o BatchMode=yes -o ConnectTimeout=15 prontosaude "cd public_html && git pull origin main"

echo.
echo ============================================================
echo   Publicacao e Deploy Concluidos com Sucesso!
echo ============================================================
echo.
pause
