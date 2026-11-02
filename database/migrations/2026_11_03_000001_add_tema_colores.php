<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tonos adicionales del tema: fondo del panel, colores del sitio web público
 * y colores propios por sucursal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracions', function (Blueprint $table) {
            $table->string('tema')->nullable();
            $table->string('color_fondo')->default('#f5f7fb');
            $table->string('web_color_primario')->default('#ffc107');
            $table->string('web_color_oscuro')->default('#0f172a');
            $table->boolean('web_usa_colores_panel')->default(false);
        });

        Schema::table('sucursales', function (Blueprint $table) {
            $table->string('color_primario')->nullable();
            $table->string('color_sidebar')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sucursales', fn (Blueprint $table) => $table->dropColumn(['color_primario', 'color_sidebar']));
        Schema::table('configuracions', fn (Blueprint $table) => $table->dropColumn(['tema', 'color_fondo', 'web_color_primario', 'web_color_oscuro', 'web_usa_colores_panel']));
    }
};
