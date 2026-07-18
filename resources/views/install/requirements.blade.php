@extends('install.layout')

@section('title', 'Etapa 1 - Requisitos do Sistema')
@section('step1', 'active')

@section('content')
<h5 class="mb-3"><i class="fas fa-clipboard-check text-primary me-2"></i>Etapa 1: Verificação de Requisitos</h5>

<h6 class="mt-3 mb-2">Extensões PHP</h6>
<ul class="list-group mb-4">
    @foreach($requirements as $req => $status)
        <li class="list-group-item d-flex justify-content-between align-items-center">
            {{ $req }}
            <span class="{{ $status ? 'status-ok' : 'status-fail' }}">
                <i class="fas {{ $status ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                {{ $status ? 'OK' : 'FALHA' }}
            </span>
        </li>
    @endforeach
</ul>

<h6 class="mt-3 mb-2">Permissões de Diretório</h6>
<ul class="list-group mb-4">
    @foreach($permissions as $folder => $status)
        <li class="list-group-item d-flex justify-content-between align-items-center">
            {{ $folder }}
            <span class="{{ $status ? 'status-ok' : 'status-fail' }}">
                <i class="fas {{ $status ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                {{ $status ? 'Gravável' : 'Sem Permissão' }}
            </span>
        </li>
    @endforeach
</ul>

<div class="text-end mt-4">
    @if($canInstall)
        <a href="{{ route('install.database') }}" class="btn btn-primary btn-lg">
            Próximo <i class="fas fa-arrow-right ms-2"></i>
        </a>
    @else
        <div class="alert alert-danger mb-3">Corrija os problemas acima antes de prosseguir.</div>
        <button class="btn btn-secondary btn-lg" onclick="location.reload()">
            <i class="fas fa-sync me-2"></i> Verificar Novamente
        </button>
    @endif
</div>
@endsection
