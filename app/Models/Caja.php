<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use App\Support\Permisos;
use App\Support\Tenant;
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

    /**
     * Empleados responsables. Si la lista está vacía, cualquiera de la sucursal puede usarla.
     */
    public function responsables()
    {
        return $this->belongsToMany(User::class, 'caja_usuario', 'id_caja', 'user_id');
    }

    /**
     * ¿Puede el usuario abrir esta caja?
     */
    public function puedeUsarla(User $user): bool
    {
        // El personal de una sede solo usa cajas de su sede (salvo quien trabaja con todas).
        if ($user->id_sucursal && ! Tenant::puedeCambiarSucursal($user) && (int) $this->id_sucursal !== (int) $user->id_sucursal) {
            return false;
        }

        if (Permisos::puede($user, 'cash.manage')) {
            return true;
        }

        $responsables = $this->relationLoaded('responsables') ? $this->responsables : $this->responsables()->get();

        return $responsables->isEmpty() || $responsables->contains('id', $user->id);
    }

    public function sesionAbierta()
    {
        return $this->hasOne(CajaSesion::class, 'id_caja', 'id_caja')->where('estado', 'abierta');
    }
}
