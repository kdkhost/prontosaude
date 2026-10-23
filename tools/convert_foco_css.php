<?php
// tools/convert_foco_css.php
// Compila pronto-modern-frontend.css sem BOM com a Paleta Oficial da Pronto Saúde
// Elimina o excesso de espaços em branco e garante navbar, cores e botões hospitalares

$dest = __DIR__ . '/../assets/css/pronto-modern-frontend.css';

$css = '/* ==============================================================================
   PRONTO SAÚDE - FOLHA DE ESTILOS MODERNA (IDENTIDADE VISUAL OFICIAL)
   Cores: Teal (#0a6b74), Dark Teal (#07474d), Verde Saúde (#10b981), Menta (#87cca4)
   Tipografia: Plus Jakarta Sans / Outfit
   Arquitetura: Densa, Robusta, sem espaços inflados em branco
   ============================================================================== */

:root {
    --ps-primary: #0a6b74;
    --ps-primary-dark: #07474d;
    --ps-primary-hover: #08585f;
    --ps-primary-light: #0d838e;
    --ps-primary-subtle: rgba(10, 107, 116, 0.08);
    --ps-primary-glow: rgba(10, 107, 116, 0.25);
    
    --ps-secondary: #10b981;
    --ps-secondary-hover: #059669;
    --ps-accent: #87cca4;
    
    --ps-dark: #071822;
    --ps-dark-card: #0c2331;
    --ps-title: #0f2430;
    --ps-text: #334155;
    --ps-muted: #64748b;
    --ps-light-bg: #f0f9fa;
    --ps-border: #e2e8f0;
    --ps-border-light: #edf2f7;
    
    --ps-shadow-sm: 0 2px 8px rgba(10, 107, 116, 0.06);
    --ps-shadow-card: 0 6px 20px rgba(10, 107, 116, 0.09);
    --ps-shadow-hover: 0 12px 30px rgba(10, 107, 116, 0.16);
    --ps-transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    
    --bs-primary: #0a6b74;
}

/* Reset Global & Tipografia */
body.ps-foco-body {
    font-family: \'Plus Jakarta Sans\', \'Outfit\', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    color: var(--ps-text);
    background-color: #ffffff;
    line-height: 1.55;
    overflow-x: hidden;
    padding-top: 110px; /* Compensa Topbar + Navbar fixos */
}

@media (max-width: 991px) {
    body.ps-foco-body {
        padding-top: 68px; /* Topbar oculta no mobile */
    }
}

/* ==============================================================================
   1. CABEÇALHO GLOBAL (TOPBAR + NAVBAR IDENTIDADE VISUAL PRONTO SAÚDE)
   ============================================================================== */
.foco-header-fixed {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1040;
    box-shadow: 0 4px 20px rgba(7, 24, 34, 0.12);
}

/* Topbar Escura Hospitalar */
.foco-topbar {
    background: #07474d;
    color: rgba(255, 255, 255, 0.88);
    font-size: 0.82rem;
    padding: 7px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.foco-topbar a {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: var(--ps-transition);
}
.foco-topbar a:hover {
    color: var(--ps-accent);
}
.foco-topbar .btn-cliente-topo {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff !important;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
}
.foco-topbar .btn-cliente-topo:hover {
    background: var(--ps-secondary);
    border-color: var(--ps-secondary);
    color: #ffffff !important;
}

/* Navbar Principal: Fundo Teal Pronto Saúde (#0a6b74) com Alta Legibilidade */
.foco-navbar {
    background: #0a6b74;
    padding: 10px 0;
    transition: var(--ps-transition);
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}

/* Tratamento Inteligente dos Logotipos: Claro para fundos escuros / Escuro para fundos claros */
.ps-logo {
    height: 42px;
    width: auto;
    object-fit: contain;
    transition: var(--ps-transition);
}
/* Em partes escuras (como navbar teal #0a6b74 ou footer #071822) */
.logo-on-dark .ps-logo-light {
    display: inline-block !important;
}
.logo-on-dark .ps-logo-dark {
    display: none !important;
}
/* Em partes claras */
.logo-on-light .ps-logo-light {
    display: none !important;
}
.logo-on-light .ps-logo-dark {
    display: inline-block !important;
}

/* Itens de Navegação Desktop */
.foco-navbar .nav-link {
    color: #ffffff !important;
    font-weight: 600;
    font-size: 0.92rem;
    padding: 8px 14px !important;
    border-radius: 8px;
    transition: var(--ps-transition);
}
.foco-navbar .nav-link:hover,
.foco-navbar .nav-link.active {
    color: #ffffff !important;
    background-color: rgba(255, 255, 255, 0.16);
}

/* Dropdown Menu Desktop - Abertura Automática no Hover em Telas Grandes (>= 992px) */
@media (min-width: 992px) {
    .foco-navbar .nav-item.dropdown {
        position: relative !important;
    }

    /* Ponte invisível que mantém o foco ativo entre o nav-link e o dropdown-menu */
    .foco-navbar .nav-item.dropdown::after {
        content: \'\';
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        height: 18px;
        background: transparent;
        z-index: 1050;
    }

    .foco-navbar .nav-item.dropdown > .dropdown-menu {
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        z-index: 1060 !important;
        display: none;
        opacity: 0;
        visibility: hidden;
        transform: translateY(8px);
        transform-origin: top left;
        transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.2s cubic-bezier(0.16, 1, 0.3, 1),
                    visibility 0.2s;
        pointer-events: none;
        margin-top: 4px !important;
        background: #ffffff !important;
        border: 1px solid var(--ps-border) !important;
        border-radius: 12px !important;
        box-shadow: 0 12px 32px rgba(7, 24, 34, 0.18) !important;
        padding: 8px !important;
        min-width: 230px !important;
    }

    .foco-navbar .nav-item.dropdown:hover > .dropdown-menu,
    .foco-navbar .nav-item.dropdown.show > .dropdown-menu,
    .foco-navbar .nav-item.dropdown > .dropdown-menu.show {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(0) !important;
        pointer-events: auto !important;
    }

    .foco-navbar .nav-item.dropdown:hover > .nav-link,
    .foco-navbar .nav-item.dropdown.show > .nav-link {
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.16) !important;
    }

    /* Rotação suave da setinha (chevron) do dropdown no hover */
    .foco-navbar .nav-item.dropdown .dropdown-toggle::after {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        display: inline-block;
        vertical-align: 0.15em;
        margin-left: 0.35em;
    }

    .foco-navbar .nav-item.dropdown:hover > .dropdown-toggle::after,
    .foco-navbar .nav-item.dropdown.show > .dropdown-toggle::after {
        transform: rotate(180deg);
    }
}

.foco-navbar .dropdown-menu {
    background: #ffffff;
    border: 1px solid var(--ps-border);
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(7, 24, 34, 0.15);
    padding: 8px;
    min-width: 230px;
}
.foco-navbar .dropdown-item {
    color: var(--ps-title) !important;
    font-size: 0.88rem;
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 6px;
    transition: var(--ps-transition);
}
.foco-navbar .dropdown-item:hover,
.foco-navbar .dropdown-item.active {
    background-color: var(--ps-light-bg);
    color: var(--ps-primary) !important;
    transform: translateX(3px);
}
.foco-navbar .dropdown-item i {
    color: var(--ps-primary);
}

/* Botões do Navbar */
.btn-nav-outline {
    border: 1px solid rgba(255, 255, 255, 0.6);
    color: #ffffff !important;
    padding: 7px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    transition: var(--ps-transition);
}
.btn-nav-outline:hover {
    background: #ffffff;
    color: var(--ps-primary) !important;
    border-color: #ffffff;
}

.btn-nav-agendar {
    background: var(--ps-secondary) !important;
    color: #ffffff !important;
    padding: 7px 18px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.88rem;
    border: none;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: var(--ps-transition);
}
.btn-nav-agendar:hover {
    background: var(--ps-secondary-hover) !important;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
}

/* Hambúrguer Mobile */
.btn-android-burger {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    font-size: 1.25rem;
    padding: 6px 12px;
    border-radius: 8px;
}
.btn-android-burger:focus {
    box-shadow: none;
    outline: none;
}

/* Drawer Android Mobile (Offcanvas Perfeito com Rodapé Estático) */
.foco-android-drawer {
    background-color: #ffffff;
    width: 330px;
    max-width: 86vw;
    height: 100vh !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    box-shadow: 4px 0 25px rgba(7, 24, 34, 0.25) !important;
}
.android-drawer-header {
    background: #07474d;
    padding: 14px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100%;
    flex-shrink: 0;
}
.android-drawer-header .logo-on-dark {
    flex: 1;
    min-width: 0;
}
.btn-android-close {
    background: rgba(255, 255, 255, 0.18);
    border: none;
    color: #ffffff;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: auto;
    flex-shrink: 0;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.2s ease;
}
.btn-android-close:hover {
    background: rgba(255, 255, 255, 0.32);
    transform: scale(1.08);
}
.android-drawer-body {
    flex: 1 1 auto;
    min-height: 0;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    height: calc(100vh - 65px) !important;
}
.android-drawer-card-status {
    background: var(--ps-light-bg);
    padding: 12px 16px;
    border-bottom: 1px solid var(--ps-border);
    flex-shrink: 0;
}
.android-pulse-dot {
    width: 9px;
    height: 9px;
    background: var(--ps-secondary);
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
}
/* Área de Rolagem do Menu */
.android-drawer-nav-scroll {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    -webkit-overflow-scrolling: touch;
}
.android-drawer-nav-scroll::-webkit-scrollbar {
    width: 5px;
}
.android-drawer-nav-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.android-drawer-nav-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.android-nav-list {
    padding: 10px 0;
}
.android-nav-item {
    display: flex;
    align-items: center;
    padding: 12px 18px;
    color: var(--ps-title);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.92rem;
    border: none;
    border-bottom: 1px solid var(--ps-border-light);
    background: transparent;
    width: 100%;
    text-align: left;
    font-family: inherit;
    cursor: pointer;
    transition: var(--ps-transition);
}
.android-nav-item:hover,
.android-nav-item.active {
    background: var(--ps-light-bg);
    color: var(--ps-primary);
    padding-left: 22px;
}
.android-item-icon {
    width: 28px;
    color: var(--ps-primary);
}
.android-item-arrow {
    margin-left: auto;
    font-size: 0.75rem;
    color: var(--ps-muted);
    transition: transform 0.25s ease, color 0.25s ease;
}
.android-nav-item[aria-expanded="true"] .android-item-arrow {
    transform: rotate(180deg);
    color: var(--ps-primary);
}

/* Rodapé Estático com WhatsApp e Contatos */
.android-drawer-footer {
    flex-shrink: 0;
    background: #ffffff;
    border-top: 1px solid var(--ps-border);
    padding: 14px 16px;
    box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.05);
    z-index: 5;
    margin-top: auto;
}

