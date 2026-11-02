<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

/**
 * Kardex: cada cambio de stock queda registrado con su motivo y responsable.
 */
class InventarioMovimiento extends Model
{
    use PerteneceAEmpresa;

    public const TIPOS = [
        'entrada' => 'Entrada de mercadería',
        'venta' => 'Venta',
        'anulacion' => 'Anulación de venta',
        'ajuste' => 'Ajuste de inventario',
        'merma' => 'Merma o pérdida',
        'salida' => 'Salida',
    ];

    protected $table = 'inventario_movimientos';

    protected $fillable = ['id_empresa', 'id_sucursal', 'id_producto', 'user_id', 'tipo', 'cantidad', 'stock_resultante', 'motivo', 'id_venta'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
