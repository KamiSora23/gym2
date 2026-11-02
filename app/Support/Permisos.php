<?php

namespace App\Support;

use App\Models\Role;
use App\Models\User;

/**
 * Qué roles pueden usar cada módulo del panel. El super admin siempre puede.
 */
class Permisos
{
    public const MODULOS = [
        'resumen' => [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA, Role::ENTRENADOR],
        'clientes' => [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA, Role::ENTRENADOR],
        'asistencias' => [Role::ADMIN, Role::RECEPCIONISTA, Role::ENTRENADOR],
        'ventas' => [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA],
        'gastos' => [Role::ADMIN, Role::CAJERA],
        'catalogo' => [Role::ADMIN, Role::CAJERA],
        'planes' => [Role::ADMIN, Role::RECEPCIONISTA],
        'entrenamiento' => [Role::ADMIN, Role::RECEPCIONISTA, Role::ENTRENADOR],
        'sucursales' => [Role::ADMIN],
        'usuarios' => [Role::ADMIN],
        'configuracion' => [Role::ADMIN],
        'empresas' => [],
    ];

    public static function puede(?User $user, string $modulo): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasRole(...(self::MODULOS[$modulo] ?? []));
    }

    /**
     * Para usar en rutas: ->middleware(Permisos::middleware('ventas'))
     */
    public static function middleware(string $modulo): string
    {
        return 'role:'.implode(',', self::MODULOS[$modulo] ?: [Role::SUPER_ADMIN]);
    }
}
