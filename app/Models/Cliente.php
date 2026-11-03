<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cliente extends Model
{
    use PerteneceAEmpresa;

    protected $table = 'clientes';

    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'id_empresa',
        'dni',
        'nombre',
        'telefono',
        'correo',
        'activo',
        'user_id',
        'id_entrenador',
        'id_nutricionista',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /**
     * Un cliente con ventas, membresías o asistencias no se elimina: se desactiva.
     */
    public function tieneHistorial(): bool
    {
        return $this->ventas()->exists()
            || $this->membresias()->exists()
            || DB::table('asistencias')->where('id_cliente', $this->id_cliente)->exists();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entrenador()
    {
        return $this->belongsTo(Entrenador::class, 'id_entrenador', 'id_entrenador');
    }

    public function nutricionista()
    {
        return $this->belongsTo(User::class, 'id_nutricionista');
    }

    public function planesNutricionales()
    {
        return $this->hasMany(PlanNutricional::class, 'id_cliente', 'id_cliente')->latest();
    }

    public function membresias()
    {
        return $this->hasMany(Membresia::class, 'id_cliente', 'id_cliente');
    }

    public function membresiaActiva()
    {
        return $this->hasOne(Membresia::class, 'id_cliente', 'id_cliente')
            ->where('estado', 'activo')
            ->whereDate('fecha_fin', '>=', today())
            ->latestOfMany('fecha_fin');
    }

    public function clases()
    {
        return $this->belongsToMany(Clase::class, 'clase_cliente', 'id_cliente', 'id_clase')->withTimestamps();
    }

    public function rutinas()
    {
        return $this->hasMany(Rutina::class, 'id_cliente', 'id_cliente')->latest();
    }

    public function progresos()
    {
        return $this->hasMany(Progreso::class, 'id_cliente', 'id_cliente')->orderByDesc('fecha');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_cliente', 'id_cliente');
    }
}
