<?php

if (! function_exists('dinero')) {
    /**
     * Formatea un monto como moneda: $1,234.50
     */
    function dinero(float|int|string|null $monto): string
    {
        $monto = (float) $monto;

        return ($monto < 0 ? '-$' : '$').number_format(abs($monto), 2);
    }
}
