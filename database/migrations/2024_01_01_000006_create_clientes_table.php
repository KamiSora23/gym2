<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id('id_cliente');
            $table->string('dni')->unique();
            $table->string('nombre');
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->foreign('id_usuario')->references('id_usuario')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
