<?php

use App\Models\Cliente;
use App\Models\Ingreso;
use App\Models\Inventario;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Producto;
use App\Models\Role;
use App\Models\TipoProducto;
use App\Models\User;
use App\Models\Venta;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
    abrirCaja($this->admin);

    $e = ['id_empresa' => $this->admin->id_empresa];
    $this->tipo = TipoProducto::create($e + ['nombre' => 'Suplementos']);
    $this->producto = Producto::create($e + ['id_tipo' => $this->tipo->id_tipo, 'nombre' => 'Proteína', 'precio' => 50, 'activo' => true]);
    Inventario::create(['id_producto' => $this->producto->id_producto, 'id_sucursal' => $this->admin->id_sucursal, 'cantidad' => 10, 'precio_compra' => 30]);
    $this->plan = Plan::create($e + ['nombre' => 'Mensual', 'precio' => 100, 'duracion_dias' => 30, 'activo' => true]);
    $this->cliente = Cliente::create($e + ['nombre' => 'Ana', 'dni' => '12345678']);
});

test('a sale discounts stock, creates the membership and the income', function () {
    $this->post('/ventas', [
        'id_cliente' => $this->cliente->id_cliente,
        'id_plan' => $this->plan->id_plan,
        'metodo_pago' => 'efectivo',
        'estado' => 'completado',
        'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 3]],
    ])->assertSessionHasNoErrors()->assertRedirect();

    $venta = Venta::first();

    expect((float) $venta->total)->toBe(250.0)
        ->and($venta->detalles)->toHaveCount(2)
        ->and($this->producto->fresh()->stockEn($this->admin->id_sucursal))->toBe(7)
        ->and((float) Ingreso::where('id_venta', $venta->id_venta)->value('monto'))->toBe(250.0);

    $membresia = Membresia::where('id_cliente', $this->cliente->id_cliente)->first();
    expect($membresia->fecha_inicio->isToday())->toBeTrue()
        ->and($membresia->fecha_fin->toDateString())->toBe(today()->addDays(29)->toDateString())
        ->and($this->cliente->fresh()->membresiaActiva)->not->toBeNull();

    $this->get("/ventas/{$venta->id_venta}")->assertOk()->assertSee('Proteína')->assertSee('Mensual');
});

test('renewing a membership starts when the current one ends', function () {
    foreach ([1, 2] as $_) {
        $this->post('/ventas', [
            'id_cliente' => $this->cliente->id_cliente,
            'id_plan' => $this->plan->id_plan,
            'metodo_pago' => 'tarjeta',
            'estado' => 'completado',
        ])->assertSessionHasNoErrors();
    }

    $segunda = Membresia::orderByDesc('id_membresia')->first();
    expect($segunda->fecha_inicio->toDateString())->toBe(today()->addDays(30)->toDateString());
});

test('a sale cannot exceed the available stock', function () {
    $this->post('/ventas', [
        'metodo_pago' => 'efectivo',
        'estado' => 'completado',
        'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 11]],
    ])->assertSessionHasErrors('items');

    expect(Venta::count())->toBe(0)
        ->and($this->producto->fresh()->stockEn($this->admin->id_sucursal))->toBe(10);
});

test('a membership requires a client and the sale requires items', function () {
    $this->post('/ventas', ['id_plan' => $this->plan->id_plan, 'metodo_pago' => 'efectivo', 'estado' => 'completado'])
        ->assertSessionHasErrors('id_cliente');

    $this->post('/ventas', ['metodo_pago' => 'efectivo', 'estado' => 'completado'])
        ->assertSessionHasErrors('items');
});

test('pending sales register income only when paid', function () {
    $this->post('/ventas', [
        'metodo_pago' => 'yape',
        'estado' => 'pendiente',
        'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 1]],
    ])->assertSessionHasNoErrors();

    $venta = Venta::first();
    expect(Ingreso::count())->toBe(0);

    $this->patch("/ventas/{$venta->id_venta}/cobrar")->assertSessionHas('success');

    expect($venta->fresh()->estado)->toBe('completado')
        ->and(Ingreso::count())->toBe(1);
});

