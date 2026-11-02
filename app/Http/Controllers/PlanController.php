<?php

namespace App\Http\Controllers;

use App\Models\Membresia;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Planes de membresía y seguimiento de membresías de clientes.
 */
class PlanController extends Controller
{
    public function index(): View
    {
        $membresias = Membresia::with(['cliente', 'plan'])
            ->whereHas('cliente')
            ->orderByDesc('fecha_fin')
            ->limit(50)
            ->get();

        return view('admin.planes', [
            'planes' => Plan::withCount(['membresias as activas_count' => fn ($q) => $q->where('estado', 'activo')->whereDate('fecha_fin', '>=', today())])
                ->orderBy('precio')
                ->get(),
            'membresias' => $membresias,
            'porVencer' => $membresias->filter(fn ($m) => $m->estado === 'activo' && $m->fecha_fin->between(today(), today()->addDays(7)))->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Plan::create($this->validar($request) + ['activo' => true]);

        return redirect()->route('planes.index')->with('success', 'Plan creado correctamente.');
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $datos = $this->validar($request);
        $datos['activo'] = $request->boolean('activo');
        $plan->update($datos);

        return redirect()->route('planes.index')->with('success', 'Plan actualizado correctamente.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->membresias()->exists()) {
            $plan->update(['activo' => false]);

            return back()->with('info', 'El plan tiene membresías vendidas: se desactivó en lugar de eliminarse.');
        }

        $plan->delete();

        return back()->with('success', 'Plan eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validateWithBag('plan', [
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio' => ['required', 'numeric', 'min:0', 'max:999999'],
            'duracion_dias' => ['required', 'integer', 'min:1', 'max:1095'],
        ]);
    }
}
