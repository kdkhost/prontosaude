@extends('install.layout')

@section('title', 'Etapa 3 - Criar Conta Administrativa')
@section('step1', 'done')
@section('step2', 'done')
@section('step3', 'active')

@section('content')
<h5 class="mb-3"><i class="fas fa-user-shield text-primary me-2"></i>Etapa 3: Administrador do Sistema</h5>
<p class="text-muted mb-4">Crie a conta de acesso principal que será usada para gerenciar o painel.</p>

@if($errors->has('migration'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ $errors->first('migration') }}
    </div>
@endif

<form action="{{ route('install.setup-admin') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">Nome do Administrador</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">E-mail de Acesso</label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Senha</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('install.database') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Voltar
        </a>
        <button type="submit" class="btn btn-success btn-lg">
            <i class="fas fa-rocket me-2"></i> Instalar Sistema
        </button>
    </div>
</form>
@endsection
