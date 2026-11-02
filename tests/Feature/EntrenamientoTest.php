<?php

use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Cliente;
use App\Models\Entrenador;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('coaches, classes and enrollments work end to end', function () {
    $this->post('/entrenadores', ['nombre' => 'Carlos Ruiz', 'especialidad' => 'Crossfit'])->assertSessionHasNoErrors();
    $coach = Entrenador::firstOrFail();

    $this->post('/clases', [
        'nombre' => 'Crossfit',
        'id_entrenador' => $coach->id_entrenador,
        'dia_semana' => 2,
        'hora_inicio' => '18:00',
        'hora_fin' => '19:00',
        'cupo' => 1,
    ])->assertSessionHasNoErrors();

    $clase = Clase::firstOrFail();
    expect($clase->id_sucursal)->toBe($this->admin->id_sucursal);

    $ana = Cliente::create(['id_empresa' => $this->admin->id_empresa, 'nombre' => 'Ana', 'dni' => '1']);
    $luis = Cliente::create(['id_empresa' => $this->admin->id_empresa, 'nombre' => 'Luis', 'dni' => '2']);

    $this->post("/clases/{$clase->id_clase}/inscripciones", ['id_cliente' => $ana->id_cliente])->assertSessionHas('success');
    $this->post("/clases/{$clase->id_clase}/inscripciones", ['id_cliente' => $ana->id_cliente])->assertSessionHas('error');
    $this->post("/clases/{$clase->id_clase}/inscripciones", ['id_cliente' => $luis->id_cliente])->assertSessionHas('error'); // cupo lleno

    expect($clase->clientes()->count())->toBe(1);

    $this->get('/entrenamiento')->assertOk()->assertSee('Carlos Ruiz')->assertSee('Crossfit');

    $this->delete("/clases/{$clase->id_clase}/inscripciones/{$ana->id_cliente}")->assertSessionHas('success');
    expect($clase->clientes()->count())->toBe(0);
});

test('class end time must be after start time', function () {
    $this->post('/clases', ['nombre' => 'Yoga', 'dia_semana' => 1, 'hora_inicio' => '10:00', 'hora_fin' => '09:00', 'cupo' => 10])
        ->assertSessionHasErrors('hora_fin', null, 'clase');
});

test('a coach user sees only their classes and cannot manage the schedule', function () {
    $usuarioCoach = User::factory()->conRol(Role::ENTRENADOR)->create(['id_empresa' => $this->admin->id_empresa, 'id_sucursal' => $this->admin->id_sucursal]);

    $this->post('/entrenadores', ['nombre' => 'María', 'user_id' => $usuarioCoach->id])->assertSessionHasNoErrors();
    $this->post('/entrenadores', ['nombre' => 'Roberto'])->assertSessionHasNoErrors();

    $maria = Entrenador::where('nombre', 'María')->first();
    $roberto = Entrenador::where('nombre', 'Roberto')->first();

    foreach ([['Pilates', $maria], ['Boxeo', $roberto]] as [$nombre, $coach]) {
        $this->post('/clases', ['nombre' => $nombre, 'id_entrenador' => $coach->id_entrenador, 'dia_semana' => 3, 'hora_inicio' => '09:00', 'hora_fin' => '10:00', 'cupo' => 10]);
    }

    $this->actingAs($usuarioCoach)->get('/entrenamiento')->assertOk()->assertSee('Pilates')->assertDontSee('Boxeo');
    $this->actingAs($usuarioCoach)->get('/entrenamiento?todas=1')->assertSee('Boxeo');
    $this->actingAs($usuarioCoach)->post('/clases', ['nombre' => 'X', 'dia_semana' => 1, 'hora_inicio' => '09:00', 'hora_fin' => '10:00', 'cupo' => 5])->assertForbidden();
});

test('clients get a personal coach', function () {
    $coach = Entrenador::create(['id_empresa' => $this->admin->id_empresa, 'nombre' => 'Carlos', 'activo' => true]);

    $this->post('/clientes', ['nombre' => 'Ana', 'dni' => '999', 'id_entrenador' => $coach->id_entrenador])->assertSessionHasNoErrors();

    expect(Cliente::first()->entrenador->nombre)->toBe('Carlos');
    $this->get('/clientes')->assertSee('Carlos');
});

test('attendance check-in and check-out', function () {
    $cliente = Cliente::create(['id_empresa' => $this->admin->id_empresa, 'nombre' => 'Ana', 'dni' => '1']);

    $this->post('/asistencias', ['id_cliente' => $cliente->id_cliente])->assertSessionHas('info'); // sin membresía
    $this->post('/asistencias', ['id_cliente' => $cliente->id_cliente])->assertSessionHas('error'); // check-in abierto

    $asistencia = Asistencia::firstOrFail();
    $this->patch("/asistencias/{$asistencia->id_asistencia}/salida")->assertSessionHas('success');

    expect($asistencia->fresh()->hora_salida)->not->toBeNull();
    $this->get('/asistencias')->assertOk()->assertSee('Ana');
});
