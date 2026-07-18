@extends('layouts.admin')

@section('title', 'Editar Médico - Pronto Saúde')
@section('page_title', 'Editar Médico')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Atualizar Informações de: {{ $doctor->name }}</h3>
            </div>
            <form action="{{ route('doctors.update', $doctor) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $doctor->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="registro" class="form-label">Registro Profissional (CRM/CRO etc.)</label>
                            <input type="text" name="registro" id="registro" class="form-control @error('registro') is-invalid @enderror" value="{{ old('registro', $doctor->registro) }}">
                            @error('registro')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">E-mail de Contato</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $doctor->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Telefone</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $doctor->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="clinica" class="form-label">Clínica / Especialidade</label>
                            <input type="text" name="clinica" id="clinica" class="form-control @error('clinica') is-invalid @enderror" value="{{ old('clinica', $doctor->clinica) }}">
                            @error('clinica')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="Active" {{ $doctor->status === 'Active' ? 'selected' : '' }}>Ativo</option>
                                <option value="Inactive" {{ $doctor->status === 'Inactive' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="degree" class="form-label">Formação / Especializações</label>
                        <textarea name="degree" id="degree" rows="2" class="form-control">{{ old('degree', $doctor->degree) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="detail" class="form-label">Biografia / Detalhes</label>
                        <textarea name="detail" id="detail" rows="4" class="form-control">{{ old('detail', $doctor->detail) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            @if($doctor->photo)
                                <div class="mb-2">
                                    <label class="form-label d-block">Foto Atual</label>
                                    <img src="{{ $doctor->photo }}" alt="Foto Atual" class="img-thumbnail" style="max-height: 120px;">
                                </div>
                            @endif
                            <x-dropzone-upload 
                                name="photo" 
                                url="{{ route('admin.upload') }}" 
                                maxFilesize="2" 
                                hintDimensions="Foto de perfil recomendada: 400x400px (1:1)"
                            >
                                Substituir Foto de Perfil
                            </x-dropzone-upload>
                        </div>
                        <div class="col-md-6 mb-3">
                            @if($doctor->banner)
                                <div class="mb-2">
                                    <label class="form-label d-block">Banner Atual</label>
                                    <img src="{{ $doctor->banner }}" alt="Banner Atual" class="img-thumbnail" style="max-height: 120px;">
                                </div>
                            @endif
                            <x-dropzone-upload 
                                name="banner" 
                                url="{{ route('admin.upload') }}" 
                                maxFilesize="4" 
                                hintDimensions="Banner largo recomendado: 1200x400px"
                            >
                                Substituir Banner de Fundo
                            </x-dropzone-upload>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('doctors.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
