<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'planes';

    protected $primaryKey = 'id_plan';

    protected $fillable = [
        'nombre',
        'descripcion',
        'foto',
        'precio',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
        ];
    }
}
