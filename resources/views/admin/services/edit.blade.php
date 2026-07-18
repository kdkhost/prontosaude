@extends('layouts.admin')

@section('title', 'Editar Serviço - Pronto Saúde')
@section('page_title', 'Editar Serviço')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Atualizar Informações de: {{ $service->name }}</h3>
            </div>
            <form action="{{ route('services.update', $service) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome do Serviço</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $service->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="short_description" class="form-label">Descrição Curta (Exibida na listagem inicial)</label>
                        <textarea name="short_description" id="short_description" rows="3" class="form-control @error('short_description') is-invalid @enderror">{{ old('short_description', $service->short_description) }}</textarea>
                        @error('short_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição Detalhada / Conteúdo</label>
                        <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror">{{ old('description', $service->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Visualização da foto atual antes de permitir substituir via Dropzone -->
                    @if($service->photo)
                        <div class="mb-2">
                            <label class="form-label d-block">Foto Atual</label>
                            <img src="{{ $service->photo }}" alt="Foto Atual" class="img-thumbnail mb-2" style="max-height: 120px;">
                        </div>
                    @endif
                    <x-dropzone-upload 
                        name="photo" 
                        url="{{ route('admin.upload') }}" 
                        maxFilesize="2" 
                        hintDimensions="Tamanho ideal: 400x300px. Deixe em branco se não quiser alterar."
                    >
                        Substituir Foto de Capa
                    </x-dropzone-upload>

                    @if($service->banner)
                        <div class="mb-2 mt-4">
                            <label class="form-label d-block">Banner Atual</label>
                            <img src="{{ $service->banner }}" alt="Banner Atual" class="img-thumbnail mb-2" style="max-height: 120px;">
                        </div>
                    @endif
                    <x-dropzone-upload 
                        name="banner" 
                        url="{{ route('admin.upload') }}" 
                        maxFilesize="4" 
                        hintDimensions="Tamanho ideal: 1200x400px. Deixe em branco se não quiser alterar."
                    >
                        Substituir Banner do Topo
                    </x-dropzone-upload>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('services.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
