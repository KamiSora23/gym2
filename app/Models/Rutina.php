<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

class Rutina extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'rutinas';

    protected $primaryKey = 'id_rutina';

    protected $fillable = ['id_empresa', 'id_cliente', 'id_entrenador', 'nombre', 'objetivo', 'notas', 'activa'];

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function entrenador()
    {
        return $this->belongsTo(Entrenador::class, 'id_entrenador', 'id_entrenador');
    }

    public function ejercicios()
    {
        return $this->hasMany(RutinaEjercicio::class, 'id_rutina', 'id_rutina')->orderBy('orden');
    }
}
