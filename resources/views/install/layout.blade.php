<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Instalador - Pronto Saúde V2')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root { --ps-primary: #0d6efd; --ps-gradient: linear-gradient(135deg, #0d6efd, #198754); }
        body { background: #f0f2f5; font-family: 'Inter', 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .install-wrapper { max-width: 700px; width: 100%; }
        .install-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,.08); overflow: hidden; }
        .install-header { background: var(--ps-gradient); color: white; padding: 30px; text-align: center; }
        .install-header h2 { margin: 0; font-weight: 700; }
        .install-header p { margin: 8px 0 0; opacity: 0.85; font-size: 0.95rem; }
        .install-body { padding: 30px; }
        .step-indicator { display: flex; justify-content: center; gap: 8px; margin-bottom: 25px; }
        .step-dot { width: 12px; height: 12px; border-radius: 50%; background: #dee2e6; transition: .3s; }
        .step-dot.active { background: #0d6efd; transform: scale(1.3); }
        .step-dot.done { background: #198754; }
        .status-ok { color: #198754; font-weight: 600; }
        .status-fail { color: #dc3545; font-weight: 600; }
    </style>
</head>
<body>
    <div class="install-wrapper">
        <div class="install-card">
            <div class="install-header">
                <h2><i class="fas fa-heartbeat me-2"></i>Pronto Saúde V2</h2>
                <p>Assistente de Instalação</p>
            </div>
            <div class="install-body">
                <!-- Indicador de Etapas -->
                <div class="step-indicator">
                    <div class="step-dot @yield('step1', '')"></div>
                    <div class="step-dot @yield('step2', '')"></div>
                    <div class="step-dot @yield('step3', '')"></div>
                    <div class="step-dot @yield('step4', '')"></div>
                </div>
                @yield('content')
            </div>
        </div>
        <p class="text-center text-muted mt-3 small">&copy; {{ date('Y') }} Pronto Saúde &mdash; Todos os direitos reservados.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>
</html>
