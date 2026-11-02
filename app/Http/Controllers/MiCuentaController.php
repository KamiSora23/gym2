<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Cliente;
use App\Models\Membresia;
use App\Models\Venta;
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
            ->with(['entrenador', 'membresiaActiva.plan'])
            ->where('user_id', $request->user()->id)
            ->first();

        $idCliente = $cliente?->id_cliente ?? 0;

        return view('mi-cuenta', [
            'cliente' => $cliente,
            'membresias' => Membresia::with('plan')->where('id_cliente', $idCliente)->orderByDesc('fecha_fin')->limit(5)->get(),
            'clases' => Clase::withoutGlobalScopes()->with('entrenador')
                ->whereHas('clientes', fn ($q) => $q->withoutGlobalScopes()->where('clientes.id_cliente', $idCliente))
                ->orderBy('dia_semana')->orderBy('hora_inicio')->get(),
            'asistencias' => Asistencia::withoutGlobalScopes()->where('id_cliente', $idCliente)->orderByDesc('fecha')->limit(8)->get(),
            'asistenciasMes' => Asistencia::withoutGlobalScopes()->where('id_cliente', $idCliente)->where('fecha', '>=', today()->startOfMonth()->toDateString())->count(),
            'compras' => Venta::withoutGlobalScopes()->with('detalles')->where('id_cliente', $idCliente)->where('estado', '!=', 'anulado')->orderByDesc('fecha')->limit(5)->get(),
        ]);
    }
}
