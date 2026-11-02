<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Franja horaria semanal en la que un coach está disponible.
 */
class Disponibilidad extends Model
{
    protected $table = 'entrenador_disponibilidad';

    protected $fillable = ['id_entrenador', 'dia_semana', 'hora_inicio', 'hora_fin'];

    protected function casts(): array
    {
        return ['dia_semana' => 'integer'];
    }

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
