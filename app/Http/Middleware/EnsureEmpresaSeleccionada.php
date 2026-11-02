<?php

namespace App\Http\Middleware;

use App\Support\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garantiza que haya una empresa activa en el contexto antes de entrar a los módulos operativos.
 */
class EnsureEmpresaSeleccionada
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $empresa = Tenant::empresa();

        if ($user->isSuperAdmin()) {
            if (! $empresa) {
                Tenant::olvidar();

                return redirect()->route('empresas.index')
                    ->with('info', 'Selecciona una empresa para administrarla.');
            }

            return $next($request);
        }

        if (! $empresa || ! $empresa->activo) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', $empresa ? 'Tu empresa está desactivada. Contacta al administrador.' : 'Tu usuario no está asignado a ninguna empresa.');
        }

        // Si la sucursal guardada en sesión ya no es válida, se vuelve a "todas".
        if (session('sucursal_id') && ! Tenant::sucursales()->contains('id_sucursal', (int) session('sucursal_id'))) {
            session()->forget('sucursal_id');
        }

        return $next($request);
    }
}