/* ==============================================================================
   2. HERO CAROUSEL COMPACTO & ROBUSTO (ZERO VÃO EM BRANCO)
   ============================================================================== */
.hero-section,
#heroCarousel,
#heroCarousel .carousel-inner,
#heroCarousel .carousel-item {
    min-height: 580px;
}
@media (min-width: 768px) {
    .hero-section,
    #heroCarousel,
    #heroCarousel .carousel-inner,
    #heroCarousel .carousel-item {
        min-height: 640px;
    }
}
@media (min-width: 992px) {
    .hero-section,
    #heroCarousel,
    #heroCarousel .carousel-inner,
    #heroCarousel .carousel-item {
        min-height: 700px;
    }
}
@media (min-width: 1400px) {
    .hero-section,
    #heroCarousel,
    #heroCarousel .carousel-inner,
    #heroCarousel .carousel-item {
        min-height: 760px;
    }
}

.hero-section {
    background-color: var(--ps-dark);
    position: relative;
    overflow: hidden;
    width: 100%;
}

#heroCarousel {
    position: relative;
    width: 100%;
    height: 100%;
}

#heroCarousel .carousel-inner {
    position: relative;
    width: 100%;
    height: 100%;
}

#heroCarousel .carousel-item {
    position: relative;
    width: 100%;
    height: 100%;
}

