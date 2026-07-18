<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Pronto Saúde')</title>
    
    <!-- Vite CSS -->
    @vite(['resources/css/admin.css'])
    
    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <!-- Navbar -->
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="fas fa-bars"></i>
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <!-- Configurações PWA e Módulos -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" title="Configurações PWA">
                            <i class="fas fa-mobile-alt"></i> PWA
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Sidebar -->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="/admin" class="brand-link">
                    <span class="brand-text fw-light">Pronto Saúde</span>
                </a>
            </div>
            <div class="sidebar-wrapper">
                <nav class="mt-2">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="/admin" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        
                        <li class="nav-header">AGENDAMENTOS</li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Calendário</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-columns"></i>
                                <p>Kanban</p>
                            </a>
                        </li>

                        <li class="nav-header">MÓDULOS (FRONTEND)</li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-stethoscope"></i>
                                <p>Serviços</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-user-md"></i>
                                <p>Médicos</p>
                            </a>
                        </li>
                        
                        <li class="nav-header">SISTEMA</li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Configurações</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>Permissões & ACL</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">@yield('page_title')</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">Pronto Saúde V2 (Laravel)</div>
            <strong>Copyright &copy; {{ date('Y') }}</strong>
        </footer>
    </div>

    <!-- Vite JS -->
    @vite(['resources/js/admin.js'])
    
    <!-- Scripts Adicionais (Modulares) -->
    @stack('scripts')
    
    <!-- Toastr Alertas Globais (Flash Messages) -->
    @if(session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            toastr.success("{{ session('success') }}");
        });
    </script>
    @endif
    @if(session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            toastr.error("{{ session('error') }}");
        });
    </script>
    @endif
</body>
</html>
