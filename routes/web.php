<?php

use App\Http\Controllers\Admin\EmpresaController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\SucursalController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ClaseController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ContextoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\EntrenadorController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\MiCuentaController;
use App\Http\Controllers\NutricionController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\Plataforma\PlataformaController;
use App\Http\Controllers\Plataforma\UsuarioGlobalController;
use App\Http\Controllers\PortalController;
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
Route::get('/', [PortalController::class, 'inicio'])->name('home');
Route::get('/shop', [PortalController::class, 'tienda'])->name('shop');
Route::get('/carrito', [PortalController::class, 'carrito'])->name('carrito');
Route::get('/checkout', [PortalController::class, 'checkout'])->name('checkout');

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
            Route::get('/plataforma/auditoria', [AuditoriaController::class, 'plataforma'])->name('plataforma.auditoria');
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
        Route::post('/pedidos', [PedidoController::class, 'store'])->middleware('throttle:10,1')->name('pedidos.store');
        Route::patch('/mi-cuenta/pedidos/{venta}/cancelar', [PedidoController::class, 'cancelar'])->name('pedidos.cancelar');
    });

    // ---------- Panel de la empresa (personal) ----------
    // Cada ruta exige un permiso del motor de autorización (ver App\Support\CatalogoPermisos).
    Route::middleware(['personal', 'empresa'])->group(function () {
        Route::post('/contexto/sucursal', [ContextoController::class, 'sucursal'])->name('contexto.sucursal');

        Route::get('/dashboard', DashboardController::class)
            ->middleware(Permisos::middleware('dashboard.view'))
            ->name('dashboard');

        Route::middleware(Permisos::middleware('clients.view|clients.view_assigned'))->group(function () {
            Route::resource('clientes', ClienteController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::patch('/clientes/{cliente}/estado', [ClienteController::class, 'estado'])->name('clientes.estado');
            Route::patch('/membresias/{membresia}/congelar', [ClienteController::class, 'congelar'])->name('membresias.congelar');
            Route::patch('/membresias/{membresia}/reanudar', [ClienteController::class, 'reanudar'])->name('membresias.reanudar');

            // Rutinas, progreso y nutrición del cliente (según asignación).
            Route::post('/clientes/{cliente}/rutinas', [RutinaController::class, 'store'])->name('rutinas.store');
            Route::put('/rutinas/{rutina}', [RutinaController::class, 'update'])->name('rutinas.update');
            Route::patch('/rutinas/{rutina}/activar', [RutinaController::class, 'activar'])->name('rutinas.activar');
            Route::delete('/rutinas/{rutina}', [RutinaController::class, 'destroy'])->name('rutinas.destroy');
            Route::post('/clientes/{cliente}/progreso', [RutinaController::class, 'progreso'])->name('progreso.store');
            Route::delete('/progreso/{progreso}', [RutinaController::class, 'eliminarProgreso'])->name('progreso.destroy');
            Route::post('/clientes/{cliente}/nutricion', [NutricionController::class, 'store'])->name('nutricion.store');
            Route::delete('/nutricion/{plan}', [NutricionController::class, 'destroy'])->name('nutricion.destroy');
        });

        Route::get('/nutricion', [NutricionController::class, 'index'])
            ->middleware(Permisos::middleware('nutrition.view'))
            ->name('nutricion.index');

        Route::middleware(Permisos::middleware('attendance.view|attendance.create'))->group(function () {
            Route::get('/asistencias', [AsistenciaController::class, 'index'])->name('asistencias.index');
            Route::post('/asistencias', [AsistenciaController::class, 'store'])->name('asistencias.store');
            Route::patch('/asistencias/{asistencia}/salida', [AsistenciaController::class, 'salida'])->name('asistencias.salida');
        });

        Route::middleware(Permisos::middleware('sales.view|sales.view_own|sales.create'))->group(function () {
            Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
            Route::get('/ventas/nueva', [VentaController::class, 'create'])->name('ventas.create');
            Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
            Route::get('/ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');
            Route::patch('/ventas/{venta}/cobrar', [VentaController::class, 'cobrar'])->name('ventas.cobrar');
            Route::patch('/ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');
        });

        Route::middleware(Permisos::middleware('cash.open|cash.view_all|cash.manage'))->group(function () {
            Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
            Route::post('/caja/abrir', [CajaController::class, 'abrir'])->name('caja.abrir');
            Route::post('/caja/movimientos', [CajaController::class, 'movimiento'])->name('caja.movimiento');
            Route::post('/caja/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');
            Route::get('/caja/sesiones/{sesion}', [CajaController::class, 'show'])->name('caja.sesion');
            Route::patch('/caja/sesiones/{sesion}/aprobar', [CajaController::class, 'aprobar'])->name('caja.aprobar');
            Route::get('/cajas', [CajaController::class, 'gestion'])->name('cajas.index');
            Route::post('/cajas', [CajaController::class, 'crearCaja'])->name('cajas.store');
            Route::put('/cajas/{caja}', [CajaController::class, 'actualizarCaja'])->name('cajas.update');
        });

        Route::middleware(Permisos::middleware('expenses.view|expenses.create'))->group(function () {
            Route::resource('gastos', GastoController::class)->only(['index', 'store', 'destroy']);
        });

        Route::middleware(Permisos::middleware('products.view|inventory.view'))->group(function () {
            Route::get('/catalogo', [ProductoController::class, 'index'])->name('productos.index');
            Route::post('/catalogo', [ProductoController::class, 'store'])->name('productos.store');
            Route::put('/catalogo/{producto}', [ProductoController::class, 'update'])->name('productos.update');
            Route::patch('/catalogo/{producto}/estado', [ProductoController::class, 'estado'])->name('productos.estado');
            Route::post('/catalogo/{producto}/stock', [ProductoController::class, 'stock'])->name('productos.stock');
            Route::post('/catalogo-categorias', [ProductoController::class, 'categoria'])->name('productos.categoria');
            Route::get('/inventario/movimientos', [ProductoController::class, 'movimientos'])->name('inventario.movimientos');
        });

        Route::middleware(Permisos::middleware('memberships.view'))->group(function () {
            Route::resource('planes', PlanController::class)->only(['index', 'store', 'update', 'destroy'])
                ->parameters(['planes' => 'plan']);
        });

        Route::middleware(Permisos::middleware('classes.view|classes.manage_own'))->group(function () {
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

        Route::middleware(Permisos::middleware('branches.manage'))->group(function () {
            Route::resource('sucursales', SucursalController::class)->only(['index', 'store', 'update', 'destroy'])
                ->parameters(['sucursales' => 'sucursal']);
        });

        Route::middleware(Permisos::middleware('users.view'))->group(function () {
            Route::resource('usuarios', UsuarioController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::put('/usuarios/{usuario}/permisos', [UsuarioController::class, 'permisos'])->name('usuarios.permisos');
            Route::patch('/usuarios/{usuario}/bloqueo', [UsuarioController::class, 'bloqueo'])->name('usuarios.bloqueo');
        });

        Route::middleware(Permisos::middleware('roles.manage'))->group(function () {
            Route::get('/roles', [RolController::class, 'index'])->name('roles.index');
            Route::post('/roles', [RolController::class, 'store'])->name('roles.store');
            Route::put('/roles/{rol}', [RolController::class, 'update'])->name('roles.update');
            Route::delete('/roles/{rol}', [RolController::class, 'destroy'])->name('roles.destroy');
        });

        Route::get('/auditoria', [AuditoriaController::class, 'index'])
            ->middleware(Permisos::middleware('audit.view'))
            ->name('auditoria.index');

        Route::middleware(Permisos::middleware('settings.manage'))->group(function () {
            Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
            Route::post('/configuracion', [ConfiguracionController::class, 'store'])->name('configuracion.store');
        });
    });
});

require __DIR__.'/auth.php';
