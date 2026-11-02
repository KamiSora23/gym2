<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

/**
 * Entrenador o coach del gimnasio.
 */
class Entrenador extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'entrenadores';

    protected $primaryKey = 'id_entrenador';

    protected $fillable = ['id_empresa', 'id_sucursal', 'user_id', 'nombre', 'especialidad', 'telefono', 'email', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function clases()
    {
        return $this->hasMany(Clase::class, 'id_entrenador', 'id_entrenador');
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_entrenador', 'id_entrenador');
    }
}
