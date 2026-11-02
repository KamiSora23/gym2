<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

/**
 * Caja física de una sucursal. Cada empleado trabaja en ella con su propia sesión.
 */
class Caja extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'cajas';

    protected $primaryKey = 'id_caja';

    protected $fillable = ['id_empresa', 'id_sucursal', 'nombre', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function sesiones()
    {
        return $this->hasMany(CajaSesion::class, 'id_caja', 'id_caja');
    }

    public function sesionAbierta()
    {
        return $this->hasOne(CajaSesion::class, 'id_caja', 'id_caja')->where('estado', 'abierta');
    }
}
