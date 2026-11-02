<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Permite el acceso solo a los roles indicados. El super admin siempre pasa.
     *
     * Uso: ->middleware('role:admin,recepcionista')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin() || $user->hasRole(...$roles)) {
            return $next($request);
        }

        if ($user->isCliente()) {
            return redirect('/')->with('error', 'No tienes permiso para acceder al panel administrativo.');
        }

        abort(403, 'No tienes permiso para realizar esta acción.');
    }
}
