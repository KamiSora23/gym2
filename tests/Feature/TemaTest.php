<?php

use App\Models\Configuracion;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tema;

test('readable text color is chosen for each background', function () {
    expect(Tema::textoSobre('#ffffff'))->toBe('#111827')
        ->and(Tema::textoSobre('#111827'))->toBe('#ffffff')
        ->and(Tema::textoSobre('#ffc107'))->toBe('#111827')
        ->and(Tema::textoSobre('#2563eb'))->toBe('#ffffff');
});

test('a light sidebar gets dark text', function () {
    $css = Tema::panel(new Configuracion(['color_primario' => '#0f766e', 'color_sidebar' => '#ffffff']));

    expect($css)->toContain('--sidebar-text: #475569')->toContain('--sidebar-color: #ffffff');
});

test('admins save panel colors, website colors and branch colors', function () {
    $admin = User::factory()->admin()->create();
    $sede = Sucursal::withoutGlobalScopes()->find($admin->id_sucursal);

    $this->actingAs($admin)->get('/configuracion')->assertOk()
        ->assertSee('Paletas predefinidas')->assertSee('Colores por sucursal')->assertSee('Sitio web público');

    $this->actingAs($admin)->post('/configuracion', [
        'nombre_gym' => 'Titan',
        'tema' => 'oceano',
        'color_primario' => '#2563EB',
        'color_sidebar' => '#0f172a',
        'color_secundario' => '#06b6d4',
        'color_fondo' => '#f1f5f9',
        'web_color_primario' => '#22c55e',
        'web_color_oscuro' => '#111111',
        'sucursal_colores' => [$sede->id_sucursal => ['propio' => 1, 'color_primario' => '#ff00aa', 'color_sidebar' => '#222222']],
    ])->assertSessionHasNoErrors();

    $config = Configuracion::where('id_empresa', $admin->id_empresa)->first();
    expect($config->color_primario)->toBe('#2563eb')
        ->and($config->tema)->toBe('oceano')
        ->and($config->web_color_primario)->toBe('#22c55e')
        ->and($sede->fresh()->color_primario)->toBe('#ff00aa');

    // El personal de esa sucursal ve el color propio de la sede.
    $cajera = User::factory()->conRol(Role::CAJERA)->create(['id_empresa' => $admin->id_empresa, 'id_sucursal' => $sede->id_sucursal]);
    $this->actingAs($cajera)->get('/ventas')->assertSee('--primary-color: #ff00aa', false);

    // El sitio público usa los colores web.
    $this->get('/')->assertSee('--primary: #22c55e', false);
});

test('the website can reuse the panel colors', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/configuracion', [
        'nombre_gym' => 'Titan',
        'color_primario' => '#7c3aed',
        'web_usa_colores_panel' => 1,
    ])->assertSessionHasNoErrors();

    auth()->logout();
    $this->get('/')->assertSee('--primary: #7c3aed', false);
});

test('invalid colors are rejected', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/configuracion', ['nombre_gym' => 'X', 'color_primario' => 'rojo'])
        ->assertSessionHasErrors('color_primario');
});
