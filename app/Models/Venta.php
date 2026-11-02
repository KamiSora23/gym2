<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use PerteneceAEmpresa;

    public const METODOS_PAGO = [
        'efectivo' => 'Efectivo',
        'tarjeta' => 'Tarjeta',
        'transferencia' => 'Transferencia',
        'yape' => 'Yape / Plin',
    ];

    protected $table = 'ventas';

    protected $primaryKey = 'id_venta';

    protected $fillable = ['id_empresa', 'id_sucursal', 'id_cliente', 'user_id', 'fecha', 'total', 'estado', 'metodo_pago'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class, 'id_venta', 'id_venta');
    }

    public function ingreso()
    {
        return $this->hasOne(Ingreso::class, 'id_venta', 'id_venta');
    }

    public function membresias()
    {
        return $this->hasMany(Membresia::class, 'id_venta', 'id_venta');
    }

    public function getFolioAttribute(): string
    {
        return '#'.str_pad((string) $this->id_venta, 5, '0', STR_PAD_LEFT);
    }
}
