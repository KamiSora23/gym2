<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('imagen')->nullable()->after('descripcion');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('correo');
        });
    }

    public function down(): void
    {
        Schema::table('productos', fn (Blueprint $table) => $table->dropColumn('imagen'));
        Schema::table('clientes', fn (Blueprint $table) => $table->dropColumn('activo'));
    }
};
