<?php

namespace App\Models;

use App\Support\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Identidad visual y datos de contacto de cada empresa.
 */
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
        'id_empresa',
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

    protected static function booted(): void
    {
        static::saved(fn () => app()->forgetInstance('gym.configuraciones'));
        static::deleted(fn () => app()->forgetInstance('gym.configuraciones'));
    }

    /**
     * Configuración de la empresa del contexto actual; para visitantes, la de la empresa principal.
     * Nunca falla aunque la base de datos aún no esté migrada.
     */
    public static function actual(): self
    {
        // Caché por petición (vive en el contenedor de la aplicación).
        if (! app()->bound('gym.configuraciones')) {
            app()->instance('gym.configuraciones', new \ArrayObject);
        }

        $cache = app('gym.configuraciones');
        $clave = 'empresa:'.(Tenant::empresaId() ?? 'publica');

        if (isset($cache[$clave])) {
            return $cache[$clave];
        }

        try {
            if (! Schema::hasTable('configuracions')) {
                return new static(self::VALORES_POR_DEFECTO);
            }

            return $cache[$clave] = static::paraEmpresa(Tenant::empresaId() ?? Empresa::principal()?->id_empresa);
        } catch (Throwable) {
            return new static(self::VALORES_POR_DEFECTO);
        }
    }

    public static function paraEmpresa(?int $empresaId): self
    {
        $config = static::query()->when($empresaId, fn ($q) => $q->where('id_empresa', $empresaId))->first()
            ?? static::query()->whereNull('id_empresa')->first();

        if ($config) {
            return $config;
        }

        $nombre = $empresaId ? Empresa::find($empresaId)?->nombre : null;

        return new static(['nombre_gym' => $nombre ?: self::VALORES_POR_DEFECTO['nombre_gym']] + self::VALORES_POR_DEFECTO);
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
