<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Responsables de cada caja y moneda de cada empresa (por defecto soles).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Si una caja tiene responsables, solo ellos pueden abrirla.
        Schema::create('caja_usuario', function (Blueprint $table) {
            $table->foreignId('id_caja')->constrained('cajas', 'id_caja')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['id_caja', 'user_id']);
        });

        Schema::table('configuracions', function (Blueprint $table) {
            $table->string('moneda', 8)->default('S/');
        });
    }

    public function down(): void
    {
        Schema::table('configuracions', fn (Blueprint $table) => $table->dropColumn('moneda'));
        Schema::dropIfExists('caja_usuario');
    }
};
