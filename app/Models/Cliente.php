<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        'codigo_acceso',
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

    /**
     * Código del pase QR del socio. Se genera la primera vez que se necesita.
     */
    public function codigoAcceso(): string
    {
        if (! $this->codigo_acceso) {
            $this->regenerarCodigoAcceso();
        }

        return $this->codigo_acceso;
    }

    /**
     * Nuevo código (si el socio perdió o compartió su pase): el anterior deja de funcionar.
     */
    public function regenerarCodigoAcceso(): void
    {
        do {
            $codigo = 'GYM'.strtoupper(Str::random(13));
        } while (static::withoutGlobalScopes()->where('codigo_acceso', $codigo)->exists());

        $this->forceFill(['codigo_acceso' => $codigo])->save();
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
        // Las condiciones van dentro de ofMany para que elija la mayor fecha_fin entre las que corren hoy.
        return $this->hasOne(Membresia::class, 'id_cliente', 'id_cliente')
            ->ofMany(['fecha_fin' => 'max'], fn ($q) => $q->where('estado', 'activo')
                ->whereDate('fecha_inicio', '<=', today())
                ->whereDate('fecha_fin', '>=', today()));
    }

    /**
     * Renovación ya pagada que empieza cuando termine la membresía actual.
     */
    public function proximaMembresia()
    {
        return $this->hasOne(Membresia::class, 'id_cliente', 'id_cliente')
            ->ofMany(['fecha_inicio' => 'min'], fn ($q) => $q->where('estado', 'activo')
                ->whereDate('fecha_inicio', '>', today()));
    }

    /**
     * Situación del socio frente a los planes: hasta cuándo tiene cubierto y si hay una membresía por pagar.
     *
     * @return array{plan: ?int, hasta: ?Carbon, pendiente: ?Venta}
     */
    public function situacionPlanes(): array
    {
        $hasta = $this->membresias()->where('estado', 'activo')->whereDate('fecha_fin', '>=', today())->max('fecha_fin');

        return [
            'plan' => $this->membresiaActiva?->id_plan,
            'hasta' => $hasta ? Carbon::parse($hasta) : null,
            'pendiente' => Venta::withoutGlobalScopes()->where('id_cliente', $this->id_cliente)->where('estado', 'pendiente')
                ->whereHas('detalles', fn ($q) => $q->whereNotNull('id_plan'))->latest('id_venta')->first(),
        ];
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
