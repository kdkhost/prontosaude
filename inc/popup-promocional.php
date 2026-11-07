<?php
/**
 * Componente de Popup Promocional & Avisos da Clínica (Frontend)
 * Totalmente configurável via Painel Administrativo em /admin/popup.php
 */
if (($popup_status ?? 0) != 1) {
    return;
}

$p_title   = trim($popup_title ?? '');
$p_text    = trim($popup_text ?? '');
$p_image   = trim($popup_image ?? '');
$p_btn_txt = trim($popup_button_text ?? 'Saiba Mais');
$p_btn_url = trim($popup_button_url ?? '');
$p_delay   = max(500, (int)($popup_delay ?? 2000));
$p_expire  = max(1, (int)($popup_expire_hours ?? 24));

// Se não houver nem título, nem texto, nem imagem, não há o que exibir
if (empty($p_title) && empty($p_text) && empty($p_image)) {
    return;
}

// Verifica se a URL do botão é externa ou WhatsApp
$is_external_btn = (!empty($p_btn_url) && (strpos($p_btn_url, 'http://') === 0 || strpos($p_btn_url, 'https://') === 0 || strpos($p_btn_url, 'wa.me') !== false));
$btn_target = $is_external_btn ? 'target="_blank" rel="noopener noreferrer"' : '';
?>

<!-- Estilos Especiais do Popup Promocional -->
<style>
#modalPopupPromocional .modal-content {
    border-radius: 1.25rem;
    overflow: hidden;
    border: 1px solid var(--bs-border-color-translucent, rgba(0, 0, 0, 0.1));
    box-shadow: 0 1.25rem 2.5rem rgba(0, 0, 0, 0.25);
    background-color: var(--bs-body-bg, #ffffff);
    color: var(--bs-body-color, #1e293b);
}
#modalPopupPromocional .popup-banner-wrapper {
    position: relative;
    max-height: 280px;
    overflow: hidden;
    background-color: rgba(10, 107, 116, 0.08);
}
#modalPopupPromocional .popup-banner-img {
    width: 100%;
    height: auto;
    max-height: 280px;
    object-fit: cover;
    display: block;
}
#modalPopupPromocional .popup-close-circle {
    position: absolute;
    top: 14px;
    right: 14px;
    z-index: 10;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: rgba(0, 0, 0, 0.55);
    color: #ffffff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    cursor: pointer;
    backdrop-filter: blur(4px);
}
#modalPopupPromocional .popup-close-circle:hover {
    background-color: rgba(0, 0, 0, 0.85);
    transform: scale(1.08);
    color: #ffffff;
}
#modalPopupPromocional .popup-badge {
    background: linear-gradient(135deg, #0a6b74 0%, #0d828d 100%);
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding: 0.35rem 0.85rem;
    border-radius: 50rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
#modalPopupPromocional .popup-title {
    font-weight: 800;
    font-size: 1.35rem;
    line-height: 1.3;
    color: var(--bs-heading-color, inherit);
    margin-top: 0.5rem;
    margin-bottom: 0.75rem;
}
#modalPopupPromocional .popup-desc {
    font-size: 0.95rem;
    line-height: 1.6;
    color: var(--bs-body-color, #475569);
    margin-bottom: 1.25rem;
}
#modalPopupPromocional .btn-popup-cta {
    background: linear-gradient(135deg, #0a6b74 0%, #08555c 100%);
    color: #ffffff;
    border: none;
    border-radius: 50rem;
    padding: 0.65rem 1.6rem;
    font-weight: 700;
    font-size: 0.92rem;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(10, 107, 116, 0.3);
}
#modalPopupPromocional .btn-popup-cta:hover {
    background: linear-gradient(135deg, #0d828d 0%, #0a6b74 100%);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(10, 107, 116, 0.4);
}
#modalPopupPromocional .btn-popup-dismiss {
    color: var(--bs-secondary-color, #64748b);
    background: transparent;
    border: none;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0.5rem 1rem;
    text-decoration: none;
    cursor: pointer;
    transition: color 0.15s ease;
}
#modalPopupPromocional .btn-popup-dismiss:hover {
    color: var(--bs-body-color, #1e293b);
}
</style>

