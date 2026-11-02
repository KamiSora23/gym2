<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

/**
 * Medición del progreso físico de un cliente.
 */
class Progreso extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'progresos';

    protected $primaryKey = 'id_progreso';

    protected $fillable = ['id_empresa', 'id_cliente', 'id_entrenador', 'fecha', 'peso', 'grasa', 'cintura', 'notas'];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function entrenador()
    {
        return $this->belongsTo(Entrenador::class, 'id_entrenador', 'id_entrenador');
    }
}
