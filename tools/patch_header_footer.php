<?php
// 1. Atualizar header.php
$headerFile = __DIR__ . '/../header.php';
$header = file_get_contents($headerFile);

if (strpos($header, '$frontend_dark_mode_status') === false) {
    $search = '$pwa_status      = (int)($row[\'pwa_status\'] ?? 1);';
    $replace = "\$frontend_dark_mode_status = (int)(\$row['frontend_dark_mode_status'] ?? 1);\n\$pwa_status      = (int)(\$row['pwa_status'] ?? 1);";
    $header = str_replace($search, $replace, $header);
    file_put_contents($headerFile, $header);
    echo "header.php: frontend_dark_mode_status adicionado com sucesso!\n";
} else {
    echo "header.php: frontend_dark_mode_status já presente.\n";
}

// 2. Atualizar footer.php
$footerFile = __DIR__ . '/../footer.php';
$footer = file_get_contents($footerFile);

$oldThemeBlock = <<<'JS'
    // ==========================================================================
    // GERENCIADOR UNIFICADO DE TEMA DARK / LIGHT DO PORTAL PRONTO SAÚDE
    // ==========================================================================
    function applyFrontendTheme(theme) {
        var resolved = theme === 'auto' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : theme;
        document.documentElement.setAttribute('data-bs-theme', resolved);
        localStorage.setItem('theme', theme);

        // Atualizar textos e estados dos botões
        var isDark = resolved === 'dark';
        $('.btn-theme-toggle-frontend .text-theme-label').text(isDark ? 'Escuro' : 'Claro');
        $('.btn-theme-toggle-mobile .text-theme-label').text(isDark ? 'Modo Escuro' : 'Modo Claro');

        // Disparar evento global
        window.dispatchEvent(new CustomEvent('prontoThemeChanged', { detail: { theme: resolved, raw: theme } }));
    }

    var currentStoredTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyFrontendTheme(currentStoredTheme);

    $(document).on('click', '#btnThemeToggleFrontend, #btnThemeToggleMobile', function (e) {
        e.preventDefault();
        var current = document.documentElement.getAttribute('data-bs-theme') || 'light';
        var next = current === 'dark' ? 'light' : 'dark';
        applyFrontendTheme(next);
        
        if (window.toastr) {
            toastr.info('Tema visual: ' + (next === 'dark' ? 'Modo Escuro' : 'Modo Claro'), 'Aparência', { timeOut: 2000, positionClass: 'toast-bottom-right' });
        }
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
        if (!localStorage.getItem('theme')) {
            applyFrontendTheme(e.matches ? 'dark' : 'light');
        }
    });
JS;

$newThemeBlock = <<<'JS'
    // ==========================================================================
    // GERENCIADOR UNIFICADO DE TEMA DARK / LIGHT DO PORTAL PRONTO SAÚDE (ISOLADO)
    // ==========================================================================
    var isThemeToggleAllowed = <?= (int)($frontend_dark_mode_status ?? 1); ?>;

    function applyFrontendTheme(theme) {
        if (!isThemeToggleAllowed) {
            document.documentElement.setAttribute('data-bs-theme', 'light');
            return;
        }
        var resolved = theme === 'dark' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', resolved);
        localStorage.setItem('pronto_frontend_theme', resolved);

        // Atualizar textos e estados dos botões
        var isDark = resolved === 'dark';
        $('.btn-theme-toggle-frontend .text-theme-label').text(isDark ? 'Escuro' : 'Claro');
        $('.btn-theme-toggle-mobile .text-theme-label').text(isDark ? 'Modo Escuro' : 'Modo Claro');

        // Disparar evento global para componentes que reagem à mudança de tema
        window.dispatchEvent(new CustomEvent('prontoThemeChanged', { detail: { theme: resolved, raw: theme } }));
    }

    if (isThemeToggleAllowed) {
        // Inicializa com o tema salvo no frontend ou padrão 'light' (nunca herdando do admin)
        var currentStoredTheme = localStorage.getItem('pronto_frontend_theme') || 'light';
        applyFrontendTheme(currentStoredTheme);

        $(document).on('click', '#btnThemeToggleFrontend, #btnThemeToggleMobile', function (e) {
            e.preventDefault();
            var current = document.documentElement.getAttribute('data-bs-theme') || 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            applyFrontendTheme(next);
            
            if (window.toastr) {
                toastr.info('Tema visual: ' + (next === 'dark' ? 'Modo Escuro' : 'Modo Claro'), 'Aparência', { timeOut: 2000, positionClass: 'toast-bottom-right' });
            }
        });
    } else {
        document.documentElement.setAttribute('data-bs-theme', 'light');
    }
JS;

// Padroniza quebras de linha para substituição segura
$oldNormalized = str_replace("\r\n", "\n", $oldThemeBlock);
$footerNormalized = str_replace("\r\n", "\n", $footer);

if (strpos($footerNormalized, $oldNormalized) !== false) {
    $footerNormalized = str_replace($oldNormalized, $newThemeBlock, $footerNormalized);
    file_put_contents($footerFile, $footerNormalized);
    echo "footer.php: Gerenciador de tema isolado atualizado com sucesso!\n";
} else {
    echo "footer.php: Bloco antigo não localizado diretamente, verificando se já foi atualizado...\n";
    if (strpos($footer, 'pronto_frontend_theme') !== false) {
        echo "footer.php: Já possui pronto_frontend_theme.\n";
    } else {
        echo "footer.php: ATENÇÃO - Bloco não encontrado para substituição.\n";
    }
}
