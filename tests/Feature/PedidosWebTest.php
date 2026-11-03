<?php

use App\Models\Cliente;
use App\Models\Inventario;
use App\Models\InventarioMovimiento;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Producto;
use App\Models\Role;
use App\Models\TipoProducto;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    $this->admin = User::factory()->admin()->create();
    $this->e = ['id_empresa' => $this->admin->id_empresa];
    $this->sede = $this->admin->id_sucursal;

    $tipo = TipoProducto::create($this->e + ['nombre' => 'Suplementos']);
    $this->proteina = Producto::create($this->e + ['id_tipo' => $tipo->id_tipo, 'nombre' => 'Proteína Whey', 'precio' => 85, 'activo' => true]);
    Inventario::create(['id_producto' => $this->proteina->id_producto, 'id_sucursal' => $this->sede, 'cantidad' => 5]);
    Producto::create($this->e + ['id_tipo' => $tipo->id_tipo, 'nombre' => 'Producto Retirado', 'precio' => 10, 'activo' => false]);
    $this->plan = Plan::create($this->e + ['nombre' => 'Trimestral', 'precio' => 320, 'duracion_dias' => 90, 'activo' => true]);

    $this->socio = User::factory()->create($this->e);
    $this->cliente = Cliente::create($this->e + ['nombre' => $this->socio->name, 'dni' => '12345678', 'user_id' => $this->socio->id]);

    $this->pedido = fn (array $items, array $extra = []) => $this->actingAs($this->socio)->post('/pedidos', $extra + [
        'items' => json_encode($items),
        'metodo_pago' => 'yape',
        'id_sucursal' => $this->sede,
    ]);
});

test('la tienda y el inicio muestran el catálogo y los planes reales', function () {
    $this->get('/shop')->assertOk()
        ->assertSee('Proteína Whey')->assertSee('S/ 85.00')->assertSee('Suplementos')
        ->assertDontSee('Producto Retirado')->assertDontSee('Guantes de Entrenamiento');

    $this->get('/')->assertOk()->assertSee('Trimestral')->assertSee('S/ 320.00')->assertSee('Proteína Whey');
});

test('un visitante debe iniciar sesión para confirmar el pedido', function () {
    $this->get('/checkout')->assertOk()->assertSee('Inicia sesión para continuar');
    $this->post('/pedidos', ['items' => '[]'])->assertRedirect(route('login'));

    // Después de iniciar sesión vuelve al checkout.
    $this->post('/login', ['email' => $this->socio->email, 'password' => 'password'])->assertRedirect(route('checkout'));
});

test('el cliente confirma un pedido: queda pendiente, reserva stock y no activa la membresía aún', function () {
    ($this->pedido)([
        ['tipo' => 'producto', 'ref' => $this->proteina->id_producto, 'qty' => 2, 'precio' => 1], // el precio enviado se ignora
        ['tipo' => 'plan', 'ref' => $this->plan->id_plan, 'qty' => 1],
    ])->assertRedirect(route('mi-cuenta'));

    $venta = Venta::firstOrFail();

    expect($venta->estado)->toBe('pendiente')
        ->and($venta->origen)->toBe('web')
        ->and((float) $venta->total)->toBe(490.0)
        ->and($venta->id_cliente)->toBe($this->cliente->id_cliente)
        ->and($this->proteina->fresh()->stockEn($this->sede))->toBe(3)
        ->and(InventarioMovimiento::first()->motivo)->toBe('Pedido web')
        ->and(Membresia::count())->toBe(0);

    $this->actingAs($this->socio)->get('/mi-cuenta')->assertSee($venta->folio)->assertSee('Por pagar en recepción')->assertSee('Ver comprobante');
});

test('recepción ve el pedido web, lo cobra en su caja y se activa la membresía', function () {
    ($this->pedido)([['tipo' => 'plan', 'ref' => $this->plan->id_plan, 'qty' => 1]]);
    $venta = Venta::firstOrFail();

    $recepcion = User::factory()->conRol(Role::RECEPCIONISTA)->create($this->e + ['id_sucursal' => $this->sede]);

    $this->actingAs($recepcion)->get('/dashboard')->assertSee('Pedidos web por cobrar')->assertSee($venta->folio);
    $this->actingAs($recepcion)->get('/ventas')->assertSee($venta->folio);

    $this->actingAs($recepcion)->patch("/ventas/{$venta->id_venta}/cobrar")->assertSessionHasErrors(); // sin caja abierta
    $sesion = abrirCaja($recepcion);
    $this->actingAs($recepcion)->patch("/ventas/{$venta->id_venta}/cobrar")->assertSessionHas('success');

    $membresia = Membresia::firstOrFail();
    expect($venta->fresh()->estado)->toBe('completado')
        ->and($membresia->id_venta)->toBe($venta->id_venta)
        ->and($membresia->fecha_fin->toDateString())->toBe(today()->addDays(89)->toDateString())
        ->and($sesion->fresh()->movimientos()->sum('monto'))->toEqual('320.00');
});

