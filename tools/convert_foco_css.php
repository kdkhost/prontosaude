<?php
// Converte site-foco7.css para pronto-modern-frontend.css sem BOM e adaptado à Pronto Saúde
$src = 'C:/Users/marce/.gemini/antigravity-ide/brain/5ba01216-7003-49d3-b6f2-9ff8098f6d0b/scratch/foco7/public/css/site-foco7.css';
$dest = __DIR__ . '/../assets/css/pronto-modern-frontend.css';

if (!file_exists($src)) {
    die("Erro: Arquivo fonte foco7 nao encontrado em: " . $src);
}

$css = file_get_contents($src);

// Paleta Oficial Pronto Saúde (Substituições precisas de cores)
$map = [
    '#d90000' => '#0a6b74',
    '#D90000' => '#0a6b74',
    '#b30000' => '#075259',
    '#B30000' => '#075259',
    '#ff2a2a' => '#0d838e',
    '#FF2A2A' => '#0d838e',
    '#ff3b3b' => '#0d838e',
    '#dc2626' => '#0a6b74',
    '#ef4444' => '#10b981',
    '217, 0, 0' => '10, 107, 116',
    '217,0,0' => '10,107,116',
    '255, 42, 42' => '13, 131, 142',
    '255,42,42' => '13,131,142',
    'Foco 7' => 'Pronto Saúde',
    'FOCO 7' => 'PRONTO SAÚDE',
    'foco 7' => 'pronto saúde',
    'foco-red' => 'foco-red', // mantém compatibilidade de nomes das variáveis
];

$css = str_replace(array_keys($map), array_values($map), $css);

// Ajusta a tipografia padrão para Plus Jakarta Sans / Outfit médica
$css = str_replace(
    "font-family: 'Outfit', 'Roboto', sans-serif;",
    "font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif !important;",
    $css
);

// Adiciona regras específicas da Pronto Saúde (Corpo Clínico, etc.)
$extra = "\n\n" . '/* ==============================================================================
   SEÇÃO CORPO CLÍNICO & MÉDICOS ESPECIALISTAS (PRONTO SAÚDE)
   ============================================================================== */
.section-medicos {
    padding: 85px 0;
    background: #f8fafc;
    position: relative;
}
.card-medico {
    background: #ffffff;
    border: 1px solid var(--foco-border);
    border-radius: 16px;
    overflow: hidden;
    transition: var(--foco-transition);
    height: 100%;
    display: flex;
    flex-direction: column;
    box-shadow: var(--foco-shadow-sm);
}
.card-medico:hover {
    transform: translateY(-6px);
    box-shadow: var(--foco-shadow-card);
    border-color: var(--foco-red);
}
.card-medico-thumb {
    width: 100%;
    height: 280px;
    object-fit: cover;
    background: #e2e8f0;
}
.card-medico-body {
    padding: 22px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.card-medico-name {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--foco-title);
    margin-bottom: 4px;
}
.card-medico-spec {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--foco-red);
    margin-bottom: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.card-medico-footer {
    margin-top: auto;
    padding-top: 14px;
    border-top: 1px solid var(--foco-border-light);
}

/* ==============================================================================
   REGRAS DE ESTABILIDADE DO CARROSSEL HERO & ZERO ESPAÇO EM BRANCO
   ============================================================================== */
.hero-section {
    min-height: 580px;
    background-color: #071822;
}
@media (min-width: 992px) {
    .hero-section {
        min-height: 640px;
    }
}
.hero-slide-bg {
    background-size: cover !important;
    background-position: center center !important;
}
.hero-slide-overlay {
    background: linear-gradient(90deg, rgba(7, 24, 34, 0.94) 0%, rgba(7, 24, 34, 0.82) 48%, rgba(7, 24, 34, 0.45) 80%, rgba(7, 24, 34, 0.2) 100%) !important;
}

/* Dropdown Menu Desktop Elegante */
.foco-navbar .dropdown-menu {
    border: 1px solid var(--foco-border);
    border-radius: 12px;
    box-shadow: var(--foco-shadow-card);
    padding: 8px;
    min-width: 230px;
    background: #ffffff;
}
.foco-navbar .dropdown-item {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--foco-text);
    padding: 8px 14px;
    border-radius: 8px;
    transition: var(--foco-transition);
}
.foco-navbar .dropdown-item:hover {
    background-color: var(--foco-red-subtle);
    color: var(--foco-red);
    transform: translateX(4px);
}
';

file_put_contents($dest, $css . $extra);
echo "Sucesso: pronto-modern-frontend.css gerado com " . strlen($css . $extra) . " bytes.\n";
