<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id('id_asistencia');
            $table->unsignedBigInteger('id_cliente');
            $table->date('fecha');
            $table->time('hora_entrada');
            $table->time('hora_salida')->nullable();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
