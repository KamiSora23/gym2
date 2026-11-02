<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Configuracion extends Model
{
    public const DIAS = [
        'lunes' => 'Lunes',
        'martes' => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves' => 'Jueves',
        'viernes' => 'Viernes',
        'sabado' => 'Sábado',
        'domingo' => 'Domingo',
    ];

    public const VALORES_POR_DEFECTO = [
        'nombre_gym' => 'GYM IMPULSO',
        'color_primario' => '#ff4d4f',
        'color_sidebar' => '#111827',
        'color_secundario' => '#ffffff',
    ];

    protected $fillable = [
        'nombre_gym',
        'slogan',
        'logo',
        'favicon',
        'color_primario',
        'color_sidebar',
        'color_secundario',
        'telefono',
        'email',
        'direccion',
        'facebook',
        'instagram',
        'tiktok',
        'imagen_login',
        'horario_lunes',
        'horario_martes',
        'horario_miercoles',
        'horario_jueves',
        'horario_viernes',
        'horario_sabado',
        'horario_domingo',
    ];

    /**
     * Devuelve la configuración guardada o una con valores por defecto.
     * Nunca falla aunque la base de datos aún no esté migrada.
     */
    public static function actual(): self
    {
        return once(function () {
            try {
                if (Schema::hasTable((new static)->getTable())) {
                    return static::query()->first() ?? new static(self::VALORES_POR_DEFECTO);
                }
            } catch (Throwable) {
                // Base de datos no disponible: se usan los valores por defecto.
            }

            return new static(self::VALORES_POR_DEFECTO);
        });
    }

    public function getLogoUrlAttribute(): string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : asset('img/logo.png');
    }

    public function getFaviconUrlAttribute(): string
    {
        return $this->favicon ? Storage::disk('public')->url($this->favicon) : asset('favicon.ico');
    }

    /**
     * Primera palabra del nombre (se pinta con el color principal) y el resto.
     *
     * @return array{0: string, 1: string}
     */
    public function nombrePartido(): array
    {
        $partes = explode(' ', trim($this->nombre_gym ?: self::VALORES_POR_DEFECTO['nombre_gym']), 2);

        return [$partes[0], $partes[1] ?? ''];
    }
}
