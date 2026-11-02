<?php

namespace App\Support;

use App\Models\Empresa;
use App\Models\Sucursal;
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

        if ($user->isAdmin()) {
            $id = session('sucursal_id');

            return $id ? (int) $id : null;
        }

        return $user->id_sucursal;
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

    public static function olvidar(): void
    {
        session()->forget(['empresa_id', 'sucursal_id']);
    }
}
