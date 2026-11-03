<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_gasto', function (Blueprint $table) {
            $table->id('id_categoria');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->string('nombre', 60);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_empresa', 'nombre']);
        });

        Schema::table('tipo_producto', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_gasto');
        Schema::table('tipo_producto', fn (Blueprint $table) => $table->dropColumn('activo'));
    }
};
