<?php
// As variáveis de settings já foram carregadas no header.php
$whats_raw = !empty($contact_phone) ? $contact_phone : '21985831464';
$whats_clean = preg_replace('/\D/', '', $whats_raw);
if (strlen($whats_clean) <= 11) {
    $whats_clean = '55' . $whats_clean;
}
?>

<!-- ==============================================================================
     RODAPÉ OFICIAL PRONTO SAÚDE (DARK SLATE HOSPITALAR & TOTALMENTE RESPONSIVO)
     ============================================================================== -->
<footer class="foco-footer">
    <div class="container">
        <div class="row g-4 justify-content-between">
            <!-- Coluna 1: Marca & Apresentação -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3 logo-on-dark">
                    <?php if(!empty($logo)): ?>
                        <img src="<?= BASE_URL; ?>assets/uploads/<?= $logo; ?>" alt="Pronto Saúde" height="42" class="ps-logo ps-logo-light">
                    <?php else: ?>
                        <h5 class="text-white fw-bold mb-0"><i class="fas fa-hospital-user me-2" style="color: var(--ps-accent);"></i> Pronto Saúde</h5>
                    <?php endif; ?>
                </div>
                <div class="small text-secondary mb-3 leading-relaxed">
                    <?= !empty($footer_about) ? $footer_about : 'Centro médico e odontológico completo, focado em medicina preventiva, exames de alta precisão e acolhimento familiar humanizado.'; ?>
                </div>
                <div class="d-flex gap-2">
                    <?php if(!empty($social_data)): ?>
                        <?php foreach ($social_data as $s_row): ?>
                            <?php if(!empty($s_row['social_url'])): ?>
                                <a href="<?= $s_row['social_url']; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-light rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="<?= $s_row['social_icon']; ?>"></i>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Coluna 2: Especialidades -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="text-white fw-bold mb-3 foco-footer-title">Especialidades</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL; ?>servicos.php">Clínica Médica</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>servicos.php">Cardiologia</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>servicos.php">Odontologia</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>servicos.php">Exames de Sangue</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>servicos.php">Ultrassonografia</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>servicos.php" style="color: var(--ps-accent); font-weight: 700;"><i class="fas fa-th-large me-1"></i> Ver Todas</a></li>
                </ul>
            </div>

            <!-- Coluna 3: Links Rápidos -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="text-white fw-bold mb-3 foco-footer-title">Links Rápidos</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL; ?>#inicio">Início</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>#sobre">Sobre Nós</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>#planos">Cartão Fidelidade</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>doctor.php">Corpo Clínico</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>#faq">Dúvidas Frequentes</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>privacidade.php"><i class="fas fa-shield-alt me-1" style="color: var(--ps-secondary);"></i> Privacidade</a></li>
                </ul>
            </div>

            <!-- Coluna 4: Atendimento & Horários -->
            <div class="col-lg-3 col-md-6">
                <h6 class="text-white fw-bold mb-3 foco-footer-title">Atendimento</h6>
                <div class="small text-secondary mb-2">
                    <i class="fas fa-map-marker-alt me-2" style="color: var(--ps-accent);"></i> <?= htmlspecialchars($contact_address ?: 'Gardênia Azul, Jacarepaguá, RJ'); ?>
                </div>
                <?php if(!empty($contact_phone)): ?>
                    <div class="small text-secondary mb-2">
                        <i class="fas fa-phone-alt me-2" style="color: var(--ps-accent);"></i> <?= htmlspecialchars($contact_phone); ?>
                    </div>
                <?php endif; ?>
                <div class="small text-secondary mb-3">
                    <i class="fas fa-clock me-2" style="color: var(--ps-accent);"></i> Seg a Sex: 07h às 19h | Sáb: 07h às 13h
                </div>
                <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar uma consulta na Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm w-100 fw-bold" style="background: var(--ps-secondary); color: #fff;">
                    <i class="fab fa-whatsapp me-1"></i> Falar no WhatsApp
                </a>
            </div>
        </div>

        <div class="border-top border-secondary border-opacity-25 mt-4 pt-3 text-center small text-secondary">
            <?= $footer_copyright ?: '© ' . date('Y') . ' Pronto Saúde - Centro Médico & Odontológico. Todos os direitos reservados.'; ?> &bull; <a href="<?= BASE_URL; ?>privacidade.php" class="text-secondary text-decoration-underline ms-1">Termos &amp; LGPD</a>
        </div>
    </div>
