@extends('layouts.admin')

@section('title', 'Kanban de Agendamentos')
@section('page_title', 'Quadro Kanban')

@push('styles')
<style>
    .kanban-board { display: flex; gap: 15px; overflow-x: auto; padding: 10px 0; }
    .kanban-col { flex: 1; min-width: 250px; background: #eef2f5; border-radius: 6px; padding: 12px; }
    .kanban-col h5 { margin-bottom: 15px; font-weight: bold; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
    .kanban-cards { min-height: 400px; display: flex; flex-direction: column; gap: 10px; }
    .kanban-card { background: #fff; padding: 10px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.15); cursor: grab; }
    .kanban-card:active { cursor: grabbing; }
</style>
@endpush

@section('content')
<div class="kanban-board">
    @foreach(['Pendente' => 'text-bg-warning', 'Confirmado' => 'text-bg-success', 'Cancelado' => 'text-bg-danger', 'Realizado' => 'text-bg-info'] as $status => $class)
        <div class="kanban-col">
            <h5 class="p-2 rounded {{ $class }} text-white">{{ $status }}</h5>
            <div class="kanban-cards" data-status="{{ $status }}" ondragover="allowDrop(event)" ondrop="drop(event, this)">
                @foreach($appointments->where('status', $status) as $app)
                    <div class="kanban-card" id="app_{{ $app->id }}" draggable="true" ondragstart="drag(event)">
                        <strong>{{ $app->patient_name }}</strong>
                        <div class="text-muted small">{{ $app->type }}</div>
                        <div class="text-muted small"><i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($app->appointment_date)->format('d/m H:i') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
    }

    function drop(ev, el) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        var card = document.getElementById(data);
        el.appendChild(card);

        var appId = data.split('_')[1];
        var newStatus = el.getAttribute('data-status');

        // Atualiza o status do agendamento no banco de dados MariaDB via AJAX
        fetch("{{ route('appointments.update-status') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ id: appId, status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                toastr.success('Status atualizado para: ' + newStatus);
            } else {
                toastr.error('Erro ao atualizar status.');
            }
        });
    }
</script>
@endpush
