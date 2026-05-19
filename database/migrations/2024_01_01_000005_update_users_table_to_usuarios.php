<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('id', 'id_usuario');
            $table->renameColumn('name', 'usuario');
            $table->renameColumn('password', 'contraseña');
            $table->renameColumn('email_verified_at', 'verificacion');
            $table->unsignedBigInteger('id_rol')->nullable()->after('verificacion');
            $table->foreign('id_rol')->references('id_rol')->on('roles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_rol']);
            $table->dropColumn('id_rol');
            $table->renameColumn('id_usuario', 'id');
            $table->renameColumn('usuario', 'name');
            $table->renameColumn('contraseña', 'password');
            $table->renameColumn('verificacion', 'email_verified_at');
        });
    }
};
