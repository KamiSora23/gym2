<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracions', function (Blueprint $table) {

            $table->id();

            // DATOS GENERALES
            $table->string('nombre_gym')->default('GYM IMPULSO');
            $table->string('slogan')->nullable();

            // LOGOS
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();

            // COLORES
            $table->string('color_primario')->default('#ff4d4f');
            $table->string('color_sidebar')->default('#111827');
            $table->string('color_secundario')->default('#ffffff');

            // CONTACTO
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();

            // HORARIO (NUEVO)
            $table->string('horario_lunes')->nullable();
            $table->string('horario_martes')->nullable();
            $table->string('horario_miercoles')->nullable();
            $table->string('horario_jueves')->nullable();
            $table->string('horario_viernes')->nullable();
            $table->string('horario_sabado')->nullable();
            $table->string('horario_domingo')->nullable();

            // REDES
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();

            // LOGIN
            $table->string('imagen_login')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracions');
    }
};
