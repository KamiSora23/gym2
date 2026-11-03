<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'gastos';

    protected $primaryKey = 'id_gasto';

    protected $fillable = ['id_empresa', 'id_sucursal', 'concepto', 'categoria', 'monto', 'fecha', 'user_id'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
