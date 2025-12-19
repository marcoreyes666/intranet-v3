<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Requests\RequestFormController;
use App\Http\Controllers\Requests\RequestApprovalController;
use App\Http\Controllers\Requests\PurchaseCompletionController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SoundRequestController;

// Página pública inicial (puedes cambiar a redirect al dashboard si quieres)
Route::get('/', fn() => view('welcome'));

// Auth (Breeze)
require __DIR__ . '/auth.php';

// ===== Rutas protegidas =====
Route::middleware('auth')->group(function () {

    // ===== Dashboard =====
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ===== Perfil =====
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // ===== Admin (usuarios / departamentos / eventos) =====
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:Administrador')
        ->group(function () {

            // Departamentos
            Route::resource('departments', DepartmentController::class)
                ->parameters(['departments' => 'department'])
                ->except(['show']);

            Route::patch('departments/{department}/toggle', [DepartmentController::class, 'toggle'])
                ->name('departments.toggle');

            // Usuarios
            Route::resource('users', UserController::class)
                ->except(['show']);

            // Eventos (solo gestión, no creación/edición)
            Route::resource('events', AdminEventController::class)
                ->only(['index', 'show', 'destroy'])
                ->parameters(['events' => 'event']);
        });

    // ===== Calendar =====
    Route::prefix('calendar')
        ->name('calendar.')
        ->group(function () {

            Route::get('/', [CalendarController::class, 'index'])
                ->name('index');

            Route::get('/events', [CalendarController::class, 'fetch'])
                ->name('fetch');

            Route::post('/events', [CalendarController::class, 'store'])
                ->name('store');

            Route::get('/events/{event}', [CalendarController::class, 'show'])
                ->name('show');

            Route::match(['put', 'patch'], '/events/{event}', [CalendarController::class, 'update'])
                ->name('update');

            Route::delete('/events/{event}', [CalendarController::class, 'destroy'])
                ->name('destroy');
        });

    // ===== Tickets =====
    Route::resource('tickets', TicketController::class);

    Route::post('tickets/{ticket}/asignar', [TicketController::class, 'asignar'])
        ->name('tickets.asignar');

    // Cambio de estado (autor / asignado)
    Route::post('tickets/{ticket}/estado', [TicketController::class, 'cambiarEstado'])
        ->name('tickets.cambiarEstado');

    // Set meta (categoría / prioridad)
    Route::post('tickets/{ticket}/meta', [TicketController::class, 'setMeta'])
        ->name('tickets.meta');

    // Gestión completa (encargado / admin)
    Route::patch('tickets/{ticket}/management', [TicketController::class, 'managementUpdate'])
        ->name('tickets.management');

    // Comentarios
    Route::post('tickets/{ticket}/comentar', [TicketController::class, 'comentar'])
        ->name('tickets.comentar');

    // Adjuntos desde show
    Route::post('tickets/{ticket}/attachments', [TicketController::class, 'uploadAttachment'])
        ->name('tickets.attachments.upload');

    Route::delete('tickets/{ticket}/attachments/{attachment}', [TicketController::class, 'deleteAttachment'])
        ->name('tickets.attachments.delete');

    // ===== Solicitud de sonido =====
    // Rutas generales (usuario normal)
    Route::resource('sound-requests', SoundRequestController::class)
        ->only(['index', 'create', 'store', 'edit', 'update']);

    // Cancelar (dueño de la solicitud o Admin/Sistemas)
    Route::post('sound-requests/{soundRequest}/cancel', [SoundRequestController::class, 'cancel'])
        ->name('sound-requests.cancel');

    // Rutas de revisión (solo Sistemas / Admin)
    Route::middleware('role:Administrador|Sistemas')->group(function () {

        Route::post('sound-requests/{soundRequest}/return', [SoundRequestController::class, 'returnToUser'])
            ->name('sound-requests.return');

        Route::post('sound-requests/{soundRequest}/accept', [SoundRequestController::class, 'accept'])
            ->name('sound-requests.accept');

        Route::post('sound-requests/{soundRequest}/reject', [SoundRequestController::class, 'reject'])
            ->name('sound-requests.reject');

        // Eliminar definitivamente
        Route::delete('sound-requests/{soundRequest}', [SoundRequestController::class, 'destroy'])
            ->name('sound-requests.destroy');
    });

    // ===== Solicitudes (permisos, compras, etc.) =====
    Route::prefix('requests')
        ->name('requests.')
        ->group(function () {

            Route::get('/', [RequestFormController::class, 'index'])
                ->name('index');

            Route::get('/create/{type}', [RequestFormController::class, 'create'])
                ->name('create');

            Route::post('/', [RequestFormController::class, 'store'])
                ->name('store');

            Route::get('/{requestForm}', [RequestFormController::class, 'show'])
                ->name('show');

            // Aprobaciones
            Route::post('/{requestForm}/approve', [RequestApprovalController::class, 'approve'])
                ->name('approve');

            Route::post('/{requestForm}/reject', [RequestApprovalController::class, 'reject'])
                ->name('reject');

            // Completar compra
            Route::post('/{requestForm}/complete', [PurchaseCompletionController::class, 'complete'])
                ->name('complete');
        });

    // ===== Anuncios =====
    Route::prefix('announcements')
        ->name('announcements.')
        ->group(function () {

            Route::get('/manage', [AnnouncementController::class, 'manage'])
                ->name('manage');

            Route::get('/create', [AnnouncementController::class, 'create'])
                ->name('create');

            Route::post('/', [AnnouncementController::class, 'store'])
                ->name('store');

            Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])
                ->whereNumber('announcement')
                ->name('edit');

            Route::put('/{announcement}', [AnnouncementController::class, 'update'])
                ->whereNumber('announcement')
                ->name('update');

            Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])
                ->whereNumber('announcement')
                ->name('destroy');

            Route::get('/feed', [AnnouncementController::class, 'feed'])
                ->name('feed');

            Route::post('/{announcement}/read', [AnnouncementController::class, 'markRead'])
                ->whereNumber('announcement')
                ->name('read');
        });

    // ===== Notificaciones =====
    Route::prefix('notificaciones')
        ->name('notifications.')
        ->group(function () {

            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/read-all', [NotificationController::class, 'readAll'])->name('readAll');
            Route::post('/{id}/read', [NotificationController::class, 'readOne'])->name('readOne');
            Route::get('/{id}/go', [NotificationController::class, 'go'])->name('go');
        });

    Route::get('/notifications/summary', [NotificationController::class, 'summary'])
        ->name('notifications.summary');
});
