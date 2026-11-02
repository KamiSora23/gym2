<?php

use App\Models\Auditoria;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Entrenador;
use App\Models\Inventario;
use App\Models\InventarioMovimiento;
use App\Models\Membresia;
use App\Models\Permiso;
use App\Models\Plan;
use App\Models\PlanNutricional;
use App\Models\Producto;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\TipoProducto;
use App\Models\User;
use App\Models\Venta;
use App\Support\Autorizacion;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    $this->admin = User::factory()->admin()->create();
    $this->e = ['id_empresa' => $this->admin->id_empresa];
    $this->sede = ['id_empresa' => $this->admin->id_empresa, 'id_sucursal' => $this->admin->id_sucursal];
    $this->recepcion = User::factory()->conRol(Role::RECEPCIONISTA)->create($this->sede);

    $tipo = TipoProducto::create($this->e + ['nombre' => 'Suplementos']);
    $this->producto = Producto::create($this->e + ['id_tipo' => $tipo->id_tipo, 'nombre' => 'Proteína', 'precio' => 50, 'activo' => true]);
    Inventario::create(['id_producto' => $this->producto->id_producto, 'id_sucursal' => $this->admin->id_sucursal, 'cantidad' => 10]);

    $this->vender = fn (User $u, int $cantidad = 1, string $estado = 'completado') => $this->actingAs($u)->post('/ventas', [
        'metodo_pago' => 'efectivo',
        'estado' => $estado,
        'items' => [['id_producto' => $this->producto->id_producto, 'cantidad' => $cantidad]],
    ]);
});

// ---------- Criterios de aceptación del análisis ----------

test('recepción no entra a configuración, roles ni empleados', function () {
    foreach (['/configuracion', '/roles', '/usuarios', '/sucursales', '/auditoria'] as $url) {
        $this->actingAs($this->recepcion)->get($url)->assertForbidden();
    }
});

test('recepción no modifica precios de productos ni de planes', function () {
    $plan = Plan::create($this->e + ['nombre' => 'Mensual', 'precio' => 100, 'duracion_dias' => 30, 'activo' => true]);

    $this->actingAs($this->recepcion)->put("/catalogo/{$this->producto->id_producto}", ['nombre' => 'Proteína', 'id_tipo' => $this->producto->id_tipo, 'precio' => 1])->assertForbidden();
    $this->actingAs($this->recepcion)->put("/planes/{$plan->id_plan}", ['nombre' => 'Mensual', 'precio' => 1, 'duracion_dias' => 30])->assertForbidden();

    expect((float) $this->producto->fresh()->precio)->toBe(50.0)->and((float) $plan->fresh()->precio)->toBe(100.0);
});

test('recepción vende con su caja abierta pero no anula ventas', function () {
    ($this->vender)($this->recepcion)->assertSessionHasErrors('metodo_pago'); // sin caja abierta
    abrirCaja($this->recepcion);
    ($this->vender)($this->recepcion)->assertSessionHasNoErrors();

    $venta = Venta::firstOrFail();
    $this->actingAs($this->recepcion)->patch("/ventas/{$venta->id_venta}/anular", ['motivo' => 'Quiero anular'])->assertForbidden();
});

test('entrenador no consulta pagos, ventas ni caja', function () {
    $coach = User::factory()->conRol(Role::ENTRENADOR)->create($this->sede);

    foreach (['/ventas', '/caja', '/gastos', '/catalogo'] as $url) {
        $this->actingAs($coach)->get($url)->assertForbidden();
    }
});

test('un cliente no accede a información de otros clientes', function () {
    $user = User::factory()->create($this->e);
    $otro = Cliente::create($this->e + ['nombre' => 'Otro Socio', 'dni' => '999']);

    $this->actingAs($user)->get("/clientes/{$otro->id_cliente}")->assertRedirect('/');
    $this->actingAs($user)->get('/mi-cuenta')->assertOk()->assertDontSee('Otro Socio');
});

