<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use App\Support\Tenant;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    protected $fillable = ['id_empresa', 'id_tipo', 'nombre', 'descripcion', 'imagen', 'precio', 'activo', 'sku', 'precio_compra', 'stock_minimo'];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'precio_compra' => 'decimal:2',
            'stock_minimo' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen ? asset('storage/'.$this->imagen) : null;
    }

    public function tipo()
    {
        return $this->belongsTo(TipoProducto::class, 'id_tipo', 'id_tipo');
    }

    public function movimientos()
    {
        return $this->hasMany(InventarioMovimiento::class, 'id_producto', 'id_producto')->latest('id');
    }

    public function inventario()
    {
        return $this->hasMany(Inventario::class, 'id_producto', 'id_producto');
    }

    /**
     * Stock en una sucursal concreta o, si no se indica, en la sucursal del contexto
     * (o en todas si no hay ninguna seleccionada).
     */
    public function stockEn(?int $sucursalId = null): int
    {
        $sucursalId ??= Tenant::sucursalId();

        return (int) $this->inventario
            ->when($sucursalId, fn ($items) => $items->where('id_sucursal', $sucursalId))
            ->sum('cantidad');
    }
}
