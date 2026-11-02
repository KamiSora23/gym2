<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresa';

    protected $primaryKey = 'id_empresa';

    protected $fillable = ['ruc', 'nombre', 'direccion', 'email', 'telefono', 'horario', 'ubicacion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'id_empresa', 'id_empresa');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_empresa', 'id_empresa');
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_empresa', 'id_empresa');
    }

    public function configuracion()
    {
        return $this->hasOne(Configuracion::class, 'id_empresa', 'id_empresa');
    }

    /**
     * Empresa cuyo sitio público se muestra a los visitantes.
     */
    public static function principal(): ?self
    {
        return static::where('activo', true)->orderBy('id_empresa')->first();
    }
}
