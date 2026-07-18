@extends('layouts.admin')

@section('title', 'Novo Serviço - Pronto Saúde')
@section('page_title', 'Cadastrar Novo Serviço')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Preencha os Dados do Serviço</h3>
            </div>
            <form action="{{ route('services.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome do Serviço</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="short_description" class="form-label">Descrição Curta (Exibida na listagem inicial)</label>
                        <textarea name="short_description" id="short_description" rows="3" class="form-control @error('short_description') is-invalid @enderror">{{ old('short_description') }}</textarea>
                        @error('short_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição Detalhada / Conteúdo</label>
                        <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Componentes de Upload Arrasta e Solta (Dropzone) com Dimensões Recomendadas -->
                    <x-dropzone-upload 
                        name="photo" 
                        url="{{ route('admin.upload') }}" 
                        maxFilesize="2" 
                        hintDimensions="Tamanho ideal: 400x300px (Proporção 4:3)"
                    >
                        Foto de Capa do Serviço
                    </x-dropzone-upload>

                    <x-dropzone-upload 
                        name="banner" 
                        url="{{ route('admin.upload') }}" 
                        maxFilesize="4" 
                        hintDimensions="Tamanho ideal: 1200x400px (Banner largo)"
                    >
                        Banner do Topo (Interna do Serviço)
                    </x-dropzone-upload>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('services.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Cadastrar Serviço</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
