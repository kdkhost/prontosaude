<?php
// As variáveis de settings já foram carregadas no header.php
$whats_raw = !empty($contact_phone) ? $contact_phone : '21985831464';
$whats_clean = preg_replace('/\D/', '', $whats_raw);
if (strlen($whats_clean) <= 11) {
    $whats_clean = '55' . $whats_clean;
}
?>

<!-- ==============================================================================
     RODAPÉ OFICIAL PADRÃO FOCO 7 (DARK SLATE PREMIUM & TOTALMENTE RESPONSIVO)
     ============================================================================== -->
<footer class="foco-footer">
    <div class="container">
        <div class="row g-4 justify-content-between">
            <!-- Coluna 1: Marca & Apresentação -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?php if(!empty($logo)): ?>
                        <img src="<?= BASE_URL; ?>assets/uploads/<?= $logo; ?>" alt="Pronto Saúde" height="42" style="filter: brightness(0) invert(1);">
                    <?php else: ?>
                        <h4 class="text-white fw-bold mb-0"><i class="fas fa-hospital-user text-danger me-2"></i> Pronto Saúde</h4>
                    <?php endif; ?>
                </div>
                <p class="small text-secondary mb-3 leading-relaxed">
                    <?= !empty($footer_about) ? nl2br(htmlspecialchars($footer_about)) : 'Centro médico e odontológico completo, focado em medicina preventiva, exames de alta precisão e acolhimento familiar humanizado.'; ?>
                </p>
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
                <h5 class="text-white fw-bold mb-3">Especialidades</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL; ?>servicos.php" class="text-secondary text-decoration-none">Clínica Médica</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>servicos.php" class="text-secondary text-decoration-none">Cardiologia</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>servicos.php" class="text-secondary text-decoration-none">Odontologia</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>servicos.php" class="text-secondary text-decoration-none">Exames de Sangue</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>servicos.php" class="text-secondary text-decoration-none">Ultrassonografia</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>servicos.php" class="text-danger fw-bold text-decoration-none"><i class="fas fa-th-large me-1"></i> Ver Todas</a></li>
                </ul>
            </div>

            <!-- Coluna 3: Links Rápidos -->
            <div class="col-lg-2 col-md-6 col-6">
                <h5 class="text-white fw-bold mb-3">Links Rápidos</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL; ?>#inicio" class="text-secondary text-decoration-none">Início</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>#sobre" class="text-secondary text-decoration-none">Sobre Nós</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>#planos" class="text-secondary text-decoration-none">Cartão Fidelidade</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>doctor.php" class="text-secondary text-decoration-none">Corpo Clínico</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>#faq" class="text-secondary text-decoration-none">Dúvidas Frequentes</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL; ?>privacidade.php" class="text-secondary text-decoration-none"><i class="fas fa-shield-alt text-danger me-1"></i> Privacidade</a></li>
                </ul>
            </div>

            <!-- Coluna 4: Onde Estamos -->
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white fw-bold mb-3">Atendimento</h5>
                <div class="small text-secondary mb-2">
                    <i class="fas fa-map-marker-alt text-danger me-2"></i> <?= htmlspecialchars($contact_address ?: 'Avenida Principal, Centro Médico'); ?>
                </div>
                <?php if(!empty($contact_phone)): ?>
                    <div class="small text-secondary mb-2">
                        <i class="fas fa-phone-alt text-danger me-2"></i> <?= htmlspecialchars($contact_phone); ?>
                    </div>
                <?php endif; ?>
                <div class="small text-secondary mb-3">
                    <i class="fas fa-clock text-danger me-2"></i> Seg a Sex: 07h às 19h | Sáb: 07h às 13h
                </div>
                <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar uma consulta na Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success w-100 fw-bold">
                    <i class="fab fa-whatsapp me-1"></i> Falar no WhatsApp
                </a>
            </div>
        </div>

        <div class="border-top border-secondary border-opacity-25 mt-5 pt-4 text-center small text-secondary">
            <?= $footer_copyright ?: '© ' . date('Y') . ' Pronto Saúde - Centro Médico & Odontológico. Todos os direitos reservados.'; ?> &bull; <a href="<?= BASE_URL; ?>privacidade.php" class="text-secondary text-decoration-underline ms-1">Termos &amp; LGPD</a>
        </div>
    </div>
</footer>

