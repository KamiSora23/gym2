<?php

use App\Models\Clase;
use App\Models\Cliente;
use App\Models\Entrenador;
use App\Models\Inventario;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Producto;
use App\Models\Progreso;
use App\Models\Role;
use App\Models\Rutina;
use App\Models\TipoProducto;
use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->e = ['id_empresa' => $this->admin->id_empresa];
    $this->sede = ['id_empresa' => $this->admin->id_empresa, 'id_sucursal' => $this->admin->id_sucursal];

    $this->usuarioCoach = User::factory()->conRol(Role::ENTRENADOR)->create($this->sede);
    $this->coach = Entrenador::create($this->e + ['nombre' => 'Coach Uno', 'user_id' => $this->usuarioCoach->id, 'activo' => true]);
    $this->otroCoach = Entrenador::create($this->e + ['nombre' => 'Coach Dos', 'activo' => true]);

    $this->clase = fn (array $extra = []) => array_merge([
        'nombre' => 'Spinning', 'dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '09:00', 'cupo' => 10,
    ], $extra);
});

// ---------- Coach: clases propias ----------

test('a coach creates classes always under their own name', function () {
    $this->actingAs($this->usuarioCoach)
        ->post('/clases', ($this->clase)(['id_entrenador' => $this->otroCoach->id_entrenador]))
        ->assertSessionHasNoErrors();

    $clase = Clase::firstOrFail();
    expect($clase->id_entrenador)->toBe($this->coach->id_entrenador)
        ->and($clase->id_sucursal)->toBe($this->usuarioCoach->id_sucursal);
});

test('a coach edits and deletes only their own classes', function () {
    $mia = Clase::create($this->sede + ($this->clase)(['id_entrenador' => $this->coach->id_entrenador]));
    $ajena = Clase::create($this->sede + ($this->clase)(['nombre' => 'Yoga', 'dia_semana' => 2, 'id_entrenador' => $this->otroCoach->id_entrenador]));

    $this->actingAs($this->usuarioCoach)->put("/clases/{$mia->id_clase}", ($this->clase)(['nombre' => 'Spinning Pro', 'cupo' => 15]))->assertSessionHasNoErrors();
    expect($mia->fresh()->nombre)->toBe('Spinning Pro')->and($mia->fresh()->id_entrenador)->toBe($this->coach->id_entrenador);

    $this->actingAs($this->usuarioCoach)->put("/clases/{$ajena->id_clase}", ($this->clase)(['dia_semana' => 2]))->assertForbidden();
    $this->actingAs($this->usuarioCoach)->delete("/clases/{$ajena->id_clase}")->assertForbidden();
    $this->actingAs($this->usuarioCoach)->delete("/clases/{$mia->id_clase}")->assertSessionHas('success');
});

test('a coach enrolls students only in their own classes', function () {
    $mia = Clase::create($this->sede + ($this->clase)(['id_entrenador' => $this->coach->id_entrenador]));
    $ajena = Clase::create($this->sede + ($this->clase)(['dia_semana' => 3, 'id_entrenador' => $this->otroCoach->id_entrenador]));
    $cliente = Cliente::create($this->e + ['nombre' => 'Ana', 'dni' => '1']);

    $this->actingAs($this->usuarioCoach)->post("/clases/{$mia->id_clase}/inscripciones", ['id_cliente' => $cliente->id_cliente])->assertSessionHas('success');
    $this->actingAs($this->usuarioCoach)->post("/clases/{$ajena->id_clase}/inscripciones", ['id_cliente' => $cliente->id_cliente])->assertForbidden();
});

test('a coach cannot have two classes at the same time', function () {
    $this->actingAs($this->usuarioCoach)->post('/clases', ($this->clase)())->assertSessionHasNoErrors();

    $this->actingAs($this->usuarioCoach)
        ->post('/clases', ($this->clase)(['nombre' => 'Yoga', 'hora_inicio' => '08:30', 'hora_fin' => '09:30']))
        ->assertSessionHasErrors('hora_inicio', null, 'clase');

    // Clases seguidas (sin cruce) sí se permiten.
    $this->actingAs($this->usuarioCoach)
        ->post('/clases', ($this->clase)(['nombre' => 'Yoga', 'hora_inicio' => '09:00', 'hora_fin' => '10:00']))
        ->assertSessionHasNoErrors();
});