.hero-slide-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    background-size: cover !important;
    background-position: center top !important;
    background-repeat: no-repeat !important;
    transform: scale(1.02);
    transition: transform 7s ease;
}
@media (min-width: 992px) {
    .hero-slide-bg {
        background-position: right top !important;
    }
}
.carousel-item.active .hero-slide-bg {
    transform: scale(1);
}
.hero-slide-overlay {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, rgba(7, 24, 34, 0.92) 0%, rgba(7, 24, 34, 0.82) 45%, rgba(7, 24, 34, 0.45) 80%, rgba(7, 24, 34, 0.22) 100%) !important;
}
@media (max-width: 991.98px) {
    .hero-slide-overlay {
        background: linear-gradient(180deg, rgba(7, 24, 34, 0.88) 0%, rgba(7, 24, 34, 0.92) 100%) !important;
    }
}
.hero-slide-content {
    position: relative;
    z-index: 2;
    padding: 70px 0;
    width: 100%;
    min-height: inherit;
    display: flex;
    align-items: center;
}
.hero-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(16, 185, 129, 0.18);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.35);
    padding: 5px 14px;
    border-radius: 30px;
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 14px;
}
.hero-title {
    font-size: clamp(1.8rem, 3.8vw, 2.75rem);
    font-weight: 800;
    color: #ffffff;
    line-height: 1.18;
    margin-bottom: 12px;
}
.hero-desc {
    color: rgba(255, 255, 255, 0.88);
    font-size: 1rem;
    max-width: 580px;
    margin-bottom: 22px;
    line-height: 1.5;
}

