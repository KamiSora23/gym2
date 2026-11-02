<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'asistencias';

    protected $primaryKey = 'id_asistencia';

    protected $fillable = ['id_empresa', 'id_sucursal', 'id_cliente', 'fecha', 'hora_entrada', 'hora_salida'];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
