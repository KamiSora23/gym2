<?php

use App\Http\Controllers\Admin\EmpresaController;
use App\Http\Controllers\Admin\SucursalController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\ClaseController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ContextoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\EntrenadorController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\MiCuentaController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\Plataforma\PlataformaController;
use App\Http\Controllers\Plataforma\UsuarioGlobalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RutinaController;
use App\Http\Controllers\VentaController;
use App\Http\Middleware\EnModoPlataforma;
use App\Models\Role;
use App\Support\Permisos;
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

    // ---------- Plataforma (solo super admin) ----------
    Route::middleware('role:'.Role::SUPER_ADMIN)->group(function () {
        Route::post('/contexto/empresa', [ContextoController::class, 'empresa'])->name('contexto.empresa');

        // Al entrar a estas páginas se sale de la empresa que se estaba administrando.
        Route::middleware(EnModoPlataforma::class)->group(function () {
            Route::get('/plataforma', [PlataformaController::class, 'index'])->name('plataforma.index');
            Route::get('/plataforma/roles', [PlataformaController::class, 'roles'])->name('plataforma.roles');
            Route::get('/plataforma/configuracion', [PlataformaController::class, 'configuracion'])->name('plataforma.configuracion');
            Route::post('/plataforma/configuracion', [PlataformaController::class, 'guardarConfiguracion'])->name('plataforma.configuracion.store');
            Route::resource('plataforma/usuarios', UsuarioGlobalController::class)
                ->only(['index', 'store', 'update', 'destroy'])
                ->names('plataforma.usuarios')
                ->parameters(['usuarios' => 'usuario']);
            Route::resource('empresas', EmpresaController::class)->only(['index', 'store', 'update', 'destroy']);
        });
    });

    // ---------- Área del cliente ----------
    Route::middleware('role:'.Role::CLIENTE)->group(function () {
        Route::get('/mi-cuenta', MiCuentaController::class)->name('mi-cuenta');
        Route::post('/mi-cuenta/clases/{clase}', [MiCuentaController::class, 'inscribirse'])->name('mi-cuenta.inscribirse');
        Route::delete('/mi-cuenta/clases/{clase}', [MiCuentaController::class, 'retirarse'])->name('mi-cuenta.retirarse');
    });

    // ---------- Panel de la empresa (todo el personal) ----------
    Route::middleware(['role:'.implode(',', Role::STAFF), 'empresa'])->group(function () {
        Route::post('/contexto/sucursal', [ContextoController::class, 'sucursal'])->name('contexto.sucursal');

        Route::get('/dashboard', DashboardController::class)
            ->middleware(Permisos::middleware('resumen'))
            ->name('dashboard');

        Route::middleware(Permisos::middleware('clientes'))->group(function () {
            Route::resource('clientes', ClienteController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

            // Rutinas y progreso del alumno (admin o su coach).
            Route::post('/clientes/{cliente}/rutinas', [RutinaController::class, 'store'])->name('rutinas.store');
            Route::put('/rutinas/{rutina}', [RutinaController::class, 'update'])->name('rutinas.update');
            Route::patch('/rutinas/{rutina}/activar', [RutinaController::class, 'activar'])->name('rutinas.activar');
            Route::delete('/rutinas/{rutina}', [RutinaController::class, 'destroy'])->name('rutinas.destroy');
            Route::post('/clientes/{cliente}/progreso', [RutinaController::class, 'progreso'])->name('progreso.store');
            Route::delete('/progreso/{progreso}', [RutinaController::class, 'eliminarProgreso'])->name('progreso.destroy');
        });

        Route::middleware(Permisos::middleware('asistencias'))->group(function () {
            Route::get('/asistencias', [AsistenciaController::class, 'index'])->name('asistencias.index');
            Route::post('/asistencias', [AsistenciaController::class, 'store'])->name('asistencias.store');
            Route::patch('/asistencias/{asistencia}/salida', [AsistenciaController::class, 'salida'])->name('asistencias.salida');
        });

        Route::middleware(Permisos::middleware('ventas'))->group(function () {
            Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
            Route::get('/ventas/nueva', [VentaController::class, 'create'])->name('ventas.create');
            Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
            Route::get('/ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');
            Route::patch('/ventas/{venta}/cobrar', [VentaController::class, 'cobrar'])->name('ventas.cobrar');
            Route::patch('/ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');
        });

        Route::middleware(Permisos::middleware('gastos'))->group(function () {
            Route::resource('gastos', GastoController::class)->only(['index', 'store', 'destroy']);
        });

        Route::middleware(Permisos::middleware('catalogo'))->group(function () {
            Route::get('/catalogo', [ProductoController::class, 'index'])->name('productos.index');
            Route::post('/catalogo', [ProductoController::class, 'store'])->name('productos.store');
            Route::put('/catalogo/{producto}', [ProductoController::class, 'update'])->name('productos.update');
            Route::delete('/catalogo/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
            Route::post('/catalogo/{producto}/stock', [ProductoController::class, 'stock'])->name('productos.stock');
            Route::post('/catalogo-categorias', [ProductoController::class, 'categoria'])->name('productos.categoria');
        });

        Route::middleware(Permisos::middleware('planes'))->group(function () {
            Route::resource('planes', PlanController::class)->only(['index', 'store', 'update', 'destroy'])
                ->parameters(['planes' => 'plan']);
        });

        Route::middleware(Permisos::middleware('entrenamiento'))->group(function () {
            Route::get('/entrenamiento', [ClaseController::class, 'index'])->name('entrenamiento.index');
            Route::get('/entrenamiento/mi-perfil', [DisponibilidadController::class, 'miPerfil'])->name('entrenamiento.mi-perfil');
            Route::get('/entrenadores/{entrenador}/disponibilidad', [DisponibilidadController::class, 'edit'])->name('entrenadores.disponibilidad');
            Route::put('/entrenadores/{entrenador}/disponibilidad', [DisponibilidadController::class, 'update'])->name('entrenadores.disponibilidad.update');
            Route::resource('entrenadores', EntrenadorController::class)->only(['store', 'update', 'destroy'])
                ->parameters(['entrenadores' => 'entrenador']);
            Route::resource('clases', ClaseController::class)->only(['store', 'update', 'destroy']);
            Route::post('/clases/{clase}/inscripciones', [ClaseController::class, 'inscribir'])->name('clases.inscribir');
            Route::delete('/clases/{clase}/inscripciones/{cliente}', [ClaseController::class, 'desinscribir'])->name('clases.desinscribir');
        });

        Route::middleware(Permisos::middleware('sucursales'))->group(function () {
            Route::resource('sucursales', SucursalController::class)->only(['index', 'store', 'update', 'destroy'])
                ->parameters(['sucursales' => 'sucursal']);
        });

        Route::middleware(Permisos::middleware('usuarios'))->group(function () {
            Route::resource('usuarios', UsuarioController::class)->only(['index', 'store', 'update', 'destroy']);
        });

        Route::middleware(Permisos::middleware('configuracion'))->group(function () {
            Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
            Route::post('/configuracion', [ConfiguracionController::class, 'store'])->name('configuracion.store');
        });
    });
});

require __DIR__.'/auth.php';
