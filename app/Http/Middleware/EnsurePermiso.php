<?php

namespace App\Http\Middleware;

use App\Support\Permisos;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige al menos uno de los permisos indicados.
 * Uso: ->middleware('permiso:clients.view|clients.view_assigned')
 */
class EnsurePermiso
{
    public function handle(Request $request, Closure $next, string $permisos): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (Permisos::puede($user, $permisos)) {
            return $next($request);
        }

        if ($user->isCliente()) {
            return redirect('/')->with('error', 'No tienes permiso para acceder al panel administrativo.');
        }

        abort(403, 'No tienes permiso para acceder a esta sección.');
    }
}
