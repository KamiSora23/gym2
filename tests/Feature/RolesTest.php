<?php

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('public pages render without a saved configuration', function () {
    $this->get('/')->assertOk()->assertSee('GYM');
    $this->get('/shop')->assertOk();
    $this->get('/login')->assertOk();
});

test('guests are redirected to login from the dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('clients cannot access the dashboard', function () {
    $cliente = User::factory()->create();

    $this->actingAs($cliente)->get('/dashboard')->assertRedirect('/');
});

test('admins can see the dashboard but not user management', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/dashboard')
        ->assertOk()
        ->assertSee('Configuración')
        ->assertDontSee('Usuarios y roles');

    $this->actingAs($admin)->post('/admin/usuarios', [])->assertForbidden();
});

test('receptionists cannot change the configuration', function () {
    $recepcionista = User::factory()->conRol(Role::RECEPCIONISTA)->create();

    $this->actingAs($recepcionista)->get('/dashboard')->assertOk();
    $this->actingAs($recepcionista)
        ->post('/configuracion', ['nombre_gym' => 'Hack'])
        ->assertForbidden();
});

test('super admin can manage users', function () {
    $super = User::factory()->superAdmin()->create();

    $this->actingAs($super)->get('/dashboard')->assertOk()->assertSee('Usuarios y roles');

    $this->actingAs($super)->post('/admin/usuarios', [
        'name' => 'Recepción',
        'email' => 'recepcion@gym.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => Role::idDe(Role::RECEPCIONISTA),
    ])->assertSessionHasNoErrors()->assertRedirect();

    $nuevo = User::where('email', 'recepcion@gym.com')->firstOrFail();
    expect($nuevo->hasRole(Role::RECEPCIONISTA))->toBeTrue();

    $this->actingAs($super)->put("/admin/usuarios/{$nuevo->id}", [
        'name' => 'Recepción Turno Tarde',
        'email' => 'recepcion@gym.com',
        'role_id' => Role::idDe(Role::CAJERA),
    ])->assertSessionHasNoErrors();

    expect($nuevo->fresh()->hasRole(Role::CAJERA))->toBeTrue();

    $this->actingAs($super)->delete("/admin/usuarios/{$nuevo->id}");
    expect(User::find($nuevo->id))->toBeNull();
});

test('the last super admin cannot be demoted or deleted', function () {
    $super = User::factory()->superAdmin()->create();

    $this->actingAs($super)->put("/admin/usuarios/{$super->id}", [
        'name' => $super->name,
        'email' => $super->email,
        'role_id' => Role::idDe(Role::ADMIN),
    ])->assertSessionHas('error');

    expect($super->fresh()->isSuperAdmin())->toBeTrue();

    $this->actingAs($super)->delete("/admin/usuarios/{$super->id}")->assertSessionHas('error');
    expect($super->fresh())->not->toBeNull();
});

test('admins can update the configuration', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/configuracion', [
        'nombre_gym' => 'POWER GYM',
        'email' => 'hola@power.com',
        'color_primario' => '#123456',
        'horario_lunes' => '7:00 - 21:00',
    ])->assertSessionHasNoErrors()->assertRedirect();

    $config = Configuracion::first();

    expect($config->nombre_gym)->toBe('POWER GYM')
        ->and($config->email)->toBe('hola@power.com')
        ->and($config->color_primario)->toBe('#123456')
        ->and($config->color_sidebar)->toBe('#111827');
});

test('staff can register expenses and attendance', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/gastos', [
        'concepto' => 'Luz',
        'monto' => 150.5,
        'fecha' => today()->toDateString(),
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('gastos', ['concepto' => 'Luz', 'user_id' => $admin->id]);

    $cliente = User::factory()->create();
    $idCliente = Cliente::create(['nombre' => $cliente->name, 'dni' => '12345678', 'user_id' => $cliente->id])->id_cliente;

    $this->actingAs($admin)->post('/asistencias', ['id_cliente' => $idCliente])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('asistencias', ['id_cliente' => $idCliente]);

    $this->actingAs($admin)->get('/dashboard?seccion=gastos')->assertOk()->assertSee('Luz');
});

test('database seeder creates the super admin and is idempotent', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(Role::count())->toBe(count(Role::ETIQUETAS))
        ->and(User::where('email', 'superadmin@gym.com')->first()?->isSuperAdmin())->toBeTrue()
        ->and(User::where('email', 'admin@gym.com')->first()?->isAdmin())->toBeTrue()
        ->and(Configuracion::count())->toBe(1);
});
