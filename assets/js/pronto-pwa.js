/**
 * Pronto Saúde - Gerenciador Inteligente do Aplicativo PWA e Banner de Instalação
 * Captura beforeinstallprompt, gerencia delay, cooldown, guia visual iOS e gatilhos globais
 * UTF-8 No BOM
 */

(function () {
    'use strict';

    // Configurações padrão
    var cfg = window.ProntoPwaConfig || {
        enabled: true,
        bannerEnabled: true,
        delay: 3,
        cooldown: 7,
        position: 'bottom-right',
        iosGuide: true,
        appName: 'Pronto Saúde'
    };

    if (!cfg.enabled) {
        return;
    }

    // 1. Registro Seguro do Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('./sw.js', { scope: './' })
                .then(function (reg) {
                    // Service worker ativo
                })
                .catch(function (err) {
                    console.warn('[Pronto PWA] Aviso ao registrar Service Worker:', err);
                });
        });
    }

    // Variáveis de Controle
    var deferredPrompt = null;
    var bannerEl = document.getElementById('psPwaInstallBanner');
    var iosModalEl = document.getElementById('psPwaIosModal');
    var btnInstall = document.getElementById('btnInstallPwaApp');
    var btnDismiss = document.getElementById('btnDismissPwaApp');
    var btnLater = document.getElementById('btnLaterPwaApp');
    var btnCloseIos = document.getElementById('btnClosePwaIosModal');

    // Detecção de Ambiente
    var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

    // Se já estiver em execução como aplicativo nativo/standalone, não exibe o banner
    if (isStandalone) {
        if (bannerEl) bannerEl.style.display = 'none';
        return;
    }

    // Função para verificar Cooldown (Reexibição)
    function isCooldownActive() {
        try {
            var dismissedUntil = localStorage.getItem('ps_pwa_dismissed_until');
            if (dismissedUntil && Date.now() < parseInt(dismissedUntil, 10)) {
                return true;
            }
            if (cfg.cooldown === 0 && sessionStorage.getItem('ps_pwa_session_dismissed')) {
                return true;
            }
        } catch (e) {}
        return false;
    }

    // Função para exibir o Banner com animação suave
    function showBanner() {
        if (!cfg.bannerEnabled || !bannerEl) return;
        if (isCooldownActive()) return;

        bannerEl.classList.add('ps-pwa-visible');
    }

    // Função para esconder o Banner e aplicar Cooldown
    function hideBanner(applyCooldown) {
        if (!bannerEl) return;
        bannerEl.classList.remove('ps-pwa-visible');

        if (applyCooldown) {
            try {
                if (cfg.cooldown > 0) {
                    var cooldownTime = Date.now() + (cfg.cooldown * 24 * 60 * 60 * 1000);
                    localStorage.setItem('ps_pwa_dismissed_until', cooldownTime.toString());
                } else {
                    sessionStorage.setItem('ps_pwa_session_dismissed', '1');
                }
            } catch (e) {}
        }
    }

    // 2. Intercepta o evento 'beforeinstallprompt' (Impede o prompt genérico feio do navegador)
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        window.deferredPwaPrompt = e;

        // Dispara evento para avisar qualquer componente do site
        window.dispatchEvent(new CustomEvent('prontoPwaCanInstall'));

        // Agenda exibição com base no tempo de delay configurado
        var delayMs = Math.max(0, parseInt(cfg.delay, 10)) * 1000;
        setTimeout(function () {
            showBanner();
        }, delayMs);
    });

    // Se for iOS e o banner estiver ativado, também agenda a exibição do banner
    if (isIOS && cfg.bannerEnabled && !isStandalone) {
        var iosDelayMs = Math.max(0, parseInt(cfg.delay, 10)) * 1000;
        setTimeout(function () {
            showBanner();
        }, iosDelayMs);
    }

    // 3. Ação do Botão Principal "Instalar Aplicativo"
    function triggerInstall() {
        // Se for iOS Safari, exibe o Guia Visual Especial
        if (isIOS) {
            if (iosModalEl) {
                hideBanner(false);
                iosModalEl.classList.add('ps-pwa-ios-visible');
            } else {
                if (window.Swal) {
                    Swal.fire({
                        title: 'Como instalar no iPhone ou iPad',
                        html: '<p class="text-start">1. Toque no ícone de <strong>Compartilhar</strong> <i class="fas fa-arrow-up-from-bracket text-primary"></i> na barra inferior do Safari.<br>2. Role a lista e toque em <strong>"Adicionar à Tela de Início"</strong> <i class="far fa-square-plus text-primary"></i>.<br>3. Toque em <strong>Adicionar</strong> no canto superior direito.</p>',
                        icon: 'info',
                        confirmButtonText: 'Entendi',
                        confirmButtonColor: '#0a6b74'
                    });
                }
            }
            return;
        }

        // Se capturamos o prompt do navegador (Chrome, Edge, Samsung Internet, etc.)
        if (deferredPrompt) {
            hideBanner(false);
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function (choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    if (window.Swal) {
                        Swal.fire({
                            title: cfg.successTitle || 'Aplicativo Instalado!',
                            text: cfg.successMsg || ('O aplicativo ' + cfg.appName + ' foi instalado com sucesso. Você já pode acessá-lo direto da sua tela inicial!'),
                            icon: 'success',
                            confirmButtonColor: '#0a6b74',
                            confirmButtonText: 'Excelente'
                        });
                    } else if (window.toastr) {
                        toastr.success(cfg.successMsg || 'Aplicativo instalado com sucesso!', cfg.successTitle || 'Tudo Pronto');
                    }
                    try {
                        localStorage.setItem('ps_pwa_installed', '1');
                    } catch (e) {}
                } else {
                    // Usuário dispensou no prompt do sistema operacional
                    hideBanner(true);
                }
                deferredPrompt = null;
                window.deferredPwaPrompt = null;
            });
            return;
        }

        // Fallback caso o navegador não suporte prompt programático
        if (window.Swal) {
            Swal.fire({
                title: 'Instalar Aplicativo ' + cfg.appName,
                html: '<p>Para instalar o aplicativo no seu dispositivo:<br>1. Clique no ícone de instalação <i class="fas fa-download text-primary"></i> na barra de endereços do seu navegador.<br>2. Confirme a instalação para ter acesso direto e rápido.</p>',
                icon: 'info',
                confirmButtonColor: '#0a6b74',
                confirmButtonText: 'Entendido'
            });
        }
    }

    if (btnInstall) {
        btnInstall.addEventListener('click', function (e) {
            e.preventDefault();
            triggerInstall();
        });
    }

    // 4. Ações de Fechar / Dispensar
    if (btnDismiss) {
        btnDismiss.addEventListener('click', function (e) {
            e.preventDefault();
            hideBanner(true);
        });
    }

    if (btnLater) {
        btnLater.addEventListener('click', function (e) {
            e.preventDefault();
            hideBanner(true);
        });
    }

    // Fechar Modal do iOS
    if (btnCloseIos) {
        btnCloseIos.addEventListener('click', function (e) {
            e.preventDefault();
            if (iosModalEl) iosModalEl.classList.remove('ps-pwa-ios-visible');
            hideBanner(true);
        });
    }

    if (iosModalEl) {
        iosModalEl.addEventListener('click', function (e) {
            if (e.target === iosModalEl) {
                iosModalEl.classList.remove('ps-pwa-ios-visible');
                hideBanner(true);
            }
        });
    }

    // 5. Notificação de Instalação Concluída pelo Sistema
    window.addEventListener('appinstalled', function () {
        hideBanner(false);
        try {
            localStorage.setItem('ps_pwa_installed', '1');
        } catch (e) {}
        if (window.toastr) {
            toastr.success('Pronto Saúde instalado com sucesso na sua área de trabalho!', 'App Pronto Saúde');
        }
    });

    // 6. Gatilho Global para Botões no Site (ex: no Menu, Rodapé ou Cards)
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('.btn-trigger-pwa-install, [data-pwa-install]');
        if (trigger) {
            e.preventDefault();
            if (deferredPrompt || isIOS) {
                triggerInstall();
            } else if (bannerEl) {
                bannerEl.classList.add('ps-pwa-visible');
            } else {
                triggerInstall();
            }
        }
    });

    // Torna funções utilitárias acessíveis globalmente
    window.ProntoPWA = {
        showBanner: showBanner,
        hideBanner: hideBanner,
        triggerInstall: triggerInstall,
        isInstalled: function () {
            return isStandalone || localStorage.getItem('ps_pwa_installed') === '1';
        }
    };
})();

