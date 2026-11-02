<?php

namespace App\Support;

use App\Models\User;

/**
 * Atajos para consultar el motor de autorización desde controladores, rutas y vistas.
 * Los permisos se guardan en la base de datos (ver CatalogoPermisos).
 */
class Permisos
{
    /**
     * ¿El usuario tiene alguno de los permisos? Acepta "a|b" o un array.
     */
    public static function puede(?User $user, string|array $permisos): bool
    {
        return Autorizacion::puedeAlguno($user, static::lista($permisos));
    }

    /**
     * Corta la petición con 403 si el usuario actual no tiene ninguno de los permisos.
     */
    public static function exigir(string|array $permisos): void
    {
        abort_unless(static::puede(auth()->user(), $permisos), 403, 'No tienes permiso para realizar esta acción.');
    }

    /**
     * Para rutas: ->middleware(Permisos::middleware('clients.view|clients.view_assigned'))
     */
    public static function middleware(string|array $permisos): string
    {
        return 'permiso:'.implode('|', static::lista($permisos));
    }

    private static function lista(string|array $permisos): array
    {
        return is_array($permisos) ? $permisos : explode('|', $permisos);
    }
}