<!-- BOTÃO VOLTAR AO TOPO FLUTUANTE (ESTILO FOCO 7) -->
<button type="button" id="btnVoltarAoTopo" class="foco-btn-topo scrollup" aria-label="Voltar ao início da página" title="Voltar ao Topo">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- BOTÃO FLUTUANTE DO WHATSAPP OFICIAL NA COR DA PALETA -->
<button type="button" id="btnFocoWhatsappFloat" class="foco-whatsapp-float" aria-label="Abrir canais de atendimento Pronto Saúde pelo WhatsApp" title="Central de Atendimento pelo WhatsApp">
    <span class="whatsapp-badge-pulse"></span>
    <i class="fab fa-whatsapp"></i>
    <span class="whatsapp-tooltip">Central WhatsApp</span>
</button>

<!-- BOX POP-UP MULTICANAIS DE SUPORTE FOCO 7 -->
<div class="foco-support-popup shadow-lg" id="popupSupportWhatsapp" role="dialog" aria-modal="true" aria-labelledby="txtSupportTitle">
    <div class="foco-support-header">
        <button type="button" class="btn-close-support" id="btnCloseSupportPopup" aria-label="Fechar box">
            <i class="fas fa-times"></i>
        </button>
        <div class="foco-support-online-badge">
            <span class="support-pulse-dot"></span>
            <span class="support-online-text">Online</span>
        </div>
        <h6 class="foco-support-title" id="txtSupportTitle">Central de Atendimento Pronto Saúde</h6>
        <small class="foco-support-subtitle">Atendimento Humanizado • Selecione o canal desejado:</small>
    </div>
    <div class="foco-support-body">
        <!-- Canal 1: Agendamentos & Consultas -->
        <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar uma consulta médica na Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="foco-channel-card">
            <div class="foco-channel-icon ch-consulta"><i class="fas fa-calendar-check"></i></div>
            <div class="foco-channel-info">
                <p class="foco-channel-title">Consultas &amp; Especialidades</p>
                <p class="foco-channel-desc">Agende com cardiologia, pediatria, ginecologia e mais</p>
            </div>
            <i class="fas fa-chevron-right foco-channel-arrow"></i>
        </a>

        <!-- Canal 2: Exames Laboratoriais & Ultrassom -->
        <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de informações e agendamento de exames laboratoriais / ultrassonografia.'); ?>" target="_blank" rel="noopener noreferrer" class="foco-channel-card">
            <div class="foco-channel-icon ch-exames"><i class="fas fa-flask"></i></div>
            <div class="foco-channel-info">
                <p class="foco-channel-title">Exames &amp; Diagnósticos</p>
                <p class="foco-channel-desc">Sangue, ultrassom, preventivo e check-up completo</p>
            </div>
            <i class="fas fa-chevron-right foco-channel-arrow"></i>
        </a>

        <!-- Canal 3: Odontologia -->
        <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Gostaria de agendar uma avaliação odontológica na Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="foco-channel-card">
            <div class="foco-channel-icon ch-odonto"><i class="fas fa-tooth"></i></div>
            <div class="foco-channel-info">
                <p class="foco-channel-title">Centro Odontológico</p>
                <p class="foco-channel-desc">Clínica geral, próteses, clareamento e limpeza</p>
            </div>
            <i class="fas fa-chevron-right foco-channel-arrow"></i>
        </a>

        <!-- Canal 4: Cartão Fidelidade & Convênios -->
        <a href="https://wa.me/<?= $whats_clean; ?>?text=<?= urlencode('Olá! Quero conhecer as vantagens e contratar o Cartão Fidelidade Pronto Saúde.'); ?>" target="_blank" rel="noopener noreferrer" class="foco-channel-card">
            <div class="foco-channel-icon ch-fidelidade"><i class="fas fa-id-card"></i></div>
            <div class="foco-channel-info">
                <p class="foco-channel-title">Cartão Fidelidade &amp; Planos</p>
                <p class="foco-channel-desc">Economize até 50% em consultas e procedimentos</p>
            </div>
            <i class="fas fa-chevron-right foco-channel-arrow"></i>
        </a>
    </div>
</div>

<!-- ==============================================================================
     SCRIPTS MODERNOS BOOTSTRAP 5, JQUERY, TOASTR E SWEETALERT2
     ============================================================================== -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
$(function () {
    // Configurações globais do Toastr
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
                $btnTopo.addClass('show');
            } else {
                $btnTopo.removeClass('show');
            }
        });

        $btnTopo.on('click', function(e) {
            e.preventDefault();
            $('html, body').stop().animate({ scrollTop: 0 }, 400);
        });
    }

    // ScrollSpy Inteligente & Navegação Suave
    $('a.nav-link[href^="#"], a.android-nav-item[href^="#"]').on('click', function (e) {
        var targetHash = $(this).attr('href');
        if (!targetHash || targetHash === '#' || targetHash === '#!') return;

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
                // Fallback amigável de sucesso
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