test('el pedido valida stock, productos inactivos y una sola membresía', function () {
    ($this->pedido)([['tipo' => 'producto', 'ref' => $this->proteina->id_producto, 'qty' => 9]])->assertSessionHasErrors('items');

    $retirado = Producto::where('nombre', 'Producto Retirado')->first();
    ($this->pedido)([['tipo' => 'producto', 'ref' => $retirado->id_producto, 'qty' => 1]])->assertSessionHasErrors('items');

    $otro = Plan::create($this->e + ['nombre' => 'Mensual', 'precio' => 120, 'duracion_dias' => 30, 'activo' => true]);
    ($this->pedido)([['tipo' => 'plan', 'ref' => $this->plan->id_plan], ['tipo' => 'plan', 'ref' => $otro->id_plan]])->assertSessionHasErrors('items');

    ($this->pedido)([])->assertSessionHasErrors('items');

    expect(Venta::count())->toBe(0)->and($this->proteina->fresh()->stockEn($this->sede))->toBe(5);
});

test('el cliente cancela su pedido pendiente y se libera el stock', function () {
    ($this->pedido)([['tipo' => 'producto', 'ref' => $this->proteina->id_producto, 'qty' => 2]]);
    $venta = Venta::firstOrFail();

    // Otro cliente no puede cancelarlo.
    $otro = User::factory()->create($this->e);
    Cliente::create($this->e + ['nombre' => 'Otro', 'dni' => '999', 'user_id' => $otro->id]);
    $this->actingAs($otro)->patch("/mi-cuenta/pedidos/{$venta->id_venta}/cancelar")->assertNotFound();

    $this->actingAs($this->socio)->patch("/mi-cuenta/pedidos/{$venta->id_venta}/cancelar")->assertSessionHas('success');

    expect($venta->fresh()->estado)->toBe('anulado')
        ->and($this->proteina->fresh()->stockEn($this->sede))->toBe(5);

    $this->actingAs($this->socio)->patch("/mi-cuenta/pedidos/{$venta->id_venta}/cancelar")->assertSessionHas('error');
});

test('el personal no hace pedidos web', function () {
    $this->actingAs($this->admin)->get('/checkout')->assertOk()->assertSee('cuenta del personal');
    $this->actingAs($this->admin)->post('/pedidos', ['items' => '[]'])->assertForbidden();
    expect(Venta::count())->toBe(0);
});

test('el cliente ve el comprobante de su compra con el detalle, y no el de otros', function () {
    ($this->pedido)([
        ['tipo' => 'producto', 'ref' => $this->proteina->id_producto, 'qty' => 2],
        ['tipo' => 'plan', 'ref' => $this->plan->id_plan],
    ]);
    $venta = Venta::firstOrFail();

    $this->actingAs($this->socio)->get(route('mi-cuenta.compra', $venta->id_venta))->assertOk()
        ->assertSee($venta->folio)->assertSee('Proteína Whey')->assertSee('S/ 170.00')
        ->assertSee('Membresía Trimestral')->assertSee('S/ 490.00')->assertSee('Por pagar en recepción');

    $otro = User::factory()->create($this->e);
    Cliente::create($this->e + ['nombre' => 'Otro', 'dni' => '888', 'user_id' => $otro->id]);
    $this->actingAs($otro)->get(route('mi-cuenta.compra', $venta->id_venta))->assertNotFound();
});

test('el socio con plan vigente ve su plan actual, cuándo empieza uno nuevo y no puede pedir dos membresías por pagar', function () {
    Membresia::create(['id_cliente' => $this->cliente->id_cliente, 'id_plan' => $this->plan->id_plan, 'fecha_inicio' => today()->subDays(10), 'fecha_fin' => today()->addDays(20), 'estado' => 'activo']);
    $inicio = today()->addDays(21)->format('d/m/Y');

    $this->actingAs($this->socio)->get('/')->assertOk()
        ->assertSee('Tu plan actual')->assertSee('Renovar mi plan')->assertSee("Empieza el {$inicio}");

    ($this->pedido)([['tipo' => 'plan', 'ref' => $this->plan->id_plan]])->assertSessionHasNoErrors();
    $this->get('/')->assertSee('Tienes un pedido por pagar');
    ($this->pedido)([['tipo' => 'plan', 'ref' => $this->plan->id_plan]])->assertSessionHasErrors('items');

    // Al cobrarla, la renovación queda encadenada y aparece como próxima en Mi cuenta.
    $recepcion = User::factory()->conRol(Role::RECEPCIONISTA)->create($this->e + ['id_sucursal' => $this->sede]);
    abrirCaja($recepcion);
    $this->actingAs($recepcion)->patch('/ventas/'.Venta::first()->id_venta.'/cobrar')->assertSessionHas('success');

    $this->actingAs($this->socio)->get('/mi-cuenta')->assertSee('Renovación pagada')->assertSee($inicio)
        ->assertSee('Del '.today()->subDays(10)->format('d/m/Y')); // la tarjeta muestra la membresía en curso, no la futura
});
