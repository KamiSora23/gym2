<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stock de un producto en una sucursal (una fila por producto y sucursal).
 */
class Inventario extends Model
{
    protected $table = 'inventario';

    protected $primaryKey = 'id_inventario';

    protected $fillable = ['id_producto', 'id_sucursal', 'cantidad', 'precio_compra'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }
}
