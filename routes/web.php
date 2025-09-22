<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Requests\RequestFormController;
use App\Http\Controllers\Requests\RequestApprovalController;
use App\Http\Controllers\Requests\PurchaseCompletionController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\NotificationController;

Route::get('/', fn () => view('welcome'));

// Auth (Breeze)
require __DIR__ . '/auth.php';

// ===== Rutas protegidas =====
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== Calendar =====
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'fetch'])->name('calendar.fetch');
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
    Route::get('/calendar/events/{event}', [CalendarController::class, 'show'])->name('calendar.show');
    Route::match(['put','patch'], '/calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    // ===== Tickets =====
    Route::resource('tickets', TicketController::class);
    Route::post('tickets/{ticket}/asignar',     [TicketController::class, 'asignar'])->name('tickets.asignar');
    Route::post('tickets/{ticket}/estado',      [TicketController::class, 'cambiarEstado'])->name('tickets.estado');
    Route::post('tickets/{ticket}/meta',        [TicketController::class, 'setMeta'])->name('tickets.meta');
    Route::patch('tickets/{ticket}/management', [TicketController::class, 'managementUpdate'])->name('tickets.management');
    Route::post('tickets/{ticket}/comentar',    [TicketController::class, 'comentar'])->name('tickets.comentar');

    // ===== Admin =====
    Route::prefix('admin')->name('admin.')->middleware('role:Administrador')->group(function () {
        Route::resource('departments', DepartmentController::class)
            ->parameters(['departments' => 'department'])
            ->except(['show']);
        Route::patch('departments/{department}/toggle', [DepartmentController::class, 'toggle'])
            ->name('departments.toggle');

        Route::resource('users', UserController::class)->except(['show']);
    });

    // ===== Solicitudes =====
    Route::get('/requests',                    [RequestFormController::class, 'index'])->name('requests.index');
    Route::get('/requests/create/{type}',      [RequestFormController::class, 'create'])->name('requests.create');
    Route::post('/requests',                   [RequestFormController::class, 'store'])->name('requests.store');
    Route::get('/requests/{requestForm}',      [RequestFormController::class, 'show'])->name('requests.show');

    // Aprobaciones
    Route::post('/requests/{requestForm}/approve', [RequestApprovalController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{requestForm}/reject',  [RequestApprovalController::class, 'reject'])->name('requests.reject');

    // Completar compra
    Route::post('/requests/{requestForm}/complete', [PurchaseCompletionController::class, 'complete'])->name('requests.complete');

    // ===== Anuncios =====
    Route::get('/announcements/manage', [AnnouncementController::class, 'manage'])->name('announcements.manage');
    Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('/announcements',       [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])
        ->whereNumber('announcement')->name('announcements.edit');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])
        ->whereNumber('announcement')->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])
        ->whereNumber('announcement')->name('announcements.destroy');
    Route::get('/announcements/feed', [AnnouncementController::class, 'feed'])->name('announcements.feed');
    Route::post('/announcements/{announcement}/read', [AnnouncementController::class, 'markRead'])
        ->whereNumber('announcement')->name('announcements.read');

    // ===== Notificaciones =====
    Route::get('/notificaciones',            [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notificaciones/read-all',  [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::post('/notificaciones/{id}/read', [NotificationController::class, 'readOne'])->name('notifications.readOne');
    Route::get('/notificaciones/{id}/go',    [NotificationController::class, 'go'])->name('notifications.go');
});
