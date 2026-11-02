<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Solo personal del gimnasio (cualquier rol que no sea cliente) entra al panel.
 */
class EnsurePersonal
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isStaff()) {
            return redirect('/')->with('error', 'No tienes permiso para acceder al panel administrativo.');
        }

        return $next($request);
    }
}
