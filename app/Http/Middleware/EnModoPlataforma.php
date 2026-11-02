<?php

namespace App\Http\Middleware;

use App\Support\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Al entrar a las páginas de plataforma se sale de la empresa que se estaba administrando,
 * para que los datos de la plataforma y los de cada empresa nunca se mezclen.
 */
class EnModoPlataforma
{
    public function handle(Request $request, Closure $next): Response
    {
        Tenant::olvidar();

        return $next($request);
    }
}
