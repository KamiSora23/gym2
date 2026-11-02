<?php

namespace App\Support;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Contexto de trabajo actual: empresa y sucursal.
 *
 * - Super admin: elige la empresa (y sucursal) desde el selector; se guarda en sesión.
 * - Admin: su empresa es fija; puede elegir sucursal o ver "todas".
 * - Resto del personal: empresa y sucursal fijas según su usuario.
 */
class Tenant
{
    public static function empresaId(): ?int
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        if ($user->isSuperAdmin()) {
            $id = session('empresa_id');

            return $id ? (int) $id : null;
        }

        return $user->id_empresa;
    }

    public static function sucursalId(): ?int
    {
        $user = auth()->user();

        if (! $user || ! static::empresaId()) {
            return null;
        }

        if (static::puedeCambiarSucursal($user)) {
            $id = session('sucursal_id');

            return $id ? (int) $id : null;
        }

        return $user->id_sucursal;
    }

    /**
     * Puede trabajar con todas las sucursales (y elegir una) si tiene el permiso
     * y no está asignado a una sucursal concreta. El administrador siempre puede.
     */
    public static function puedeCambiarSucursal(User $user): bool
    {
        return $user->isAdmin() || (! $user->id_sucursal && Permisos::puede($user, 'branches.switch'));
    }

    public static function empresa(): ?Empresa
    {
        $id = static::empresaId();

        return $id ? Empresa::find($id) : null;
    }

    public static function sucursal(): ?Sucursal
    {
        $id = static::sucursalId();

        return $id ? Sucursal::find($id) : null;
    }

    /**
     * Sucursales activas de la empresa actual.
     */
    public static function sucursales(): Collection
    {
        $id = static::empresaId();

        return $id
            ? Sucursal::where('id_empresa', $id)->where('activo', true)->orderBy('nombre')->get()
            : collect();
    }

    /**
     * Sucursal a usar para un registro nuevo: la del contexto, la enviada en el formulario
     * (si pertenece a la empresa) o la primera de la empresa.
     */
    public static function sucursalParaRegistro(?int $solicitada = null): ?int
    {
        if ($actual = static::sucursalId()) {
            return $actual;
        }

        $sucursales = static::sucursales();

        if ($solicitada && $sucursales->contains('id_sucursal', $solicitada)) {
            return $solicitada;
        }

        return $sucursales->first()?->id_sucursal;
    }

    /**
     * Si el usuario trabaja en una sucursal concreta, el registro debe ser de esa sucursal.
     * Evita acceder por URL a ventas o cajas de otra sede.
     */
    public static function exigirSucursal(?int $sucursalDelRegistro): void
    {
        $actual = static::sucursalId();

        abort_if($actual && $sucursalDelRegistro && $actual !== (int) $sucursalDelRegistro, 403, 'Este registro pertenece a otra sucursal.');
    }

    public static function olvidar(): void
    {
        session()->forget(['empresa_id', 'sucursal_id']);
    }
}
