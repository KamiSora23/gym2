<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('codigo_acceso', 40)->nullable()->unique()->after('activo');
        });

        Schema::table('configuracions', function (Blueprint $table) {
            $table->boolean('checkin_qr')->default(false)->after('moneda');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['codigo_acceso']);
            $table->dropColumn('codigo_acceso');
        });

        Schema::table('configuracions', fn (Blueprint $table) => $table->dropColumn('checkin_qr'));
    }
};
