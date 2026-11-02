<?php

use App\Support\CatalogoPermisos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Motor de autorización (RBAC): catálogo de permisos, permisos por rol,
 * roles propios por empresa y permisos individuales por usuario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('modulo');
            $table->string('descripcion');
            $table->string('grupo');
            $table->timestamps();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('id_empresa')->nullable()->constrained('empresa', 'id_empresa')->cascadeOnDelete();
            $table->string('etiqueta')->nullable();
            $table->string('descripcion')->nullable();
            $table->boolean('es_sistema')->default(false);
        });

        Schema::create('role_permiso', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete();
            $table->primary(['role_id', 'permiso_id']);
        });

        // Excepciones por usuario: "grant" concede un permiso extra, "deny" lo restringe (prevalece).
        Schema::create('user_permiso', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete();
            $table->enum('tipo', ['grant', 'deny']);
            $table->foreignId('otorgado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'permiso_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('estado')->default('activo'); // activo | bloqueado
            $table->string('motivo_bloqueo')->nullable();
            $table->timestamp('bloqueado_en')->nullable();
        });

        Schema::table('roles', fn (Blueprint $table) => $table->index(['id_empresa', 'nombre']));

        CatalogoPermisos::sincronizar();
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['estado', 'motivo_bloqueo', 'bloqueado_en']));
        Schema::dropIfExists('user_permiso');
        Schema::dropIfExists('role_permiso');
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex(['id_empresa', 'nombre']);
            $table->dropConstrainedForeignId('id_empresa');
            $table->dropColumn(['etiqueta', 'descripcion', 'es_sistema']);
        });
        Schema::dropIfExists('permisos');
    }
};
