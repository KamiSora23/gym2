<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convierte el sistema en multiempresa: empresas -> sucursales,
 * y cada registro operativo pasa a pertenecer a una empresa (y sucursal).
 */
return new class extends Migration
{
    /** Tablas cuyos registros pertenecen a una empresa. */
    private array $porEmpresa = ['users', 'clientes', 'planes', 'tipo_producto', 'productos', 'ventas', 'gastos', 'ingresos', 'asistencias', 'configuracions'];

    /** Tablas cuyos registros pertenecen además a una sucursal. */
    private array $porSucursal = ['users', 'inventario', 'ventas', 'gastos', 'ingresos', 'asistencias'];

    public function up(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->string('ruc')->nullable()->change();
            $table->boolean('activo')->default(true)->after('ubicacion');
        });

        Schema::create('sucursales', function (Blueprint $table) {
            $table->id('id_sucursal');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        foreach ($this->porEmpresa as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('id_empresa')->nullable()->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            });
        }

        foreach ($this->porSucursal as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('id_sucursal')->nullable()->constrained('sucursales', 'id_sucursal')->nullOnDelete();
            });
        }

        // El DNI pasa a ser único por empresa (no global).
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['dni']);
            $table->unique(['id_empresa', 'dni']);
        });

        Schema::table('planes', function (Blueprint $table) {
            $table->unsignedInteger('duracion_dias')->default(30);
            $table->boolean('activo')->default(true);
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->boolean('activo')->default(true);
        });

        Schema::table('inventario', function (Blueprint $table) {
            $table->decimal('precio_compra', 10, 2)->default(0)->change();
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cliente')->nullable()->change();
            $table->string('metodo_pago')->default('efectivo');
        });

        Schema::table('venta_detalle', function (Blueprint $table) {
            $table->unsignedBigInteger('id_producto')->nullable()->change();
            $table->foreignId('id_plan')->nullable()->constrained('planes', 'id_plan')->nullOnDelete();
            $table->string('descripcion')->nullable();
        });

        Schema::table('membresias', function (Blueprint $table) {
            $table->foreignId('id_venta')->nullable()->constrained('ventas', 'id_venta')->nullOnDelete();
        });

        Schema::table('gastos', function (Blueprint $table) {
            $table->string('categoria')->nullable();
        });

        $this->asignarDatosExistentes();
    }

    /**
     * Si ya había datos, se agrupan en una empresa y sucursal principal.
     */
    private function asignarDatosExistentes(): void
    {
        $hayDatos = collect($this->porEmpresa)->some(fn ($tabla) => DB::table($tabla)->exists());

        if (! $hayDatos) {
            return;
        }

        $config = DB::table('configuracions')->first();

        $empresaId = DB::table('empresa')->insertGetId([
            'nombre' => $config->nombre_gym ?? 'GYM IMPULSO',
            'email' => $config->email ?? null,
            'telefono' => $config->telefono ?? null,
            'direccion' => $config->direccion ?? null,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_empresa');

        $sucursalId = DB::table('sucursales')->insertGetId([
            'id_empresa' => $empresaId,
            'nombre' => 'Sede principal',
            'direccion' => $config->direccion ?? null,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_sucursal');

        $superAdmin = DB::table('roles')->where('nombre', 'super_admin')->value('id');

        foreach ($this->porEmpresa as $tabla) {
            DB::table($tabla)
                ->whereNull('id_empresa')
                ->when($tabla === 'users' && $superAdmin, fn ($q) => $q->where('role_id', '!=', $superAdmin))
                ->update(['id_empresa' => $empresaId]);
        }

        foreach ($this->porSucursal as $tabla) {
            DB::table($tabla)
                ->whereNull('id_sucursal')
                ->when($tabla === 'users', fn ($q) => $q->whereNotNull('id_empresa'))
                ->update(['id_sucursal' => $sucursalId]);
        }
    }

    public function down(): void
    {
        Schema::table('gastos', fn (Blueprint $table) => $table->dropColumn('categoria'));
        Schema::table('membresias', fn (Blueprint $table) => $table->dropConstrainedForeignId('id_venta'));

        Schema::table('venta_detalle', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_plan');
            $table->dropColumn('descripcion');
        });

        Schema::table('ventas', fn (Blueprint $table) => $table->dropColumn('metodo_pago'));
        Schema::table('productos', fn (Blueprint $table) => $table->dropColumn('activo'));
        Schema::table('planes', fn (Blueprint $table) => $table->dropColumn(['duracion_dias', 'activo']));

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['id_empresa', 'dni']);
            $table->unique('dni');
        });

        foreach ($this->porSucursal as $tabla) {
            Schema::table($tabla, fn (Blueprint $table) => $table->dropConstrainedForeignId('id_sucursal'));
        }

        foreach ($this->porEmpresa as $tabla) {
            Schema::table($tabla, fn (Blueprint $table) => $table->dropConstrainedForeignId('id_empresa'));
        }

        Schema::dropIfExists('sucursales');
        Schema::table('empresa', fn (Blueprint $table) => $table->dropColumn('activo'));
    }
};
