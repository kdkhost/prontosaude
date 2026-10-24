/**
 * Pronto Saúde - Biblioteca Global de Interações, SweetAlert2 e Toastr
 * Padrão: AdminLTE 4 + Bootstrap 5.3 + PHP 8.4
 */

(function () {
  'use strict';

  // ==========================================
  // 1. GERENCIADOR DE TEMA DARK / LIGHT (AdminLTE 4)
  // ==========================================
  const getStoredTheme = () => localStorage.getItem('theme');
  const setStoredTheme = theme => localStorage.setItem('theme', theme);

  const getPreferredTheme = () => {
    const storedTheme = getStoredTheme();
    if (storedTheme) {
      return storedTheme;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };

  const setTheme = theme => {
    const resolvedTheme = (theme === 'auto')
      ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
      : theme;
    document.documentElement.setAttribute('data-bs-theme', resolvedTheme);
    
    // Sincroniza a barra lateral se existir
    const sidebar = document.querySelector('#mainAppSidebar');
    if (sidebar) {
      sidebar.setAttribute('data-bs-theme', resolvedTheme);
    }

    // Dispara evento global para componentes reativos (ApexCharts, DataTables, etc.)
    window.dispatchEvent(new CustomEvent('prontoThemeChanged', { detail: { theme: resolvedTheme, raw: theme } }));
  };

  setTheme(getPreferredTheme());

  const showActiveTheme = (theme, focus = false) => {
    const themeSwitcher = document.querySelector('#bd-theme');
    if (!themeSwitcher) return;

    const themeSwitcherText = document.querySelector('#bd-theme-text');
    const activeThemeIcon = document.querySelector('.theme-icon-active');
    const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`);

    if (!btnToActive) return;

    document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
      element.classList.remove('active');
      element.setAttribute('aria-pressed', 'false');
    });

    btnToActive.classList.add('active');
    btnToActive.setAttribute('aria-pressed', 'true');

    if (activeThemeIcon) {
      activeThemeIcon.className = 'bi my-1 theme-icon-active ' + (
        theme === 'light' ? 'bi-sun-fill text-warning' :
        theme === 'dark' ? 'bi-moon-stars-fill text-info' : 'bi-circle-half text-secondary'
      );
    }

    if (focus) {
      themeSwitcher.focus();
    }
  };

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    const storedTheme = getStoredTheme();
    if (storedTheme !== 'light' && storedTheme !== 'dark') {
      setTheme(getPreferredTheme());
    }
  });

  window.addEventListener('DOMContentLoaded', () => {
    showActiveTheme(getPreferredTheme());

    document.querySelectorAll('[data-bs-theme-value]').forEach(toggle => {
      toggle.addEventListener('click', () => {
        const theme = toggle.getAttribute('data-bs-theme-value');
        setStoredTheme(theme);
        setTheme(theme);
        showActiveTheme(theme, true);
        
        // Atualiza gráficos ApexCharts caso existam
        if (window.ApexCharts && window.renderDashboardCharts) {
          window.renderDashboardCharts(theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches));
        }
      });
    });
  });

  // ==========================================
  // 2. CONFIGURAÇÃO GLOBAL DO TOASTR
  // ==========================================
  if (typeof toastr !== 'undefined') {
    toastr.options = {
      closeButton: true,
      debug: false,
      newestOnTop: true,
      progressBar: true,
      positionClass: 'toast-top-right',
      preventDuplicates: true,
      onclick: null,
      showDuration: '300',
      hideDuration: '600',
      timeOut: '4500',
      extendedTimeOut: '1500',
      showEasing: 'swing',
      hideEasing: 'linear',
      showMethod: 'fadeIn',
      hideMethod: 'fadeOut'
    };

    let lastNotifKey = '';
    let lastNotifTime = 0;

    window.notificar = function (tipo, mensagem, titulo) {
      titulo = titulo || '';
      const now = Date.now();
      const currentKey = `${tipo}|${titulo}|${mensagem}`;

      // Suprime notificações duplicadas disparadas dentro de 1.5s
      if (lastNotifKey === currentKey && (now - lastNotifTime) < 1500) {
        return;
      }
      lastNotifKey = currentKey;
      lastNotifTime = now;

      switch (tipo) {
        case 'success':
        case 'sucesso':
          toastr.success(mensagem, titulo || 'Sucesso!');
          break;
        case 'error':
        case 'erro':
          toastr.error(mensagem, titulo || 'Atenção!');
          break;
        case 'warning':
        case 'aviso':
          toastr.warning(mensagem, titulo || 'Aviso');
          break;
        default:
          toastr.info(mensagem, titulo || 'Informação');
          break;
      }
    };
  }

  // ==========================================
  // 3. INTERCEPTAÇÃO GLOBAL DE ALERT() E CONFIRM() NATIVOS
  // ==========================================
  if (typeof Swal !== 'undefined') {
    window.alert = function (mensagem) {
      Swal.fire({
        title: 'Informação',
        html: mensagem,
        icon: 'info',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#0a6b74',
        customClass: {
          confirmButton: 'btn btn-primary px-4'
        },
        buttonsStyling: false
      });
    };

    window.confirm = function (mensagem) {
      console.warn('Chamada nativa a confirm() interceptada. Utilize SweetAlert2 assíncrono diretamente.');
      Swal.fire({
        title: 'Confirmação',
        html: mensagem,
        icon: 'warning',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0a6b74',
        customClass: {
          confirmButton: 'btn btn-primary px-4'
        },
        buttonsStyling: false
      });
      return false;
    };
  }

  // ==========================================
  // 4. CONFIRMAÇÕES DE EXCLUSÃO E LOGOUT COM SWEETALERT2
  // ==========================================
  document.addEventListener('DOMContentLoaded', function () {
    // Converter alertas existentes na tela (.callout) em Toastr
    const callouts = document.querySelectorAll('.callout');
    callouts.forEach(function (c) {
      const texto = c.innerText.trim();
      if (!texto) return;
      if (c.classList.contains('callout-success')) {
        window.notificar && window.notificar('success', texto, 'Operação Concluída');
      } else if (c.classList.contains('callout-danger')) {
        window.notificar && window.notificar('error', texto, 'Erro Identificado');
      } else if (c.classList.contains('callout-warning')) {
        window.notificar && window.notificar('warning', texto, 'Atenção');
      } else {
        window.notificar && window.notificar('info', texto, 'Informação');
      }
    });

    // Confirmação de Logout Elegante
    const logoutBtns = document.querySelectorAll('#btn-header-logout, #btn-sidebar-logout, a[href="logout.php"]');
    logoutBtns.forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const url = this.getAttribute('href');
        Swal.fire({
          title: 'Deseja encerrar a sessão?',
          text: 'Você precisará informar seu e-mail e senha para acessar novamente o painel.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i> Sim, deslogar',
          cancelButtonText: 'Permanecer conectado',
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-secondary'
          },
          buttonsStyling: false
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = url;
          }
        });
      });
    });

    // Interceptação Global de Botões de Exclusão (data-target="#confirm-delete" ou links de exclusão)
    document.addEventListener('click', function (e) {
      const target = e.target.closest('a[data-href], button[data-href], a[href*="-delete.php"], [data-target="#confirm-delete"]');
      if (!target) return;

      const deleteUrl = target.getAttribute('data-href') || target.getAttribute('href');
      if (!deleteUrl || deleteUrl === '#' || deleteUrl.startsWith('javascript:')) return;

      e.preventDefault();
      e.stopPropagation();

      Swal.fire({
        title: 'Confirmar exclusão?',
        text: 'Esta ação não poderá ser revertida. Deseja realmente excluir este registro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Sim, excluir!',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        customClass: {
          confirmButton: 'btn btn-danger me-2',
          cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        focusCancel: true
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = deleteUrl;
        }
      });
    }, true);

    // Suporte Global Imediato para Alternância de Abas em qualquer tela
    document.addEventListener('click', function (e) {
      const tabTrigger = e.target.closest(
        '[data-toggle="tab"], [data-bs-toggle="tab"], [data-toggle="pill"], [data-bs-toggle="pill"], ' +
        '.nav-tabs button, .nav-tabs a, .nav-pills button, .nav-pills a, ' +
        '.card-outline-tabs .nav-link, .nav-tabs-custom .nav-link, .nav-tabs-custom li > a'
      );
      if (!tabTrigger) return;

      const targetSelector = tabTrigger.getAttribute('data-bs-target') || 
                             tabTrigger.getAttribute('data-target') || 
                             tabTrigger.getAttribute('href');

      if (!targetSelector || !targetSelector.startsWith('#')) return;

      e.preventDefault();
      e.stopPropagation();

      if (window.switchProntoTab) {
        window.switchProntoTab(tabTrigger);
      } else {
        const targetPane = document.querySelector(targetSelector);
        if (!targetPane) return;

        const navList = tabTrigger.closest('.nav-tabs, .nav-pills, .nav, .nav-tabs-custom') || tabTrigger.parentElement.parentElement;
        if (navList) {
          navList.querySelectorAll('.nav-link, button, a, li, .nav-item').forEach(el => {
            el.classList.remove('active');
            if (el.tagName === 'BUTTON' || el.tagName === 'A') el.setAttribute('aria-selected', 'false');
          });
        }

        tabTrigger.classList.add('active');
        tabTrigger.setAttribute('aria-selected', 'true');
        const parentLi = tabTrigger.closest('li, .nav-item');
        if (parentLi) parentLi.classList.add('active');

        const tabContent = targetPane.closest('.tab-content') || targetPane.parentElement;
        if (tabContent) {
          tabContent.querySelectorAll('.tab-pane').forEach(p => {
            p.classList.remove('active', 'show');
            p.style.setProperty('display', 'none', 'important');
          });
        }

        targetPane.classList.add('active', 'show');
        targetPane.style.setProperty('display', 'block', 'important');
        targetPane.style.setProperty('opacity', '1', 'important');
        targetPane.style.setProperty('visibility', 'visible', 'important');
        try { history.replaceState(null, null, targetSelector); } catch (err) {}
      }
    }, true);
  });

})();
