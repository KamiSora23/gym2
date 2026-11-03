<?php

use App\Models\Asistencia;
use App\Models\Auditoria;
use App\Models\CategoriaGasto;
use App\Models\Cliente;
use App\Models\Gasto;
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
    $this->sede = ['id_sucursal' => $this->admin->id_sucursal];
});

// ----- Categorías -----

test('la zona de categorías gestiona las de productos sin borrar las que están en uso', function () {
    $this->actingAs($this->admin)->get('/categorias')->assertOk()->assertSee('Productos')->assertSee('Gastos');

    $this->post('/categorias/productos', ['nombre' => 'Suplementos'])->assertSessionHas('success');
    $tipo = TipoProducto::where('nombre', 'Suplementos')->first();

    $this->put("/categorias/productos/{$tipo->id_tipo}", ['nombre' => 'Suplementación'])->assertSessionHas('success');
    expect($tipo->fresh()->nombre)->toBe('Suplementación');

    Producto::create($this->e + ['id_tipo' => $tipo->id_tipo, 'nombre' => 'Whey', 'precio' => 80, 'activo' => true]);
    $this->delete("/categorias/productos/{$tipo->id_tipo}")->assertSessionHas('error');

    // Desactivada: no se ofrece para productos nuevos.
    $this->patch("/categorias/productos/{$tipo->id_tipo}/estado");
    expect($tipo->fresh()->activo)->toBeFalse();
    $this->post('/catalogo', ['nombre' => 'Creatina', 'id_tipo' => $tipo->id_tipo, 'precio' => 50])->assertSessionHasErrors('id_tipo', null, 'producto');

    $vacia = TipoProducto::create($this->e + ['nombre' => 'Ropa']);
    $this->delete("/categorias/productos/{$vacia->id_tipo}")->assertSessionHas('success');
});

test('las categorías de gastos son editables y renombrarlas actualiza los gastos registrados', function () {
    $this->actingAs($this->admin)->get('/gastos')->assertOk()->assertSee('Renta');
    expect(CategoriaGasto::count())->toBe(count(CategoriaGasto::POR_DEFECTO));

    $this->post('/categorias/gastos', ['nombre' => 'Limpieza'])->assertSessionHas('success');
    $this->post('/gastos', ['concepto' => 'Detergente', 'categoria' => 'Limpieza', 'monto' => 25, 'fecha' => today()->toDateString()])->assertSessionHasNoErrors();

    $limpieza = CategoriaGasto::where('nombre', 'Limpieza')->first();
    $this->put("/categorias/gastos/{$limpieza->id_categoria}", ['nombre' => 'Limpieza y aseo'])->assertSessionHas('success');
    expect(Gasto::first()->categoria)->toBe('Limpieza y aseo');

    $this->delete("/categorias/gastos/{$limpieza->id_categoria}")->assertSessionHas('error');

    $this->patch("/categorias/gastos/{$limpieza->id_categoria}/estado");
    $this->post('/gastos', ['concepto' => 'Escobas', 'categoria' => 'Limpieza y aseo', 'monto' => 10, 'fecha' => today()->toDateString()])
        ->assertSessionHasErrors('categoria', null, 'gasto');
});

test('el almacenero gestiona categorías de productos pero no las de gastos', function () {
    $almacen = User::factory()->conRol(Role::ALMACENERO)->create($this->e + $this->sede);

    $this->actingAs($almacen)->get('/categorias')->assertOk();
    $this->actingAs($almacen)->post('/categorias/productos', ['nombre' => 'Bebidas'])->assertSessionHas('success');
    $this->actingAs($almacen)->post('/categorias/gastos', ['nombre' => 'X'])->assertForbidden();

    $coach = User::factory()->conRol(Role::ENTRENADOR)->create($this->e + $this->sede);
    $this->actingAs($coach)->get('/categorias')->assertForbidden();
});

// ----- Reportes -----

