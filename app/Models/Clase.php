<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

class Clase extends Model
{
    use PerteneceAEmpresa;

    public const DIAS = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];

    protected $table = 'clases';

    protected $primaryKey = 'id_clase';

    protected $fillable = ['id_empresa', 'id_sucursal', 'id_entrenador', 'nombre', 'dia_semana', 'hora_inicio', 'hora_fin', 'cupo'];

    protected function casts(): array
    {
        return ['dia_semana' => 'integer', 'cupo' => 'integer'];
    }

    public function entrenador()
    {
        return $this->belongsTo(Entrenador::class, 'id_entrenador', 'id_entrenador');
    }

    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'clase_cliente', 'id_clase', 'id_cliente')->withTimestamps();
    }

    /**
     * Las horas se guardan siempre como HH:MM:SS para compararlas igual en cualquier base de datos.
     */
    public function setHoraInicioAttribute(?string $valor): void
    {
        $this->attributes['hora_inicio'] = $valor ? substr($valor, 0, 5).':00' : null;
    }

    public function setHoraFinAttribute(?string $valor): void
    {
        $this->attributes['hora_fin'] = $valor ? substr($valor, 0, 5).':00' : null;
    }

    public function getHorarioAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5).' - '.substr($this->hora_fin, 0, 5);
    }
}
