<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $setting->pwa_name }}</title>
    
    <!-- Configurações PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="{{ $setting->pwa_theme_color }}">
    <link rel="apple-touch-icon" href="{{ $setting->pwa_icon_512 ?: '/favicon.ico' }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: {{ $setting->color ?? '#2ecc71' }};
        }
        body { font-family: 'Inter', sans-serif; }
        .hero { background: linear-gradient(135deg, var(--primary-color) 0%, #1e824c 100%); color: white; padding: 100px 0; }
        .section-title { position: relative; margin-bottom: 40px; padding-bottom: 10px; }
        .section-title::after { content: ''; position: absolute; left: 0; bottom: 0; width: 60px; height: 3px; background-color: var(--primary-color); }
        .nav-link.active { border-bottom: 2px solid var(--primary-color); }
        
        /* Menu deslizante Android */
        .offcanvas-menu { background-color: #2c3e50; color: white; }
        .offcanvas-menu .nav-link { color: rgba(255,255,255,0.8); font-size: 1.1rem; padding: 12px 20px; }
        .offcanvas-menu .nav-link:hover { color: white; background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark" href="#">
                @if($setting->logo)
                    <img src="{{ $setting->logo }}" alt="Logo" style="height: 40px;">
                @else
                    {{ $setting->pwa_name }}
                @endif
            </a>
            
            <!-- Botão de menu hamburguer que abre o menu deslizante Android em telas menores -->
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse d-none d-lg-block">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    @if($setting->enable_services)
                        <li class="nav-item"><a class="nav-link" href="#servicos">Serviços</a></li>
                    @endif
                    @if($setting->enable_doctors)
                        <li class="nav-item"><a class="nav-link" href="#medicos">Médicos</a></li>
                    @endif
                    @if($setting->enable_appointments)
                        <li class="nav-item"><a class="nav-link btn btn-primary text-white ms-2 px-3" href="#agendar">Agendar Online</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- Menu deslizante (Android Offcanvas Drawer) para Telas Menores -->
    <div class="offcanvas offcanvas-start offcanvas-menu" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title" id="mobileMenuLabel">{{ $setting->pwa_name }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <ul class="nav flex-column mt-3">
                <li class="nav-item"><a class="nav-link" href="#home" data-bs-dismiss="offcanvas">Home</a></li>
                @if($setting->enable_services)
                    <li class="nav-item"><a class="nav-link" href="#servicos" data-bs-dismiss="offcanvas">Serviços</a></li>
                @endif
                @if($setting->enable_doctors)
                    <li class="nav-item"><a class="nav-link" href="#medicos" data-bs-dismiss="offcanvas">Médicos</a></li>
                @endif
                @if($setting->enable_appointments)
                    <li class="nav-item p-3"><a class="btn btn-primary w-100 text-white" href="#agendar" data-bs-dismiss="offcanvas">Agendar Online</a></li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Hero Section -->
    <header id="home" class="hero text-center text-md-start">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold mb-3">Seu Bem-Estar e Saúde em Primeiro Lugar</h1>
                    <p class="lead mb-4">Agende suas consultas e exames de forma simples, rápida e 100% online.</p>
                    @if($setting->enable_appointments)
                        <a href="#agendar" class="btn btn-light btn-lg text-success fw-bold px-4">Agendar Agora</a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Seção de Serviços -->
    @if($setting->enable_services)
        <section id="servicos" class="py-5 bg-light">
            <div class="container">
                <h2 class="section-title">Nossos Serviços</h2>
                <div class="row g-4">
                    @forelse($services as $service)
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm">
                                @if($service->photo)
                                    <img src="{{ $service->photo }}" class="card-img-top" alt="{{ $service->name }}">
                                @endif
                                <div class="card-body">
                                    <h5 class="card-title fw-bold">{{ $service->name }}</h5>
                                    <p class="card-text text-muted">{{ $service->short_description }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">Serviços serão cadastrados em breve.</p>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    <!-- Seção de Médicos -->
    @if($setting->enable_doctors)
        <section id="medicos" class="py-5">
            <div class="container">
                <h2 class="section-title">Corpo Clínico</h2>
                <div class="row g-4">
                    @forelse($doctors as $doctor)
                        <div class="col-md-3">
                            <div class="card text-center border-0 shadow-sm h-100">
                                <div class="p-4">
                                    @if($doctor->photo)
                                        <img src="{{ $doctor->photo }}" alt="{{ $doctor->name }}" class="rounded-circle img-fluid mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 120px; height: 120px; font-size: 2.5rem;">
                                            <i class="far fa-user"></i>
                                        </div>
                                    @endif
                                    <h5 class="fw-bold mb-1">{{ $doctor->name }}</h5>
                                    <small class="text-success fw-bold d-block mb-2">{{ $doctor->clinica }}</small>
                                    <span class="text-muted small">{{ $doctor->registro }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">Médicos cadastrados em breve.</p>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    <!-- Seção de Agendamento -->
    @if($setting->enable_appointments)
        <section id="agendar" class="py-5 bg-light">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card border-0 shadow p-4">
                            <h3 class="fw-bold mb-4 text-center">Solicitar Agendamento</h3>
                            
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            <form action="{{ route('frontend.book') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="patient_name" class="form-label">Seu Nome Completo</label>
                                    <input type="text" name="patient_name" id="patient_name" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="patient_phone" class="form-label">Telefone Celular</label>
                                        <input type="text" name="patient_phone" id="patient_phone" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="type" class="form-label">Tipo de Agendamento</label>
                                        <select name="type" id="type" class="form-select" required>
                                            <option value="Consulta">Consulta Médica</option>
                                            <option value="Exame">Realização de Exame</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="appointment_date" class="form-label">Data e Hora Sugerida</label>
                                    <input type="datetime-local" name="appointment_date" id="appointment_date" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Observações Médicas (Opcional)</label>
                                    <textarea name="notes" id="notes" rows="3" class="form-control"></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-success btn-lg px-5">Enviar Solicitação</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <footer class="bg-dark text-white py-4 text-center">
        <div class="container">
            <p class="mb-1">{{ $setting->footer_copyright ?? '© ' . date('Y') . ' Pronto Saúde. Todos os direitos reservados.' }}</p>
            <small class="text-muted">{{ $setting->contact_address }}</small>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Registro do Service Worker do PWA -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').then(function() {
                console.log('PWA Service Worker registrado com sucesso!');
            });
        }
    </script>
</body>
</html>
