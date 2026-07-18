<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\InstallController;

// Rota principal do Frontend
Route::get('/', [MainController::class, 'index'])->name('frontend.home');
Route::post('/book', [MainController::class, 'book'])->name('frontend.book');

Route::get('/install', [InstallController::class, 'index'])->name('install');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/upload', [UploadController::class, 'store'])->name('admin.upload');
    
    // CRUD de Serviços
    Route::resource('/admin/services', ServiceController::class);
    
    // CRUD de Médicos
    Route::resource('/admin/doctors', DoctorController::class);
    
    // Configurações e PWA
    Route::get('/admin/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/admin/settings', [SettingController::class, 'update'])->name('settings.update');

    // Agendamentos (Kanban e FullCalendar)
    Route::get('/admin/appointments/calendar', [AppointmentController::class, 'calendar'])->name('appointments.calendar');
    Route::get('/admin/appointments/kanban', [AppointmentController::class, 'kanban'])->name('appointments.kanban');
    Route::get('/admin/appointments/feed', [AppointmentController::class, 'feed'])->name('appointments.feed');
    Route::post('/admin/appointments/update-date', [AppointmentController::class, 'updateDate'])->name('appointments.update-date');
    Route::post('/admin/appointments/update-status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
