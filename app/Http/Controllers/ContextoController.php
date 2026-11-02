<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Cambia la empresa (super admin) o la sucursal (admin) con la que se está trabajando.
 */
class ContextoController extends Controller
{
    public function empresa(Request $request): RedirectResponse
    {
        $datos = $request->validate(['id_empresa' => ['nullable', 'exists:empresa,id_empresa']]);

        Tenant::olvidar();

        if (empty($datos['id_empresa'])) {
            return redirect()->route('plataforma.index');
        }

        session(['empresa_id' => (int) $datos['id_empresa']]);

        $empresa = Empresa::find($datos['id_empresa']);

        return redirect()->route('dashboard')->with('info', "Ahora estás administrando {$empresa->nombre}.");
    }

    public function sucursal(Request $request): RedirectResponse
    {
        abort_unless(Tenant::puedeCambiarSucursal($request->user()), 403);

        $datos = $request->validate(['id_sucursal' => ['nullable', 'integer']]);
        $id = $datos['id_sucursal'] ?? null;

        if ($id && ! Tenant::sucursales()->contains('id_sucursal', (int) $id)) {
            return back()->with('error', 'La sucursal no pertenece a esta empresa.');
        }

        $id ? session(['sucursal_id' => (int) $id]) : session()->forget('sucursal_id');

        return back();
    }
}
