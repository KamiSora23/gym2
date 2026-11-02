<?php

namespace Database\Seeders;

use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Entrenador;
use App\Models\Gasto;
use App\Models\Ingreso;
use App\Models\Inventario;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Producto;
use App\Models\Progreso;
use App\Models\Role;
use App\Models\Rutina;
use App\Models\Sucursal;
use App\Models\TipoProducto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Seeder;

/**
 * Datos de demostración para la empresa principal y una segunda empresa.
 * Solo se ejecuta si la empresa principal aún no tiene planes (no duplica datos).
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RolesSeeder::class, SuperAdminSeeder::class, EmpresaPrincipalSeeder::class]);

        $empresa = Empresa::where('nombre', 'GYM IMPULSO')->firstOrFail();

        if (Plan::withoutGlobalScopes()->where('id_empresa', $empresa->id_empresa)->exists()) {
            $this->command?->warn('La empresa principal ya tiene datos; no se generó la demo.');

            return;
        }

        $this->poblar($empresa, ['Sede principal', 'Sede Miraflores']);

        $otra = Empresa::firstOrCreate(['nombre' => 'POWER FIT'], ['email' => 'hola@powerfit.pe', 'activo' => true]);
        $this->poblar($otra, ['Sede Surco']);

        User::firstOrCreate(['email' => 'admin@powerfit.pe'], [
            'name' => 'Admin Power Fit',
            'password' => '12345678',
            'role_id' => Role::idDe(Role::ADMIN),
            'id_empresa' => $otra->id_empresa,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Lun-Vie mañana y tarde, sábado por la mañana.
     */
    public static function disponibilidadDemo(Entrenador $coach): void
    {
        if ($coach->disponibilidad()->exists()) {
            return;
        }

        foreach (range(1, 5) as $dia) {
            $coach->disponibilidad()->create(['dia_semana' => $dia, 'hora_inicio' => '06:00', 'hora_fin' => '12:00']);
            $coach->disponibilidad()->create(['dia_semana' => $dia, 'hora_inicio' => '16:00', 'hora_fin' => '21:00']);
        }

        $coach->disponibilidad()->create(['dia_semana' => 6, 'hora_inicio' => '08:00', 'hora_fin' => '13:00']);
    }

    public static function rutinaDemo(Cliente $cliente, Entrenador $coach): void
    {
        if (Rutina::withoutGlobalScopes()->where('id_cliente', $cliente->id_cliente)->exists()) {
            return;
        }

        $rutina = Rutina::create([
            'id_empresa' => $cliente->id_empresa,
            'id_cliente' => $cliente->id_cliente,
            'id_entrenador' => $coach->id_entrenador,
            'nombre' => 'Fuerza e hipertrofia 3 días',
            'objetivo' => 'Ganar masa muscular',
            'notas' => 'Calentar 10 minutos. Prioriza la técnica antes que el peso.',
            'activa' => true,
        ]);

        $ejercicios = [
            ['Día 1 · Piernas', 'Sentadilla', 4, '8-10', '2 min'], ['Día 1 · Piernas', 'Prensa', 3, '12', '90 s'],
            ['Día 2 · Empuje', 'Press banca', 4, '8-10', '2 min'], ['Día 2 · Empuje', 'Press militar', 3, '10', '90 s'],
            ['Día 3 · Tirón', 'Dominadas', 4, '6-8', '2 min'], ['Día 3 · Tirón', 'Remo con barra', 3, '10', '90 s'],
        ];

        foreach ($ejercicios as $orden => [$dia, $nombre, $series, $reps, $descanso]) {
            $rutina->ejercicios()->create(['dia' => $dia, 'ejercicio' => $nombre, 'series' => $series, 'repeticiones' => $reps, 'descanso' => $descanso, 'orden' => $orden]);
        }

        foreach ([[56, 82.4, 22.0], [28, 81.1, 21.2], [0, 79.8, 20.1]] as [$diasAtras, $peso, $grasa]) {
            Progreso::create([
                'id_empresa' => $cliente->id_empresa,
                'id_cliente' => $cliente->id_cliente,
                'id_entrenador' => $coach->id_entrenador,
                'fecha' => today()->subDays($diasAtras),
                'peso' => $peso,
                'grasa' => $grasa,
            ]);
        }
    }

    private function poblar(Empresa $empresa, array $nombresSucursales): void
    {
        $e = ['id_empresa' => $empresa->id_empresa];

        $sucursales = collect($nombresSucursales)->map(fn ($nombre) => Sucursal::withoutGlobalScopes()->firstOrCreate($e + ['nombre' => $nombre], ['activo' => true]));
        $principal = $sucursales->first();

        foreach ([['Recepción', 'recepcionista'], ['Caja', 'cajera'], ['Gerencia', 'gerente'], ['Almacén', 'almacenero'], ['Nutrición', 'nutricionista']] as [$nombre, $rol]) {
            $slug = strtolower(preg_replace('/\W+/', '', $empresa->nombre));
            User::firstOrCreate(['email' => "{$rol}@{$slug}.test"], [
                'name' => "{$nombre} {$empresa->nombre}",
                'password' => '12345678',
                'role_id' => Role::idDe($rol),
                'id_empresa' => $empresa->id_empresa,
                // El gerente supervisa toda la empresa; el resto trabaja en la sede principal.
                'id_sucursal' => $rol === 'gerente' ? null : $principal->id_sucursal,
                'email_verified_at' => now(),
            ]);
        }

        $nutricionista = User::where('email', "nutricionista@{$slug}.test")->first();

        $planes = collect([
            ['Mensual', 120, 30, 'Acceso ilimitado a sala de máquinas'],
            ['Trimestral', 320, 90, 'Sala + clases grupales'],
            ['Anual', 1100, 365, 'Todo incluido + evaluación física'],
        ])->map(fn ($p) => Plan::create($e + ['nombre' => $p[0], 'precio' => $p[1], 'duracion_dias' => $p[2], 'descripcion' => $p[3], 'activo' => true]));

        $categorias = collect(['Suplementos', 'Accesorios', 'Bebidas'])
            ->mapWithKeys(fn ($n) => [$n => TipoProducto::create($e + ['nombre' => $n])]);

        $productos = collect([
            ['Proteína Whey 2kg', 'Suplementos', 85],
            ['Creatina 300g', 'Suplementos', 45],
            ['Guantes de entrenamiento', 'Accesorios', 25],
            ['Shaker 700ml', 'Accesorios', 12],
            ['Bebida isotónica', 'Bebidas', 3.5],
            ['Agua mineral 625ml', 'Bebidas', 2],
        ])->map(function ($p) use ($e, $categorias, $sucursales) {
            $producto = Producto::create($e + ['nombre' => $p[0], 'id_tipo' => $categorias[$p[1]]->id_tipo, 'precio' => $p[2], 'activo' => true]);

            foreach ($sucursales as $s) {
                Inventario::create(['id_producto' => $producto->id_producto, 'id_sucursal' => $s->id_sucursal, 'cantidad' => random_int(4, 40), 'precio_compra' => round($p[2] * 0.6, 2)]);
            }

            return $producto;
        });

        $coaches = collect([
            ['Carlos Ruiz', 'Crossfit y funcional'],
            ['María López', 'Yoga y pilates'],
            ['Roberto Gómez', 'Spinning y boxeo'],
        ])->map(fn ($c) => Entrenador::create($e + ['nombre' => $c[0], 'especialidad' => $c[1], 'id_sucursal' => $principal->id_sucursal, 'activo' => true]));

        $slug = strtolower(preg_replace('/\W+/', '', $empresa->nombre));
        $usuarioCoach = User::firstOrCreate(['email' => "entrenador@{$slug}.test"], [
            'name' => $coaches[0]->nombre,
            'password' => '12345678',
            'role_id' => Role::idDe(Role::ENTRENADOR),
            'id_empresa' => $empresa->id_empresa,
            'id_sucursal' => $principal->id_sucursal,
            'email_verified_at' => now(),
        ]);
        $coaches[0]->update(['user_id' => $usuarioCoach->id, 'bio' => 'Coach certificado en entrenamiento funcional y crossfit.']);
        static::disponibilidadDemo($coaches[0]);

        $clases = collect([
            ['Yoga', 1, '07:00', '08:00', 1], ['Spinning', 1, '19:00', '20:00', 2], ['Crossfit', 2, '18:00', '19:00', 0],
            ['Pilates', 3, '09:00', '10:00', 1], ['Funcional', 4, '07:00', '08:00', 0], ['Boxeo', 5, '19:00', '20:00', 2],
            ['Full Body', 6, '09:00', '10:30', 0], ['Yoga', 7, '10:00', '11:00', 1],
        ])->map(fn ($c) => Clase::create($e + [
            'id_sucursal' => $principal->id_sucursal, 'nombre' => $c[0], 'dia_semana' => $c[1],
            'hora_inicio' => $c[2], 'hora_fin' => $c[3], 'cupo' => 15, 'id_entrenador' => $coaches[$c[4]]->id_entrenador,
        ]));

        $nombres = ['Ana García', 'Luis Torres', 'Sofía Ramírez', 'Diego Castillo', 'Valeria Flores', 'Jorge Mendoza', 'Camila Rojas', 'Mateo Vargas'];

        foreach ($nombres as $i => $nombre) {
            $cliente = Cliente::create($e + [
                'nombre' => $nombre,
                'dni' => (string) (70000000 + $empresa->id_empresa * 1000 + $i),
                'telefono' => '9'.random_int(10000000, 99999999),
                'id_entrenador' => $i % 3 === 0 ? $coaches[$i % 3]->id_entrenador : null,
                'id_nutricionista' => $i % 2 === 0 ? $nutricionista?->id : null,
            ]);

            $clases->random(2)->each(fn ($clase) => $clase->clientes()->syncWithoutDetaching([$cliente->id_cliente]));

            if ($i === 0) {
                static::rutinaDemo($cliente, $coaches[0]);
            }

            if ($i < 6) {
                $plan = $planes[$i % 3];
                $fecha = today()->subDays($i * 2);
                $venta = Venta::create($e + [
                    'id_sucursal' => $principal->id_sucursal, 'id_cliente' => $cliente->id_cliente, 'user_id' => User::where('id_empresa', $empresa->id_empresa)->value('id') ?? 1,
                    'fecha' => $fecha, 'total' => $plan->precio, 'estado' => $i === 5 ? 'pendiente' : 'completado', 'metodo_pago' => 'efectivo',
                ]);
                $venta->detalles()->create(['id_plan' => $plan->id_plan, 'descripcion' => "Membresía {$plan->nombre}", 'cantidad' => 1, 'precio_unitario' => $plan->precio, 'subtotal' => $plan->precio]);
                Membresia::create(['id_cliente' => $cliente->id_cliente, 'id_plan' => $plan->id_plan, 'id_venta' => $venta->id_venta, 'fecha_inicio' => $fecha, 'fecha_fin' => $fecha->copy()->addDays($plan->duracion_dias - 1), 'estado' => 'activo']);

                if ($venta->estado === 'completado') {
                    Ingreso::create($e + ['id_sucursal' => $principal->id_sucursal, 'concepto' => "Venta {$venta->folio}", 'monto' => $venta->total, 'fecha' => $fecha, 'user_id' => $venta->user_id, 'id_venta' => $venta->id_venta]);
                }
            }

            foreach (range(0, 6) as $dia) {
                if (random_int(0, 2) === 0) {
                    continue;
                }
                $fecha = today()->startOfWeek()->addDays($dia);
                if ($fecha->isFuture()) {
                    continue;
                }
                Asistencia::create($e + ['id_sucursal' => $principal->id_sucursal, 'id_cliente' => $cliente->id_cliente, 'fecha' => $fecha, 'hora_entrada' => sprintf('%02d:%02d:00', random_int(6, 20), random_int(0, 59)), 'hora_salida' => $fecha->isToday() ? null : '21:00:00']);
            }
        }

        foreach ([['Renta del local', 'Renta', 800], ['Luz y agua', 'Servicios', 180], ['Limpieza', 'Mantenimiento', 90]] as $i => [$concepto, $categoria, $monto]) {
            Gasto::create($e + ['id_sucursal' => $principal->id_sucursal, 'concepto' => $concepto, 'categoria' => $categoria, 'monto' => $monto, 'fecha' => today()->subDays($i), 'user_id' => User::where('id_empresa', $empresa->id_empresa)->value('id') ?? 1]);
        }
    }
}
