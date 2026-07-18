@extends('layouts.admin')

@section('title', 'Configurações do Sistema e PWA')
@section('page_title', 'Painel de Configurações')

@section('content')
<div class="row">
    <div class="col-md-12">
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card card-primary card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-geral-tab" data-bs-toggle="tab" href="#tab-geral" role="tab" aria-controls="tab-geral" aria-selected="true">Geral</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-modulos-tab" data-bs-toggle="tab" href="#tab-modulos" role="tab" aria-controls="tab-modulos" aria-selected="false">Ativação de Módulos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-pwa-tab" data-bs-toggle="tab" href="#tab-pwa" role="tab" aria-controls="tab-pwa" aria-selected="false">PWA (Aplicativo)</a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-one-tabContent">
                        
                        <!-- TAB GERAL -->
                        <div class="tab-pane fade show active" id="tab-geral" role="tabpanel" aria-labelledby="tab-geral-tab">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contact_email" class="form-label">E-mail de Contato</label>
                                    <input type="email" name="contact_email" id="contact_email" class="form-control" value="{{ $setting->contact_email ?? '' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contact_phone" class="form-label">Telefone de Contato</label>
                                    <input type="text" name="contact_phone" id="contact_phone" class="form-control" value="{{ $setting->contact_phone ?? '' }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="contact_address" class="form-label">Endereço Físico</label>
                                <input type="text" name="contact_address" id="contact_address" class="form-control" value="{{ $setting->contact_address ?? '' }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <x-dropzone-upload name="logo" url="{{ route('admin.upload') }}" hintDimensions="Logo Principal (.png) - 200x60px">
                                        Logo da Clínica
                                    </x-dropzone-upload>
                                </div>
                                <div class="col-md-6">
                                    <x-dropzone-upload name="favicon" url="{{ route('admin.upload') }}" hintDimensions="Favicon do Navegador - 32x32px">
                                        Favicon
                                    </x-dropzone-upload>
                                </div>
                            </div>
                        </div>

                        <!-- TAB MODULOS (Ativação granular solicitado: 'cada sessao do frontend deve ser modular com funcao de ativar') -->
                        <div class="tab-pane fade" id="tab-modulos" role="tabpanel" aria-labelledby="tab-modulos-tab">
                            <h5 class="mb-4">Ative ou Desative a visibilidade das seções no site público:</h5>
                            
                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="enable_services" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="enable_services" name="enable_services" value="1" {{ ($setting->enable_services ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_services">Exibir Seção de <strong>Serviços</strong></label>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="enable_doctors" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="enable_doctors" name="enable_doctors" value="1" {{ ($setting->enable_doctors ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_doctors">Exibir Seção de <strong>Médicos / Especialistas</strong></label>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="enable_testimonials" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="enable_testimonials" name="enable_testimonials" value="1" {{ ($setting->enable_testimonials ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_testimonials">Exibir Seção de <strong>Depoimentos</strong></label>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="enable_news" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="enable_news" name="enable_news" value="1" {{ ($setting->enable_news ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_news">Exibir Seção de <strong>Notícias / Blog</strong></label>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="enable_appointments" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="enable_appointments" name="enable_appointments" value="1" {{ ($setting->enable_appointments ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_appointments">Exibir Módulo de <strong>Agendamento Online</strong></label>
                            </div>
                        </div>

                        <!-- TAB PWA (Aplicativo solicitado: 'aplicativo opcional pwa completo e com personalização total pelo painel admin') -->
                        <div class="tab-pane fade" id="tab-pwa" role="tabpanel" aria-labelledby="tab-pwa-tab">
                            <h5 class="mb-3">Personalização Visual do Aplicativo Mobile (PWA)</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pwa_name" class="form-label">Nome do Aplicativo</label>
                                    <input type="text" name="pwa_name" id="pwa_name" class="form-control" value="{{ $setting->pwa_name ?? 'Pronto Saúde' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="pwa_short_name" class="form-label">Nome Curto (Exibido na tela inicial)</label>
                                    <input type="text" name="pwa_short_name" id="pwa_short_name" class="form-control" value="{{ $setting->pwa_short_name ?? 'ProntoSaude' }}">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pwa_theme_color" class="form-label">Cor do Tema (Barra do App)</label>
                                    <input type="color" name="pwa_theme_color" id="pwa_theme_color" class="form-control form-control-color" value="{{ $setting->pwa_theme_color ?? '#007bff' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="pwa_background_color" class="form-label">Cor do Plano de Fundo (Splash Screen)</label>
                                    <input type="color" name="pwa_background_color" id="pwa_background_color" class="form-control form-control-color" value="{{ $setting->pwa_background_color ?? '#ffffff' }}">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <x-dropzone-upload name="pwa_icon_512" url="{{ route('admin.upload') }}" hintDimensions="Ícone Quadrado (.png) - Mínimo 512x512px">
                                        Ícone do Aplicativo (Instalador PWA)
                                    </x-dropzone-upload>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary btn-lg">Salvar Configurações</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
