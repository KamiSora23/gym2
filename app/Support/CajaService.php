<?php

namespace App\Support;

use App\Models\Auditoria;
use App\Models\CajaMovimiento;
use App\Models\CajaSesion;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Validation\ValidationException;

/**
 * Operaciones de caja: todo cobro o devolución queda en la sesión de caja del empleado.
 */
class CajaService
{
    public static function sesionAbierta(?User $user): ?CajaSesion
    {
        if (! $user) {
            return null;
        }

        return CajaSesion::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('estado', 'abierta')
            ->first();
    }

    /**
     * Devuelve la sesión abierta del usuario o corta la operación con un mensaje claro.
     */
    public static function exigirSesion(User $user, string $campo = 'caja'): CajaSesion
    {
        return static::sesionAbierta($user) ?? throw ValidationException::withMessages([
            $campo => 'Primero abre tu caja: todo cobro debe quedar registrado en una sesión de caja.',
        ]);
    }

    public static function ingreso(CajaSesion $sesion, float $monto, string $metodo, string $origen, string $concepto, ?Venta $venta = null): CajaMovimiento
    {
        return static::movimiento($sesion, 'ingreso', $monto, $metodo, $origen, $concepto, $venta);
    }

    public static function egreso(CajaSesion $sesion, float $monto, string $metodo, string $origen, string $concepto, ?Venta $venta = null): CajaMovimiento
    {
        return static::movimiento($sesion, 'egreso', $monto, $metodo, $origen, $concepto, $venta);
    }

    private static function movimiento(CajaSesion $sesion, string $tipo, float $monto, string $metodo, string $origen, string $concepto, ?Venta $venta): CajaMovimiento
    {
        $movimiento = CajaMovimiento::create([
            'id_sesion' => $sesion->id_sesion,
            'id_empresa' => $sesion->id_empresa,
            'user_id' => auth()->id() ?? $sesion->user_id,
            'tipo' => $tipo,
            'origen' => $origen,
            'metodo_pago' => $metodo,
            'monto' => round($monto, 2),
            'concepto' => $concepto,
            'id_venta' => $venta?->id_venta,
        ]);

        if ($origen === 'manual') {
            Auditoria::registrar('caja.movimiento', ucfirst($tipo).' manual de '.dinero($monto).": {$concepto}", $sesion);
        }

        return $movimiento;
    }
}
