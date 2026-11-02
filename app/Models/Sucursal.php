<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'sucursales';

    protected $primaryKey = 'id_sucursal';

    protected $fillable = ['id_empresa', 'nombre', 'direccion', 'telefono', 'activo', 'color_primario', 'color_sidebar'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Cada sucursal nueva nace con su caja principal.
        static::created(fn (self $sucursal) => Caja::create([
            'id_empresa' => $sucursal->id_empresa,
            'id_sucursal' => $sucursal->id_sucursal,
            'nombre' => 'Caja principal',
            'activo' => true,
        ]));
    }

    public function cajas()
    {
        return $this->hasMany(Caja::class, 'id_sucursal', 'id_sucursal');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_sucursal', 'id_sucursal');
    }
}
