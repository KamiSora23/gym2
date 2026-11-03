<?php

use App\Models\Clase;
use App\Models\Cliente;
use App\Models\Entrenador;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use App\Models\Venta;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->e = ['id_empresa' => $this->admin->id_empresa];
    $this->mismaSede = ['id_empresa' => $this->admin->id_empresa, 'id_sucursal' => $this->admin->id_sucursal];
});

test('each role gets its own home panel', function () {
    $this->actingAs($this->admin)->get('/dashboard')->assertOk()->assertSee('Ganancia neta');

    $recepcion = User::factory()->conRol(Role::RECEPCIONISTA)->create($this->mismaSede);
    $this->actingAs($recepcion)->get('/dashboard')->assertOk()->assertSee('Check-in rápido')->assertDontSee('Ganancia neta');

    $cajera = User::factory()->conRol(Role::CAJERA)->create($this->mismaSede);
    $this->actingAs($cajera)->get('/dashboard')->assertOk()->assertSee('Cobrado hoy')->assertSee('Pendientes de cobro');

    $coach = User::factory()->conRol(Role::ENTRENADOR)->create($this->mismaSede);
    $this->actingAs($coach)->get('/dashboard')->assertOk()->assertSee('Mi panel de coach')
        ->assertDontSee('Ganancia neta')->assertDontSee('Ingresos semana');
});

test('the coach panel shows their classes and students only', function () {
    $usuario = User::factory()->conRol(Role::ENTRENADOR)->create($this->mismaSede);
    $mio = Entrenador::create($this->e + ['nombre' => 'Coach Uno', 'user_id' => $usuario->id, 'activo' => true]);
    $otro = Entrenador::create($this->e + ['nombre' => 'Coach Dos', 'activo' => true]);

    $personal = Cliente::create($this->e + ['nombre' => 'Alumno Personal', 'dni' => '1', 'id_entrenador' => $mio->id_entrenador]);
    $deClase = Cliente::create($this->e + ['nombre' => 'Alumno Clase', 'dni' => '2']);
    Cliente::create($this->e + ['nombre' => 'Ajeno', 'dni' => '3', 'id_entrenador' => $otro->id_entrenador]);

    $clase = Clase::create($this->e + ['id_sucursal' => $this->admin->id_sucursal, 'id_entrenador' => $mio->id_entrenador, 'nombre' => 'Pilates', 'dia_semana' => today()->dayOfWeekIso, 'hora_inicio' => '09:00', 'hora_fin' => '10:00', 'cupo' => 10]);
    $clase->clientes()->attach($deClase->id_cliente);

    $this->actingAs($usuario)->get('/dashboard')
        ->assertSee('Pilates')->assertSee('Alumno Personal')->assertSee('Alumno Clase')->assertDontSee('Ajeno');

    $this->actingAs($usuario)->get('/clientes')->assertOk()
        ->assertSee('Mis alumnos')->assertSee('Alumno Personal')->assertDontSee('Ajeno')->assertDontSee('Nuevo cliente');
});

test('coaches cannot create, edit or delete clients and only admins delete', function () {
    $coach = User::factory()->conRol(Role::ENTRENADOR)->create($this->mismaSede);
    $recepcion = User::factory()->conRol(Role::RECEPCIONISTA)->create($this->mismaSede);
    $cliente = Cliente::create($this->e + ['nombre' => 'Ana', 'dni' => '1']);

    $this->actingAs($coach)->post('/clientes', ['nombre' => 'X', 'dni' => '9'])->assertForbidden();
    $this->actingAs($coach)->put("/clientes/{$cliente->id_cliente}", ['nombre' => 'X', 'dni' => '1'])->assertForbidden();
    $this->actingAs($recepcion)->post('/clientes', ['nombre' => 'Luis', 'dni' => '8'])->assertSessionHasNoErrors();
    $this->actingAs($recepcion)->delete("/clientes/{$cliente->id_cliente}")->assertForbidden();
    $this->actingAs($this->admin)->delete("/clientes/{$cliente->id_cliente}")->assertSessionHas('success');
});

test('clients have their own account area', function () {
    $user = User::factory()->create(['id_empresa' => $this->admin->id_empresa]);
    $coach = Entrenador::create($this->e + ['nombre' => 'Coach Uno', 'activo' => true]);
    $cliente = Cliente::create($this->e + ['nombre' => $user->name, 'dni' => '5', 'user_id' => $user->id, 'id_entrenador' => $coach->id_entrenador]);
    $plan = Plan::create($this->e + ['nombre' => 'Mensual Plus', 'precio' => 100, 'duracion_dias' => 30, 'activo' => true]);
    Membresia::create(['id_cliente' => $cliente->id_cliente, 'id_plan' => $plan->id_plan, 'fecha_inicio' => today(), 'fecha_fin' => today()->addDays(29), 'estado' => 'activo']);

    $this->actingAs($user)->get('/mi-cuenta')->assertOk()
        ->assertSee('Mensual Plus')->assertSee('Coach Uno')->assertSee('Activa');

    $this->actingAs($this->admin)->get('/mi-cuenta')->assertForbidden();
    $this->actingAs($user)->get('/dashboard')->assertRedirect('/');
});

test('cashier panel lists pending sales to collect', function () {
    $cajera = User::factory()->conRol(Role::CAJERA)->create($this->mismaSede);
    Venta::create($this->mismaSede + ['user_id' => $cajera->id, 'fecha' => today(), 'total' => 75, 'estado' => 'pendiente', 'metodo_pago' => 'yape']);

    $this->actingAs($cajera)->get('/dashboard')->assertSee('#00001')->assertSee('S/ 75.00');
});
