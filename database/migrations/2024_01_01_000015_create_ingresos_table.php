<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id('id_ingreso');
            $table->string('concepto');
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('id_venta')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_venta')->references('id_venta')->on('ventas')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingresos');
    }
};
