@extends('layouts.admin')

@section('title', 'Médicos - Pronto Saúde')
@section('page_title', 'Gerenciar Médicos')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de Médicos Cadastrados</h3>
                <div class="card-tools">
                    <a href="{{ route('doctors.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Novo Médico
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th style="width: 100px;">Foto</th>
                            <th>Nome</th>
                            <th>Registro Profissional</th>
                            <th>Status</th>
                            <th style="width: 200px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctors as $doctor)
                            <tr>
                                <td>{{ $doctor->id }}</td>
                                <td>
                                    @if($doctor->photo)
                                        <img src="{{ $doctor->photo }}" alt="{{ $doctor->name }}" class="img-thumbnail" style="max-height: 50px;">
                                    @else
                                        <span class="badge text-bg-secondary">Sem Foto</span>
                                    @endif
                                </td>
                                <td><strong>{{ $doctor->name }}</strong></td>
                                <td>{{ $doctor->registro ?? 'Não informado' }}</td>
                                <td>
                                    <span class="badge {{ $doctor->status === 'Active' ? 'text-bg-success' : 'text-bg-danger' }}">
                                        {{ $doctor->status === 'Active' ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('doctors.destroy', $doctor) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm delete-btn" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Nenhum médico cadastrado ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($doctors->hasPages())
                <div class="card-footer">
                    {{ $doctors->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                let form = this.closest('.delete-form');
                window.confirmAction(
                    'Deseja mesmo excluir?', 
                    'Esta ação não poderá ser desfeita!', 
                    () => form.submit()
                );
            });
        });
    });
</script>
@endpush
