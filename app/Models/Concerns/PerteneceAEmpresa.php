<?php

namespace App\Models\Concerns;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Support\Tenant;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filtra automáticamente los registros por la empresa del contexto actual
 * y la asigna al crear registros nuevos.
 */
trait PerteneceAEmpresa
{
    public static function bootPerteneceAEmpresa(): void
    {
        static::addGlobalScope('empresa', function (Builder $query) {
            if ($empresaId = Tenant::empresaId()) {
                $query->where($query->getModel()->getTable().'.id_empresa', $empresaId);
            }
        });

        static::creating(function ($model) {
            $model->id_empresa ??= Tenant::empresaId();
        });
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }

    /**
     * Filtra por la sucursal seleccionada (si hay una).
     */
    public function scopeDeSucursal(Builder $query): Builder
    {
        $sucursalId = Tenant::sucursalId();

        return $query->when($sucursalId, fn ($q) => $q->where($this->getTable().'.id_sucursal', $sucursalId));
    }
}
