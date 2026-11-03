<?php

use App\Models\Asistencia;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Empresa;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);
    $this->admin = User::factory()->admin()->create();
    $this->e = ['id_empresa' => $this->admin->id_empresa];
    $this->recepcion = User::factory()->conRol(Role::RECEPCIONISTA)->create($this->e + ['id_sucursal' => $this->admin->id_sucursal]);

    $this->activarQr = fn (bool $activo = true) => Configuracion::updateOrCreate(['id_empresa' => $this->admin->id_empresa], ['nombre_gym' => 'Titan Gym', 'checkin_qr' => $activo]);

    $plan = Plan::create($this->e + ['nombre' => 'Mensual', 'precio' => 120, 'duracion_dias' => 30, 'activo' => true]);
    $this->user = User::factory()->create($this->e);
    $this->socio = Cliente::create($this->e + ['nombre' => 'Ana QR', 'dni' => '5', 'user_id' => $this->user->id]);
    Membresia::create(['id_cliente' => $this->socio->id_cliente, 'id_plan' => $plan->id_plan, 'fecha_inicio' => today(), 'fecha_fin' => today()->addDays(29), 'estado' => 'activo']);
});

test('la opción se activa en configuración y solo entonces aparece el lector y el pase', function () {
    $this->actingAs($this->recepcion)->get('/asistencias')->assertDontSee('Escanear pase QR');
    $this->actingAs($this->user)->get('/mi-cuenta')->assertDontSee('Mi pase de acceso');
    $this->actingAs($this->recepcion)->post('/asistencias/qr', ['codigo' => 'X'])->assertNotFound();

    $this->actingAs($this->admin)->post('/configuracion', ['nombre_gym' => 'Titan Gym', 'checkin_qr' => 1])->assertSessionHasNoErrors();
    expect(Configuracion::where('id_empresa', $this->admin->id_empresa)->value('checkin_qr'))->toBeTrue();

    $this->actingAs($this->recepcion)->get('/asistencias')->assertSee('Escanear pase QR');
    $this->actingAs($this->recepcion)->get('/dashboard')->assertSee('Escanear pase QR');

    $codigo = $this->socio->fresh()->codigoAcceso();
    $this->actingAs($this->user)->get('/mi-cuenta')->assertSee('Mi pase de acceso')->assertSee($codigo)->assertSee('<svg', false);
    $this->actingAs($this->admin)->get("/clientes/{$this->socio->id_cliente}")->assertSee('Imprimir carnet')->assertSee($codigo);
});

test('escanear el pase registra la entrada y un segundo escaneo la salida', function () {
    ($this->activarQr)();
    $codigo = $this->socio->codigoAcceso();

    $this->actingAs($this->recepcion)->post('/asistencias/qr', ['codigo' => strtolower($codigo)])->assertSessionHas('success');
    $asistencia = Asistencia::first();
    expect($asistencia->id_cliente)->toBe($this->socio->id_cliente)->and($asistencia->hora_salida)->toBeNull();

    $this->post('/asistencias/qr', ['codigo' => " {$codigo} "])->assertSessionHas('success');
    expect(Asistencia::count())->toBe(1)->and($asistencia->fresh()->hora_salida)->not->toBeNull();
});

test('el pase respeta el control de acceso y los códigos inválidos o de otra empresa', function () {
    ($this->activarQr)();

    $sinPlan = Cliente::create($this->e + ['nombre' => 'Sin Plan', 'dni' => '6']);
    $this->actingAs($this->recepcion)->post('/asistencias/qr', ['codigo' => $sinPlan->codigoAcceso()])
        ->assertSessionHas('error')->assertSessionHas('sin_membresia');

    $this->post('/asistencias/qr', ['codigo' => 'GYMNOEXISTE123'])->assertSessionHas('error');

    $otra = Empresa::create(['nombre' => 'Otra', 'activo' => true]);
    $ajeno = Cliente::withoutGlobalScopes()->create(['id_empresa' => $otra->id_empresa, 'nombre' => 'Ajeno', 'dni' => '7']);
    $this->post('/asistencias/qr', ['codigo' => $ajeno->codigoAcceso()])->assertSessionHas('error');

    $this->socio->update(['activo' => false]);
    $this->post('/asistencias/qr', ['codigo' => $this->socio->codigoAcceso()])->assertSessionHas('error');

    expect(Asistencia::count())->toBe(0);
});

test('generar un nuevo código invalida el anterior', function () {
    ($this->activarQr)();
    $anterior = $this->socio->codigoAcceso();

    $this->actingAs($this->admin)->patch("/clientes/{$this->socio->id_cliente}/codigo-acceso")->assertSessionHas('success');
    $nuevo = $this->socio->fresh()->codigo_acceso;

    expect($nuevo)->not->toBe($anterior);
    $this->actingAs($this->recepcion)->post('/asistencias/qr', ['codigo' => $anterior])->assertSessionHas('error');
    $this->post('/asistencias/qr', ['codigo' => $nuevo])->assertSessionHas('success');

    $coach = User::factory()->conRol(Role::ENTRENADOR)->create($this->e + ['id_sucursal' => $this->admin->id_sucursal]);
    $this->actingAs($coach)->patch("/clientes/{$this->socio->id_cliente}/codigo-acceso")->assertForbidden();
});
