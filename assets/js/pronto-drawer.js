/**
 * Pronto Saúde - Menu Deslizante Offcanvas (Slide da esquerda para a direita)
 * Movimento suave com aceleração de hardware e suporte a touch gestures (swipe)
 */
(function() {
    'use strict';

    function initDrawer() {
        var openBtn = document.getElementById('btnOpenMobileDrawer');
        var closeBtn = document.getElementById('btnCloseMobileDrawer');
        var backdrop = document.getElementById('psDrawerBackdrop');
        var drawer = document.getElementById('psOffcanvasDrawer');
        var body = document.body;

        if (!drawer) return;

        function openDrawer() {
            body.classList.add('ps-drawer-open');
            if (backdrop) backdrop.classList.add('active');
        }

        function closeDrawer() {
            body.classList.remove('ps-drawer-open');
            if (backdrop) backdrop.classList.remove('active');
        }

        // Listener de Abertura
        if (openBtn) {
            openBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openDrawer();
            });
        }

        // Listener de Fechamento via Botão X
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeDrawer();
            });
        }

        // Listener de Fechamento via Backdrop
        if (backdrop) {
            backdrop.addEventListener('click', function(e) {
                e.preventDefault();
                closeDrawer();
            });
        }

        // Fechar com tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && body.classList.contains('ps-drawer-open')) {
                closeDrawer();
            }
        });

        function toggleSubmenuItem(parentItem) {
            if (!parentItem) return;
            var wasOpen = parentItem.classList.contains('open');
            
            // Fechar outros submenus abertos no mesmo nível (modo acordeão limpo)
            var siblings = parentItem.parentElement.querySelectorAll('.ps-drawer-item.open');
            siblings.forEach(function(sib) {
                if (sib !== parentItem) {
                    sib.classList.remove('open');
                }
            });

            if (!wasOpen) {
                parentItem.classList.add('open');
            } else {
                parentItem.classList.remove('open');
            }
        }

        // Toggle de Submenus via Botão Chevron
        var subToggles = drawer.querySelectorAll('.ps-drawer-toggle-sub');
        subToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSubmenuItem(this.closest('.ps-drawer-item'));
            });
        });

        // Toggle de Submenus ao clicar no texto do link pai caso seja agregador (#)
        var parentLinks = drawer.querySelectorAll('.ps-drawer-link.has-children');
        parentLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                var href = this.getAttribute('href');
                if (!href || href === '#' || href.indexOf('javascript:') === 0) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSubmenuItem(this.closest('.ps-drawer-item'));
                }
            });
        });

        // Fechar drawer ao clicar em links finais de navegação
        var navLinks = drawer.querySelectorAll('.ps-drawer-link:not(.has-children), .ps-drawer-sublink');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                setTimeout(closeDrawer, 180);
            });
        });

        // Suporte a Touch Swipe para fechar deslizando para a esquerda
        var touchStartX = 0;
        var touchStartY = 0;

        drawer.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });

        drawer.addEventListener('touchend', function(e) {
            var touchEndX = e.changedTouches[0].screenX;
            var touchEndY = e.changedTouches[0].screenY;
            var diffX = touchEndX - touchStartX;
            var diffY = touchEndY - touchStartY;

            // Se deslizou mais de 50px para a esquerda e o movimento horizontal foi maior que o vertical
            if (diffX < -50 && Math.abs(diffX) > Math.abs(diffY)) {
                closeDrawer();
            }
        }, { passive: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDrawer);
    } else {
        initDrawer();
    }
})();
