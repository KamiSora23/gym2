<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_usuario');
            $table->date('fecha');
            $table->decimal('total', 10, 2);
            $table->string('estado')->default('completado');
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id_usuario')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
