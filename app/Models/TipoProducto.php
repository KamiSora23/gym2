<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

class TipoProducto extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'tipo_producto';

    protected $primaryKey = 'id_tipo';

    protected $fillable = ['id_empresa', 'nombre'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_tipo', 'id_tipo');
    }
}
