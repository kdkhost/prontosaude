/**
 * Pronto Saúde - Módulo de Interface e Interatividade do Centro de Segurança & WAF
 */

(function() {
  'use strict';

  function initSecurityPanel(container) {
    container = container || document;
    const panel = container.querySelector('#securityTabsContent');
    if (!panel) return;

    // 0. Suporte Robusto para Troca Suave de Abas
    const tabButtons = container.querySelectorAll('#securityTabs button[data-bs-toggle="pill"], #securityTabs button[data-bs-toggle="tab"]');
    tabButtons.forEach(btn => {
      if (btn.dataset.tabBound === 'true') return;
      btn.dataset.tabBound = 'true';

      btn.addEventListener('click', function(e) {
        e.preventDefault();
        tabButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const targetSelector = this.getAttribute('data-bs-target');
        const targetPane = container.querySelector(targetSelector);
        if (targetPane) {
          container.querySelectorAll('#securityTabsContent .tab-pane').forEach(p => {
            p.classList.remove('show', 'active');
          });
          targetPane.classList.add('show', 'active');
        }
      });
    });

    // 1. Alternador Instantâneo via AJAX para os Switches de Segurança
    const toggles = container.querySelectorAll('.sec-toggle');
    toggles.forEach(toggle => {
      if (toggle.dataset.secBound === 'true') return;
      toggle.dataset.secBound = 'true';

      toggle.addEventListener('change', function() {
        const chave = this.dataset.chave;
        const status = this.checked ? 1 : 0;
        const elem = this;

        fetch('seguranca-ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: new URLSearchParams({
            action: 'update_toggle',
            chave: chave,
            status: status
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            if (window.notificar) {
              window.notificar('success', data.message, 'Segurança Atualizada');
            }
          } else {
            elem.checked = !elem.checked;
            if (window.notificar) {
              window.notificar('error', data.message || 'Erro ao alterar parâmetro.', 'Erro');
            }
          }
        })
        .catch(err => {
          elem.checked = !elem.checked;
          if (window.notificar) {
            window.notificar('error', 'Falha na comunicação com o servidor.', 'Erro de Conexão');
          }
        });
      });
    });

    // 2. Submissão de Formulários de Parâmetros de Login e Listas de IP
    ['form-security-settings', 'form-ip-lists'].forEach(formId => {
      const formElem = container.querySelector('#' + formId);
      if (!formElem || formElem.dataset.secBound === 'true') return;
      formElem.dataset.secBound = 'true';

      formElem.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(formElem);

        fetch('seguranca-ajax.php', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            if (window.notificar) {
              window.notificar('success', data.message, 'Configurações Salvas');
            } else if (typeof toastr !== 'undefined') {
              toastr.success(data.message);
            }
          } else {
            if (window.notificar) {
              window.notificar('error', data.message || 'Erro ao salvar configurações.', 'Atenção');
            } else if (typeof toastr !== 'undefined') {
              toastr.error(data.message || 'Erro ao salvar configurações.');
            }
          }
        })
        .catch(err => {
          if (window.notificar) {
            window.notificar('error', 'Falha na conexão com o servidor.', 'Erro');
          }
        });
      });
    });

    // 3. Desbloquear IP Dinamicamente com Confirmação SweetAlert2
    const unblockBtns = container.querySelectorAll('.btn-unblock-ip');
    unblockBtns.forEach(btn => {
      if (btn.dataset.secBound === 'true') return;
      btn.dataset.secBound = 'true';

      btn.addEventListener('click', function() {
        const ip = this.dataset.ip;
        const row = this.closest('tr');

        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: 'Desbloquear IP?',
            text: `Deseja realmente remover o bloqueio ativo para o IP ${ip}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0a6b74',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, desbloquear',
            cancelButtonText: 'Cancelar'
          }).then(result => {
            if (result.isConfirmed) {
              executarDesbloqueio(ip, row);
            }
          });
        } else {
          executarDesbloqueio(ip, row);
        }
      });
    });

    function executarDesbloqueio(ip, row) {
      fetch('seguranca-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          action: 'unblock_ip',
          ip: ip
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          if (window.notificar) {
            window.notificar('success', data.message, 'IP Desbloqueado');
          }
          if (row) {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
          }
        } else {
          if (window.notificar) {
            window.notificar('error', data.message, 'Erro');
          }
        }
      });
    }

    // 4. Limpar Logs de Auditoria com SweetAlert2
    const btnClearLogs = container.querySelector('#btn-clear-logs');
    if (btnClearLogs && btnClearLogs.dataset.secBound !== 'true') {
      btnClearLogs.dataset.secBound = 'true';
      btnClearLogs.addEventListener('click', function() {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: 'Limpar todos os registros de auditoria?',
            text: 'Esta ação removerá permanentemente o histórico de ataques e incidentes registrados.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, limpar tudo',
            cancelButtonText: 'Cancelar'
          }).then(result => {
            if (result.isConfirmed) {
              fetch('seguranca-ajax.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                  'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({ action: 'clear_logs' })
              })
              .then(res => res.json())
              .then(data => {
                if (data.success) {
                  if (window.notificar) {
                    window.notificar('success', data.message, 'Auditoria Limpa');
                  } else if (typeof toastr !== 'undefined') {
                    toastr.success(data.message);
                  }
                  if (window.loadPageSPA) {
                    window.loadPageSPA('seguranca.php', false);
                  } else {
                    location.reload();
                  }
                }
              });
            }
          });
        }
      });
    }
  }

  window.initSecurityPanel = initSecurityPanel;

  document.addEventListener('DOMContentLoaded', function() {
    initSecurityPanel(document);
  });
})();
