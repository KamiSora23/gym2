<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;

/**
 * Entrenador o coach del gimnasio.
 */
class Entrenador extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'entrenadores';

    protected $primaryKey = 'id_entrenador';

    protected $fillable = ['id_empresa', 'id_sucursal', 'user_id', 'nombre', 'especialidad', 'telefono', 'email', 'activo', 'bio'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function clases()
    {
        return $this->hasMany(Clase::class, 'id_entrenador', 'id_entrenador');
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_entrenador', 'id_entrenador');
    }

    public function disponibilidad()
    {
        return $this->hasMany(Disponibilidad::class, 'id_entrenador', 'id_entrenador')
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio');
    }

    /**
     * El cliente es su alumno si lo entrena en persona o está inscrito en una de sus clases.
     */
    public function esAlumno(Cliente $cliente): bool
    {
        return (int) $cliente->id_entrenador === $this->id_entrenador
            || $cliente->clases()->where('clases.id_entrenador', $this->id_entrenador)->exists();
    }

    /**
     * Si el coach definió disponibilidad, la franja debe caber dentro de alguna de ellas.
     * Sin disponibilidad registrada se considera disponible siempre.
     */
    public function disponibleEn(int $dia, string $inicio, string $fin): bool
    {
        $franjas = $this->disponibilidad()->get();

        if ($franjas->isEmpty()) {
            return true;
        }

        return $franjas->contains(fn ($f) => $f->dia_semana === $dia
            && substr($f->hora_inicio, 0, 5) <= $inicio
            && substr($f->hora_fin, 0, 5) >= $fin);
    }
}
