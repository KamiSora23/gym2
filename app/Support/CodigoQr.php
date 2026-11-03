<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Genera códigos QR como SVG (se ven nítidos en pantalla y al imprimir, sin depender de internet).
 */
class CodigoQr
{
    public static function svg(string $texto, int $tamano = 220): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($tamano, 1), new SvgImageBackEnd));

        // Se quita la declaración XML para poder insertarlo directamente en el HTML.
        return trim(preg_replace('/^<\?xml[^>]*\?>/', '', $writer->writeString($texto)));
    }
}
