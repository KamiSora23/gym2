<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Herramientas del coach: disponibilidad semanal, rutinas para sus alumnos
 * y registro del progreso físico.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entrenadores', function (Blueprint $table) {
            $table->text('bio')->nullable();
        });

        Schema::create('entrenador_disponibilidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_entrenador')->constrained('entrenadores', 'id_entrenador')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana'); // 1 = lunes ... 7 = domingo
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();
        });

        Schema::create('rutinas', function (Blueprint $table) {
            $table->id('id_rutina');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->foreignId('id_cliente')->constrained('clientes', 'id_cliente')->cascadeOnDelete();
            $table->foreignId('id_entrenador')->nullable()->constrained('entrenadores', 'id_entrenador')->nullOnDelete();
            $table->string('nombre');
            $table->string('objetivo')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('rutina_ejercicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_rutina')->constrained('rutinas', 'id_rutina')->cascadeOnDelete();
            $table->string('dia')->nullable(); // "Día 1", "Lunes", "Piernas"...
            $table->string('ejercicio');
            $table->unsignedSmallInteger('series')->nullable();
            $table->string('repeticiones')->nullable(); // "10-12", "30 s"
            $table->string('descanso')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('progresos', function (Blueprint $table) {
            $table->id('id_progreso');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->foreignId('id_cliente')->constrained('clientes', 'id_cliente')->cascadeOnDelete();
            $table->foreignId('id_entrenador')->nullable()->constrained('entrenadores', 'id_entrenador')->nullOnDelete();
            $table->date('fecha');
            $table->decimal('peso', 5, 2)->nullable();
            $table->decimal('grasa', 4, 1)->nullable();
            $table->decimal('cintura', 5, 1)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progresos');
        Schema::dropIfExists('rutina_ejercicios');
        Schema::dropIfExists('rutinas');
        Schema::dropIfExists('entrenador_disponibilidad');
        Schema::table('entrenadores', fn (Blueprint $table) => $table->dropColumn('bio'));
    }
};
