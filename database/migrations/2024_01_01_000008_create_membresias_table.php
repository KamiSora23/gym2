<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membresias', function (Blueprint $table) {
            $table->id('id_membresia');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_plan');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado')->default('activo');
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->onDelete('cascade');
            $table->foreign('id_plan')->references('id_plan')->on('planes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membresias');
    }
};
