<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\PlanNutricional;
use App\Support\Alcance;
use App\Support\Permisos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Planes nutricionales. Información sensible: solo quien tiene "nutrition.*"
 * y únicamente sobre los clientes de su ámbito.
 */
class NutricionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $pacientes = Alcance::clientes($user, Cliente::query())
            ->with(['planesNutricionales' => fn ($q) => $q->where('activo', true), 'nutricionista'])
            ->orderBy('nombre')
            ->get();

        return view('admin.nutricion', [
            'pacientes' => $pacientes,
            'puedeGestionar' => $user->permite('nutrition.manage'),
        ]);
    }

    public function store(Request $request, Cliente $cliente): RedirectResponse
    {
        Permisos::exigir('nutrition.manage');
        Alcance::exigirCliente($request->user(), $cliente);

        $datos = $request->validateWithBag('nutricion', [
            'objetivo' => ['required', 'string', 'max:150'],
            'calorias' => ['nullable', 'integer', 'min:500', 'max:10000'],
            'proteinas' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'carbohidratos' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'grasas' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'indicaciones' => ['nullable', 'string', 'max:3000'],
        ]);

        // Un solo plan activo por cliente: el nuevo reemplaza al anterior.
        PlanNutricional::where('id_cliente', $cliente->id_cliente)->update(['activo' => false]);

        $plan = PlanNutricional::create($datos + [
            'id_empresa' => $cliente->id_empresa,
            'id_cliente' => $cliente->id_cliente,
            'user_id' => $request->user()->id,
            'activo' => true,
        ]);

        Auditoria::registrar('nutricion.plan', "Asignó un plan nutricional a {$cliente->nombre}", $plan);

        return back()->with('success', 'Plan nutricional asignado.');
    }

    public function destroy(Request $request, PlanNutricional $plan): RedirectResponse
    {
        Permisos::exigir('nutrition.manage');
        Alcance::exigirCliente($request->user(), Cliente::findOrFail($plan->id_cliente));

        $plan->delete();

        return back()->with('success', 'Plan nutricional eliminado.');
    }
}
