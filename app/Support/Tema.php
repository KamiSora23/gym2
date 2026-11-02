<?php

namespace App\Support;

use App\Models\Configuracion;
use App\Models\Sucursal;

/**
 * Convierte los colores configurados en variables CSS, calculando tonos derivados
 * (suave, oscuro) y el color de texto legible sobre cada fondo.
 */
class Tema
{
    /**
     * Temas predefinidos que el administrador puede aplicar con un clic.
     */
    public const PRESETS = [
        'energia' => ['nombre' => 'Energía', 'color_primario' => '#ff4d4f', 'color_sidebar' => '#111827', 'color_secundario' => '#f59e0b', 'color_fondo' => '#f5f7fb'],
        'oceano' => ['nombre' => 'Océano', 'color_primario' => '#2563eb', 'color_sidebar' => '#0f172a', 'color_secundario' => '#06b6d4', 'color_fondo' => '#f1f5f9'],
        'fit' => ['nombre' => 'Fit verde', 'color_primario' => '#16a34a', 'color_sidebar' => '#052e16', 'color_secundario' => '#84cc16', 'color_fondo' => '#f3f7f4'],
        'power' => ['nombre' => 'Power morado', 'color_primario' => '#7c3aed', 'color_sidebar' => '#1e1b4b', 'color_secundario' => '#ec4899', 'color_fondo' => '#f6f5fb'],
        'fuego' => ['nombre' => 'Fuego', 'color_primario' => '#ea580c', 'color_sidebar' => '#1c1917', 'color_secundario' => '#facc15', 'color_fondo' => '#faf7f5'],
        'oro' => ['nombre' => 'Negro y oro', 'color_primario' => '#ca8a04', 'color_sidebar' => '#0a0a0a', 'color_secundario' => '#a3a3a3', 'color_fondo' => '#f7f7f5'],
        'claro' => ['nombre' => 'Claro minimal', 'color_primario' => '#0f766e', 'color_sidebar' => '#ffffff', 'color_secundario' => '#6366f1', 'color_fondo' => '#f8fafc'],
        'rosa' => ['nombre' => 'Rosa', 'color_primario' => '#db2777', 'color_sidebar' => '#281421', 'color_secundario' => '#f97316', 'color_fondo' => '#fbf6f8'],
    ];

    /**
     * Variables CSS del panel administrativo. Los colores de la sucursal (si tiene) tienen prioridad.
     */
    public static function panel(Configuracion $config, ?Sucursal $sucursal = null): string
    {
        $primario = static::hex($sucursal?->color_primario) ?? static::hex($config->color_primario) ?? '#ff4d4f';
        $sidebar = static::hex($sucursal?->color_sidebar) ?? static::hex($config->color_sidebar) ?? '#111827';
        $acento = static::hex($config->color_secundario);
        $fondo = static::hex($config->color_fondo) ?? '#f5f7fb';

        // El color secundario por defecto era blanco: en ese caso se usa un acento derivado.
        if (! $acento || static::luminancia($acento) > 0.9) {
            $acento = static::mezclar($primario, '#f59e0b', 0.5);
        }

        $sidebarOscuro = static::luminancia($sidebar) < 0.45;

        return static::variables([
            '--primary-color' => $primario,
            '--primary-text' => static::textoSobre($primario),
            '--primary-dark' => static::mezclar($primario, '#000000', 0.18),
            '--sidebar-color' => $sidebar,
            '--sidebar-text' => $sidebarOscuro ? '#cbd5e1' : '#475569',
            '--sidebar-strong' => $sidebarOscuro ? '#ffffff' : '#0f172a',
            '--sidebar-muted' => $sidebarOscuro ? '#64748b' : '#94a3b8',
            '--sidebar-hover' => $sidebarOscuro ? 'rgba(255,255,255,.07)' : 'rgba(15,23,42,.05)',
            '--sidebar-line' => $sidebarOscuro ? 'rgba(255,255,255,.08)' : '#e2e8f0',
            '--secondary-color' => $acento,
            '--bg-color' => $fondo,
        ]);
    }

    /**
     * Variables CSS del sitio público (usa los nombres de public/css/estilos.css).
     */
    public static function web(Configuracion $config): string
    {
        $usaPanel = (bool) $config->web_usa_colores_panel;
        $primario = static::hex($usaPanel ? $config->color_primario : $config->web_color_primario) ?? '#ffc107';
        $oscuro = static::hex($usaPanel ? $config->color_sidebar : $config->web_color_oscuro) ?? '#0f172a';

        if (static::luminancia($oscuro) > 0.45) {
            $oscuro = '#0f172a';
        }

        return static::variables([
            '--primary' => $primario,
            '--primary-glow' => static::rgba($primario, 0.4),
            '--primary-text' => static::textoSobre($primario),
            '--primary-hover' => static::mezclar($primario, '#000000', 0.1),
            '--dark' => $oscuro,
            '--dark-light' => static::mezclar($oscuro, '#ffffff', 0.1),
        ]);
    }

    public static function hex(?string $color): ?string
    {
        return $color && preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? strtolower($color) : null;
    }

    /**
     * Luminancia relativa (0 = negro, 1 = blanco).
     */
    public static function luminancia(string $hex): float
    {
        [$r, $g, $b] = array_map(function ($c) {
            $c /= 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, static::rgb($hex));

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Blanco o casi negro, el que tenga mejor contraste sobre el fondo.
     */
    public static function textoSobre(string $hex): string
    {
        $l = static::luminancia($hex);

        return (1.05 / ($l + 0.05)) >= (($l + 0.05) / 0.0625) ? '#ffffff' : '#111827';
    }

    public static function mezclar(string $a, string $b, float $peso): string
    {
        $ca = static::rgb($a);
        $cb = static::rgb($b);

        return sprintf('#%02x%02x%02x', ...array_map(fn ($i) => (int) round($ca[$i] * (1 - $peso) + $cb[$i] * $peso), [0, 1, 2]));
    }

    private static function rgba(string $hex, float $alpha): string
    {
        return 'rgba('.implode(', ', static::rgb($hex)).", {$alpha})";
    }

    /** @return array{0:int,1:int,2:int} */
    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private static function variables(array $vars): string
    {
        return collect($vars)->map(fn ($v, $k) => "{$k}: {$v};")->implode(' ');
    }
}
