<?php

use App\Models\Asistencia;
use App\Models\CajaSesion;
use App\Models\Clase;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Entrenador;
use App\Models\Gasto;
use App\Models\Plan;
use App\Models\Producto;
use App\Models\Rutina;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Route;

/**
 * Recorre todas las páginas del sistema con cada rol de la demo y verifica que ninguna
 * falle: cada respuesta debe ser 200 (permitido), 302 (redirección) o 403 (sin permiso).
 */
beforeEach(function () {
    $this->seed(DemoSeeder::class);
    $this->empresa = Empresa::where('nombre', 'GYM IMPULSO')->firstOrFail();
    $e = $this->empresa->id_empresa;

    // Una venta pendiente, una cobrada en caja y un cierre de caja para tener datos en todas las páginas.
    $admin = User::where('email', 'admin@gym.com')->firstOrFail();
    $this->actingAs($admin);
    abrirCaja($admin);
    $producto = Producto::withoutGlobalScopes()->where('id_empresa', $e)->firstOrFail();
    $this->post('/ventas', ['metodo_pago' => 'efectivo', 'estado' => 'completado', 'items' => [['id_producto' => $producto->id_producto, 'cantidad' => 1]]]);
    $this->post('/caja/cerrar', ['contado' => ['efectivo' => 0, 'tarjeta' => 0, 'transferencia' => 0, 'yape' => 0]]);
    auth()->logout();

    $id = fn ($modelo, $clave) => $modelo::withoutGlobalScopes()->where('id_empresa', $e)->value($clave);

    $this->params = [
        'cliente' => $id(Cliente::class, 'id_cliente'),
        'venta' => $id(Venta::class, 'id_venta'),
        'sesion' => CajaSesion::withoutGlobalScopes()->where('id_empresa', $e)->value('id_sesion'),
        'entrenador' => $id(Entrenador::class, 'id_entrenador'),
        'usuario' => User::where('id_empresa', $e)->value('id'),
        'clase' => $id(Clase::class, 'id_clase'),
        'plan' => $id(Plan::class, 'id_plan'),
        'gasto' => $id(Gasto::class, 'id_gasto'),
        'rutina' => $id(Rutina::class, 'id_rutina'),
        'sucursal' => $id(Sucursal::class, 'id_sucursal'),
        'asistencia' => $id(Asistencia::class, 'id_asistencia'),
    ];
});

/**
 * Todas las rutas GET del panel con sus parámetros resueltos.
 */
function rutasGet(array $params): array
{
    $urls = [];

    foreach (Route::getRoutes() as $ruta) {
        if (! in_array('GET', $ruta->methods()) || str_starts_with($ruta->uri(), '_') || in_array($ruta->uri(), ['up', 'storage/{path}'])) {
            continue;
        }

        $uri = $ruta->uri();
        $faltan = false;

        $uri = preg_replace_callback('/\{(\w+)\??\}/', function ($m) use ($params, &$faltan) {
            if (! isset($params[$m[1]])) {
                $faltan = true;

                return '';
            }

            return $params[$m[1]];
        }, $uri);

        if (! $faltan && ! str_contains($uri, 'verify-email/') && ! str_contains($uri, 'reset-password/')) {
            $urls[] = '/'.ltrim($uri, '/');
        }
    }

    return array_unique($urls);
}

dataset('usuarios', [
    'admin' => ['admin@gym.com'],
    'gerente' => ['gerente@gymimpulso.test'],
    'recepción' => ['recepcionista@gymimpulso.test'],
    'cajero' => ['cajera@gymimpulso.test'],
    'entrenador' => ['entrenador@gymimpulso.test'],
    'nutricionista' => ['nutricionista@gymimpulso.test'],
    'almacenero' => ['almacenero@gymimpulso.test'],
    'super admin' => ['superadmin@gym.com'],
]);

test('ninguna página falla para', function (string $email) {
    $user = User::where('email', $email)->firstOrFail();
    $errores = [];

    $sesion = $user->isSuperAdmin() ? ['empresa_id' => $this->empresa->id_empresa] : [];

    foreach (rutasGet($this->params) as $url) {
        $respuesta = $this->actingAs($user)->withSession($sesion)->get($url);

        if (! in_array($respuesta->status(), [200, 302, 403], true)) {
            $errores[] = "{$url} → {$respuesta->status()}";
        }
    }

    expect($errores)->toBe([]);
})->with('usuarios');

test('el portal público y el área del cliente no fallan', function () {
    foreach (['/', '/shop', '/carrito', '/checkout', '/login', '/register', '/forgot-password'] as $url) {
        $this->get($url)->assertOk();
    }

    $socio = User::factory()->create(['id_empresa' => $this->empresa->id_empresa]);
    Cliente::withoutGlobalScopes()->where('id_empresa', $this->empresa->id_empresa)->first()->update(['user_id' => $socio->id]);

    foreach (['/', '/shop', '/carrito', '/checkout', '/mi-cuenta', '/profile'] as $url) {
        $this->actingAs($socio)->get($url)->assertOk();
    }

    // Un cliente que intenta entrar al panel vuelve al sitio público.
    $this->actingAs($socio)->get('/dashboard')->assertRedirect('/');
    $this->actingAs($socio)->get('/caja')->assertRedirect('/');
});
