@extends('install.layout')

@section('title', 'Etapa 2 - Configuração do Banco de Dados')
@section('step1', 'done')
@section('step2', 'active')

@section('content')
<h5 class="mb-3"><i class="fas fa-database text-primary me-2"></i>Etapa 2: Banco de Dados MariaDB</h5>
<p class="text-muted mb-4">Informe as credenciais do seu servidor MariaDB. O banco de dados precisa existir previamente.</p>

@if($errors->has('db_connection'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ $errors->first('db_connection') }}
    </div>
@endif

<form action="{{ route('install.setup-database') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-8 mb-3">
            <label for="db_host" class="form-label">Host do Servidor</label>
            <input type="text" name="db_host" id="db_host" class="form-control" value="{{ old('db_host', '127.0.0.1') }}" required>
        </div>
        <div class="col-md-4 mb-3">
            <label for="db_port" class="form-label">Porta</label>
            <input type="number" name="db_port" id="db_port" class="form-control" value="{{ old('db_port', '3306') }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="db_database" class="form-label">Nome do Banco de Dados</label>
        <input type="text" name="db_database" id="db_database" class="form-control @error('db_database') is-invalid @enderror" value="{{ old('db_database', 'prontosaude_garden') }}" required>
        @error('db_database')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="db_username" class="form-label">Usuário</label>
            <input type="text" name="db_username" id="db_username" class="form-control" value="{{ old('db_username', 'root') }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="db_password" class="form-label">Senha</label>
            <input type="password" name="db_password" id="db_password" class="form-control" value="{{ old('db_password') }}">
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('install.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Voltar
        </a>
        <button type="submit" class="btn btn-primary btn-lg">
            Testar e Continuar <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>
</form>
@endsection