test('voiding a sale returns stock, removes income and cancels the membership', function () {
    $this->post('/ventas', [
        'id_cliente' => $this->cliente->id_cliente,
        'id_plan' => $this->plan->id_plan,
        'metodo_pago' => 'efectivo',
        'estado' => 'completado',
        'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 4]],
    ]);

    $venta = Venta::first();
    $this->patch("/ventas/{$venta->id_venta}/anular")->assertSessionHasErrors('motivo');
    $this->patch("/ventas/{$venta->id_venta}/anular", ['motivo' => 'Cliente se arrepintió'])->assertSessionHas('success');

    expect($venta->fresh()->estado)->toBe('anulado')
        ->and($venta->fresh()->motivo_anulacion)->toBe('Cliente se arrepintió')
        ->and($venta->fresh()->anulada_por)->toBe($this->admin->id)
        ->and($this->producto->fresh()->stockEn($this->admin->id_sucursal))->toBe(10)
        ->and(Ingreso::count())->toBe(0)
        ->and(Membresia::first()->estado)->toBe('anulado')
        ->and($this->cliente->fresh()->membresiaActiva)->toBeNull();
});

test('receptionists can sell but not void sales', function () {
    $recepcion = User::factory()->conRol(Role::RECEPCIONISTA)->create(['id_empresa' => $this->admin->id_empresa, 'id_sucursal' => $this->admin->id_sucursal]);
    abrirCaja($recepcion);

    $this->actingAs($recepcion)->post('/ventas', [
        'metodo_pago' => 'efectivo',
        'estado' => 'completado',
        'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => 1]],
    ])->assertSessionHasNoErrors();

    $venta = Venta::first();
    expect($venta->id_sucursal)->toBe($recepcion->id_sucursal);

    $this->actingAs($recepcion)->patch("/ventas/{$venta->id_venta}/anular", ['motivo' => 'Prueba de permiso'])->assertForbidden();
});

test('catalog: products, categories and stock adjustments', function () {
    $this->post('/catalogo-categorias', ['nombre' => 'Bebidas'])->assertSessionHasNoErrors();
    $bebidas = TipoProducto::where('nombre', 'Bebidas')->firstOrFail();

    $this->post('/catalogo', ['nombre' => 'Agua', 'id_tipo' => $bebidas->id_tipo, 'precio' => 2, 'stock_inicial' => 24])
        ->assertSessionHasNoErrors();

    $agua = Producto::where('nombre', 'Agua')->firstOrFail();
    expect($agua->stockEn($this->admin->id_sucursal))->toBe(24);

    $this->post("/catalogo/{$agua->id_producto}/stock", ['cantidad' => -4])->assertSessionHas('error'); // falta el motivo
    $this->post("/catalogo/{$agua->id_producto}/stock", ['cantidad' => -4, 'tipo' => 'merma', 'motivo' => 'Botellas rotas'])->assertSessionHas('success');
    expect($agua->fresh()->stockEn($this->admin->id_sucursal))->toBe(20);

    $this->post("/catalogo/{$agua->id_producto}/stock", ['cantidad' => -50])->assertSessionHas('error');

    $this->get('/catalogo')->assertOk()->assertSee('Agua');
});

test('plans can be created and are deactivated instead of deleted when sold', function () {
    $this->post('/planes', ['nombre' => 'Anual', 'precio' => 900, 'duracion_dias' => 365])->assertSessionHasNoErrors();
    expect(Plan::where('nombre', 'Anual')->exists())->toBeTrue();

    $this->post('/ventas', ['id_cliente' => $this->cliente->id_cliente, 'id_plan' => $this->plan->id_plan, 'metodo_pago' => 'efectivo', 'estado' => 'completado']);
    $this->delete("/planes/{$this->plan->id_plan}")->assertSessionHas('info');

    expect($this->plan->fresh()->activo)->toBeFalse();
});
