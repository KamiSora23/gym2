<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'planes';

    protected $primaryKey = 'id_plan';

    protected $fillable = [
        'id_empresa',
        'nombre',
        'descripcion',
        'foto',
        'precio',
        'duracion_dias',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function membresias()
    {
        return $this->hasMany(Membresia::class, 'id_plan', 'id_plan');
    }
}
