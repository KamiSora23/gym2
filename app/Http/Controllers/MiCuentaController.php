<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\PlanNutricional;
use App\Models\Progreso;
use App\Models\Rutina;
use App\Models\Venta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Área del cliente: su membresía, clases, coach, asistencias y compras.
 */
class MiCuentaController extends Controller
{
    public function __invoke(Request $request): View
    {
        $cliente = Cliente::withoutGlobalScopes()
            ->with(['entrenador', 'membresiaActiva.plan', 'proximaMembresia.plan'])
            ->where('user_id', $request->user()->id)
            ->first();

        $idCliente = $cliente?->id_cliente ?? 0;

        $misClases = Clase::with('entrenador')
            ->whereHas('clientes', fn ($q) => $q->where('clientes.id_cliente', $idCliente))
            ->orderBy('dia_semana')->orderBy('hora_inicio')->get();

        return view('mi-cuenta', [
            'disponibles' => Clase::with('entrenador')->withCount('clientes')
                ->whereNotIn('id_clase', $misClases->pluck('id_clase'))
                ->orderBy('dia_semana')->orderBy('hora_inicio')->get(),
            'rutina' => Rutina::with(['ejercicios', 'entrenador'])->where('id_cliente', $idCliente)->where('activa', true)->latest()->first(),
            'progresos' => Progreso::where('id_cliente', $idCliente)->orderBy('fecha')->get(),
            'planNutricional' => PlanNutricional::with('nutricionista')->where('id_cliente', $idCliente)->where('activo', true)->latest()->first(),
            'cliente' => $cliente,
            'membresias' => Membresia::with('plan')->where('id_cliente', $idCliente)->orderByDesc('fecha_fin')->limit(5)->get(),
            'clases' => $misClases,
            'asistencias' => Asistencia::withoutGlobalScopes()->where('id_cliente', $idCliente)->orderByDesc('fecha')->limit(8)->get(),
            'asistenciasMes' => Asistencia::withoutGlobalScopes()->where('id_cliente', $idCliente)->where('fecha', '>=', today()->startOfMonth()->toDateString())->count(),
            'compras' => Venta::withoutGlobalScopes()->with(['detalles', 'sucursal'])->where('id_cliente', $idCliente)->orderByDesc('fecha')->orderByDesc('id_venta')->limit(10)->get(),
            'totalPagado' => (float) Venta::withoutGlobalScopes()->where('id_cliente', $idCliente)->where('estado', 'completado')->sum('total'),
        ]);
    }

    /**
     * Comprobante de una compra del propio cliente.
     */
    public function compra(Request $request, int $venta): View
    {
        $cliente = $this->cliente($request);

        $venta = Venta::withoutGlobalScopes()->with(['detalles', 'sucursal', 'membresias.plan'])
            ->where('id_cliente', $cliente->id_cliente)
            ->findOrFail($venta);

        return view('mi-cuenta-compra', ['venta' => $venta, 'cliente' => $cliente]);
    }

    /**
     * El cliente se inscribe en una clase de su gimnasio (requiere membresía vigente y cupo).
     */
    public function inscribirse(Request $request, Clase $clase): RedirectResponse
    {
        $cliente = $this->cliente($request);

        if (! $cliente->membresiaActiva) {
            return back()->with('error', 'Necesitas una membresía vigente para inscribirte en clases.');
        }

        if ($clase->clientes()->where('clientes.id_cliente', $cliente->id_cliente)->exists()) {
            return back()->with('error', 'Ya estás inscrito en esta clase.');
        }

        if ($clase->clientes()->count() >= $clase->cupo) {
            return back()->with('error', "La clase {$clase->nombre} ya no tiene cupos.");
        }

        $clase->clientes()->attach($cliente->id_cliente);

        return back()->with('success', "Te inscribiste en {$clase->nombre} (".Clase::DIAS[$clase->dia_semana]." {$clase->horario}).");
    }

    public function retirarse(Request $request, Clase $clase): RedirectResponse
    {
        $cliente = $this->cliente($request);

        $clase->clientes()->detach($cliente->id_cliente);

        return back()->with('success', "Te retiraste de {$clase->nombre}.");
    }

    private function cliente(Request $request): Cliente
    {
        $cliente = Cliente::with('membresiaActiva')->where('user_id', $request->user()->id)->first();

        abort_unless($cliente, 403, 'Tu cuenta no tiene ficha de cliente.');

        return $cliente;
    }
}