test('un gerente de sede no ve la caja ni las ventas de otra sede', function () {
    $norte = Sucursal::withoutGlobalScopes()->create($this->e + ['nombre' => 'Sede Norte', 'activo' => true]);
    $cajeroNorte = User::factory()->conRol(Role::CAJERA)->create($this->e + ['id_sucursal' => $norte->id_sucursal]);
    $sesionNorte = abrirCaja($cajeroNorte);

    $gerenteSede = User::factory()->conRol(Role::GERENTE)->create($this->sede);

    $this->actingAs($gerenteSede)->get("/caja/sesiones/{$sesionNorte->id_sesion}")->assertForbidden();
    $this->actingAs($gerenteSede)->get('/caja')->assertOk()->assertDontSee($cajeroNorte->name);

    // Un gerente de empresa (sin sede fija) sí puede.
    $gerenteEmpresa = User::factory()->conRol(Role::GERENTE)->create($this->e + ['id_sucursal' => null]);
    $this->actingAs($gerenteEmpresa)->get("/caja/sesiones/{$sesionNorte->id_sesion}")->assertOk();
});

test('un empleado no abre cajas de una sucursal que no es la suya', function () {
    $norte = Sucursal::withoutGlobalScopes()->create($this->e + ['nombre' => 'Sede Norte', 'activo' => true]);
    $cajaNorte = Caja::withoutGlobalScopes()->where('id_sucursal', $norte->id_sucursal)->firstOrFail();

    $this->actingAs($this->recepcion)->post('/caja/abrir', ['id_caja' => $cajaNorte->id_caja, 'monto_inicial' => 50])->assertForbidden();
});

test('un usuario bloqueado no puede iniciar sesión y pierde la sesión abierta', function () {
    $this->actingAs($this->admin)->patch("/usuarios/{$this->recepcion->id}/bloqueo")->assertSessionHasErrors('motivo');
    $this->actingAs($this->admin)->patch("/usuarios/{$this->recepcion->id}/bloqueo", ['motivo' => 'Fin de contrato'])->assertSessionHas('success');

    expect($this->recepcion->fresh()->isBloqueado())->toBeTrue()
        ->and(Autorizacion::puede($this->recepcion->fresh(), 'clients.view'))->toBeFalse();

    $this->actingAs($this->recepcion->fresh())->get('/dashboard')->assertRedirect(route('login'));
    $this->assertGuest();

    $this->post('/login', ['email' => $this->recepcion->email, 'password' => 'password'])->assertSessionHasErrors('email');
    $this->assertGuest();
});

// ---------- Permisos individuales y autoridad ----------

test('conceder y restringir permisos individuales cambia lo que puede hacer', function () {
    abrirCaja($this->recepcion);
    ($this->vender)($this->recepcion);
    $venta = Venta::firstOrFail();

    $this->actingAs($this->admin)->put("/usuarios/{$this->recepcion->id}/permisos", [
        'permisos' => ['sales.cancel' => 'grant', 'clients.delete' => 'rol', 'expenses.view' => 'rol'],
    ])->assertSessionHas('success');

    $this->actingAs($this->recepcion)->patch("/ventas/{$venta->id_venta}/anular", ['motivo' => 'Error de cobro'])->assertSessionHas('success');

    // La restricción prevalece sobre el rol.
    $this->actingAs($this->admin)->put("/usuarios/{$this->recepcion->id}/permisos", ['permisos' => ['sales.create' => 'deny']]);
    ($this->vender)($this->recepcion->fresh())->assertForbidden();
    expect(Autorizacion::efectivos($this->recepcion->fresh())->contains('sales.cancel'))->toBeFalse(); // el grant anterior se quitó al guardar
});

