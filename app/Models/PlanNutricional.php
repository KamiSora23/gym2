<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

class PlanNutricional extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'planes_nutricionales';

    protected $primaryKey = 'id_plan_nutricional';

    protected $fillable = ['id_empresa', 'id_cliente', 'user_id', 'objetivo', 'calorias', 'proteinas', 'carbohidratos', 'grasas', 'indicaciones', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function nutricionista()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
