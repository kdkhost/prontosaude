/**
 * Pronto Saúde - Gestor Global de Atalhos Rápidos (Switches Toggle AJAX)
 * Gerencia a ativação/desativação instantânea com suporte a DataTables e SPA
 */
(function () {
    'use strict';

    function initProntoSwitches(context) {
        context = context || document;

        // Delegação de eventos no document para garantir que funcione em paginação do DataTables
        if (window.__prontoSwitchesInitialized) return;
        window.__prontoSwitchesInitialized = true;

        document.addEventListener('change', function (e) {
            const toggle = e.target.closest('.pronto-status-switch, .switch-page-status, .switch-plan-status');
            if (!toggle) return;

            const entity = toggle.getAttribute('data-entity') || 'page';
            const id = toggle.getAttribute('data-id') || toggle.getAttribute('data-page-id');
            const itemName = toggle.getAttribute('data-name') || toggle.getAttribute('data-page-name') || 'Item';
            const isChecked = toggle.checked;
            const newStatus = isChecked ? (toggle.getAttribute('data-status-type') === 'int' ? 1 : 'Active') : (toggle.getAttribute('data-status-type') === 'int' ? 0 : 'Inactive');
            
            // Localiza badge de status associado
            const badgeId = toggle.getAttribute('data-badge-target');
            let badge = null;
            if (badgeId) {
                badge = document.querySelector(badgeId);
            }
            if (!badge) {
                badge = document.querySelector('#badge_status_' + id) || 
                        document.querySelector('.status-badge-' + id) ||
                        toggle.closest('tr')?.querySelector('.status-badge');
            }

            const row = toggle.closest('tr') || toggle.closest('.page-row');

            // Feedback visual de salvamento imediato
            if (badge) {
                badge.setAttribute('data-prev-text', badge.textContent);
                badge.setAttribute('data-prev-class', badge.className);
                badge.textContent = 'Salvando...';
                badge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small';
            }
            toggle.disabled = true;

            const formData = new FormData();
            formData.append('entity', entity);
            formData.append('id', id);
            formData.append('status', newStatus);

            // Tenta enviar para o endpoint unificado toggle-ajax.php
            fetch('toggle-ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Falha na resposta do servidor (HTTP ' + res.status + ')');
                }
                return res.json();
            })
            .then(data => {
                toggle.disabled = false;
                if (data.success) {
                    // Atualiza o badge
                    if (badge) {
                        if (data.is_active) {
                            badge.textContent = toggle.getAttribute('data-active-text') || 'Ativo no Site';
                            badge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill small status-badge';
                            if (row) row.setAttribute('data-status', 'active');
                        } else {
                            badge.textContent = toggle.getAttribute('data-inactive-text') || 'Desativado';
                            badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small status-badge';
                            if (row) row.setAttribute('data-status', 'inactive');
                        }
                    }

                    // Notificação suave ao usuário
                    const titleNotice = data.is_active ? 'Status Atualizado' : 'Item Desativado';
                    if (window.notificar) {
                        window.notificar(data.is_active ? 'success' : 'warning', data.message, titleNotice);
                    } else if (typeof toastr !== 'undefined') {
                        toastr[data.is_active ? 'success' : 'warning'](data.message);
                    }

                    // Se houver função de atualizar contadores na página (ex: admin/page.php)
                    if (typeof window.updateSummaryCounters === 'function') {
                        window.updateSummaryCounters();
                    }
                } else {
                    // Reverte o switch
                    toggle.checked = !isChecked;
                    if (badge) {
                        badge.textContent = badge.getAttribute('data-prev-text') || (toggle.checked ? 'Ativo' : 'Desativado');
                        badge.className = badge.getAttribute('data-prev-class') || '';
                    }
                    const errMsg = data.message || 'Não foi possível alterar o status.';
                    if (window.notificar) {
                        window.notificar('error', errMsg, 'Erro');
                    } else if (typeof toastr !== 'undefined') {
                        toastr.error(errMsg);
                    }
                }
            })
            .catch(err => {
                toggle.disabled = false;
                toggle.checked = !isChecked;
                if (badge) {
                    badge.textContent = badge.getAttribute('data-prev-text') || (toggle.checked ? 'Ativo' : 'Desativado');
                    badge.className = badge.getAttribute('data-prev-class') || '';
                }
                const errDetail = err.message || 'Falha na comunicação com o servidor.';
                if (window.notificar) {
                    window.notificar('error', 'Falha na comunicação com o servidor. Verifique sua conexão.', 'Erro de Conexão');
                } else if (typeof toastr !== 'undefined') {
                    toastr.error('Falha na comunicação com o servidor.');
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initProntoSwitches(document));
    } else {
        initProntoSwitches(document);
    }

    window.initProntoSwitches = initProntoSwitches;
})();
