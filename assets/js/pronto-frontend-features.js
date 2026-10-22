/**
 * Pronto Saúde - Recursos Modernos do Frontend
 * PWA Service Worker, LGPD Glassmorphism, Popup Promocional & Lazy Loading Assíncrono
 */
(function() {
    'use strict';

    /* ==========================================================================
       1. PROGRESSIVE WEB APP (PWA) & INSTALAÇÃO
       ========================================================================== */
    function initPWA() {
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('./sw.js', { scope: './' })
                    .then(reg => {
                        // Service Worker registrado com sucesso
                    })
                    .catch(err => {
                        console.warn('[PWA] Falha no registro do Service Worker:', err);
                    });
            });
        }

        let deferredPrompt = null;
        const installBanner = document.getElementById('psPwaInstallToast');
        const btnInstall = document.getElementById('btnInstallPwaApp');
        const btnDismiss = document.getElementById('btnDismissPwaApp');

        window.addEventListener('beforeinstallprompt', e => {
            // Previne o infobar padrão do navegador
            e.preventDefault();
            deferredPrompt = e;

            // Se o usuário já dispensou nas últimas 48h, não exibe
            const dismissedAt = localStorage.getItem('ps_pwa_dismissed');
            if (dismissedAt && (Date.now() - parseInt(dismissedAt, 10)) < (48 * 3600 * 1000)) {
                return;
            }

            if (installBanner) {
                setTimeout(() => {
                    installBanner.classList.add('ps-pwa-show');
                }, 3000);
            }
        });

        if (btnInstall) {
            btnInstall.addEventListener('click', () => {
                if (!deferredPrompt) return;
                installBanner?.classList.remove('ps-pwa-show');
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(choiceResult => {
                    deferredPrompt = null;
                });
            });
        }

        if (btnDismiss) {
            btnDismiss.addEventListener('click', () => {
                installBanner?.classList.remove('ps-pwa-show');
                localStorage.setItem('ps_pwa_dismissed', Date.now().toString());
            });
        }
    }

    /* ==========================================================================
       2. BANNER MODERNO DE LGPD (GLASSMORPHISM & PRIVACIDADE)
       ========================================================================== */
    function initLGPD() {
        const consentKey = 'pronto_lgpd_consent_v2';
        const consent = localStorage.getItem(consentKey);
        const banner = document.getElementById('psLgpdBanner');
        if (!banner) return;

        if (!consent) {
            setTimeout(() => {
                banner.classList.add('ps-lgpd-visible');
            }, 1200);
        }

        const btnAccept = document.getElementById('btnAcceptLgpd');
        const btnPreferences = document.getElementById('btnLgpdPreferences');
        const modalPrefs = document.getElementById('psLgpdModal');
        const btnSavePrefs = document.getElementById('btnSaveLgpdPrefs');
        const btnCloseModal = document.getElementById('btnCloseLgpdModal');

        if (btnAccept) {
            btnAccept.addEventListener('click', function() {
                localStorage.setItem(consentKey, JSON.stringify({
                    analytics: true,
                    functional: true,
                    accepted_at: new Date().toISOString()
                }));
                banner.classList.remove('ps-lgpd-visible');
            });
        }

        if (btnPreferences && modalPrefs) {
            btnPreferences.addEventListener('click', function() {
                modalPrefs.classList.add('ps-modal-open');
            });
        }

        if (btnCloseModal && modalPrefs) {
            btnCloseModal.addEventListener('click', function() {
                modalPrefs.classList.remove('ps-modal-open');
            });
        }

        if (btnSavePrefs && modalPrefs) {
            btnSavePrefs.addEventListener('click', function() {
                const analyticsAllowed = document.getElementById('lgpdOptAnalytics')?.checked ?? true;
                localStorage.setItem(consentKey, JSON.stringify({
                    analytics: analyticsAllowed,
                    functional: true,
                    accepted_at: new Date().toISOString()
                }));
                modalPrefs.classList.remove('ps-modal-open');
                banner.classList.remove('ps-lgpd-visible');
            });
        }
    }

    /* ==========================================================================
       3. BANNER DE POPUP PROMOCIONAL / INFORMATIVO NA HOME
       ========================================================================== */
    function initPopup() {
        const popupModal = document.getElementById('psPromoPopup');
        if (!popupModal) return;

        const popupStatus = popupModal.getAttribute('data-active') === '1';
        if (!popupStatus) return;

        const expireHours = parseInt(popupModal.getAttribute('data-expire-hours') || '24', 10);
        const delayMs = parseInt(popupModal.getAttribute('data-delay') || '2000', 10);
        const storageKey = 'ps_promo_popup_closed_at';

        const lastClosed = localStorage.getItem(storageKey);
        if (lastClosed) {
            const elapsed = Date.now() - parseInt(lastClosed, 10);
            if (elapsed < (expireHours * 3600 * 1000)) {
                return; // Ainda dentro da janela de não exibição
            }
        }

        setTimeout(() => {
            popupModal.classList.add('ps-popup-active');
            document.body.classList.add('ps-popup-lock');
        }, delayMs);

        // Ações de fechar
        function closePopup() {
            popupModal.classList.remove('ps-popup-active');
            document.body.classList.remove('ps-popup-lock');
            localStorage.setItem(storageKey, Date.now().toString());
        }

        popupModal.querySelectorAll('.ps-close-popup').forEach(btn => {
            btn.addEventListener('click', closePopup);
        });

        // Fechar ao clicar no backdrop escuro
        popupModal.addEventListener('click', function(e) {
            if (e.target === popupModal) {
                closePopup();
            }
        });

        // Tecla ESC fecha
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && popupModal.classList.contains('ps-popup-active')) {
                closePopup();
            }
        });
    }

    /* ==========================================================================
       4. CARREGAMENTO ASSÍNCRONO & LAZY LOADING DE IMAGENS E VÍDEOS
       ========================================================================== */
    function initLazyLoading() {
        // Suporte nativo a loading="lazy" com transição de opacidade suave
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            if (!img.hasAttribute('loading') && !img.closest('.main-slider') && !img.closest('.logo')) {
                img.setAttribute('loading', 'lazy');
                img.setAttribute('decoding', 'async');
            }
            if (img.complete) {
                img.classList.add('ps-img-loaded');
            } else {
                img.addEventListener('load', () => img.classList.add('ps-img-loaded'));
            }
        });

        // IntersectionObserver para elementos de animação de entrada suave
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('ps-in-viewport');
                        obs.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '0px 0px -50px 0px', threshold: 0.1 });

            document.querySelectorAll('.service-card-box, .doctor-card-item, .pricing-item, .blog-card-item, .feature-card-item').forEach(el => {
                el.classList.add('ps-fade-up-element');
                observer.observe(el);
            });
        }
    }

    /* Inicialização Geral ao Carregar o DOM */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initPWA();
            initLGPD();
            initPopup();
            initLazyLoading();
        });
    } else {
        initPWA();
        initLGPD();
        initPopup();
        initLazyLoading();
    }
})();