</footer>

<!-- BOTÃO VOLTAR AO TOPO FLUTUANTE -->
<button type="button" id="btnVoltarAoTopo" class="btn-voltar-topo" aria-label="Voltar ao início da página" title="Voltar ao Topo">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- BOTÃO WHATSAPP FLUTUANTE MULTICANAIS PRONTO SAÚDE -->
<div class="foco-whatsapp-float">
    <!-- Card Pop-up Multicanais -->
    <div class="whatsapp-popup-card shadow-lg" id="popupSupportWhatsapp" role="dialog" aria-modal="true" aria-labelledby="txtSupportTitle">
        <div class="whatsapp-popup-header">
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small" style="font-size: 0.7rem;">
                    <span class="android-pulse-dot me-1"></span> Online
                </span>
                <h6 class="fw-bold mb-0 mt-1" id="txtSupportTitle" style="font-size: 0.95rem;">Pronto Saúde • Atendimento</h6>
            </div>
            <button type="button" class="btn-android-close" id="btnCloseSupportPopup" aria-label="Fechar canais">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="whatsapp-popup-body">
            <p class="small text-muted mb-3" style="font-size: 0.8rem;">Selecione o canal para iniciar seu atendimento no WhatsApp:</p>

            <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar uma consulta médica na Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="whatsapp-channel-btn">
                <i class="fas fa-calendar-check" style="color: var(--ps-primary); font-size: 1.1rem;"></i>
                <div>
                    <span class="d-block text-dark fw-bold">Consultas Médicas</span>
                    <small class="text-muted" style="font-size: 0.72rem;">Clínica geral, cardiologia, ginecologia e mais</small>
                </div>
            </a>

            <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar exames laboratoriais / ultrassonografia na Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="whatsapp-channel-btn">
                <i class="fas fa-flask" style="color: var(--ps-primary); font-size: 1.1rem;"></i>
                <div>
                    <span class="d-block text-dark fw-bold">Exames &amp; Diagnósticos</span>
                    <small class="text-muted" style="font-size: 0.72rem;">Sangue, ultrassom e check-up com laudos ágeis</small>
                </div>
            </a>

            <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar atendimento odontológico na Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="whatsapp-channel-btn">
                <i class="fas fa-tooth" style="color: var(--ps-primary); font-size: 1.1rem;"></i>
                <div>
                    <span class="d-block text-dark fw-bold">Centro Odontológico</span>
                    <small class="text-muted" style="font-size: 0.72rem;">Limpeza, profilaxia, próteses e restaurações</small>
                </div>
            </a>

            <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Quero conhecer as vantagens do Cartão Fidelidade Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="whatsapp-channel-btn">
                <i class="fas fa-id-card" style="color: var(--ps-secondary); font-size: 1.1rem;"></i>
                <div>
                    <span class="d-block text-dark fw-bold">Cartão Fidelidade</span>
                    <small class="text-muted" style="font-size: 0.72rem;">Economize até 50% em consultas e exames</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Botão Pulsante do WhatsApp -->
    <button type="button" class="btn-whatsapp-pulse" id="btnFocoWhatsappFloat" aria-label="Abrir canais de WhatsApp Pronto Saúde" title="Atendimento WhatsApp">
        <span class="whatsapp-pulse-ring"></span>
        <i class="fab fa-whatsapp"></i>
    </button>
</div>

<!-- ==============================================================================
     SCRIPTS MODERNOS BOOTSTRAP 5, JQUERY, TOASTR E SWEETALERT2
     ============================================================================== -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>

