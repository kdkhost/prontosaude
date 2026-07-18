<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador Pronto Saúde - Requisitos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .install-container { max-width: 800px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .status-icon { font-weight: bold; }
        .status-ok { color: #198754; }
        .status-fail { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container install-container">
        <h2 class="text-center mb-4 text-primary">Instalação Pronto Saúde (V2)</h2>
        <p class="text-muted text-center mb-4">Verificação de Requisitos do Sistema</p>

        <h4>Extensões PHP</h4>
        <ul class="list-group mb-4">
            @foreach($requirements as $req => $status)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $req }}
                    <span class="status-icon {{ $status ? 'status-ok' : 'status-fail' }}">
                        {{ $status ? '✔ OK' : '✖ FALHA' }}
                    </span>
                </li>
            @endforeach
        </ul>

        <h4>Permissões de Diretório</h4>
        <ul class="list-group mb-4">
            @foreach($permissions as $folder => $status)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $folder }}
                    <span class="status-icon {{ $status ? 'status-ok' : 'status-fail' }}">
                        {{ $status ? '✔ Writable' : '✖ Not Writable' }}
                    </span>
                </li>
            @endforeach
        </ul>

        <div class="text-center mt-4">
            @if($canInstall)
                <div class="alert alert-success">Todos os requisitos foram atendidos!</div>
                <button class="btn btn-primary btn-lg" onclick="alert('Funcionalidade de instalação será acoplada ao frontend na próxima fase.')">Continuar Instalação</button>
            @else
                <div class="alert alert-danger">Por favor, resolva os problemas acima antes de prosseguir com a instalação.</div>
                <button class="btn btn-secondary btn-lg" onclick="location.reload()">Verificar Novamente</button>
            @endif
        </div>
    </div>
</body>
</html>
