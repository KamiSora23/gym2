<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Motor de autorización.
 *
 * Permisos efectivos = permisos del rol + concesiones individuales − restricciones individuales.
 * Las restricciones siempre prevalecen. El super admin tiene todos los permisos.
 * Un usuario bloqueado no tiene ningún permiso.
 *
 * El ámbito de los datos (empresa, sucursal, asignación, propiedad) lo aplican
 * Tenant, los scopes de los modelos y cada controlador.
 */
class Autorizacion
{
    private const CACHE = 'gym.permisos-efectivos';

    public static function puede(?User $user, string $permiso): bool
    {
        if (! $user || $user->estado === 'bloqueado') {
            return false;
        }

        return $user->isSuperAdmin() || static::efectivos($user)->contains($permiso);
    }

    /**
     * ¿Tiene al menos uno de los permisos?
     */
    public static function puedeAlguno(?User $user, array $permisos): bool
    {
        foreach ($permisos as $permiso) {
            if (static::puede($user, $permiso)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Códigos de permiso efectivos del usuario (sin contar el bypass del super admin).
     */
    public static function efectivos(User $user): Collection
    {
        $cache = static::cache();

        return $cache[$user->id] ??= static::calcular($user);
    }

    /**
     * Detalle para la pantalla de permisos: origen de cada permiso.
     *
     * @return array{rol: Collection, concedidos: Collection, restringidos: Collection}
     */
    public static function detalle(User $user): array
    {
        $individuales = DB::table('user_permiso')
            ->join('permisos', 'permisos.id', '=', 'user_permiso.permiso_id')
            ->where('user_permiso.user_id', $user->id)
            ->get(['permisos.codigo', 'user_permiso.tipo']);

        return [
            'rol' => static::delRol($user->role_id),
            'concedidos' => $individuales->where('tipo', 'grant')->pluck('codigo')->values(),
            'restringidos' => $individuales->where('tipo', 'deny')->pluck('codigo')->values(),
        ];
    }

    public static function delRol(?int $roleId): Collection
    {
        if (! $roleId) {
            return collect();
        }

        return DB::table('role_permiso')
            ->join('permisos', 'permisos.id', '=', 'role_permiso.permiso_id')
            ->where('role_permiso.role_id', $roleId)
            ->pluck('permisos.codigo');
    }

    /**
     * Un usuario solo puede conceder (a un rol o a otra persona) permisos que él mismo tiene.
     * Así nadie puede elevar a otro por encima de su propia autoridad.
     */
    public static function puedeConceder(User $otorgante, string $permiso): bool
    {
        return static::puede($otorgante, $permiso);
    }

    public static function olvidar(): void
    {
        app()->forgetInstance(self::CACHE);
    }

    private static function calcular(User $user): Collection
    {
        $detalle = static::detalle($user);

        $base = $detalle['rol']->merge($detalle['concedidos'])->unique();

        // Los permisos amplios incluyen sus versiones reducidas.
        $implicados = $base->flatMap(fn ($p) => CatalogoPermisos::IMPLICA[$p] ?? []);

        return $base->merge($implicados)
            ->unique()
            ->diff($detalle['restringidos'])
            ->values();
    }

    private static function cache(): \ArrayObject
    {
        if (! app()->bound(self::CACHE)) {
            app()->instance(self::CACHE, new \ArrayObject);
        }

        return app(self::CACHE);
    }
}