test('nadie concede permisos que no tiene ni modifica a alguien con más permisos', function () {
    $gerente = User::factory()->conRol(Role::GERENTE)->create($this->e + ['id_sucursal' => null]);

    // El admin le permite gestionar permisos y empleados.
    $this->actingAs($this->admin)->put("/usuarios/{$gerente->id}/permisos", [
        'permisos' => ['permissions.assign' => 'grant', 'users.manage' => 'grant'],
    ]);

    // El gerente intenta dar "roles.manage" (que él no tiene) a la recepcionista.
    $this->actingAs($gerente->fresh())->put("/usuarios/{$this->recepcion->id}/permisos", [
        'permisos' => ['roles.manage' => 'grant', 'sales.cancel' => 'grant'],
    ])->assertSessionHas('info');

    $efectivos = Autorizacion::efectivos($this->recepcion->fresh());
    expect($efectivos->contains('roles.manage'))->toBeFalse()->and($efectivos->contains('sales.cancel'))->toBeTrue();

    // No puede editar al administrador (tiene más permisos) ni asignarle el rol de admin a nadie.
    $this->actingAs($gerente->fresh())->put("/usuarios/{$this->admin->id}/permisos", ['permisos' => ['sales.cancel' => 'deny']])->assertForbidden();
    $this->actingAs($gerente->fresh())->put("/usuarios/{$this->recepcion->id}", [
        'name' => 'X', 'email' => $this->recepcion->email, 'role_id' => Role::idDe(Role::ADMIN),
    ])->assertSessionHasErrors('role_id', null, 'usuario');
});

test('la empresa crea roles propios a partir de una plantilla', function () {
    $this->actingAs($this->admin)->post('/roles', [
        'etiqueta' => 'Supervisor de ventas',
        'plantilla' => Role::idDe(Role::RECEPCIONISTA),
    ])->assertRedirect();

    $rol = Role::where('nombre', 'supervisor_de_ventas')->firstOrFail();
    expect($rol->id_empresa)->toBe($this->admin->id_empresa)
        ->and($rol->permisos()->count())->toBe(Role::where('nombre', 'recepcionista')->whereNull('id_empresa')->first()->permisos()->count());

    $codigos = $rol->permisos->pluck('codigo')->push('sales.cancel')->push('sales.view')->all();
    $this->actingAs($this->admin)->put("/roles/{$rol->id}", ['etiqueta' => 'Supervisor de ventas', 'permisos' => $codigos])->assertSessionHas('success');

    $supervisor = User::factory()->create($this->sede + ['role_id' => $rol->id]);
    expect($supervisor->isStaff())->toBeTrue()
        ->and(Autorizacion::puede($supervisor, 'sales.cancel'))->toBeTrue();
    $this->actingAs($supervisor)->get('/dashboard')->assertOk();

    // Las plantillas del sistema no se editan.
    $this->actingAs($this->admin)->put('/roles/'.Role::idDe(Role::RECEPCIONISTA), ['etiqueta' => 'X', 'permisos' => []])->assertForbidden();

    // Otra empresa no ve ni usa ese rol.
    $otra = Empresa::create(['nombre' => 'Otra', 'activo' => true]);
    $adminOtra = User::factory()->admin()->deEmpresa($otra)->create();
    $this->actingAs($adminOtra)->get('/roles')->assertOk()->assertDontSee('id="rol-'.$rol->id.'"', false);
    $this->actingAs($adminOtra)->put("/roles/{$rol->id}", ['etiqueta' => 'Robado', 'permisos' => []])->assertForbidden();
});

// ---------- Caja ----------