/* Card Agendamento Rápido no Hero */
.card-simulacao-hero {
    background: #ffffff;
    border-radius: 16px;
    padding: 22px;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.btn-tipo-veiculo {
    display: block;
    background: var(--ps-light-bg);
    border: 1px solid var(--ps-border);
    border-radius: 10px;
    padding: 10px 8px;
    text-decoration: none;
    color: var(--ps-title);
    transition: var(--ps-transition);
}
.btn-tipo-veiculo:hover {
    background: #ffffff;
    border-color: var(--ps-primary);
    transform: translateY(-2px);
    box-shadow: var(--ps-shadow-sm);
    color: var(--ps-primary);
}
.btn-tipo-veiculo i {
    color: var(--ps-primary);
}

/* ==============================================================================
   3. BOTÕES GLOBAIS COM IDENTIDADE VISUAL PRONTO SAÚDE
   ============================================================================== */
.btn-foco-primary {
    background: linear-gradient(135deg, #0a6b74 0%, #07474d 100%) !important;
    color: #ffffff !important;
    border: none !important;
    padding: 10px 22px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.92rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 4px 16px rgba(10, 107, 116, 0.28) !important;
    transition: var(--ps-transition);
}
.btn-foco-primary:hover {
    background: linear-gradient(135deg, #0d838e 0%, #0a6b74 100%) !important;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(10, 107, 116, 0.4) !important;
}

.btn-foco-outline {
    background: transparent !important;
    color: #ffffff !important;
    border: 1.5px solid rgba(255, 255, 255, 0.7) !important;
    padding: 9px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.92rem;
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    transition: var(--ps-transition);
}
.btn-foco-outline:hover {
    background: #ffffff !important;
    color: var(--ps-primary) !important;
    border-color: #ffffff !important;
}

.btn-outline-danger {
    color: var(--ps-primary) !important;
    border: 1.5px solid var(--ps-primary) !important;
    background: transparent !important;
    transition: var(--ps-transition);
}
.btn-outline-danger:hover {
    background: var(--ps-primary) !important;
    color: #ffffff !important;
}

.btn-agendar-estetica {
    background: linear-gradient(135deg, #0a6b74 0%, #07474d 100%) !important;
    color: #ffffff !important;
    border: none !important;
    width: 100%;
    padding: 9px 14px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.88rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: var(--ps-transition);
}
.btn-agendar-estetica:hover {
    background: linear-gradient(135deg, #0d838e 0%, #0a6b74 100%) !important;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(10, 107, 116, 0.35);
}

/* ==============================================================================
   4. SEÇÕES DENSO-FUNCIONAIS (ESPAÇOS EM BRANCO ENXUTOS: 45px - 50px)
   ============================================================================== */
.section-risco {
    padding: 48px 0;
    background: #ffffff;
}
.section-sobre {
    padding: 48px 0;
    background: var(--ps-light-bg);
}
.section-planos {
    padding: 52px 0;
    background: #ffffff;
}
.section-estetica {
    padding: 48px 0;
    background: var(--ps-light-bg);
}
.section-medicos {
    padding: 48px 0;
    background: #ffffff;
}
.section-depoimentos {
    padding: 45px 0;
    background: var(--ps-light-bg);
}
.section-convenios,
.section-parceiros {
    padding: 36px 0;
    background: #ffffff;
}
.section-faq {
    padding: 45px 0;
    background: var(--ps-light-bg);
}
.section-contato {
    padding: 48px 0;
    background: #ffffff;
}

@media (max-width: 767px) {
    .section-risco,
    .section-sobre,
    .section-planos,
    .section-estetica,
    .section-medicos,
    .section-depoimentos,
    .section-faq,
    .section-contato {
        padding: 34px 0;
    }
}

/* Títulos das Seções Compactos */
.max-w-700 {
    max-width: 700px;
}
.max-w-800 {
    max-width: 800px;
}
.section-header-compact {
    margin-bottom: 28px !important;
}

/* ==============================================================================
   5. CARDS E ELEMENTOS DA IDENTIDADE VISUAL PRONTO SAÚDE
   ============================================================================== */
/* Cards de Riscos / Diferenciais */
.card-risco {
    background: #ffffff;
    border: 1px solid var(--ps-border);
    border-radius: 14px;
    padding: 24px;
    height: 100%;
    transition: var(--ps-transition);
    box-shadow: var(--ps-shadow-sm);
}
.card-risco:hover {
    transform: translateY(-4px);
    box-shadow: var(--ps-shadow-card);
    border-color: var(--ps-primary);
}
.icon-risco {
    width: 52px;
    height: 52px;
    background: var(--ps-light-bg);
    color: var(--ps-primary);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin-bottom: 16px;
}

/* Cards de Planos e Fidelidade */
.card-plano {
    background: #ffffff;
    border: 1.5px solid var(--ps-border);
    border-radius: 16px;
    padding: 26px;
    transition: var(--ps-transition);
    position: relative;
    display: flex;
    flex-direction: column;
    box-shadow: var(--ps-shadow-sm);
}
.card-plano:hover {
    transform: translateY(-5px);
    box-shadow: var(--ps-shadow-hover);
    border-color: var(--ps-primary);
}
.card-plano.destaque {
    border: 2.5px solid var(--ps-primary);
    box-shadow: 0 10px 30px rgba(10, 107, 116, 0.18);
}
.tag-plano-destaque {
    position: absolute;
    top: -13px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #0a6b74 0%, #07474d 100%);
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 4px 14px;
    border-radius: 20px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.icone-plano-box {
    width: 48px;
    height: 48px;
    background: var(--ps-light-bg);
    color: var(--ps-primary);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin: 0 auto 12px;
}
.plano-titulo {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--ps-title);
    margin-bottom: 4px;
}
.plano-subtitulo {
    font-size: 0.82rem;
    color: var(--ps-muted);
    margin-bottom: 14px;
}
.bloco-preco {
    background: var(--ps-light-bg);
    border-radius: 10px;
    padding: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.preco-valor {
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--ps-primary);
}
.preco-periodo {
    font-size: 0.8rem;
    color: var(--ps-muted);
}
.badge-adesao {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 6px;
    background: rgba(16, 185, 129, 0.15);
    color: var(--ps-secondary-hover);
}
.recursos-list {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
}
.recursos-list li {
    font-size: 0.85rem;
    padding: 6px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--ps-text);
}
.recursos-list li i {
    color: var(--ps-secondary);
    font-size: 0.9rem;
}

/* Cards de Especialidades & Serviços Médicos na Home (Padrão Fidelidade / De Ponta a Ponta) */
.card-estetica {
    background: #ffffff;
    border: 1.5px solid var(--ps-border);
    border-radius: 16px;
    padding: 0 !important;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: var(--ps-transition);
    box-shadow: var(--ps-shadow-sm);
}
.card-estetica:hover {
    transform: translateY(-5px);
    box-shadow: var(--ps-shadow-hover);
    border-color: var(--ps-primary);
}
.card-estetica-thumb {
    width: 100%;
    height: 200px;
    position: relative;
    overflow: hidden;
    background: #07474d;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}
.card-estetica-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.card-estetica:hover .card-estetica-thumb img {
    transform: scale(1.03);
}
.card-estetica-body {
    padding: 20px 22px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    justify-content: space-between;
}
.badge-estetica-topo {
    background: var(--ps-light-bg);
    color: var(--ps-primary);
    border: 1px solid rgba(10, 107, 116, 0.2);
}
.icon-estetica-box {
    width: 48px;
    height: 48px;
    background: var(--ps-light-bg);
    color: var(--ps-primary);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

/* Cards de Corpo Clínico / Médicos */
.card-medico {
    background: #ffffff;
    border: 1px solid var(--ps-border);
    border-radius: 14px;
    overflow: hidden;
    transition: var(--ps-transition);
    height: 100%;
    display: flex;
    flex-direction: column;
    box-shadow: var(--ps-shadow-sm);
}
.card-medico:hover {
    transform: translateY(-5px);
    box-shadow: var(--ps-shadow-card);
    border-color: var(--ps-primary);
}
.card-medico-thumb {
    width: 100%;
    height: 220px;
    object-fit: cover;
    background: #e2e8f0;
}
.card-medico-body {
    padding: 18px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.card-medico-name {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--ps-title);
    margin-bottom: 2px;
}
.card-medico-spec {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ps-primary);
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.card-medico-footer {
    margin-top: auto;
    padding-top: 12px;
    border-top: 1px solid var(--ps-border-light);
}

/* Accordion FAQ */
.accordion-foco .accordion-item {
    border: 1px solid var(--ps-border);
    border-radius: 10px !important;
    margin-bottom: 10px;
    overflow: hidden;
}
.accordion-foco .accordion-button {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--ps-title);
    background: #ffffff;
    padding: 16px 20px;
}
.accordion-foco .accordion-button:not(.collapsed) {
    background: var(--ps-light-bg);
    color: var(--ps-primary);
    box-shadow: none;
}
.accordion-foco .accordion-button:focus {
    box-shadow: none;
}
.accordion-foco .accordion-body {
    font-size: 0.9rem;
    color: var(--ps-text);
    padding: 16px 20px;
    background: #ffffff;
    line-height: 1.6;
}

/* Formulário de Contato */
.card-contato-form {
    background: #ffffff;
    border: 1px solid var(--ps-border);
    border-radius: 16px;
    padding: 30px;
    box-shadow: var(--ps-shadow-card);
}
.card-contato-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

/* Utilitários de Cor Pronto Saúde */
.text-danger {
    color: var(--ps-primary) !important;
}
.bg-danger {
    background-color: var(--ps-primary) !important;
}
.border-danger {
    border-color: var(--ps-primary) !important;
}

/* ==============================================================================
   6. RODAPÉ GLOBAL HOSPITALAR (PRONTO SAÚDE)
   ============================================================================== */
.foco-footer {
    background-color: var(--ps-dark);
    color: rgba(255, 255, 255, 0.82);
    padding: 50px 0 25px;
    border-top: 3px solid var(--ps-primary);
}
.foco-footer a {
    color: rgba(255, 255, 255, 0.78);
    text-decoration: none;
    transition: var(--ps-transition);
}
.foco-footer a:hover {
    color: var(--ps-accent);
}
.foco-footer-title {
    color: #ffffff;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 8px;
}
.foco-footer-title::after {
    content: \'\';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 36px;
    height: 3px;
    background: var(--ps-primary);
    border-radius: 2px;
}
.foco-footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 35px;
    padding-top: 20px;
    font-size: 0.82rem;
    color: rgba(255, 255, 255, 0.6);
}

/* ==============================================================================
   7. BOTÃO WHATSAPP FLUTUANTE MULTICANAIS & VOLTAR AO TOPO
   ============================================================================== */
.foco-whatsapp-float {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
}
.btn-whatsapp-pulse {
    width: 60px;
    height: 60px;
    background-color: #25d366;
    color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    box-shadow: 0 6px 24px rgba(37, 211, 102, 0.45);
    border: none;
    cursor: pointer;
    transition: var(--ps-transition);
    position: relative;
}
.btn-whatsapp-pulse:hover {
    transform: scale(1.08);
    background-color: #20ba5a;
    color: #ffffff;
}
.whatsapp-pulse-ring {
    position: absolute;
    inset: -6px;
    border: 2px solid #25d366;
    border-radius: 50%;
    animation: psWhatsPulse 2s infinite cubic-bezier(0.25, 0, 0, 1);
}
@keyframes psWhatsPulse {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.25); opacity: 0; }
    100% { transform: scale(0.95); opacity: 0; }
}

.whatsapp-popup-card {
    position: absolute;
    bottom: 74px;
    right: 0;
    width: 320px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 16px 45px rgba(7, 24, 34, 0.22);
    border: 1px solid var(--ps-border);
    overflow: hidden;
    display: none;
    animation: psFadeInUp 0.3s ease;
}
.whatsapp-popup-card.show {
    display: block;
}
.whatsapp-popup-header {
    background: #07474d;
    color: #ffffff;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.whatsapp-popup-body {
    padding: 14px 16px;
}
.whatsapp-channel-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: var(--ps-light-bg);
    border: 1px solid var(--ps-border);
    border-radius: 10px;
    color: var(--ps-title);
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 600;
    margin-bottom: 8px;
    transition: var(--ps-transition);
}
.whatsapp-channel-btn:hover {
    background: #ffffff;
    border-color: var(--ps-secondary);
    color: var(--ps-secondary-hover);
    transform: translateX(3px);
}
@keyframes psFadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.btn-voltar-topo {
    position: fixed;
    bottom: 24px;
    left: 24px;
    width: 44px;
    height: 44px;
    background: rgba(7, 71, 77, 0.88);
    color: #ffffff;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    cursor: pointer;
    z-index: 1030;
    box-shadow: var(--ps-shadow-card);
    transition: var(--ps-transition);
    opacity: 0;
    visibility: hidden;
}
.btn-voltar-topo.visible {
    opacity: 1;
    visibility: visible;
}
/* ==============================================================================
   8. ESTILOS DAS PÁGINAS INTERNAS (ARQUITETURA MULTIPAGE PRONTO SAÚDE)
   PADRONIZAÇÃO DEFINITIVA DE ALTURA, LARGURA E OVERLAY TRANSLÚCIDO DO BANNER
   ============================================================================== */
.page-banner {
    position: relative;
    width: 100%;
    min-height: 290px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 50px 0;
    background-size: cover !important;
    background-position: center center !important;
    background-repeat: no-repeat !important;
    background-color: var(--ps-dark);
    overflow: hidden;
    box-sizing: border-box;
}
.page-banner .overlay {
    position: absolute;
    inset: 0;
    /* Gradiente translúcido elegante: revela os detalhes da imagem de fundo com nitidez e riqueza de detalhes sem ofuscar a leitura */
    background: linear-gradient(135deg, rgba(7, 24, 34, 0.55) 0%, rgba(10, 107, 116, 0.45) 100%) !important;
    pointer-events: none;
    z-index: 1;
}
.page-banner > .container {
    position: relative;
    z-index: 2;
    width: 100%;
}
.page-banner .banner-text {
    position: relative;
    z-index: 2;
    text-align: center;
    margin: 0 auto;
}
.page-banner .banner-text h1 {
    color: #ffffff !important;
    font-size: clamp(1.85rem, 3.2vw, 2.5rem);
    font-weight: 800;
    margin-top: 6px;
    margin-bottom: 8px;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 10px rgba(7, 24, 34, 0.85), 0 1px 3px rgba(7, 24, 34, 0.65);
}
.page-banner .banner-text p {
    color: rgba(255, 255, 255, 0.95) !important;
    font-size: 1.05rem;
    max-width: 680px;
    margin: 0 auto;
    line-height: 1.5;
    text-shadow: 0 1px 6px rgba(7, 24, 34, 0.8);
}
.page-banner .ps-section-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.35);
    padding: 5px 14px;
    border-radius: 9999px;
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
}

