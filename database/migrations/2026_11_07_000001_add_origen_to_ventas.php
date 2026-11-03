<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen de la venta: en caja (recepción) o pedido del sitio web.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('origen')->default('caja');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', fn (Blueprint $table) => $table->dropColumn('origen'));
    }
};