test('flujo completo de caja: apertura, ventas, egreso, arqueo con diferencia y aprobación', function () {
    $caja = Caja::where('id_sucursal', $this->recepcion->id_sucursal)->firstOrFail();

    $this->actingAs($this->recepcion)->post('/caja/abrir', ['id_caja' => $caja->id_caja, 'monto_inicial' => 100])->assertSessionHas('success');
    $this->actingAs($this->recepcion)->post('/caja/abrir', ['id_caja' => $caja->id_caja, 'monto_inicial' => 100])->assertSessionHas('error'); // ya tiene una

    // La misma caja no la puede abrir otro empleado a la vez.
    $otro = User::factory()->conRol(Role::CAJERA)->create($this->sede);
    $this->actingAs($otro)->post('/caja/abrir', ['id_caja' => $caja->id_caja, 'monto_inicial' => 50])->assertSessionHas('error');

    ($this->vender)($this->recepcion, 3)->assertSessionHasNoErrors(); // +150 efectivo
    $this->actingAs($this->recepcion)->post('/caja/movimientos', ['tipo' => 'egreso', 'monto' => 30, 'metodo_pago' => 'efectivo', 'concepto' => 'Útiles de limpieza'])->assertSessionHas('success');

    $sesion = CajaSesion::where('user_id', $this->recepcion->id)->firstOrFail();
    expect($sesion->load('movimientos')->saldoEsperado()['efectivo'])->toBe(220.0);

    // Arqueo: faltan 10 en efectivo.
    $this->actingAs($this->recepcion)->post('/caja/cerrar', ['contado' => ['efectivo' => 210, 'tarjeta' => 0, 'transferencia' => 0, 'yape' => 0]])->assertRedirect();
    $sesion->refresh();
    expect($sesion->estado)->toBe('observada')->and((float) $sesion->diferencia)->toBe(-10.0);

    // Los movimientos no se pueden modificar.
    expect(fn () => $sesion->movimientos()->first()->update(['monto' => 1]))->toThrow(LogicException::class);

    // Nadie aprueba su propia caja; un gerente sí.
    $this->actingAs($this->recepcion)->patch("/caja/sesiones/{$sesion->id_sesion}/aprobar", ['nota_revision' => 'ok'])->assertForbidden();
    $gerente = User::factory()->conRol(Role::GERENTE)->create($this->e + ['id_sucursal' => null]);
    $this->actingAs($gerente)->patch("/caja/sesiones/{$sesion->id_sesion}/aprobar", ['nota_revision' => 'Faltante repuesto por la empleada'])->assertSessionHas('success');

    expect($sesion->fresh()->estado)->toBe('aprobada')->and($sesion->fresh()->revisado_por)->toBe($gerente->id);
});

test('una caja que cuadra se cierra sin revisión', function () {
    $sesion = abrirCaja($this->recepcion, 50);

    $this->actingAs($this->recepcion)->post('/caja/cerrar', ['contado' => ['efectivo' => 50, 'tarjeta' => 0, 'transferencia' => 0, 'yape' => 0]]);

    expect($sesion->fresh()->estado)->toBe('cuadrada');
});

// ---------- Inventario, anulaciones y auditoría ----------

test('ventas y anulaciones dejan rastro en kardex, caja y auditoría', function () {
    abrirCaja($this->admin);
    ($this->vender)($this->admin, 2);
    $venta = Venta::firstOrFail();

    $this->actingAs($this->admin)->patch("/ventas/{$venta->id_venta}/anular", ['motivo' => 'Producto dañado']);

    expect(InventarioMovimiento::pluck('tipo')->all())->toBe(['venta', 'anulacion'])
        ->and(InventarioMovimiento::latest('id')->first()->stock_resultante)->toBe(10)
        ->and(Auditoria::where('accion', 'venta.anulada')->first()?->descripcion)->toContain('Producto dañado');

    $this->actingAs($this->admin)->get('/auditoria')->assertOk()->assertSee('Producto dañado');
    $this->actingAs($this->admin)->get('/inventario/movimientos')->assertOk()->assertSee('Anulación de venta');
});

