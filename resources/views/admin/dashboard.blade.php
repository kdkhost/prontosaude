@extends('layouts.admin')

@section('title', 'Dashboard - Pronto Saúde')
@section('page_title', 'Visão Geral')

@section('content')
<div class="row">
    <!-- Caixa de Agendamentos Pendentes -->
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-warning">
            <div class="inner">
                <h3>15</h3>
                <p>Agendamentos Pendentes</p>
            </div>
            <div class="small-box-icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="#" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                Gerenciar no Kanban <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Caixa de Consultas Hoje -->
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-success">
            <div class="inner">
                <h3>53</h3>
                <p>Consultas Realizadas</p>
            </div>
            <div class="small-box-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                Ver Histórico <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Modulos Ativos -->
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-info">
            <div class="inner">
                <h3>6</h3>
                <p>Módulos Ativos no Site</p>
            </div>
            <div class="small-box-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <a href="#" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                Configurar Site <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- PWA Installs -->
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-primary">
            <div class="inner">
                <h3>2,401</h3>
                <p>Instalações do App PWA</p>
            </div>
            <div class="small-box-icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                Configurar PWA <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title">Resumo do FullCalendar (Agendamentos Hoje)</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted">Aqui será renderizado o FullCalendar 4 em versão mini, integrado diretamente com o banco de dados MariaDB.</p>
                <div id="mini-calendar"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Exemplo de integração do Toastr e SweetAlert via JS Modular
        // toastr.info('Painel AdminLTE 4 carregado com sucesso!');
    });
</script>
@endpush
