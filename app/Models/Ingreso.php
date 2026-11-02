<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'ingresos';

    protected $primaryKey = 'id_ingreso';

    protected $fillable = ['id_empresa', 'id_sucursal', 'concepto', 'monto', 'fecha', 'user_id', 'id_venta'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
        ];
    }
}
