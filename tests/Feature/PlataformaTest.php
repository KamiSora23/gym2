<?php

use App\Models\Configuracion;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use Database\Factories\UserFactory;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->super = User::factory()->superAdmin()->create();
    $this->empresa = UserFactory::empresaDePrueba();
});

test('super admin lands on the platform panel and sees only platform menus', function () {
    $this->post('/login', ['email' => $this->super->email, 'password' => 'password'])
        ->assertRedirect(route('plataforma.index'));

    $this->get('/plataforma')->assertOk()
        ->assertSee('Panel de plataforma')
        ->assertSee('Usuarios globales')
        ->assertSee('Roles y permisos')
        ->assertDontSee('Planes y membresías'); // menú de empresa oculto en modo plataforma

    foreach (['/plataforma/usuarios', '/plataforma/roles', '/plataforma/configuracion', '/empresas'] as $url) {
        $this->get($url)->assertOk();
    }
});

test('entering a company shows the company menu and a way back; platform pages leave the company', function () {
    $this->actingAs($this->super)
        ->post('/contexto/empresa', ['id_empresa' => $this->empresa->id_empresa])
        ->assertRedirect(route('dashboard'));

    $this->get('/dashboard')->assertOk()
        ->assertSee('Modo empresa')
        ->assertSee('Volver a la plataforma')
        ->assertSee('Planes y membresías');

    // Visitar una página de plataforma sale de la empresa.
    $this->get('/plataforma')->assertOk();
    expect(session('empresa_id'))->toBeNull();
    $this->get('/clientes')->assertRedirect(route('plataforma.index'));
});

test('the company users page does not list super admins', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/usuarios')->assertOk()->assertDontSee($this->super->email);

    $this->actingAs($this->super)->withSession(['empresa_id' => $this->empresa->id_empresa])
        ->get('/usuarios')->assertOk()->assertDontSee($this->super->email)->assertSee($admin->email);
});

test('global users: super admin creates staff for any company and other super admins', function () {
    $otra = Empresa::create(['nombre' => 'Otra', 'activo' => true]);

    $this->actingAs($this->super)->post('/plataforma/usuarios', [
        'name' => 'Admin Otra', 'email' => 'admin@otra.com', 'password' => 'password123', 'password_confirmation' => 'password123',
        'role_id' => Role::idDe(Role::ADMIN), 'id_empresa' => $otra->id_empresa,
    ])->assertSessionHasNoErrors();

    $this->post('/plataforma/usuarios', [
        'name' => 'Socio', 'email' => 'super2@gym.com', 'password' => 'password123', 'password_confirmation' => 'password123',
        'role_id' => Role::idDe(Role::SUPER_ADMIN), 'id_empresa' => $otra->id_empresa,
    ])->assertSessionHasNoErrors();

    $this->post('/plataforma/usuarios', [
        'name' => 'Sin empresa', 'email' => 'x@gym.com', 'password' => 'password123', 'password_confirmation' => 'password123',
        'role_id' => Role::idDe(Role::CAJERA),
    ])->assertSessionHasErrors('id_empresa', null, 'usuario');

    expect(User::where('email', 'admin@otra.com')->first()->id_empresa)->toBe($otra->id_empresa)
        ->and(User::where('email', 'super2@gym.com')->first()->id_empresa)->toBeNull();

    $this->get('/plataforma/usuarios?empresa='.$otra->id_empresa)->assertSee('admin@otra.com')->assertDontSee('super2@gym.com');
});

test('only super admins reach the platform', function () {
    $admin = User::factory()->admin()->create();

    foreach (['/plataforma', '/plataforma/usuarios', '/plataforma/roles', '/plataforma/configuracion'] as $url) {
        $this->actingAs($admin)->get($url)->assertForbidden();
    }
});

test('platform branding is separate from company branding', function () {
    $this->actingAs($this->super)->post('/plataforma/configuracion', [
        'nombre_gym' => 'Mi Plataforma',
        'color_primario' => '#123abc',
        'color_sidebar' => '#000000',
        'color_secundario' => '#00ffaa',
        'color_fondo' => '#eeeeee',
    ])->assertSessionHasNoErrors();

    expect(Configuracion::whereNull('id_empresa')->first()->nombre_gym)->toBe('Mi Plataforma');

    $this->get('/plataforma')->assertSee('#123abc', false)->assertSee('Mi Plataforma');

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/dashboard')->assertDontSee('#123abc', false);
});

test('roles page shows the permission matrix', function () {
    $this->seed(RolesSeeder::class);

    $this->actingAs($this->super)->get('/plataforma/roles')->assertOk()
        ->assertSee('Matriz de permisos')
        ->assertSee('Entrenador');
});