// ---------- Coach: disponibilidad ----------

test('a coach sets their availability and classes must fit in it', function () {
    $this->actingAs($this->usuarioCoach)->get('/entrenamiento/mi-perfil')
        ->assertRedirect(route('entrenadores.disponibilidad', $this->coach));

    $this->actingAs($this->usuarioCoach)->put("/entrenadores/{$this->coach->id_entrenador}/disponibilidad", [
        'especialidad' => 'Spinning',
        'bio' => 'Coach certificado',
        'franjas' => [
            ['dia_semana' => 1, 'hora_inicio' => '06:00', 'hora_fin' => '12:00'],
            ['dia_semana' => 1, 'hora_inicio' => '17:00', 'hora_fin' => '21:00'],
        ],
    ])->assertSessionHasNoErrors();

    expect($this->coach->disponibilidad()->count())->toBe(2)
        ->and($this->coach->fresh()->especialidad)->toBe('Spinning');

    // Dentro de su disponibilidad: ok. Fuera (martes o lunes 14:00): error.
    $this->actingAs($this->usuarioCoach)->post('/clases', ($this->clase)())->assertSessionHasNoErrors();
    $this->actingAs($this->usuarioCoach)->post('/clases', ($this->clase)(['hora_inicio' => '14:00', 'hora_fin' => '15:00']))
        ->assertSessionHasErrors('hora_inicio', null, 'clase');
    $this->actingAs($this->usuarioCoach)->post('/clases', ($this->clase)(['dia_semana' => 2]))
        ->assertSessionHasErrors('hora_inicio', null, 'clase');

    $this->actingAs($this->usuarioCoach)->get('/entrenamiento')->assertOk()->assertSee('06:00 - 12:00');
});

