<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Entrenador;
use App\Models\Plan;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\TipoProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Sitio público: muestra el catálogo, los planes y los coaches reales del gimnasio.
 * Un cliente con sesión ve los de su empresa; un visitante, los de la empresa principal.
 */
class PortalController extends Controller
{
    public function inicio(Request $request): View
    {
        $datos = $this->catalogo($request);

        return view('index', [
            'planes' => $datos['planes'],
            'destacados' => $datos['productos']->take(4),
            'coaches' => $datos['coaches'],
        ]);
    }

    public function tienda(Request $request): View
    {
        return view('shop', $this->catalogo($request));
    }

    public function carrito(): View
    {
        return view('carrito');
    }

    public function checkout(Request $request): View
    {
        $empresaId = $this->empresaId($request);

        // Si inicia sesión o se registra desde aquí, vuelve al checkout con su carrito.
        if (! $request->user()) {
            session()->put('url.intended', route('checkout'));
        }

        return view('checkout', [
            'sucursales' => Sucursal::withoutGlobalScopes()->where('id_empresa', $empresaId)->where('activo', true)->orderBy('id_sucursal')->get(),
            'sedePreferida' => $request->user()?->id_sucursal,
            'esCliente' => (bool) $request->user()?->isCliente(),
        ]);
    }

    private function empresaId(Request $request): ?int
    {
        $user = $request->user();

        return $user?->isCliente() && $user->id_empresa ? $user->id_empresa : Empresa::principal()?->id_empresa;
    }

    private function catalogo(Request $request): array
    {
        $empresaId = $this->empresaId($request);

        $productos = Producto::withoutGlobalScopes()
            ->with(['tipo' => fn ($q) => $q->withoutGlobalScopes(), 'inventario'])
            ->where('id_empresa', $empresaId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->each(function ($p) {
                $p->stock_total = (int) $p->inventario->sum('cantidad');
                $p->categoria_slug = Str::slug($p->tipo?->nombre ?? 'otros');
            });

        return [
            'productos' => $productos,
            'categorias' => TipoProducto::withoutGlobalScopes()->where('id_empresa', $empresaId)->orderBy('nombre')->get()
                ->each(fn ($c) => $c->slug = Str::slug($c->nombre)),
            'planes' => Plan::withoutGlobalScopes()->where('id_empresa', $empresaId)->where('activo', true)->orderBy('precio')->get(),
            'coaches' => Entrenador::withoutGlobalScopes()->where('id_empresa', $empresaId)->where('activo', true)->orderBy('nombre')->get(),
        ];
    }
}
