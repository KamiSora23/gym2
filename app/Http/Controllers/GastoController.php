<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GastoController extends Controller
{
    public function index(Request $request): View
    {
        $gastos = Gasto::with(['usuario', 'sucursal'])
            ->deSucursal()
            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria', $request->categoria))
            ->when($request->filled('mes'), fn ($q) => $q->where('fecha', 'like', $request->mes.'%'))
            ->orderByDesc('fecha')
            ->orderByDesc('id_gasto')
            ->paginate(25)
            ->withQueryString();

        $base = fn () => Gasto::deSucursal();

        return view('admin.gastos', [
            'gastos' => $gastos,
            'sucursales' => Tenant::sucursales(),
            'stats' => [
                'hoy' => (float) $base()->whereDate('fecha', today())->sum('monto'),
                'semana' => (float) $base()->whereBetween('fecha', [today()->startOfWeek()->toDateString(), today()->endOfWeek()->toDateString()])->sum('monto'),
                'mes' => (float) $base()->where('fecha', '>=', today()->startOfMonth()->toDateString())->sum('monto'),
                'registros' => $base()->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validateWithBag('gasto', [
            'concepto' => ['required', 'string', 'max:255'],
            'categoria' => ['nullable', Rule::in(Gasto::CATEGORIAS)],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'id_sucursal' => ['nullable', 'integer'],
        ]);

        $datos['id_sucursal'] = Tenant::sucursalParaRegistro($datos['id_sucursal'] ?? null);
        $datos['user_id'] = $request->user()->id;

        Gasto::create($datos);

        return redirect()->route('gastos.index')->with('success', 'Gasto registrado correctamente.');
    }

    public function destroy(Request $request, Gasto $gasto): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $gasto->delete();

        return back()->with('success', 'Gasto eliminado.');
    }
}
