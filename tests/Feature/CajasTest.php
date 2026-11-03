<?php

use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Configuracion;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    $this->admin = User::factory()->admin()->create();
    $this->sede = ['id_empresa' => $this->admin->id_empresa, 'id_sucursal' => $this->admin->id_sucursal];
    $this->cajaPrincipal = Caja::withoutGlobalScopes()->where('id_sucursal', $this->admin->id_sucursal)->firstOrFail();

    $this->ana = User::factory()->conRol(Role::RECEPCIONISTA)->create($this->sede + ['name' => 'Ana Recepción']);
    $this->luis = User::factory()->conRol(Role::RECEPCIONISTA)->create($this->sede + ['name' => 'Luis Recepción']);
    $this->rosa = User::factory()->conRol(Role::RECEPCIONISTA)->create($this->sede + ['name' => 'Rosa Recepción']);
});

test('el admin crea una caja por recepcionista y asigna responsables', function () {
    $this->actingAs($this->admin)->get('/cajas')->assertOk()->assertSee('Gestión de cajas');

    $this->actingAs($this->admin)->post('/cajas', [
        'nombre' => 'Caja 2', 'id_sucursal' => $this->admin->id_sucursal, 'responsables' => [$this->luis->id],
    ])->assertSessionHas('success');

    $this->actingAs($this->admin)->put("/cajas/{$this->cajaPrincipal->id_caja}", [
        'nombre' => 'Caja 1', 'activo' => 1, 'responsables' => [$this->ana->id],
    ])->assertSessionHas('success');

    $caja2 = Caja::where('nombre', 'Caja 2')->firstOrFail();

    expect($this->cajaPrincipal->fresh()->responsables->pluck('id')->all())->toBe([$this->ana->id])
        ->and($caja2->responsables->pluck('id')->all())->toBe([$this->luis->id]);

    // Recepción no gestiona cajas.
    $this->actingAs($this->ana)->get('/cajas')->assertForbidden();
});

test('dos o tres recepcionistas trabajan a la vez, cada uno en su caja', function () {
    $caja2 = Caja::create($this->sede + ['nombre' => 'Caja 2', 'activo' => true]);
    $caja3 = Caja::create($this->sede + ['nombre' => 'Caja 3', 'activo' => true]);
    $this->cajaPrincipal->responsables()->sync([$this->ana->id]);
    $caja2->responsables()->sync([$this->luis->id]);

    // Cada uno ve preseleccionada su caja asignada.
    $this->actingAs($this->ana)->get('/caja')->assertOk()->assertSee('asignada a ti');

    // Luis no puede abrir la caja de Ana.
    $this->actingAs($this->luis)->post('/caja/abrir', ['id_caja' => $this->cajaPrincipal->id_caja, 'monto_inicial' => 100])->assertForbidden();

    $this->actingAs($this->ana)->post('/caja/abrir', ['id_caja' => $this->cajaPrincipal->id_caja, 'monto_inicial' => 100])->assertSessionHas('success');
    $this->actingAs($this->luis)->post('/caja/abrir', ['id_caja' => $caja2->id_caja, 'monto_inicial' => 80])->assertSessionHas('success');
    // Rosa no tiene caja asignada, pero la Caja 3 no tiene responsables: la puede usar cualquiera.
    $this->actingAs($this->rosa)->post('/caja/abrir', ['id_caja' => $caja3->id_caja, 'monto_inicial' => 50])->assertSessionHas('success');

    expect(CajaSesion::where('estado', 'abierta')->count())->toBe(3);

    // El admin ve las tres cajas abiertas y quién las tiene.
    $this->actingAs($this->admin)->get('/caja')->assertOk()
        ->assertSee('3 de 3 abiertas')
        ->assertSee('Ana Recepción')->assertSee('Luis Recepción')->assertSee('Rosa Recepción');
});

test('si todas sus cajas están ocupadas, el recepcionista no puede abrir otra', function () {
    $this->cajaPrincipal->responsables()->sync([$this->ana->id, $this->luis->id]);

    $this->actingAs($this->ana)->post('/caja/abrir', ['id_caja' => $this->cajaPrincipal->id_caja, 'monto_inicial' => 100]);

    $this->actingAs($this->luis)->get('/caja')->assertSee('Todas tus cajas están en uso');
    $this->actingAs($this->luis)->post('/caja/abrir', ['id_caja' => $this->cajaPrincipal->id_caja, 'monto_inicial' => 100])->assertSessionHas('error');
});

test('no se desactiva una caja con un turno abierto', function () {
    abrirCaja($this->ana);

    $this->actingAs($this->admin)->put("/cajas/{$this->cajaPrincipal->id_caja}", ['nombre' => 'Caja 1'])->assertSessionHas('error');
    expect($this->cajaPrincipal->fresh()->activo)->toBeTrue();
});

test('los montos se muestran en soles por defecto y la moneda se puede cambiar', function () {
    expect(dinero(1234.5))->toBe('S/ 1,234.50')->and(dinero(-10))->toBe('-S/ 10.00');

    $this->actingAs($this->admin)->get('/dashboard')->assertSee('S/ 0.00');
    Plan::create(['id_empresa' => $this->admin->id_empresa, 'nombre' => 'Mensual', 'precio' => 120, 'duracion_dias' => 30, 'activo' => true]);
    auth()->logout();
    $this->get('/')->assertSee('S/ 120.00')->assertSee('Mensual');
    $this->actingAs($this->admin);

    $this->actingAs($this->admin)->post('/configuracion', ['nombre_gym' => 'Gym', 'moneda' => '$'])->assertSessionHasNoErrors();
    expect(Configuracion::where('id_empresa', $this->admin->id_empresa)->value('moneda'))->toBe('$');

    $this->actingAs($this->admin)->post('/configuracion', ['nombre_gym' => 'Gym', 'moneda' => 'XYZ'])->assertSessionHasErrors('moneda');
});
