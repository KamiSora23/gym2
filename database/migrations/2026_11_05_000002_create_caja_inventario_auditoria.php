<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Caja por sesiones, kardex de inventario, anulaciones con motivo,
 * congelamiento de membresías, nutrición y auditoría.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- Caja ----------
        Schema::create('cajas', function (Blueprint $table) {
            $table->id('id_caja');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->foreignId('id_sucursal')->constrained('sucursales', 'id_sucursal')->cascadeOnDelete();
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('caja_sesiones', function (Blueprint $table) {
            $table->id('id_sesion');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->foreignId('id_sucursal')->constrained('sucursales', 'id_sucursal')->cascadeOnDelete();
            $table->foreignId('id_caja')->constrained('cajas', 'id_caja')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('monto_inicial', 10, 2)->default(0);
            $table->timestamp('abierta_en');
            $table->timestamp('cerrada_en')->nullable();
            $table->json('esperado')->nullable();   // por método de pago
            $table->json('contado')->nullable();    // por método de pago
            $table->decimal('diferencia', 10, 2)->nullable();
            $table->string('estado')->default('abierta'); // abierta | cuadrada | observada | aprobada
            $table->text('observaciones')->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_en')->nullable();
            $table->text('nota_revision')->nullable();
            $table->timestamps();
        });

        // Los movimientos nunca se editan: las correcciones son movimientos nuevos.
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->foreignId('id_sesion')->constrained('caja_sesiones', 'id_sesion')->cascadeOnDelete();
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('tipo');    // ingreso | egreso
            $table->string('origen');  // venta | cobro | anulacion | manual
            $table->string('metodo_pago')->default('efectivo');
            $table->decimal('monto', 10, 2);
            $table->string('concepto');
            $table->foreignId('id_venta')->nullable()->constrained('ventas', 'id_venta')->nullOnDelete();
            $table->timestamps();
        });

        // ---------- Productos e inventario ----------
        Schema::table('productos', function (Blueprint $table) {
            $table->string('sku')->nullable();
            $table->decimal('precio_compra', 10, 2)->default(0);
            $table->unsignedInteger('stock_minimo')->default(5);
        });

        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->foreignId('id_sucursal')->constrained('sucursales', 'id_sucursal')->cascadeOnDelete();
            $table->foreignId('id_producto')->constrained('productos', 'id_producto')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo'); // entrada | venta | anulacion | ajuste | merma | salida
            $table->integer('cantidad'); // positiva entra, negativa sale
            $table->integer('stock_resultante');
            $table->string('motivo')->nullable();
            $table->foreignId('id_venta')->nullable()->constrained('ventas', 'id_venta')->nullOnDelete();
            $table->timestamps();
        });

        // ---------- Ventas: quién y por qué se anuló ----------
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('id_sesion')->nullable()->constrained('caja_sesiones', 'id_sesion')->nullOnDelete();
            $table->foreignId('anulada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulada_en')->nullable();
            $table->string('motivo_anulacion')->nullable();
        });

        // ---------- Membresías: congelamiento ----------
        Schema::table('membresias', function (Blueprint $table) {
            $table->date('congelada_desde')->nullable();
            $table->unsignedInteger('dias_congelados')->default(0);
        });

        // ---------- Nutrición ----------
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('id_nutricionista')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('planes_nutricionales', function (Blueprint $table) {
            $table->id('id_plan_nutricional');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->foreignId('id_cliente')->constrained('clientes', 'id_cliente')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('objetivo');
            $table->unsignedInteger('calorias')->nullable();
            $table->unsignedInteger('proteinas')->nullable();
            $table->unsignedInteger('carbohidratos')->nullable();
            $table->unsignedInteger('grasas')->nullable();
            $table->text('indicaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ---------- Auditoría ----------
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empresa')->nullable()->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion');
            $table->string('descripcion');
            $table->string('entidad')->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->json('datos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['id_empresa', 'created_at']);
        });

        // Una caja principal por cada sucursal existente.
        foreach (DB::table('sucursales')->get() as $s) {
            DB::table('cajas')->insert([
                'id_empresa' => $s->id_empresa,
                'id_sucursal' => $s->id_sucursal,
                'nombre' => 'Caja principal',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
        Schema::dropIfExists('planes_nutricionales');
        Schema::table('clientes', fn (Blueprint $table) => $table->dropConstrainedForeignId('id_nutricionista'));
        Schema::table('membresias', fn (Blueprint $table) => $table->dropColumn(['congelada_desde', 'dias_congelados']));
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_sesion');
            $table->dropConstrainedForeignId('anulada_por');
            $table->dropColumn(['anulada_en', 'motivo_anulacion']);
        });
        Schema::dropIfExists('inventario_movimientos');
        Schema::table('productos', fn (Blueprint $table) => $table->dropColumn(['sku', 'precio_compra', 'stock_minimo']));
        Schema::dropIfExists('caja_movimientos');
        Schema::dropIfExists('caja_sesiones');
        Schema::dropIfExists('cajas');
    }
};