<!-- Modal Bootstrap 5 do Popup Promocional -->
<div class="modal fade" id="modalPopupPromocional" tabindex="-1" aria-labelledby="modalPopupPromocionalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content shadow-lg position-relative">
            
            <!-- Botão de Fechar Rápido (X) Flutuante -->
            <button type="button" class="popup-close-circle" data-bs-dismiss="modal" aria-label="Fechar" title="Fechar aviso">
                <i class="fas fa-times"></i>
            </button>

            <!-- Banner Ilustrativo (Se configurado) -->
            <?php if (!empty($p_image)): ?>
                <div class="popup-banner-wrapper">
                    <?php if (!empty($p_btn_url)): ?>
                        <a href="<?= htmlspecialchars($p_btn_url, ENT_QUOTES, 'UTF-8'); ?>" <?= $btn_target; ?> class="d-block popup-cta-trigger">
                            <img src="<?= htmlspecialchars($p_image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($p_title ?: 'Aviso Pronto Saúde', ENT_QUOTES, 'UTF-8'); ?>" class="popup-banner-img">
                        </a>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($p_image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($p_title ?: 'Aviso Pronto Saúde', ENT_QUOTES, 'UTF-8'); ?>" class="popup-banner-img">
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Conteúdo Textual e Ações -->
            <div class="p-4 <?= empty($p_image) ? 'pt-5' : ''; ?> text-center">
                <div class="mb-2">
                    <span class="popup-badge">
                        <i class="fas fa-bullhorn"></i> Destaque Pronto Saúde
                    </span>
                </div>

                <?php if (!empty($p_title)): ?>
                    <h4 class="popup-title" id="modalPopupPromocionalLabel">
                        <?= htmlspecialchars($p_title, ENT_QUOTES, 'UTF-8'); ?>
                    </h4>
                <?php endif; ?>

                <?php if (!empty($p_text)): ?>
                    <div class="popup-desc">
                        <?= nl2br(htmlspecialchars($p_text, ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                <?php endif; ?>

                <div class="d-flex flex-column flex-sm-row align-items-center justify-content-center gap-2 mt-3 pt-2 border-top border-secondary-subtle">
                    <?php if (!empty($p_btn_url)): ?>
                        <a href="<?= htmlspecialchars($p_btn_url, ENT_QUOTES, 'UTF-8'); ?>" <?= $btn_target; ?> class="btn-popup-cta w-100 w-sm-auto popup-cta-trigger">
                            <span><?= htmlspecialchars($p_btn_txt, ENT_QUOTES, 'UTF-8'); ?></span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                    <button type="button" class="btn-popup-dismiss" data-bs-dismiss="modal">
                        Agora não, fechar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Script de Controle de Abertura e Expiração (localStorage) -->
<script>
(function() {
    var POPUP_DELAY_MS    = <?= (int)$p_delay; ?>;
    var EXPIRE_HOURS      = <?= (int)$p_expire; ?>;
    var STORAGE_KEY       = 'pronto_saude_popup_dismissed_until';

    function initPopupPromocional() {
        var modalEl = document.getElementById('modalPopupPromocional');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        // Verifica se o visitante já fechou o popup recentemente
        var dismissedUntil = localStorage.getItem(STORAGE_KEY);
        var now = new Date().getTime();

        if (dismissedUntil && now < parseInt(dismissedUntil, 10)) {
            // Ainda está no período de silêncio/expiração
            return;
        }

        // Função para registrar o fechamento e gravar expiração
        function registrarFechamento() {
            var expireTime = new Date().getTime() + (EXPIRE_HOURS * 60 * 60 * 1000);
            try {
                localStorage.setItem(STORAGE_KEY, expireTime.toString());
            } catch (e) {}
        }

        // Aguarda o delay configurado no admin para abrir suavemente
        setTimeout(function() {
            try {
                var bsModal = new bootstrap.Modal(modalEl, {
                    backdrop: true,
                    keyboard: true
                });
                bsModal.show();

                // Registra fechamento ao fechar o modal
                modalEl.addEventListener('hidden.bs.modal', function () {
                    registrarFechamento();
                }, { once: true });

                // Registra fechamento se clicar no botão de CTA
                var ctaButtons = modalEl.querySelectorAll('.popup-cta-trigger');
                ctaButtons.forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        registrarFechamento();
                    });
                });
            } catch (err) {
                console.warn('Erro ao inicializar modal promocional:', err);
            }
        }, POPUP_DELAY_MS);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPopupPromocional);
    } else {
        initPopupPromocional();
    }
})();
</script>