test('congelar una membresía extiende su vencimiento al reanudarla', function () {
    $cliente = Cliente::create($this->e + ['nombre' => 'Ana', 'dni' => '1']);
    $plan = Plan::create($this->e + ['nombre' => 'Mensual', 'precio' => 100, 'duracion_dias' => 30, 'activo' => true]);
    $m = Membresia::create(['id_cliente' => $cliente->id_cliente, 'id_plan' => $plan->id_plan, 'fecha_inicio' => today()->subDays(5), 'fecha_fin' => today()->addDays(24), 'estado' => 'activo']);

    $this->actingAs($this->recepcion)->patch("/membresias/{$m->id_membresia}/congelar")->assertForbidden(); // recepción sin permiso
    $this->actingAs($this->admin)->patch("/membresias/{$m->id_membresia}/congelar")->assertSessionHas('success');
    expect($cliente->fresh()->membresiaActiva)->toBeNull();

    $this->travel(10)->days();
    $this->actingAs($this->admin)->patch("/membresias/{$m->id_membresia}/reanudar")->assertSessionHas('success');

    expect($m->fresh()->estado)->toBe('activo')
        ->and($m->fresh()->fecha_fin->toDateString())->toBe(today()->subDays(10)->addDays(34)->toDateString())
        ->and($m->fresh()->dias_congelados)->toBe(10);
});

// ---------- Nutrición: datos sensibles ----------

test('la nutrición es privada: el coach no la ve, la nutricionista solo ve a sus pacientes', function () {
    $nutri = User::factory()->conRol(Role::NUTRICIONISTA)->create($this->sede);
    $usuarioCoach = User::factory()->conRol(Role::ENTRENADOR)->create($this->sede);
    $coach = Entrenador::create($this->e + ['nombre' => 'Coach', 'user_id' => $usuarioCoach->id, 'activo' => true]);

    $paciente = Cliente::create($this->e + ['nombre' => 'Paciente Uno', 'dni' => '1', 'id_nutricionista' => $nutri->id, 'id_entrenador' => $coach->id_entrenador]);
    $ajeno = Cliente::create($this->e + ['nombre' => 'Ajeno', 'dni' => '2']);

    $this->actingAs($nutri)->get('/nutricion')->assertOk()->assertSee('Paciente Uno')->assertDontSee('Ajeno');
    $this->actingAs($nutri)->post("/clientes/{$paciente->id_cliente}/nutricion", ['objetivo' => 'Bajar 5 kg', 'calorias' => 1800])->assertSessionHas('success');
    $this->actingAs($nutri)->post("/clientes/{$ajeno->id_cliente}/nutricion", ['objetivo' => 'X'])->assertForbidden();

    // El coach ve la ficha de su alumno pero no la sección nutricional.
    $this->actingAs($usuarioCoach)->get("/clientes/{$paciente->id_cliente}")->assertOk()->assertDontSee('Bajar 5 kg');
    $this->actingAs($usuarioCoach)->get('/nutricion')->assertForbidden();

    // El cliente ve su propio plan.
    $socio = User::factory()->create($this->e);
    $paciente->update(['user_id' => $socio->id]);
    $this->actingAs($socio)->get('/mi-cuenta')->assertOk()->assertSee('Bajar 5 kg');

    expect(PlanNutricional::count())->toBe(1);
});

// ---------- Menú dinámico ----------

test('el menú se construye según los permisos de cada persona', function () {
    $this->actingAs($this->recepcion)->get('/dashboard')->assertOk()
        ->assertSee('Mi caja')->assertSee('Mis ventas')->assertDontSee('Roles y permisos')->assertDontSee('Empleados');

    $almacen = User::factory()->conRol(Role::ALMACENERO)->create($this->sede);
    $this->actingAs($almacen)->get('/dashboard')->assertOk()
        ->assertSee('Almacén')->assertSee('Inventario')->assertDontSee('Mi caja')->assertDontSee('Clientes');

    // Un permiso individual hace aparecer el módulo sin cambiar de rol.
    $this->actingAs($this->admin)->put("/usuarios/{$almacen->id}/permisos", ['permisos' => ['clients.view' => 'grant']]);
    $this->actingAs($almacen->fresh())->get('/dashboard')->assertSee('Clientes');
});
