<?php

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\User;

function empresaConSucursal(string $nombre): array
{
    $empresa = Empresa::create(['nombre' => $nombre, 'activo' => true]);
    $sucursal = Sucursal::withoutGlobalScopes()->create(['id_empresa' => $empresa->id_empresa, 'nombre' => "Sede {$nombre}", 'activo' => true]);

    return [$empresa, $sucursal];
}

test('super admin creates a company with its branch and admin', function () {
    $super = User::factory()->superAdmin()->create();

    $this->actingAs($super)->get('/empresas')->assertOk();

    $this->actingAs($super)->post('/empresas', [
        'nombre' => 'Titan Gym',
        'ruc' => '20123456789',
        'sucursal' => 'Sede Centro',
        'admin_nombre' => 'Admin Titan',
        'admin_email' => 'admin@titan.com',
        'admin_password' => 'password123',
    ])->assertSessionHasNoErrors();

    $empresa = Empresa::where('nombre', 'Titan Gym')->firstOrFail();
    $admin = User::where('email', 'admin@titan.com')->firstOrFail();

    expect(Sucursal::withoutGlobalScopes()->where('id_empresa', $empresa->id_empresa)->count())->toBe(1)
        ->and($admin->isAdmin())->toBeTrue()
        ->and($admin->id_empresa)->toBe($empresa->id_empresa);

    $this->post('/logout');
    $this->assertGuest();

    $this->post('/login', ['email' => 'admin@titan.com', 'password' => 'password123'])
        ->assertRedirect(route('dashboard', absolute: false));
});

test('super admin must pick a company before using the modules', function () {
    $super = User::factory()->superAdmin()->create();
    [$empresa] = empresaConSucursal('Alfa');

    $this->actingAs($super)->get('/dashboard')->assertRedirect(route('empresas.index'));

    $this->actingAs($super)->post('/contexto/empresa', ['id_empresa' => $empresa->id_empresa])
        ->assertRedirect(route('dashboard'));

    $this->actingAs($super)->get('/dashboard')->assertOk()->assertSee('Alfa');
});

test('data is isolated between companies', function () {
    [$alfa, $sedeAlfa] = empresaConSucursal('Alfa');
    [$beta, $sedeBeta] = empresaConSucursal('Beta');

    $adminAlfa = User::factory()->admin()->deEmpresa($alfa, $sedeAlfa)->create();
    $adminBeta = User::factory()->admin()->deEmpresa($beta, $sedeBeta)->create();

    $this->actingAs($adminAlfa)->post('/clientes', ['nombre' => 'Cliente Alfa', 'dni' => '11111111'])->assertSessionHasNoErrors();
    $this->actingAs($adminBeta)->post('/clientes', ['nombre' => 'Cliente Beta', 'dni' => '11111111'])->assertSessionHasNoErrors();

    $this->actingAs($adminAlfa)->get('/clientes')->assertSee('Cliente Alfa')->assertDontSee('Cliente Beta');
    $this->actingAs($adminBeta)->get('/clientes')->assertSee('Cliente Beta')->assertDontSee('Cliente Alfa');

    $clienteBeta = Cliente::withoutGlobalScopes()->where('nombre', 'Cliente Beta')->first();

    // Un admin no puede tocar registros de otra empresa (404 por el filtro de empresa).
    $this->actingAs($adminAlfa)->delete("/clientes/{$clienteBeta->id_cliente}")->assertNotFound();

    // Ni usar sus registros en una venta.
    $this->actingAs($adminAlfa)->post('/asistencias', ['id_cliente' => $clienteBeta->id_cliente])
        ->assertSessionHasErrors('id_cliente', null, 'asistencia');
});

test('inactive companies cannot use the panel', function () {
    [$empresa, $sede] = empresaConSucursal('Cerrada');
    $empresa->update(['activo' => false]);

    $admin = User::factory()->admin()->deEmpresa($empresa, $sede)->create();

    $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('login'));
    $this->assertGuest();
});

test('admins manage branches and staff only sees their own branch', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/sucursales', ['nombre' => 'Sede Norte'])->assertSessionHasNoErrors();
    $norte = Sucursal::where('nombre', 'Sede Norte')->withoutGlobalScopes()->firstOrFail();
    expect($norte->id_empresa)->toBe($admin->id_empresa);

    $sedeAdmin = $admin->id_sucursal;

    $cajeraNorte = User::factory()->conRol(Role::CAJERA)->create(['id_empresa' => $admin->id_empresa, 'id_sucursal' => $norte->id_sucursal]);

    $this->actingAs($admin)->post('/gastos', ['concepto' => 'Gasto sede admin', 'monto' => 10, 'fecha' => today()->toDateString(), 'id_sucursal' => $sedeAdmin]);
    $this->actingAs($cajeraNorte)->post('/gastos', ['concepto' => 'Gasto norte', 'monto' => 20, 'fecha' => today()->toDateString()]);

    $this->actingAs($cajeraNorte)->get('/gastos')->assertSee('Gasto norte')->assertDontSee('Gasto sede admin');
    $this->actingAs($admin)->get('/gastos')->assertSee('Gasto norte')->assertSee('Gasto sede admin');

    // El admin puede filtrar por sucursal.
    $this->actingAs($admin)->post('/contexto/sucursal', ['id_sucursal' => $norte->id_sucursal]);
    $this->actingAs($admin)->get('/gastos')->assertSee('Gasto norte')->assertDontSee('Gasto sede admin');
});

test('public registration assigns the client to the main company', function () {
    [$principal] = empresaConSucursal('Principal');

    $this->post('/register', [
        'name' => 'Nuevo Socio',
        'email' => 'socio@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect('/');

    $user = User::where('email', 'socio@example.com')->first();

    expect($user->id_empresa)->toBe($principal->id_empresa)
        ->and($user->cliente->id_empresa)->toBe($principal->id_empresa);
});
