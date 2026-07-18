@extends('layouts.admin')

@section('title', 'Serviços - Pronto Saúde')
@section('page_title', 'Gerenciar Serviços')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de Serviços Cadastrados</h3>
                <div class="card-tools">
                    <a href="{{ route('services.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Novo Serviço
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
                            <th>Descrição Curta</th>
                            <th style="width: 200px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                            <tr>
                                <td>{{ $service->id }}</td>
                                <td>
                                    @if($service->photo)
                                        <img src="{{ $service->photo }}" alt="{{ $service->name }}" class="img-thumbnail" style="max-height: 50px;">
                                    @else
                                        <span class="badge text-bg-secondary">Sem Foto</span>
                                    @endif
                                </td>
                                <td><strong>{{ $service->name }}</strong></td>
                                <td>{{ Str::limit($service->short_description, 80) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('services.edit', $service) }}" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('services.destroy', $service) }}" method="POST" class="d-inline delete-form">
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
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Nenhum serviço cadastrado ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($services->hasPages())
                <div class="card-footer">
                    {{ $services->links() }}
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
