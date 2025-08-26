<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ApprovalController;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard (Breeze)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Auth routes (Breeze)
require __DIR__ . '/auth.php';

// ===== Rutas protegidas =====
Route::middleware('auth')->group(function () {

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Área Admin (solo Administrador)
    Route::prefix('admin')->name('admin.')->middleware('role:Administrador')->group(function () {
        Route::resource('departments', DepartmentController::class)
            ->parameters(['departments' => 'department'])
            ->except(['show']);

        Route::patch('departments/{department}/toggle', [DepartmentController::class, 'toggle'])
            ->name('departments.toggle');

        Route::resource('users', UserController::class)->except(['show']);
    });

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'fetch'])->name('calendar.fetch');
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    // Tickets
    Route::resource('tickets', TicketController::class); // usa {ticket} por defecto
    Route::post('tickets/{ticket}/asignar', [TicketController::class, 'asignar'])->name('tickets.asignar');
    Route::post('tickets/{ticket}/comentarios', [TicketController::class, 'comentar'])->name('tickets.comentar');
    Route::post('tickets/{ticket}/estado', [TicketController::class, 'cambiarEstado'])->name('tickets.cambiarEstado');
    Route::post('tickets/{ticket}/meta', [TicketController::class, 'setMeta'])->name('tickets.setMeta');
    Route::post('tickets/{ticket}/attachments', [TicketController::class, 'uploadAttachment'])->name('tickets.attachments.upload');
    Route::delete('tickets/{ticket}/attachments/{attachment}', [TicketController::class, 'deleteAttachment'])->name('tickets.attachments.delete');
    Route::patch('tickets/{ticket}/management', [TicketController::class, 'managementUpdate'])->name('tickets.management.update');

    // Usuario
    Route::get('/solicitudes', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/solicitudes/crear/{type}', [RequestController::class, 'create'])->name('requests.create');
    Route::post('/solicitudes', [RequestController::class, 'store'])->name('requests.store');
    Route::get('/solicitudes/{request}', [RequestController::class, 'show'])->name('requests.show');

    // Aprobaciones
    Route::post('/solicitudes/{request}/approve', [ApprovalController::class, 'approve'])->name('requests.approve');
    Route::post('/solicitudes/{request}/reject', [ApprovalController::class, 'reject'])->name('requests.reject');

    // Documentos
    Route::get('/solicitudes/{request}/documento', [DocumentController::class, 'download'])->name('requests.document');
});
