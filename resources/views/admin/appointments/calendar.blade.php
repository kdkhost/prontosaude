@extends('layouts.admin')

@section('title', 'Agenda de Consultas e Exames')
@section('page_title', 'Calendário de Agendamentos')

@push('styles')
<!-- FullCalendar 4 CSS -->
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.2/main.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.2/main.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.2/main.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-body p-0">
                <!-- Div que abrigará o FullCalendar -->
                <div id="calendar" class="p-3"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- FullCalendar 4 JS -->
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.2/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.2/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.2/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@4.4.2/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.2/locales/pt-br.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [ 'interaction', 'dayGrid', 'timeGrid' ],
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            locale: 'pt-br',
            navLinks: true,
            editable: true,
            eventLimit: true,
            events: "{{ route('appointments.feed') }}",
            eventDrop: function(info) {
                // Ao arrastar o evento no calendário, atualiza a data no banco MariaDB via AJAX
                updateDate(info.event.id, info.event.start.toISOString());
            }
        });
        calendar.render();

        function updateDate(id, date) {
            fetch("{{ route('appointments.update-date') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ id: id, date: date })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Data do agendamento atualizada!');
                } else {
                    toastr.error('Erro ao atualizar agendamento.');
                }
            });
        }
    });
</script>
@endpush