@media (max-width: 767.98px) {
    .page-banner {
        min-height: 220px;
        padding: 35px 0;
    }
    .page-banner .banner-text h1 {
        font-size: 1.6rem;
    }
    .page-banner .banner-text p {
        font-size: 0.92rem;
    }
}

/* Cards das páginas internas de Serviços e Especialidades */
.ps-service-card-modern,
.service-card-box {
    background: #ffffff;
    border: 1px solid var(--ps-border);
    border-radius: 16px;
    overflow: hidden;
    transition: var(--ps-transition);
    height: 100%;
    display: flex;
    flex-direction: column;
    box-shadow: var(--ps-shadow-sm);
}
.ps-service-card-modern:hover,
.service-card-box:hover {
    transform: translateY(-5px);
    box-shadow: var(--ps-shadow-hover);
    border-color: var(--ps-primary);
}
.ps-service-thumb,
.service-card-box .photo {
    width: 100%;
    height: 200px;
    background-size: cover !important;
    background-position: center center !important;
    position: relative;
}
.ps-service-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.ps-service-price-tag,
.service-card-price-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: linear-gradient(135deg, var(--ps-primary) 0%, var(--ps-primary-dark) 100%);
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}
.ps-service-body,
.service-card-box .text {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.ps-service-title,
.service-card-box .text h3 {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--ps-title);
    margin-bottom: 8px;
}
.ps-service-title a,
.service-card-box .text h3 a {
    color: var(--ps-title);
    text-decoration: none;
    transition: var(--ps-transition);
}
.ps-service-title a:hover,
.service-card-box .text h3 a:hover {
    color: var(--ps-primary);
}
.ps-service-desc {
    color: var(--ps-muted);
    font-size: 0.88rem;
    line-height: 1.55;
    margin-bottom: 14px;
}
.ps-service-benefits,
.service-card-benefits {
    list-style: none;
    padding: 0;
    margin: 0 0 16px 0;
}
.ps-service-benefits li,
.service-card-benefits li {
    font-size: 0.82rem;
    color: var(--ps-text);
    padding: 4px 0;
    display: flex;
    align-items: center;
    gap: 6px;
}
.ps-service-benefits li i,
.service-card-benefits li i {
    color: var(--ps-secondary);
}
.ps-service-action-btn {
    margin-top: auto;
    background: var(--ps-light-bg);
    color: var(--ps-primary);
    border: 1px solid var(--ps-border);
    border-radius: 8px;
    padding: 9px 16px;
    font-weight: 700;
    font-size: 0.88rem;
    text-align: center;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: var(--ps-transition);
}
.ps-service-action-btn:hover {
    background: var(--ps-primary);
    color: #ffffff;
    border-color: var(--ps-primary);
    transform: translateY(-2px);
}

