<?php

use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\Entrenador;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);
    $this->admin = User::factory()->admin()->create();
    $this->e = ['id_empresa' => $this->admin->id_empresa];

    $this->nuevo = fn (string $rol, array $extra = []) => $this->actingAs($this->admin)->post('/usuarios', $extra + [
        'name' => 'Empleado Nuevo',
        'email' => 'nuevo@gym.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => Role::idDe($rol),
    ]);
});

test('el listado de empleados no muestra cuentas de clientes ni ofrece el rol cliente', function () {
    $socio = User::factory()->create($this->e + ['name' => 'Socio Con Cuenta']);
    Cliente::create($this->e + ['nombre' => $socio->name, 'dni' => '1', 'user_id' => $socio->id]);

    $this->actingAs($this->admin)->get('/usuarios')->assertOk()->assertDontSee('Socio Con Cuenta');

    ($this->nuevo)(Role::CLIENTE)->assertSessionHasErrors('role_id', null, 'usuario');
});

test('crear un empleado con rol entrenador crea y vincula su perfil de coach', function () {
    ($this->nuevo)(Role::ENTRENADOR, ['name' => 'Lucía Coach'])->assertSessionHasNoErrors();

    $user = User::where('email', 'nuevo@gym.test')->first();
    $perfil = Entrenador::where('user_id', $user->id)->first();

    expect($perfil)->not->toBeNull()
        ->and($perfil->nombre)->toBe('Lucía Coach')
        ->and($perfil->id_sucursal)->toBe($this->admin->id_sucursal);

    $this->actingAs($user)->get('/entrenamiento/mi-perfil')->assertRedirect(route('entrenadores.disponibilidad', $perfil));
    $this->actingAs($user)->get(route('entrenadores.disponibilidad', $perfil))->assertOk();
});

test('el personal de una sola sede necesita sucursal; con una sola sede se asigna sola', function () {
    ($this->nuevo)(Role::RECEPCIONISTA)->assertSessionHasNoErrors();
    expect(User::where('email', 'nuevo@gym.test')->value('id_sucursal'))->toBe($this->admin->id_sucursal);

    Sucursal::create($this->e + ['nombre' => 'Sede Norte', 'activo' => true]);

    ($this->nuevo)(Role::CAJERA, ['email' => 'caja2@gym.test'])->assertSessionHasErrors('id_sucursal', null, 'usuario');

    // El gerente trabaja con todas las sedes: puede quedar sin sucursal.
    ($this->nuevo)(Role::GERENTE, ['email' => 'gerente2@gym.test'])->assertSessionHasNoErrors();
    expect(User::where('email', 'gerente2@gym.test')->value('id_sucursal'))->toBeNull();
});

test('un empleado bloqueado no puede entrar y al desbloquearlo vuelve a tener acceso', function () {
    $cajera = User::factory()->conRol(Role::CAJERA)->create($this->e + ['id_sucursal' => $this->admin->id_sucursal]);

    $this->actingAs($this->admin)->patch("/usuarios/{$cajera->id}/bloqueo", ['motivo' => 'Fin de contrato'])->assertSessionHas('success');
    expect($cajera->fresh()->isBloqueado())->toBeTrue();

    $this->actingAs($cajera->fresh())->get('/dashboard')->assertRedirect(route('login'));

    $this->actingAs($this->admin)->patch("/usuarios/{$cajera->id}/bloqueo")->assertSessionHas('success');
    $this->actingAs($cajera->fresh())->get('/dashboard')->assertOk();
});

test('dar de baja a un coach con historial desactiva también su perfil', function () {
    ($this->nuevo)(Role::ENTRENADOR);
    $user = User::where('email', 'nuevo@gym.test')->first();
    Auditoria::create($this->e + ['user_id' => $user->id, 'accion' => 'prueba', 'descripcion' => 'x']);

    $this->actingAs($this->admin)->delete("/usuarios/{$user->id}")->assertSessionHas('info');

    expect($user->fresh()->isBloqueado())->toBeTrue()
        ->and(Entrenador::where('user_id', $user->id)->value('activo'))->toBeFalse();
});

test('un empleado que solo inició sesión se puede eliminar junto con su perfil de coach vacío', function () {
    ($this->nuevo)(Role::ENTRENADOR);
    $user = User::where('email', 'nuevo@gym.test')->first();
    Auditoria::create($this->e + ['user_id' => $user->id, 'accion' => 'sesion.login', 'descripcion' => 'Inició sesión']);

    $this->actingAs($this->admin)->delete("/usuarios/{$user->id}")->assertSessionHas('success');

    expect(User::find($user->id))->toBeNull()
        ->and(Entrenador::where('nombre', 'Empleado Nuevo')->exists())->toBeFalse();
});
