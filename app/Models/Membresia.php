<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membresia extends Model
{
    protected $table = 'membresias';

    protected $primaryKey = 'id_membresia';

    protected $fillable = ['id_cliente', 'id_plan', 'id_venta', 'fecha_inicio', 'fecha_fin', 'estado'];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }
}
