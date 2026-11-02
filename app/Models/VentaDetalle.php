<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    protected $table = 'venta_detalle';

    protected $primaryKey = 'id_venta_detalle';

    protected $fillable = ['id_venta', 'id_producto', 'id_plan', 'descripcion', 'cantidad', 'precio_unitario', 'subtotal', 'descuento'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }
}