/* Cards Planos Fidelidade Padronizados com Serviços/Especialidades (Sem Cortes) */
.ps-plan-card {
    background: #ffffff;
    border: 1.5px solid var(--ps-border);
    border-radius: 16px;
    padding: 0;
    transition: var(--ps-transition);
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
    box-shadow: var(--ps-shadow-sm);
    margin-top: 14px;
}
.ps-plan-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--ps-shadow-hover);
    border-color: var(--ps-primary);
}
.ps-plan-card.ps-plan-popular {
    border: 2px solid var(--ps-primary);
    box-shadow: 0 10px 30px rgba(10, 107, 116, 0.16);
}
.ps-plan-thumb {
    width: 100%;
    aspect-ratio: 2592 / 1693;
    position: relative;
    overflow: hidden;
    background: #07474d;
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
}
.ps-plan-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    display: block;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.ps-plan-card:hover .ps-plan-thumb img {
    transform: scale(1.02);
}
.ps-plan-badge-tag {
    position: absolute;
    top: -13px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
    background: #07474d;
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 4px 16px;
    border-radius: 50px;
    text-transform: uppercase;
    box-shadow: 0 4px 12px rgba(7, 24, 34, 0.25);
    letter-spacing: 0.04em;
    white-space: nowrap;
}
.ps-plan-body {
    padding: 22px 24px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.ps-plan-name {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--ps-title);
    margin-bottom: 12px;
}
.ps-plan-price-wrap {
    background: var(--ps-light-bg);
    border-radius: 10px;
    padding: 12px;
    text-align: center;
    margin-bottom: 18px;
}
.ps-plan-from {
    display: block;
    font-size: 0.72rem;
    text-transform: uppercase;
    color: var(--ps-muted);
    font-weight: 700;
    letter-spacing: 0.05em;
}
.ps-plan-amount {
    color: var(--ps-primary);
}
.ps-plan-currency {
    font-size: 1rem;
    font-weight: 700;
}
.ps-plan-number {
    font-size: 1.8rem;
    font-weight: 800;
}
.ps-plan-period {
    font-size: 0.82rem;
    color: var(--ps-muted);
}
.ps-plan-features {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
    flex-grow: 1;
}
.ps-plan-features li {
    font-size: 0.86rem;
    padding: 6px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--ps-text);
}
.ps-plan-features li i {
    color: var(--ps-secondary);
}
.ps-plan-btn {
    background: linear-gradient(135deg, var(--ps-primary) 0%, var(--ps-primary-dark) 100%);
    color: #ffffff;
    border-radius: 10px;
    padding: 11px 18px;
    font-weight: 700;
    font-size: 0.9rem;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    box-shadow: 0 4px 14px rgba(10, 107, 116, 0.25);
    transition: var(--ps-transition);
}
.ps-plan-btn:hover {
    background: linear-gradient(135deg, var(--ps-primary-light) 0%, var(--ps-primary) 100%);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(10, 107, 116, 0.35);
}

