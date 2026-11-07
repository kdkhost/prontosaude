# Pronto Saude - Publicacao Automatica
$ErrorActionPreference = "Stop"
Set-Location "G:\Tudo\MEU-SISTEMA\PRONTO SAUDE"

Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "   Sincronizacao Git e Deploy Automatico - Pronto Saude" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "[1/4] Adicionando arquivos..." -ForegroundColor Yellow
git add admin/doctor-edit.php admin/designation.php admin/extra.php admin/extra-add.php admin/extra-edit.php admin/news.php inc/popup-promocional.php footer.php README.md CHANGELOG.md

Write-Host "[2/4] Criando commit..." -ForegroundColor Yellow
git commit -m "feat(admin): reformula modulos de medicos, especialidades e escalas, ativa popup e corrige capas"

Write-Host "[3/4] Enviando ao GitHub..." -ForegroundColor Yellow
git push origin main

Write-Host "[4/4] Atualizando servidor de producao via SSH..." -ForegroundColor Yellow
ssh -o BatchMode=yes -o ConnectTimeout=15 prontosaude "cd public_html && git pull origin main"

Write-Host ""
Write-Host "Publicacao e deploy concluidos com sucesso!" -ForegroundColor Green
