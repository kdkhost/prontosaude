<?php
/**
 * Pronto Saúde - Componente do Banner de Instalação PWA
 * Renderizado de forma inteligente conforme configurações do painel administrativo
 * UTF-8 No BOM
 */

if (!defined('BASE_URL')) {
    exit;
}

// Só exibe se o PWA global e o Banner estiverem ativados
if (($pwa_status ?? 0) != 1 || ($pwa_banner_status ?? 0) != 1) {
    return;
}

$banner_title    = !empty($pwa_banner_title) ? $pwa_banner_title : 'Instale o App Pronto Saúde';
$banner_desc     = !empty($pwa_banner_desc) ? $pwa_banner_desc : 'Agendamento rápido em 1 minuto, carteirinha digital e histórico de consultas sem ocupar memória do aparelho.';
$banner_btn_text = !empty($pwa_banner_btn_text) ? $pwa_banner_btn_text : 'Instalar Aplicativo Grátis';
$banner_pos      = in_array($pwa_banner_position ?? '', ['bottom-right', 'bottom-left', 'bottom-center', 'bottom-bar']) ? $pwa_banner_position : 'bottom-right';
$banner_delay    = max(0, (int)($pwa_banner_delay ?? 3));
$banner_cooldown = max(0, (int)($pwa_banner_cooldown ?? 7));
$banner_ios      = (int)($pwa_banner_ios_guide ?? 1);

// URL do ícone 192x192
$app_icon = !empty($pwa_icon_192) ? $pwa_icon_192 : BASE_URL . 'assets/uploads/' . ($favicon ?: 'favicon.png');
$app_short_name = !empty($pwa_short_name) ? $pwa_short_name : 'Pronto Saúde';
?>

<!-- ==============================================================================
     BANNER INTELIGENTE DE INSTALAÇÃO DO PWA (PRONTO SAÚDE)
     ============================================================================== -->
<aside id="psPwaInstallBanner" class="ps-pwa-banner ps-pwa-pos-<?= htmlspecialchars($banner_pos, ENT_QUOTES, 'UTF-8'); ?>" aria-label="Instalar Aplicativo Pronto Saúde">
    <div class="ps-pwa-card">
        <!-- Botão Fechar / Dispensar -->
        <button type="button" class="ps-pwa-close-btn" id="btnDismissPwaApp" title="Fechar e lembrar mais tarde" aria-label="Fechar">
            <i class="fas fa-times"></i>
        </button>

        <!-- Badge de Aplicativo Oficial -->
        <div class="ps-pwa-badge">
            <i class="fas fa-certificate"></i> App Oficial Pronto Saúde
        </div>

        <!-- Cabeçalho do Banner: Ícone + Títulos -->
        <div class="ps-pwa-header">
            <img src="<?= htmlspecialchars($app_icon, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($app_short_name, ENT_QUOTES, 'UTF-8'); ?>" class="ps-pwa-app-icon" loading="lazy">
            <div class="ps-pwa-titles">
                <h4 class="ps-pwa-title"><?= htmlspecialchars($banner_title, ENT_QUOTES, 'UTF-8'); ?></h4>
                <p class="ps-pwa-subtitle"><i class="fas fa-shield-heart me-1"></i> Pronto Saúde na palma da sua mão</p>
            </div>
        </div>

        <!-- Descrição e Benefícios -->
        <p class="ps-pwa-desc">
            <?= nl2br(htmlspecialchars($banner_desc, ENT_QUOTES, 'UTF-8')); ?>
        </p>

        <!-- Chips de Vantagens -->
        <div class="ps-pwa-chips">
            <span class="ps-pwa-chip"><i class="fas fa-bolt"></i> Acesso em 1 clique</span>
            <span class="ps-pwa-chip"><i class="fas fa-id-card"></i> Carteirinha Digital</span>
            <span class="ps-pwa-chip"><i class="fas fa-feather-pointed"></i> Ultraleve (0 MB)</span>
        </div>

        <!-- Ações do Banner -->
        <div class="ps-pwa-actions">
            <button type="button" id="btnInstallPwaApp" class="ps-pwa-btn-install">
                <i class="fas fa-download"></i> <?= htmlspecialchars($banner_btn_text, ENT_QUOTES, 'UTF-8'); ?>
            </button>
            <button type="button" id="btnLaterPwaApp" class="ps-pwa-btn-later">
                Agora não
            </button>
        </div>
    </div>
</aside>

<?php if ($banner_ios == 1): ?>
<!-- Modal Guia Visual Especial para iOS (Safari iPhone / iPad) -->
<div id="psPwaIosModal" class="ps-pwa-ios-modal" role="dialog" aria-modal="true" aria-labelledby="psIosModalTitle">
    <div class="ps-pwa-ios-sheet">
        <div class="text-center mb-3">
            <div class="d-inline-flex p-3 rounded-circle bg-primary-subtle text-primary mb-2 fs-3">
                <i class="fab fa-apple"></i>
            </div>
            <h5 class="fw-bold mb-1" id="psIosModalTitle">Instalar no seu iPhone ou iPad</h5>
            <p class="text-muted small mb-0">Siga os 3 passos simples abaixo no navegador Safari:</p>
        </div>

        <div class="ps-pwa-ios-step">
            <span class="ps-pwa-ios-step-num">1</span>
            <div class="small">
                Toque no ícone de <strong>Compartilhar</strong> <i class="fas fa-arrow-up-from-bracket text-primary mx-1 fs-6"></i> na barra inferior do Safari.
            </div>
        </div>

        <div class="ps-pwa-ios-step">
            <span class="ps-pwa-ios-step-num">2</span>
            <div class="small">
                Role o menu para baixo e toque em <strong>"Adicionar à Tela de Início"</strong> <i class="far fa-square-plus text-primary mx-1 fs-6"></i>.
            </div>
        </div>

        <div class="ps-pwa-ios-step">
            <span class="ps-pwa-ios-step-num">3</span>
            <div class="small">
                No canto superior direito, toque em <strong>"Adicionar"</strong>. Pronto! O app estará na sua tela inicial.
            </div>
        </div>

        <div class="mt-3 pt-2 text-center">
            <button type="button" class="btn btn-primary w-100 py-2 fw-semibold rounded-3" id="btnClosePwaIosModal">
                <i class="fas fa-check me-1"></i> Entendi, fechar
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Configuração de Inicialização do PWA -->
<script>
window.ProntoPwaConfig = {
    enabled: <?= $pwa_status == 1 ? 'true' : 'false'; ?>,
    bannerEnabled: <?= $pwa_banner_status == 1 ? 'true' : 'false'; ?>,
    delay: <?= $banner_delay; ?>,
    cooldown: <?= $banner_cooldown; ?>,
    position: '<?= htmlspecialchars($banner_pos, ENT_QUOTES, 'UTF-8'); ?>',
    iosGuide: <?= $banner_ios == 1 ? 'true' : 'false'; ?>,
    appName: '<?= htmlspecialchars($app_short_name, ENT_QUOTES, 'UTF-8'); ?>'
};
</script>

