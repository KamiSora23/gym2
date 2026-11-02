<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Entrenadores (coaches). Pueden estar vinculados a un usuario con rol "entrenador".
        Schema::create('entrenadores', function (Blueprint $table) {
            $table->id('id_entrenador');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->foreignId('id_sucursal')->nullable()->constrained('sucursales', 'id_sucursal')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre');
            $table->string('especialidad')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Clases grupales del horario semanal.
        Schema::create('clases', function (Blueprint $table) {
            $table->id('id_clase');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->foreignId('id_sucursal')->nullable()->constrained('sucursales', 'id_sucursal')->nullOnDelete();
            $table->foreignId('id_entrenador')->nullable()->constrained('entrenadores', 'id_entrenador')->nullOnDelete();
            $table->string('nombre');
            $table->unsignedTinyInteger('dia_semana'); // 1 = lunes ... 7 = domingo
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->unsignedInteger('cupo')->default(20);
            $table->timestamps();
        });

        // Clientes inscritos en cada clase.
        Schema::create('clase_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_clase')->constrained('clases', 'id_clase')->cascadeOnDelete();
            $table->foreignId('id_cliente')->constrained('clientes', 'id_cliente')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['id_clase', 'id_cliente']);
        });

        // Coach personal asignado al cliente.
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('id_entrenador')->nullable()->constrained('entrenadores', 'id_entrenador')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clientes', fn (Blueprint $table) => $table->dropConstrainedForeignId('id_entrenador'));
        Schema::dropIfExists('clase_cliente');
        Schema::dropIfExists('clases');
        Schema::dropIfExists('entrenadores');
    }
};