/* ==============================================================================
   GALERIA DE FOTOS & MÍDIAS PRONTO SAÚDE (DESIGN MODERNO & LIGHTBOX FANCYBOX 5)
   ============================================================================== */
.ps-gallery-section {
    padding: 55px 0 85px 0;
    background-color: #f8fafc;
    position: relative;
}

.ps-gallery-header {
    text-align: center;
    max-width: 760px;
    margin: 0 auto 35px auto;
}

.ps-gallery-title {
    font-size: 2.15rem;
    font-weight: 800;
    color: var(--ps-title);
    letter-spacing: -0.02em;
    margin-bottom: 12px;
}

.ps-gallery-subtitle {
    font-size: 1.02rem;
    color: var(--ps-muted);
    line-height: 1.6;
}

.ps-gallery-filter-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-bottom: 35px;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .ps-gallery-filter-wrap {
        justify-content: flex-start;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 10px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .ps-gallery-filter-wrap::-webkit-scrollbar {
        display: none;
    }
}

.ps-gallery-filter-btn {
    border-radius: 50px;
    padding: 9px 20px;
    font-weight: 700;
    font-size: 0.9rem;
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    color: var(--ps-text);
    transition: var(--ps-transition);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(10, 107, 116, 0.04);
    white-space: nowrap;
    user-select: none;
    text-decoration: none;
}

.ps-gallery-filter-btn:hover {
    background: #f0fdf9;
    border-color: var(--ps-accent);
    color: var(--ps-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(10, 107, 116, 0.12);
}

.ps-gallery-filter-btn.active {
    background: linear-gradient(135deg, var(--ps-primary) 0%, var(--ps-primary-dark) 100%) !important;
    color: #ffffff !important;
    border-color: transparent !important;
    box-shadow: 0 6px 20px rgba(10, 107, 116, 0.28) !important;
}

.ps-gallery-filter-btn .ps-gallery-count {
    font-size: 0.75rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 20px;
    background: rgba(10, 107, 116, 0.08);
    color: var(--ps-primary);
    transition: var(--ps-transition);
}

.ps-gallery-filter-btn.active .ps-gallery-count {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

.ps-gallery-card {
    position: relative;
    background: #ffffff;
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(10, 107, 116, 0.05);
    transition: var(--ps-transition);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.ps-gallery-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 36px rgba(10, 107, 116, 0.15);
    border-color: rgba(10, 107, 116, 0.25);
}

.ps-gallery-img-link {
    position: relative;
    display: block;
    overflow: hidden;
    height: 250px;
    background: #e2e8f0;
    text-decoration: none;
}

.ps-gallery-img-link img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.ps-gallery-card:hover .ps-gallery-img-link img {
    transform: scale(1.08);
}

.ps-gallery-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(7, 24, 34, 0.08) 0%, rgba(7, 24, 34, 0.72) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
}

.ps-gallery-card:hover .ps-gallery-overlay {
    opacity: 1;
}

.ps-gallery-zoom-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #ffffff;
    color: var(--ps-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    transform: scale(0.7);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.ps-gallery-card:hover .ps-gallery-zoom-icon {
    transform: scale(1);
}

.ps-gallery-badge-cat {
    position: absolute;
    top: 14px;
    left: 14px;
    z-index: 2;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    color: var(--ps-primary-dark);
    font-size: 0.75rem;
    font-weight: 700;
    padding: 5px 13px;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border: 1px solid rgba(255,255,255,0.7);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    pointer-events: none;
}

.ps-gallery-footer {
    padding: 14px 18px;
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.ps-gallery-caption {
    margin: 0;
    font-size: 0.94rem;
    font-weight: 700;
    color: var(--ps-title);
    line-height: 1.35;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ps-gallery-sub {
    font-size: 0.78rem;
    color: var(--ps-muted);
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}

.ps-gallery-item {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.ps-gallery-item.ps-hidden {
    display: none !important;
}

/* ==============================================================================
   9. PRELOADER OFICIAL DA PRONTO SAÚDE (CONFIGURÁVEL NO PAINEL ADMIN)
   ============================================================================== */
#preloader {
    position: fixed;
    inset: 0;
    z-index: 9999999;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 1;
    visibility: visible;
    transition: opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.4s ease;
}

#preloader.ps-preloader-hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

#status {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 18px;
    text-align: center;
    padding: 20px;
}

.ps-preloader-spinner-wrap {
    position: relative;
    width: 86px;
    height: 86px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ps-preloader-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 3.5px solid rgba(10, 107, 116, 0.12);
    border-top-color: var(--ps-primary);
    border-right-color: var(--ps-secondary);
    animation: ps-preloader-spin 0.9s cubic-bezier(0.6, 0.2, 0.4, 0.8) infinite;
}

@keyframes ps-preloader-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.ps-preloader-center {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--ps-primary) 0%, var(--ps-primary-dark) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(10, 107, 116, 0.28);
}

.ps-preloader-icon {
    font-size: 1.45rem;
    color: #ffffff;
    animation: ps-preloader-pulse 1.3s ease-in-out infinite;
}

@keyframes ps-preloader-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.18); }
}

