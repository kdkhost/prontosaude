@extends('install.layout')

@section('title', 'Instalação Concluída!')
@section('step1', 'done')
@section('step2', 'done')
@section('step3', 'done')
@section('step4', 'active')

@section('content')
<div class="text-center py-4">
    <div class="mb-4">
        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
    </div>
    <h3 class="fw-bold text-success mb-3">Instalação Concluída com Sucesso!</h3>
    <p class="text-muted mb-4">
        O sistema Pronto Saúde V2 foi instalado e configurado no seu servidor.
        <br>Agora você pode acessar o painel administrativo ou visitar o site público.
    </p>

    <div class="d-grid gap-3 col-md-8 mx-auto">
        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
            <i class="fas fa-sign-in-alt me-2"></i> Acessar o Painel Administrativo
        </a>
        <a href="{{ route('frontend.home') }}" class="btn btn-outline-success btn-lg">
            <i class="fas fa-globe me-2"></i> Visitar o Site Público
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    Swal.fire({
        title: 'Parabéns!',
        text: 'O Pronto Saúde V2 foi instalado com sucesso no seu servidor!',
        icon: 'success',
        confirmButtonText: 'Vamos lá!',
        confirmButtonColor: '#198754',
        timer: 5000,
        timerProgressBar: true
    });
</script>
@endpush
