<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

/**
 * Turno de un empleado en una caja: apertura, movimientos, arqueo y cierre.
 */
class CajaSesion extends Model
{
    use PerteneceAEmpresa;

    public const ESTADOS = [
        'abierta' => ['Abierta', 'status-info'],
        'cuadrada' => ['Cerrada sin diferencias', 'status-active'],
        'observada' => ['Con diferencia por revisar', 'status-pending'],
        'aprobada' => ['Diferencia aprobada', 'status-active'],
    ];

    protected $table = 'caja_sesiones';

    protected $primaryKey = 'id_sesion';

    protected $fillable = [
        'id_empresa', 'id_sucursal', 'id_caja', 'user_id', 'monto_inicial', 'abierta_en', 'cerrada_en',
        'esperado', 'contado', 'diferencia', 'estado', 'observaciones', 'revisado_por', 'revisado_en', 'nota_revision',
    ];

    protected function casts(): array
    {
        return [
            'abierta_en' => 'datetime',
            'cerrada_en' => 'datetime',
            'revisado_en' => 'datetime',
            'esperado' => 'array',
            'contado' => 'array',
            'monto_inicial' => 'decimal:2',
            'diferencia' => 'decimal:2',
        ];
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja', 'id_caja');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class, 'id_sesion', 'id_sesion')->orderBy('id_movimiento');
    }

    public function estaAbierta(): bool
    {
        return $this->estado === 'abierta';
    }

    /**
     * Saldo esperado por método de pago: monto inicial (en efectivo) + ingresos − egresos.
     *
     * @return array<string, float>
     */
    public function saldoEsperado(): array
    {
        $saldo = array_fill_keys(array_keys(Venta::METODOS_PAGO), 0.0);
        $saldo['efectivo'] = (float) $this->monto_inicial;

        foreach ($this->movimientos as $m) {
            $saldo[$m->metodo_pago] = ($saldo[$m->metodo_pago] ?? 0) + ($m->tipo === 'ingreso' ? 1 : -1) * (float) $m->monto;
        }

        return array_map(fn ($v) => round($v, 2), $saldo);
    }
}
