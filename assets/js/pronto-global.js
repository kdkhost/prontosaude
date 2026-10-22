/**
 * Pronto Saúde - Integração Global SweetAlert2 & Toastr Notifications (Frontend Público)
 * Padronizado para PT-BR, com interceptação global de alert() e confirm()
 * Envio assíncrono via AJAX de formulários públicos com feedback visual
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
                confirmButtonColor: '#0a6b74',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
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
            else if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: titulo || 'Sucesso', text: msg, timer: 3500 });
        },
        erro: function(msg, titulo) {
            if (typeof toastr !== 'undefined') toastr.error(msg, titulo || 'Atenção');
            else if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: titulo || 'Atenção', text: msg });
        },
        aviso: function(msg, titulo) {
            if (typeof toastr !== 'undefined') toastr.warning(msg, titulo || 'Aviso');
            else if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: titulo || 'Aviso', text: msg });
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
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, confirmar!',
            cancelButtonText: 'Cancelar'
        }, options || {});

        if (typeof Swal !== 'undefined') {
            return Swal.fire(opts);
        }
        return Promise.resolve({ isConfirmed: true });
    };

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Converter mensagens estáticas do PHP (.error e .success) em SweetAlert2 / Toastr
        var errorDiv = document.querySelector('.error:not(.form-error)');
        if (errorDiv && errorDiv.innerText.trim()) {
            var errorMsg = errorDiv.innerHTML.trim();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Atenção',
                    html: errorMsg,
                    confirmButtonColor: '#0a6b74'
                });
            } else {
                window.notificar.erro(errorMsg);
            }
            errorDiv.style.display = 'none';
        }

        var successDiv = document.querySelector('.success:not(.form-success)');
        if (successDiv && successDiv.innerText.trim()) {
            var successMsg = successDiv.innerHTML.trim();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Mensagem Enviada!',
                    html: successMsg,
                    confirmButtonColor: '#0a6b74'
                });
            } else {
                window.notificar.sucesso(successMsg);
            }
            successDiv.style.display = 'none';
        }

        // 2. Interceptador de Envio de Formulários Públicos (Contato, Mensagens) via AJAX
        var contactForms = document.querySelectorAll('form.cform-1, form[name="form_contact"]');
        contactForms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                var submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
                var originalText = '';
                if (submitBtn) {
                    originalText = submitBtn.value || submitBtn.innerHTML;
                    if (submitBtn.tagName === 'INPUT') submitBtn.value = 'Enviando...';
                    else submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Enviando...';
                    submitBtn.disabled = true;
                }

                // Permite o envio normal se houver recaptcha tradicional ou continua via fetch
                // Se desejar submeter via AJAX:
                e.preventDefault();
                var formData = new FormData(form);
                if (submitBtn && submitBtn.name) {
                    formData.append(submitBtn.name, submitBtn.value || '1');
                }

                var actionUrl = form.getAttribute('action') || window.location.href;

                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(res) {
                    return res.text();
                })
                .then(function(html) {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');

                    var errEl = doc.querySelector('.error');
                    var succEl = doc.querySelector('.success');

                    if (submitBtn) {
                        if (submitBtn.tagName === 'INPUT') submitBtn.value = originalText;
                        else submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }

                    if (errEl && errEl.innerText.trim()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Verifique os dados',
                            html: errEl.innerHTML,
                            confirmButtonColor: '#0a6b74'
                        });
                    } else if (succEl && succEl.innerText.trim()) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Mensagem Enviada com Sucesso!',
                            html: succEl.innerHTML,
                            confirmButtonColor: '#0a6b74'
                        });
                        form.reset();
                    } else {
                        // Se não achou divs mas submeteu com sucesso
                        Swal.fire({
                            icon: 'success',
                            title: 'Obrigado pelo contato!',
                            text: 'Sua mensagem foi recebida com sucesso. Em breve retornaremos.',
                            confirmButtonColor: '#0a6b74'
                        });
                        form.reset();
                    }
                })
                .catch(function(err) {
                    if (submitBtn) {
                        if (submitBtn.tagName === 'INPUT') submitBtn.value = originalText;
                        else submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Falha no envio',
                        text: 'Ocorreu um erro ao enviar sua mensagem. Por favor, tente novamente ou entre em contato via WhatsApp.',
                        confirmButtonColor: '#0a6b74'
                    });
                });
            });
        });

        // 3. Máscara de Telefone e Celular Automática
        var phoneInputs = document.querySelectorAll('input[name*="phone"], input[name*="telefone"], input[name*="celular"], input[type="tel"]');
        phoneInputs.forEach(function(input) {
            input.addEventListener('input', function(e) {
                var v = e.target.value.replace(/\D/g, '');
                if (v.length > 11) v = v.substring(0, 11);
                if (v.length > 10) {
                    e.target.value = '(' + v.substring(0, 2) + ') ' + v.substring(2, 7) + '-' + v.substring(7);
                } else if (v.length > 6) {
                    e.target.value = '(' + v.substring(0, 2) + ') ' + v.substring(2, 6) + '-' + v.substring(6);
                } else if (v.length > 2) {
                    e.target.value = '(' + v.substring(0, 2) + ') ' + v.substring(2);
                } else if (v.length > 0) {
                    e.target.value = '(' + v;
                }
            });
        });

    });
})();