test('los reportes muestran dinero, membresías, productos y socios por recuperar', function () {
    $plan = Plan::create($this->e + ['nombre' => 'Mensual', 'precio' => 120, 'duracion_dias' => 30, 'activo' => true]);
    $ana = Cliente::create($this->e + ['nombre' => 'Ana Vencida', 'dni' => '1', 'telefono' => '999111222']);
    $venta = Venta::create($this->e + $this->sede + ['id_cliente' => $ana->id_cliente, 'user_id' => $this->admin->id, 'fecha' => today(), 'total' => 120, 'estado' => 'completado', 'metodo_pago' => 'yape']);
    Membresia::create(['id_cliente' => $ana->id_cliente, 'id_plan' => $plan->id_plan, 'id_venta' => $venta->id_venta, 'fecha_inicio' => today()->subDays(40), 'fecha_fin' => today()->subDays(5), 'estado' => 'activo']);
    Gasto::create($this->e + $this->sede + ['concepto' => 'Luz', 'categoria' => 'Servicios', 'monto' => 50, 'fecha' => today(), 'user_id' => $this->admin->id]);

    $this->actingAs($this->admin)->get('/reportes')->assertOk()
        ->assertSee('S/ 120.00')->assertSee('S/ 50.00')->assertSee('S/ 70.00') // ingresos, gastos, utilidad
        ->assertSee('Yape')->assertSee('Servicios')->assertSee('Mensual')
        ->assertSee('Ana Vencida'); // venció hace 5 días y no renovó

    $this->get('/reportes?desde='.today()->subMonths(2)->toDateString().'&hasta='.today()->subMonth()->toDateString())
        ->assertOk()->assertSee('Sin ventas en el periodo');

    $csv = $this->get('/reportes/ventas.csv')->assertOk()->streamedContent();
    expect($csv)->toContain($venta->folio)->toContain('Ana Vencida')->toContain('120.00');
});

test('solo quien ve indicadores financieros accede a los reportes', function () {
    $recepcion = User::factory()->conRol(Role::RECEPCIONISTA)->create($this->e + $this->sede);
    $this->actingAs($recepcion)->get('/reportes')->assertForbidden();

    $gerente = User::factory()->conRol(Role::GERENTE)->create($this->e);
    $this->actingAs($gerente)->get('/reportes')->assertOk();
});

// ----- Control de acceso -----

test('sin membresía vigente el check-in se rechaza, salvo un pase registrado con motivo', function () {
    $luis = Cliente::create($this->e + ['nombre' => 'Luis Sin Plan', 'dni' => '2']);

    $this->actingAs($this->admin)->post('/asistencias', ['id_cliente' => $luis->id_cliente])
        ->assertSessionHas('error')->assertSessionHas('sin_membresia');
    expect(Asistencia::count())->toBe(0);

    $this->post('/asistencias', ['id_cliente' => $luis->id_cliente, 'cortesia' => 1])->assertSessionHasErrors('motivo', null, 'asistencia');

    $this->post('/asistencias', ['id_cliente' => $luis->id_cliente, 'cortesia' => 1, 'motivo' => 'Clase de prueba'])->assertSessionHas('info');
    expect(Asistencia::count())->toBe(1)
        ->and(Auditoria::where('accion', 'asistencia.pase')->value('descripcion'))->toContain('Clase de prueba');
});

test('una membresía congelada no da acceso y una por vencer avisa para renovar', function () {
    $plan = Plan::create($this->e + ['nombre' => 'Mensual', 'precio' => 120, 'duracion_dias' => 30, 'activo' => true]);
    $rosa = Cliente::create($this->e + ['nombre' => 'Rosa', 'dni' => '3']);
    Membresia::create(['id_cliente' => $rosa->id_cliente, 'id_plan' => $plan->id_plan, 'fecha_inicio' => today()->subDays(10), 'fecha_fin' => today()->addDays(20), 'estado' => 'congelada', 'congelada_desde' => today()->subDay()]);

    $this->actingAs($this->admin)->post('/asistencias', ['id_cliente' => $rosa->id_cliente])->assertSessionHas('error');
    expect(session('error'))->toContain('congelada');

    $pedro = Cliente::create($this->e + ['nombre' => 'Pedro', 'dni' => '4']);
    Membresia::create(['id_cliente' => $pedro->id_cliente, 'id_plan' => $plan->id_plan, 'fecha_inicio' => today()->subDays(27), 'fecha_fin' => today()->addDays(2), 'estado' => 'activo']);

    $this->post('/asistencias', ['id_cliente' => $pedro->id_cliente])->assertSessionHas('success')->assertSessionHas('info');
    expect(session('info'))->toContain('vence en 2');
});
