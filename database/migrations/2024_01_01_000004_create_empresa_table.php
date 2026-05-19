<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->id('id_empresa');
            $table->string('ruc');
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('horario')->nullable();
            $table->string('ubicacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
