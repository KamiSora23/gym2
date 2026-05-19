<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id('id_inventario');
            $table->unsignedBigInteger('id_producto');
            $table->integer('cantidad');
            $table->decimal('precio_compra', 10, 2);
            $table->foreign('id_producto')->references('id_producto')->on('productos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};
