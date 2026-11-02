<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Movimiento de caja. Es inmutable: no se edita ni se borra.
 */
class CajaMovimiento extends Model
{
    protected $table = 'caja_movimientos';

    protected $primaryKey = 'id_movimiento';

    protected $fillable = ['id_sesion', 'id_empresa', 'user_id', 'tipo', 'origen', 'metodo_pago', 'monto', 'concepto', 'id_venta'];

    protected function casts(): array
    {
        return ['monto' => 'decimal:2'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Los movimientos de caja no se pueden modificar.'));
        static::deleting(fn () => throw new LogicException('Los movimientos de caja no se pueden eliminar.'));
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
