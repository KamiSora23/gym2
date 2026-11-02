<?php

namespace App\Support;

use App\Models\Inventario as Stock;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Validation\ValidationException;

/**
 * Único punto para cambiar el stock: actualiza la existencia y registra el movimiento (kardex).
 */
class Inventario
{
    /**
     * @param  int  $cantidad  positiva = entra, negativa = sale
     */
    public static function mover(Producto $producto, int $sucursalId, int $cantidad, string $tipo, ?string $motivo = null, ?Venta $venta = null): InventarioMovimiento
    {
        $fila = Stock::where('id_producto', $producto->id_producto)
            ->where('id_sucursal', $sucursalId)
            ->lockForUpdate()
            ->first()
            ?? Stock::create(['id_producto' => $producto->id_producto, 'id_sucursal' => $sucursalId, 'cantidad' => 0, 'precio_compra' => $producto->precio_compra ?? 0]);

        if ($fila->cantidad + $cantidad < 0) {
            throw ValidationException::withMessages([
                'items' => "Stock insuficiente de {$producto->nombre} (disponible: {$fila->cantidad}).",
            ]);
        }

        $fila->increment('cantidad', $cantidad);

        return InventarioMovimiento::create([
            'id_empresa' => $producto->id_empresa,
            'id_sucursal' => $sucursalId,
            'id_producto' => $producto->id_producto,
            'user_id' => auth()->id(),
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'stock_resultante' => $fila->cantidad,
            'motivo' => $motivo,
            'id_venta' => $venta?->id_venta,
        ]);
    }
}
