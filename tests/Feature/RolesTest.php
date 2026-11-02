<?php

use App\Models\Configuracion;
use App\Models\Role;
use App\Models\User;
use Database\Factories\UserFactory;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;

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

test('every module renders for an admin', function () {
    $admin = User::factory()->admin()->create();

    foreach (['/dashboard', '/clientes', '/asistencias', '/ventas', '/ventas/nueva', '/gastos', '/catalogo', '/planes', '/entrenamiento', '/sucursales', '/usuarios', '/configuracion'] as $url) {
        $this->actingAs($admin)->get($url)->assertOk();
    }
});

test('admins cannot access the platform pages', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/empresas')->assertForbidden();
    $this->actingAs($admin)->post('/contexto/empresa', ['id_empresa' => $admin->id_empresa])->assertForbidden();
});

test('module permissions follow the role', function () {
    $recepcion = User::factory()->conRol(Role::RECEPCIONISTA)->create();
    $cajera = User::factory()->conRol(Role::CAJERA)->create();
    $coach = User::factory()->conRol(Role::ENTRENADOR)->create();

    $this->actingAs($recepcion)->get('/ventas')->assertOk();
    $this->actingAs($recepcion)->get('/gastos')->assertForbidden();
    $this->actingAs($recepcion)->post('/configuracion', ['nombre_gym' => 'Hack'])->assertForbidden();
    $this->actingAs($recepcion)->get('/usuarios')->assertForbidden();

    $this->actingAs($cajera)->get('/gastos')->assertOk();
    $this->actingAs($cajera)->get('/entrenamiento')->assertForbidden();

    $this->actingAs($coach)->get('/entrenamiento')->assertOk();
    $this->actingAs($coach)->get('/ventas')->assertForbidden();
});

test('an admin manages the users of their company but cannot create super admins', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/usuarios', [
        'name' => 'Recepción',
        'email' => 'recepcion@gym.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => Role::idDe(Role::RECEPCIONISTA),
    ])->assertSessionHasNoErrors();

    $nuevo = User::where('email', 'recepcion@gym.com')->firstOrFail();
    expect($nuevo->id_empresa)->toBe($admin->id_empresa);

    $this->actingAs($admin)->post('/usuarios', [
        'name' => 'Intruso',
        'email' => 'intruso@gym.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => Role::idDe(Role::SUPER_ADMIN),
    ])->assertSessionHasErrors('role_id', null, 'usuario');

    $super = User::factory()->superAdmin()->create();
    $this->actingAs($admin)->delete("/usuarios/{$super->id}")->assertForbidden();
});

test('the last super admin cannot be demoted or deleted', function () {
    $super = User::factory()->superAdmin()->create();
    $otro = User::factory()->superAdmin()->create();
    $empresa = UserFactory::empresaDePrueba();

    $this->actingAs($super);

    // Desde una empresa no se puede tocar a un super admin: se gestionan en la plataforma.
    $this->withSession(['empresa_id' => $empresa->id_empresa])
        ->delete("/usuarios/{$otro->id}")->assertForbidden();

    $this->delete("/plataforma/usuarios/{$otro->id}")->assertSessionHas('success');

    $this->put("/plataforma/usuarios/{$super->id}", [
        'name' => $super->name,
        'email' => $super->email,
        'role_id' => Role::idDe(Role::ADMIN),
        'id_empresa' => $empresa->id_empresa,
    ])->assertSessionHas('error');

    expect($super->fresh()->isSuperAdmin())->toBeTrue();

    $this->delete("/plataforma/usuarios/{$super->id}")->assertSessionHas('error');
    expect($super->fresh())->not->toBeNull();
});

test('admins update the configuration of their own company', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/configuracion', [
        'nombre_gym' => 'POWER GYM',
        'email' => 'hola@power.com',
        'color_primario' => '#123456',
        'horario_lunes' => '7:00 - 21:00',
    ])->assertSessionHasNoErrors()->assertRedirect();

    $config = Configuracion::where('id_empresa', $admin->id_empresa)->first();

    expect($config->nombre_gym)->toBe('POWER GYM')
        ->and($config->color_primario)->toBe('#123456')
        ->and($config->color_sidebar)->toBe('#111827');

    $this->actingAs($admin)->get('/dashboard')->assertSee('#123456', false);
});

test('database seeder creates the super admin and main company, and is idempotent', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    $admin = User::where('email', 'admin@gym.com')->first();

    expect(Role::count())->toBe(count(Role::ETIQUETAS))
        ->and(User::where('email', 'superadmin@gym.com')->first()?->isSuperAdmin())->toBeTrue()
        ->and($admin?->isAdmin())->toBeTrue()
        ->and($admin->id_empresa)->not->toBeNull()
        ->and($admin->id_sucursal)->not->toBeNull()
        ->and(Configuracion::count())->toBe(1);
});

test('demo seeder fills two companies without errors', function () {
    $this->seed(DemoSeeder::class);

    $admin = User::where('email', 'admin@gym.com')->first();

    $this->actingAs($admin)->get('/dashboard')->assertOk();
    $this->actingAs($admin)->get('/entrenamiento')->assertOk()->assertSee('Carlos Ruiz');
    $this->actingAs($admin)->get('/ventas')->assertOk();
});