.ps-preloader-brand {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.ps-preloader-title {
    font-family: Outfit, sans-serif;
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -0.3px;
    color: var(--ps-title);
}

.ps-preloader-sub {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--ps-muted);
    letter-spacing: 0.02em;
}

/* ==============================================================================
   10. GALERIA DE VÍDEOS: FACHADA COM CAPA, SKELETON SHIMMER E PLAY INSTANTÂNEO
   ============================================================================== */
.ps-video-card {
    position: relative;
    background: #ffffff;
    border: 1px solid var(--ps-border);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(10, 107, 116, 0.05);
    transition: var(--ps-transition);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.ps-video-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 36px rgba(10, 107, 116, 0.14);
    border-color: rgba(10, 107, 116, 0.25);
}

/* Esqueleto Animado (Skeleton Loading com Shimmer) */
.ps-video-skeleton {
    position: absolute;
    inset: 0;
    z-index: 10;
    background: #ffffff;
    border-radius: 18px;
    display: flex;
    flex-direction: column;
    pointer-events: none;
    opacity: 1;
    visibility: visible;
    transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.5s ease;
}

.ps-video-card.ps-skeleton-loaded .ps-video-skeleton {
    opacity: 0;
    visibility: hidden;
}

.ps-skeleton-shimmer {
    background: linear-gradient(90deg, #f1f5f9 0%, #e2e8f0 50%, #f1f5f9 100%);
    background-size: 200% 100%;
    animation: ps-shimmer 1.4s infinite linear;
}

@keyframes ps-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.ps-skeleton-thumb {
    width: 100%;
    aspect-ratio: 16 / 9;
}

.ps-skeleton-body {
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex: 1;
}

.ps-skeleton-pill {
    height: 12px;
    width: 35%;
    border-radius: 6px;
}

.ps-skeleton-title {
    height: 18px;
    width: 80%;
    border-radius: 6px;
}

.ps-skeleton-meta {
    height: 14px;
    width: 55%;
    margin-top: auto;
    border-radius: 6px;
}

/* Área do Player e Capa da Frente */
.ps-video-player-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    background: #071822;
    overflow: hidden;
}

.ps-video-player-wrap video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    background: #071822;
}

/* Capa na frente do box de vídeo (Video Facade) */
.ps-video-cover-facade {
    position: absolute;
    inset: 0;
    z-index: 4;
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: opacity 0.4s ease, visibility 0.4s ease;
    overflow: hidden;
}

.ps-video-cover-facade.is-hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

.ps-video-cover-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(7, 24, 34, 0.25) 0%, rgba(7, 24, 34, 0.72) 100%);
    transition: background 0.3s ease;
}

.ps-video-cover-facade:hover .ps-video-cover-overlay {
    background: linear-gradient(180deg, rgba(7, 24, 34, 0.15) 0%, rgba(7, 24, 34, 0.82) 100%);
}

/* Botão de Play Pulsante */
.ps-video-play-btn {
    position: relative;
    z-index: 5;
    width: 62px;
    height: 62px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--ps-secondary) 0%, #059669 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.45);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
    padding-left: 3px;
}

.ps-play-pulse-ring {
    position: absolute;
    inset: -6px;
    border-radius: 50%;
    border: 2px solid rgba(16, 185, 129, 0.65);
    animation: ps-play-ring 2s cubic-bezier(0.25, 1, 0.5, 1) infinite;
    pointer-events: none;
}

@keyframes ps-play-ring {
    0% { transform: scale(0.95); opacity: 0.95; }
    50% { transform: scale(1.35); opacity: 0; }
    100% { transform: scale(1.35); opacity: 0; }
}

.ps-video-cover-facade:hover .ps-video-play-btn {
    transform: scale(1.12);
    box-shadow: 0 12px 30px rgba(16, 185, 129, 0.65);
}

.ps-video-tag-hd {
    position: absolute;
    bottom: 12px;
    right: 12px;
    z-index: 5;
    background: rgba(7, 24, 34, 0.85);
    backdrop-filter: blur(8px);
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    letter-spacing: 0.03em;
}

.ps-video-badge-cat {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 5;
    background: rgba(7, 24, 34, 0.85);
    backdrop-filter: blur(8px);
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 50px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    pointer-events: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.ps-video-body {
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    background: #ffffff;
}

.ps-video-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--ps-title);
    margin-bottom: 6px;
    line-height: 1.35;
}

.ps-video-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: auto;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
}

.ps-video-status {
    font-size: 0.78rem;
    color: var(--ps-muted);
    display: flex;
    align-items: center;
    gap: 5px;
}
';

// Grava arquivo garantindo UTF-8 puro SEM BOM
if (substr($css, 0, 3) === "\xEF\xBB\xBF") {
    $css = substr($css, 3);
}
file_put_contents($dest, $css);
echo "Sucesso: pronto-modern-frontend.css compilado com " . strlen($css) . " bytes.\n";
