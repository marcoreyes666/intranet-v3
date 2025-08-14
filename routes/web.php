<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CalendarController;

Route::get('/', function () {
    return view('welcome');
});

// Autenticados y verificados (Breeze)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ===== Rutas de autenticación (Breeze) =====
// IMPORTANTE: Deben ir fuera de cualquier grupo 'auth'
require __DIR__.'/auth.php';

// ===== Rutas protegidas (requieren login) =====
Route::middleware('auth')->group(function () {

    // Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== PRUEBAS DE ROLES (Spatie) =====

    // Solo Administrador
    Route::get('/solo-admin', function () {
        return 'Hola Admin';
    })->middleware('role:Administrador');

    // Admin o Rector
    Route::get('/admin-o-rector', function () {
        return 'Acceso: Admin o Rector';
    })->middleware('role:Administrador|Rector');

    // ===== Área Admin (solo Administrador) =====
    Route::prefix('admin')->name('admin.')->middleware('role:Administrador')->group(function () {
        Route::resource('departments', DepartmentController::class)
            ->parameters(['departments' => 'department'])
            ->except(['show']);

        Route::patch('departments/{department}/toggle', [DepartmentController::class, 'toggle'])
            ->name('departments.toggle');
    });
    
    Route::middleware(['auth','role:Administrador'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
    Route::get('/calendar', [CalendarController::class, 'index'])
    ->middleware(['auth'])
    ->name('calendar.index');

    Route::middleware(['auth','role:Administrador|Encargado de departamento|Rector'])->group(function () {
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
});
});
