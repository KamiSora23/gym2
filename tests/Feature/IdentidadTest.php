<?php

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Role;
use App\Models\TipoProducto;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\RolesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolesSeeder::class);
    $this->admin = User::factory()->admin()->create();
    $this->e = ['id_empresa' => $this->admin->id_empresa];
    $this->tipo = TipoProducto::create($this->e + ['nombre' => 'Suplementos']);
});

test('el nombre, el logo y el favicon de la configuración se ven en el panel y en la web', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)->post('/configuracion', [
        'nombre_gym' => 'Titan Gym',
        'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        'favicon' => UploadedFile::fake()->image('icono.png', 32, 32),
    ])->assertSessionHasNoErrors();

    $c = Configuracion::where('id_empresa', $this->admin->id_empresa)->first();
    Storage::disk('public')->assertExists([$c->logo, $c->favicon]);

    // La URL usa el host de la petición (no APP_URL), así funciona también con otro puerto.
    expect($c->logo_url)->toBe(asset('storage/'.$c->logo));

    $this->get('/dashboard')->assertSee('Titan Gym')->assertSee('storage/'.$c->logo)->assertSee('storage/'.$c->favicon);

    auth()->logout();
    $this->get('/')->assertSee('Titan')->assertSee('storage/'.$c->logo)->assertDontSee('GYM IMPULSO');
    $this->get('/shop')->assertSee('storage/'.$c->logo);
});

test('sin favicon propio se usa el logo como ícono de la pestaña', function () {
    expect(Configuracion::paraEmpresa($this->admin->id_empresa)->favicon_url)->toBe(asset('img/logo.png'));
});

test('el producto acepta foto y la muestra en el catálogo y la tienda', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)->post('/catalogo', [
        'nombre' => 'Creatina', 'id_tipo' => $this->tipo->id_tipo, 'precio' => 60,
        'imagen' => UploadedFile::fake()->image('creatina.jpg', 400, 400),
    ])->assertSessionHasNoErrors();

    $producto = Producto::where('nombre', 'Creatina')->first();
    Storage::disk('public')->assertExists($producto->imagen);
    $this->get('/catalogo')->assertSee('storage/'.$producto->imagen);
    $this->get('/shop')->assertSee('storage/'.$producto->imagen);

    // Cambiar la foto borra la anterior; luego se puede quitar.
    $anterior = $producto->imagen;
    $this->put("/catalogo/{$producto->id_producto}", [
        'nombre' => 'Creatina', 'id_tipo' => $this->tipo->id_tipo, 'precio' => 60, 'activo' => 1,
        'imagen' => UploadedFile::fake()->image('nueva.png'),
    ])->assertSessionHasNoErrors();
    Storage::disk('public')->assertMissing($anterior);

    $this->put("/catalogo/{$producto->id_producto}", [
        'nombre' => 'Creatina', 'id_tipo' => $this->tipo->id_tipo, 'precio' => 60, 'activo' => 1, 'quitar_imagen' => 1,
    ]);
    expect($producto->fresh()->imagen)->toBeNull();

    $this->post('/catalogo', ['nombre' => 'X', 'id_tipo' => $this->tipo->id_tipo, 'precio' => 1, 'imagen' => UploadedFile::fake()->create('doc.pdf', 10)])
        ->assertSessionHasErrors('imagen', null, 'producto');
});

test('los productos no se eliminan: se desactivan y se reactivan', function () {
    $producto = Producto::create($this->e + ['id_tipo' => $this->tipo->id_tipo, 'nombre' => 'Agua', 'precio' => 2, 'activo' => true]);
    Inventario::create(['id_producto' => $producto->id_producto, 'id_sucursal' => $this->admin->id_sucursal, 'cantidad' => 4]);

    $this->actingAs($this->admin)->delete("/catalogo/{$producto->id_producto}")->assertStatus(405);

    $this->patch("/catalogo/{$producto->id_producto}/estado")->assertSessionHas('success');
    expect($producto->fresh()->activo)->toBeFalse();
    $this->get('/shop')->assertDontSee('Agua');
    $this->get('/ventas/create')->assertDontSee('Agua');

    $this->patch("/catalogo/{$producto->id_producto}/estado");
    expect($producto->fresh()->activo)->toBeTrue()->and(Producto::count())->toBe(1);
});

test('un cliente con historial se desactiva en lugar de eliminarse y deja de aparecer en caja', function () {
    $ana = Cliente::create($this->e + ['nombre' => 'Ana Torres', 'dni' => '111']);
    Venta::create($this->e + ['id_sucursal' => $this->admin->id_sucursal, 'id_cliente' => $ana->id_cliente, 'user_id' => $this->admin->id, 'fecha' => now(), 'total' => 10, 'estado' => 'completado']);
    $sinHistorial = Cliente::create($this->e + ['nombre' => 'Registro Erróneo', 'dni' => '222']);

    $this->actingAs($this->admin)->delete("/clientes/{$ana->id_cliente}")->assertSessionHas('info');
    expect($ana->fresh())->not->toBeNull()->and($ana->fresh()->activo)->toBeFalse();

    $this->get('/clientes'); // consume el aviso flash, que menciona su nombre
    $this->get('/clientes')->assertDontSee('Ana Torres');
    $this->get('/clientes?estado=inactivos')->assertSee('Ana Torres');
    $this->get('/ventas/create')->assertDontSee('Ana Torres');
    $this->post('/asistencias', ['id_cliente' => $ana->id_cliente])->assertSessionHasErrors('id_cliente', null, 'asistencia');

    $this->patch("/clientes/{$ana->id_cliente}/estado")->assertSessionHas('success');
    expect($ana->fresh()->activo)->toBeTrue();

    // Un registro sin nada asociado sí se puede borrar.
    $this->delete("/clientes/{$sinHistorial->id_cliente}")->assertSessionHas('success');
    expect(Cliente::find($sinHistorial->id_cliente))->toBeNull();
});

test('un usuario del personal con historial se da de baja (bloqueado) en lugar de eliminarse', function () {
    $cajera = User::factory()->conRol(Role::CAJERA)->create($this->e + ['id_sucursal' => $this->admin->id_sucursal]);
    Venta::create($this->e + ['id_sucursal' => $this->admin->id_sucursal, 'user_id' => $cajera->id, 'fecha' => now(), 'total' => 10, 'estado' => 'completado']);
    $nuevo = User::factory()->conRol(Role::CAJERA)->create($this->e + ['id_sucursal' => $this->admin->id_sucursal]);

    $this->actingAs($this->admin)->delete("/usuarios/{$cajera->id}")->assertSessionHas('info');
    expect($cajera->fresh())->not->toBeNull()->and($cajera->fresh()->isBloqueado())->toBeTrue();

    $this->delete("/usuarios/{$nuevo->id}")->assertSessionHas('success');
    expect(User::find($nuevo->id))->toBeNull();
});
