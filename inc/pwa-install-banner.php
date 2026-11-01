<?php
/**
 * Pronto Saúde - Componente do Banner de Instalação PWA
 * Renderizado de forma inteligente conforme configurações 100% dinâmicas do painel administrativo
 * UTF-8 No BOM
 */

if (!defined('BASE_URL')) {
    exit;
}

// Só exibe se o PWA global e o Banner estiverem ativados
if (($pwa_status ?? 0) != 1 || ($pwa_banner_status ?? 0) != 1) {
    return;
}

// Parâmetros de Comportamento
$banner_pos      = in_array($pwa_banner_position ?? '', ['bottom-right', 'bottom-left', 'bottom-center', 'bottom-bar']) ? $pwa_banner_position : 'bottom-right';
$banner_delay    = max(0, (int)($pwa_banner_delay ?? 3));
$banner_cooldown = max(0, (int)($pwa_banner_cooldown ?? 7));
$banner_ios      = (int)($pwa_banner_ios_guide ?? 1);

// Textos do Banner (100% Configuráveis)
$banner_badge     = !empty($pwa_banner_badge) ? $pwa_banner_badge : 'App Oficial Pronto Saúde';
$banner_title     = !empty($pwa_banner_title) ? $pwa_banner_title : 'Instale o App Pronto Saúde';
$banner_subtitle  = !empty($pwa_banner_subtitle) ? $pwa_banner_subtitle : 'Pronto Saúde na palma da sua mão';
$banner_desc      = !empty($pwa_banner_desc) ? $pwa_banner_desc : 'Agendamento rápido em 1 minuto, carteirinha digital e histórico de consultas sem ocupar memória do aparelho.';
$banner_chip1     = !empty($pwa_banner_chip1) ? $pwa_banner_chip1 : 'Acesso em 1 clique';
$banner_chip2     = !empty($pwa_banner_chip2) ? $pwa_banner_chip2 : 'Carteirinha Digital';
$banner_chip3     = !empty($pwa_banner_chip3) ? $pwa_banner_chip3 : 'Ultraleve (0 MB)';
$banner_btn_text  = !empty($pwa_banner_btn_text) ? $pwa_banner_btn_text : 'Instalar Aplicativo Grátis';
$banner_btn_later = !empty($pwa_banner_btn_later) ? $pwa_banner_btn_later : 'Agora não';

// Textos do Guia iOS
$ios_title        = !empty($pwa_banner_ios_title) ? $pwa_banner_ios_title : 'Instalar no seu iPhone ou iPad';
$ios_subtitle     = !empty($pwa_banner_ios_subtitle) ? $pwa_banner_ios_subtitle : 'Siga os 3 passos simples abaixo no navegador Safari:';
$ios_step1        = !empty($pwa_banner_ios_step1) ? $pwa_banner_ios_step1 : 'Toque no ícone de Compartilhar na barra inferior do Safari.';
$ios_step2        = !empty($pwa_banner_ios_step2) ? $pwa_banner_ios_step2 : 'Role a lista para baixo e toque em "Adicionar à Tela de Início".';
$ios_step3        = !empty($pwa_banner_ios_step3) ? $pwa_banner_ios_step3 : 'No canto superior direito, toque em "Adicionar". Pronto!';
$ios_btn_text     = !empty($pwa_banner_ios_btn_text) ? $pwa_banner_ios_btn_text : 'Entendi, fechar';