test('availability slots are validated', function () {
    $url = "/entrenadores/{$this->coach->id_entrenador}/disponibilidad";

    $this->actingAs($this->usuarioCoach)->put($url, ['franjas' => [['dia_semana' => 1, 'hora_inicio' => '10:00', 'hora_fin' => '09:00']]])
        ->assertSessionHasErrors();
    $this->actingAs($this->usuarioCoach)->put($url, ['franjas' => [
        ['dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '12:00'],
        ['dia_semana' => 1, 'hora_inicio' => '11:00', 'hora_fin' => '13:00'],
    ]])->assertSessionHasErrors('franjas');
});

test('a coach cannot edit another coach availability, the admin can', function () {
    $url = "/entrenadores/{$this->otroCoach->id_entrenador}/disponibilidad";

    $this->actingAs($this->usuarioCoach)->get($url)->assertForbidden();
    $this->actingAs($this->admin)->get($url)->assertOk();
    $this->actingAs($this->admin)->put($url, ['franjas' => [['dia_semana' => 5, 'hora_inicio' => '07:00', 'hora_fin' => '11:00']]])
        ->assertSessionHasNoErrors();

    expect($this->otroCoach->disponibilidad()->count())->toBe(1);
});

// ---------- Rutinas y progreso ----------

test('a coach creates routines and records progress for their students only', function () {
    $alumno = Cliente::create($this->e + ['nombre' => 'Alumno', 'dni' => '1', 'id_entrenador' => $this->coach->id_entrenador]);
    $ajeno = Cliente::create($this->e + ['nombre' => 'Ajeno', 'dni' => '2']);

    $rutina = [
        'nombre' => 'Fuerza 3 días',
        'objetivo' => 'Ganar fuerza',
        'ejercicios' => [
            ['dia' => 'Día 1', 'ejercicio' => 'Sentadilla', 'series' => 4, 'repeticiones' => '8', 'descanso' => '2 min'],
            ['dia' => 'Día 1', 'ejercicio' => '', 'series' => null],
            ['dia' => 'Día 2', 'ejercicio' => 'Press banca', 'series' => 4, 'repeticiones' => '8'],
        ],
    ];

    $this->actingAs($this->usuarioCoach)->get("/clientes/{$alumno->id_cliente}")->assertOk()->assertSee('Rutina activa');
    $this->actingAs($this->usuarioCoach)->get("/clientes/{$ajeno->id_cliente}")->assertForbidden();

    $this->actingAs($this->usuarioCoach)->post("/clientes/{$alumno->id_cliente}/rutinas", $rutina)->assertSessionHasNoErrors();
    $this->actingAs($this->usuarioCoach)->post("/clientes/{$ajeno->id_cliente}/rutinas", $rutina)->assertForbidden();

    $creada = Rutina::with('ejercicios')->firstOrFail();
    expect($creada->ejercicios)->toHaveCount(2)
        ->and($creada->id_entrenador)->toBe($this->coach->id_entrenador)
        ->and($creada->activa)->toBeTrue();

    // Una nueva rutina reemplaza a la activa.
    $this->actingAs($this->usuarioCoach)->post("/clientes/{$alumno->id_cliente}/rutinas", ['nombre' => 'Cardio', 'ejercicios' => [['ejercicio' => 'Bici']]]);
    expect(Rutina::where('activa', true)->count())->toBe(1)->and($creada->fresh()->activa)->toBeFalse();

    $this->actingAs($this->usuarioCoach)->post("/clientes/{$alumno->id_cliente}/progreso", ['fecha' => today()->toDateString(), 'peso' => 80.5, 'grasa' => 18])
        ->assertSessionHas('success');
    expect(Progreso::first()->peso)->toEqual('80.50');

    $this->actingAs($this->usuarioCoach)->post("/clientes/{$ajeno->id_cliente}/progreso", ['fecha' => today()->toDateString(), 'peso' => 70])->assertForbidden();
});

test('receptionists cannot manage routines or coaches but can manage any class', function () {
    $recepcion = User::factory()->conRol(Role::RECEPCIONISTA)->create($this->sede);
    $cliente = Cliente::create($this->e + ['nombre' => 'Ana', 'dni' => '1']);

    $this->actingAs($recepcion)->post("/clientes/{$cliente->id_cliente}/rutinas", ['nombre' => 'X', 'ejercicios' => [['ejercicio' => 'Y']]])->assertForbidden();
    $this->actingAs($recepcion)->post('/entrenadores', ['nombre' => 'Nuevo'])->assertForbidden();
    $this->actingAs($recepcion)->post('/clases', ($this->clase)(['id_entrenador' => $this->otroCoach->id_entrenador]))->assertSessionHasNoErrors();

    expect(Clase::first()->id_entrenador)->toBe($this->otroCoach->id_entrenador);
});

// ---------- Otros roles ----------

test('only the admin edits plans', function () {
    $recepcion = User::factory()->conRol(Role::RECEPCIONISTA)->create($this->sede);

    $this->actingAs($recepcion)->get('/planes')->assertOk()->assertDontSee('Nuevo plan');
    $this->actingAs($recepcion)->post('/planes', ['nombre' => 'Hack', 'precio' => 1, 'duracion_dias' => 30])->assertForbidden();
});

test('the cashier can add stock but not remove it', function () {
    $cajera = User::factory()->conRol(Role::CAJERA)->create($this->sede);
    $tipo = TipoProducto::create($this->e + ['nombre' => 'Bebidas']);
    $producto = Producto::create($this->e + ['id_tipo' => $tipo->id_tipo, 'nombre' => 'Agua', 'precio' => 2, 'activo' => true]);
    Inventario::create(['id_producto' => $producto->id_producto, 'id_sucursal' => $cajera->id_sucursal, 'cantidad' => 10]);

    $this->actingAs($cajera)->post("/catalogo/{$producto->id_producto}/stock", ['cantidad' => 5])->assertSessionHas('success');
    $this->actingAs($cajera)->post("/catalogo/{$producto->id_producto}/stock", ['cantidad' => -3])->assertForbidden();
    $this->actingAs($cajera)->post('/catalogo', ['nombre' => 'X', 'id_tipo' => $tipo->id_tipo, 'precio' => 1])->assertForbidden();

    expect($producto->fresh()->stockEn($cajera->id_sucursal))->toBe(15);
});

test('the cashier registers check-ins and clients but cannot assign a coach', function () {
    $cajera = User::factory()->conRol(Role::CAJERA)->create($this->sede);

    $this->actingAs($cajera)->post('/clientes', ['nombre' => 'Luis', 'dni' => '77', 'id_entrenador' => $this->coach->id_entrenador])->assertSessionHasNoErrors();
    $luis = Cliente::where('dni', '77')->first();
    expect($luis->id_entrenador)->toBeNull();

    $this->actingAs($cajera)->post('/asistencias', ['id_cliente' => $luis->id_cliente])->assertSessionHas('info');
});

// ---------- Cliente ----------

test('clients see their routine and progress, and enroll in classes themselves', function () {
    $user = User::factory()->create($this->e);
    $cliente = Cliente::create($this->e + ['nombre' => $user->name, 'dni' => '5', 'user_id' => $user->id, 'id_entrenador' => $this->coach->id_entrenador]);
    $clase = Clase::create($this->sede + ($this->clase)(['nombre' => 'Zumba', 'cupo' => 1, 'id_entrenador' => $this->coach->id_entrenador]));

    $rutina = Rutina::create($this->e + ['id_cliente' => $cliente->id_cliente, 'id_entrenador' => $this->coach->id_entrenador, 'nombre' => 'Rutina Verano', 'activa' => true]);
    $rutina->ejercicios()->create(['ejercicio' => 'Burpees', 'series' => 3, 'repeticiones' => '15']);
    Progreso::create($this->e + ['id_cliente' => $cliente->id_cliente, 'fecha' => today(), 'peso' => 70]);

    // Sin membresía no puede inscribirse.
    $this->actingAs($user)->post("/mi-cuenta/clases/{$clase->id_clase}")->assertSessionHas('error');

    $plan = Plan::create($this->e + ['nombre' => 'Mensual', 'precio' => 100, 'duracion_dias' => 30, 'activo' => true]);
    Membresia::create(['id_cliente' => $cliente->id_cliente, 'id_plan' => $plan->id_plan, 'fecha_inicio' => today(), 'fecha_fin' => today()->addDays(29), 'estado' => 'activo']);

    $this->actingAs($user)->get('/mi-cuenta')->assertOk()
        ->assertSee('Rutina Verano')->assertSee('Burpees')->assertSee('Mi progreso')->assertSee('Zumba');

    $this->actingAs($user)->post("/mi-cuenta/clases/{$clase->id_clase}")->assertSessionHas('success');
    expect($clase->clientes()->count())->toBe(1);

    // Cupo lleno para otro cliente.
    $otro = User::factory()->create($this->e);
    $otroCliente = Cliente::create($this->e + ['nombre' => 'Otro', 'dni' => '6', 'user_id' => $otro->id]);
    Membresia::create(['id_cliente' => $otroCliente->id_cliente, 'id_plan' => $plan->id_plan, 'fecha_inicio' => today(), 'fecha_fin' => today()->addDays(29), 'estado' => 'activo']);
    $this->actingAs($otro)->post("/mi-cuenta/clases/{$clase->id_clase}")->assertSessionHas('error');

    $this->actingAs($user)->delete("/mi-cuenta/clases/{$clase->id_clase}")->assertSessionHas('success');
    expect($clase->clientes()->count())->toBe(0);
});

test('the roles page lists the action permissions', function () {
    $this->seed(RolesSeeder::class);
    $super = User::factory()->superAdmin()->create();

    $this->actingAs($super)->get('/plataforma/roles')->assertOk()
        ->assertSee('Acciones permitidas')
        ->assertSee('Crear y editar sus propias clases')
        ->assertSee('Definir su disponibilidad semanal');
});
