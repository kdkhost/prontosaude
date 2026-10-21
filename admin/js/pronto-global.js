/**
 * Pronto Saúde - Integração Global SweetAlert2 & Toastr Notifications
 * Padronizado para PT-BR, com interceptação global de alert() e confirm()
 */
(function() {
    'use strict';

    // Configuração padrão do Toastr
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
    }

    // Sobrescrita global de window.alert para SweetAlert2
    window.alert = function(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Atenção',
                html: message,
                icon: 'info',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#0a6b74'
            });
        } else if (typeof toastr !== 'undefined') {
            toastr.info(message, 'Atenção');
        } else {
            console.warn('[ALERTA]:', message);
        }
    };

    // Helper global para Notificações Toastr
    window.notificar = {
        sucesso: function(msg, titulo) {
            if (typeof toastr !== 'undefined') toastr.success(msg, titulo || 'Sucesso');
            else if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: titulo || 'Sucesso', text: msg, timer: 3000 });
        },
        erro: function(msg, titulo) {
            if (typeof toastr !== 'undefined') toastr.error(msg, titulo || 'Erro');
            else if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: titulo || 'Erro', text: msg });
        },
        aviso: function(msg, titulo) {
            if (typeof toastr !== 'undefined') toastr.warning(msg, titulo || 'Atenção');
            else if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: titulo || 'Atenção', text: msg });
        },
        info: function(msg, titulo) {
            if (typeof toastr !== 'undefined') toastr.info(msg, titulo || 'Informação');
            else if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', title: titulo || 'Informação', text: msg });
        }
    };

    // Helper global para Confirmações com SweetAlert2
    window.confirmarAcao = function(options) {
        var opts = Object.assign({
            title: 'Tem certeza?',
            text: 'Esta ação não poderá ser desfeita!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, confirmar!',
            cancelButtonText: 'Cancelar'
        }, options || {});

        if (typeof Swal !== 'undefined') {
            return Swal.fire(opts);
        }
        return Promise.resolve({ isConfirmed: true });
    };

    // Interceptação automática de cliques em botões de exclusão
    document.addEventListener('DOMContentLoaded', function() {
        document.body.addEventListener('click', function(e) {
            var target = e.target.closest('[data-target="#confirm-delete"], .btn-danger[data-href], a[href*="-delete.php"]');
            if (target) {
                var url = target.getAttribute('data-href') || target.getAttribute('href');
                if (url && url !== '#' && !target.hasAttribute('data-swal-confirmed')) {
                    e.preventDefault();
                    e.stopPropagation();

                    Swal.fire({
                        title: 'Confirmar Exclusão',
                        text: 'Tem certeza de que deseja excluir este registro?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sim, excluir!',
                        cancelButtonText: 'Cancelar'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });
                }
            }
        }, true);
    });
})();