// Textos de Feedback de Sucesso
$success_title    = !empty($pwa_banner_success_title) ? $pwa_banner_success_title : 'Aplicativo Instalado!';
$success_msg      = !empty($pwa_banner_success_msg) ? $pwa_banner_success_msg : 'O aplicativo foi instalado com sucesso. Você já pode acessá-lo direto da sua tela inicial!';

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
            <i class="fas fa-certificate"></i> <span id="psPwaBadgeText"><?= htmlspecialchars($banner_badge, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <!-- Cabeçalho do Banner: Ícone + Títulos -->
        <div class="ps-pwa-header">
            <img src="<?= htmlspecialchars($app_icon, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($app_short_name, ENT_QUOTES, 'UTF-8'); ?>" class="ps-pwa-app-icon" loading="lazy">
            <div class="ps-pwa-titles">
                <h4 class="ps-pwa-title" id="psPwaTitleText"><?= htmlspecialchars($banner_title, ENT_QUOTES, 'UTF-8'); ?></h4>
                <p class="ps-pwa-subtitle" id="psPwaSubtitleText"><i class="fas fa-shield-heart me-1"></i> <?= htmlspecialchars($banner_subtitle, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>

        <!-- Descrição e Benefícios -->
        <p class="ps-pwa-desc" id="psPwaDescText">
            <?= nl2br(htmlspecialchars($banner_desc, ENT_QUOTES, 'UTF-8')); ?>
        </p>

        <!-- Chips de Vantagens -->
        <div class="ps-pwa-chips">
            <?php if (!empty($banner_chip1)): ?>
                <span class="ps-pwa-chip" id="psPwaChip1"><i class="fas fa-bolt"></i> <?= htmlspecialchars($banner_chip1, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
            <?php if (!empty($banner_chip2)): ?>
                <span class="ps-pwa-chip" id="psPwaChip2"><i class="fas fa-id-card"></i> <?= htmlspecialchars($banner_chip2, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
            <?php if (!empty($banner_chip3)): ?>
                <span class="ps-pwa-chip" id="psPwaChip3"><i class="fas fa-feather-pointed"></i> <?= htmlspecialchars($banner_chip3, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
        </div>

        <!-- Ações do Banner -->
        <div class="ps-pwa-actions">
            <button type="button" id="btnInstallPwaApp" class="ps-pwa-btn-install">
                <i class="fas fa-download"></i> <span id="psPwaBtnText"><?= htmlspecialchars($banner_btn_text, ENT_QUOTES, 'UTF-8'); ?></span>
            </button>
            <button type="button" id="btnLaterPwaApp" class="ps-pwa-btn-later">
                <span id="psPwaBtnLaterText"><?= htmlspecialchars($banner_btn_later, ENT_QUOTES, 'UTF-8'); ?></span>
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
            <h5 class="fw-bold mb-1" id="psIosModalTitle"><?= htmlspecialchars($ios_title, ENT_QUOTES, 'UTF-8'); ?></h5>
            <p class="text-muted small mb-0" id="psIosModalSubtitle"><?= htmlspecialchars($ios_subtitle, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <div class="ps-pwa-ios-step">
            <span class="ps-pwa-ios-step-num">1</span>
            <div class="small" id="psIosModalStep1">
                <?= htmlspecialchars($ios_step1, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>

        <div class="ps-pwa-ios-step">
            <span class="ps-pwa-ios-step-num">2</span>
            <div class="small" id="psIosModalStep2">
                <?= htmlspecialchars($ios_step2, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>

        <div class="ps-pwa-ios-step">
            <span class="ps-pwa-ios-step-num">3</span>
            <div class="small" id="psIosModalStep3">
                <?= htmlspecialchars($ios_step3, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>

        <div class="mt-3 pt-2 text-center">
            <button type="button" class="btn btn-primary w-100 py-2 fw-semibold rounded-3" id="btnClosePwaIosModal">
                <i class="fas fa-check me-1"></i> <?= htmlspecialchars($ios_btn_text, ENT_QUOTES, 'UTF-8'); ?>
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
    appName: '<?= htmlspecialchars($app_short_name, ENT_QUOTES, 'UTF-8'); ?>',
    successTitle: '<?= htmlspecialchars($success_title, ENT_QUOTES, 'UTF-8'); ?>',
    successMsg: '<?= htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8'); ?>'
};
</script>