<script>
$(function () {
    // Inicialização global do Fancybox 5 para fotos e mídias
    if (typeof Fancybox !== 'undefined') {
        Fancybox.bind('[data-fancybox]', {
            Toolbar: {
                display: {
                    left: ['infobar'],
                    middle: [],
                    right: ['zoomIn', 'zoomOut', 'slideshow', 'fullscreen', 'thumbs', 'close']
                }
            },
            Thumbs: {
                autoStart: false
            }
        });
    }

    // Notificações Toastr Globais
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 4000
        };
    }

    // Controle do Botão e Popup do WhatsApp Flutuante
    var $btnWhatsappFloat = $('#btnFocoWhatsappFloat');
    var $popupSupport = $('#popupSupportWhatsapp');
    var $btnCloseSupport = $('#btnCloseSupportPopup');
    var $btnTopo = $('#btnVoltarAoTopo');

    if ($btnWhatsappFloat.length && $popupSupport.length) {
        $btnWhatsappFloat.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $popupSupport.toggleClass('show');
        });

        if ($btnCloseSupport.length) {
            $btnCloseSupport.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $popupSupport.removeClass('show');
            });
        }

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#popupSupportWhatsapp, #btnFocoWhatsappFloat').length) {
                $popupSupport.removeClass('show');
            }
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $popupSupport.hasClass('show')) {
                $popupSupport.removeClass('show');
            }
        });
    }

    // Botão Voltar ao Topo
    if ($btnTopo.length) {
        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 300) {
                $btnTopo.addClass('visible');
            } else {
                $btnTopo.removeClass('visible');
            }
        });

        $btnTopo.on('click', function(e) {
            e.preventDefault();
            $('html, body').stop().animate({ scrollTop: 0 }, 400);
        });
    }

    // ScrollSpy Inteligente & Navegação Suave (apenas âncoras reais na página, ignorando toggles de submenu)
    $('a.nav-link[href^="#"]:not([data-bs-toggle]), a.android-nav-item[href^="#"]:not([data-bs-toggle]):not([data-bs-target])').on('click', function (e) {
        var targetHash = $(this).attr('href');
        if (!targetHash || targetHash === '#' || targetHash === '#!' || targetHash.indexOf('#drawerSub') === 0) return;

        var $target = $(targetHash);
        if ($target.length) {
            e.preventDefault();
            var headerHeight = $('#focoHeader').outerHeight() || 80;
            var posFinal = targetHash === '#inicio' ? 0 : Math.max(0, $target.offset().top - headerHeight + 5);

            $('html, body').stop().animate({
                scrollTop: posFinal
            }, 450, 'swing');

            var drawerEl = document.getElementById('menuDrawerAndroid');
            if (drawerEl) {
                var bsOffcanvas = bootstrap.Offcanvas.getInstance(drawerEl);
                if (bsOffcanvas) bsOffcanvas.hide();
            }
        }
    });

    // Controle de Submenus no Menu Móvel Deslizante
    // 1. Evita fechamento acidental ao clicar nos botões de abrir/fechar submenu
    $(document).on('click', '#menuDrawerAndroid .android-nav-group [data-bs-toggle="collapse"]', function (e) {
        e.stopPropagation();
    });

    // 2. Fecha o menu deslizante quando o usuário clica em um link de página real (que navega de fato)
    $(document).on('click', '#menuDrawerAndroid a.android-nav-item:not([data-bs-toggle="collapse"])', function () {
        var href = $(this).attr('href');
        if (href && href !== '#' && href !== '#!' && href.indexOf('#drawerSub') === -1) {
            var drawerEl = document.getElementById('menuDrawerAndroid');
            if (drawerEl) {
                var bsOffcanvas = bootstrap.Offcanvas.getInstance(drawerEl);
                if (bsOffcanvas) {
                    setTimeout(function() {
                        bsOffcanvas.hide();
                    }, 120);
                }
            }
        }
    });

    // Submissão do Formulário de Contato via AJAX com SweetAlert2
    $('#formContatoSite').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var originalBtn = $btn.html();

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Enviando...');

        $.ajax({
            url: $form.attr('action') || 'contato_ajax.php',
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function (res) {
                $btn.prop('disabled', false).html(originalBtn);
                if (res.success || res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Mensagem Enviada!',
                        text: res.message || 'Obrigado por entrar em contato! Retornaremos em instantes.',
                        confirmButtonColor: '#0a6b74'
                    });
                    $form[0].reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Atenção',
                        text: res.message || 'Não foi possível enviar sua mensagem. Tente novamente.',
                        confirmButtonColor: '#0a6b74'
                    });
                }
            },
            error: function () {
                $btn.prop('disabled', false).html(originalBtn);
                Swal.fire({
                    icon: 'success',
                    title: 'Mensagem Registrada!',
                    text: 'Recebemos sua mensagem e entraremos em contato rapidamente pelo WhatsApp ou Telefone.',
                    confirmButtonColor: '#0a6b74'
                });
                $form[0].reset();
            }
        });
    });
});
</script>
</body>
</html>
