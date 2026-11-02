<?php

namespace App\Models;

use App\Support\Tenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Registro de actividades importantes (quién hizo qué, cuándo y desde dónde).
 */
class Auditoria extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'auditoria';

    protected $fillable = ['id_empresa', 'user_id', 'accion', 'descripcion', 'entidad', 'entidad_id', 'datos', 'ip'];

    protected function casts(): array
    {
        return ['datos' => 'array', 'created_at' => 'datetime'];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    /**
     * Registra una actividad. La empresa es la del contexto, o la del modelo afectado.
     */
    public static function registrar(string $accion, string $descripcion, ?Model $modelo = null, array $datos = [], ?int $empresaId = null): self
    {
        return static::create([
            'id_empresa' => $empresaId ?? $modelo?->getAttribute('id_empresa') ?? Tenant::empresaId() ?? auth()->user()?->id_empresa,
            'user_id' => auth()->id(),
            'accion' => $accion,
            'descripcion' => $descripcion,
            'entidad' => $modelo ? class_basename($modelo) : null,
            'entidad_id' => $modelo?->getKey(),
            'datos' => $datos ?: null,
            'ip' => request()?->ip(),
        ]);
    }
}
