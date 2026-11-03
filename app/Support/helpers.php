<?php

use App\Models\Configuracion;

if (! function_exists('moneda')) {
    /**
     * Símbolo de moneda de la empresa actual (por defecto soles).
     */
    function moneda(): string
    {
        return Configuracion::actual()->moneda ?: 'S/';
    }
}

if (! function_exists('dinero')) {
    /**
     * Formatea un monto con la moneda de la empresa: S/ 1,234.50
     */
    function dinero(float|int|string|null $monto): string
    {
        $monto = (float) $monto;

        return ($monto < 0 ? '-' : '').moneda().' '.number_format(abs($monto), 2);
    }
}
