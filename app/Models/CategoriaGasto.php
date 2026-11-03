<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use App\Support\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Categorías de gastos de cada empresa (renta, salarios, servicios…). Se pueden renombrar y desactivar.
 */
class CategoriaGasto extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'categorias_gasto';

    protected $primaryKey = 'id_categoria';

    protected $fillable = ['id_empresa', 'nombre', 'activo'];

    public const POR_DEFECTO = ['Renta', 'Salarios', 'Servicios', 'Insumos', 'Mantenimiento', 'Marketing', 'Otros'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /**
     * Categorías de la empresa actual; la primera vez se crean las de por defecto.
     */
    public static function deLaEmpresa(): Collection
    {
        if (Tenant::empresaId() && ! static::query()->exists()) {
            foreach (self::POR_DEFECTO as $nombre) {
                static::create(['nombre' => $nombre]);
            }
        }

        return static::query()->orderByRaw("nombre = 'Otros'")->orderBy('nombre')->get();
    }

    public function gastos()
    {
        return Gasto::where('categoria', $this->nombre);
    }
}
