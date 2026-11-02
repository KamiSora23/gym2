<?php

use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\ProfileController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------
// Sitio público
// ---------------------------------------------------------------
Route::view('/', 'index')->name('home');
Route::view('/shop', 'shop')->name('shop');
Route::view('/carrito', 'carrito')->name('carrito');
Route::view('/checkout', 'checkout')->name('checkout');

// ---------------------------------------------------------------
// Usuarios autenticados
// ---------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Panel administrativo: todo el personal (el super admin siempre tiene acceso).
    Route::middleware('role:'.implode(',', Role::STAFF))->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::post('/asistencias', [AsistenciaController::class, 'store'])
            ->middleware('role:admin,recepcionista,entrenador')
            ->name('asistencias.store');

        Route::post('/gastos', [GastoController::class, 'store'])
            ->middleware('role:admin,cajera')
            ->name('gastos.store');

        Route::post('/configuracion', [ConfiguracionController::class, 'store'])
            ->middleware('role:admin')
            ->name('configuracion.store');
    });

    // Gestión de usuarios: solo super administrador.
    Route::middleware('role:'.Role::SUPER_ADMIN)
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('usuarios', UsuarioController::class)->only(['store', 'update', 'destroy']);
        });
});

require __DIR__.'/auth.php';
