<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Permiso del catálogo global, por ejemplo "sales.cancel".
 */
class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = ['codigo', 'modulo', 'descripcion', 'grupo'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permiso', 'permiso_id', 'role_id');
    }
}
