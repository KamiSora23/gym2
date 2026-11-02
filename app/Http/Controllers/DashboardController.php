<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Cliente;
use App\Models\Gasto;
use App\Models\Ingreso;
use App\Models\Membresia;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Resumen de la empresa (y sucursal) seleccionada.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $hoy = Carbon::today();
        $inicioSemana = $hoy->copy()->startOfWeek(Carbon::MONDAY);
        $rangoSemana = [$inicioSemana->toDateString(), $hoy->copy()->endOfWeek(Carbon::SUNDAY)->toDateString()];
        $inicioMes = $hoy->copy()->startOfMonth();

        $ingresosSemana = (float) Ingreso::deSucursal()->whereBetween('fecha', $rangoSemana)->sum('monto');
        $gastosSemana = (float) Gasto::deSucursal()->whereBetween('fecha', $rangoSemana)->sum('monto');

        $porDia = Asistencia::deSucursal()
            ->whereBetween('fecha', $rangoSemana)
            ->select('fecha', DB::raw('count(*) as total'))
            ->groupBy('fecha')
            ->get()
            ->mapWithKeys(fn ($fila) => [Carbon::parse($fila->fecha)->toDateString() => (int) $fila->total]);

        $graficoAsistencias = collect(range(0, 6))
            ->map(fn ($i) => $porDia[$inicioSemana->copy()->addDays($i)->toDateString()] ?? 0);

        $membresias = Membresia::whereHas('cliente');

        return view('admin.dashboard', [
            'stats' => [
                'clientes_total' => Cliente::count(),
                'clientes_activos' => (clone $membresias)->where('estado', 'activo')->whereDate('fecha_fin', '>=', $hoy)->distinct()->count('id_cliente'),
                'ingresos_semana' => $ingresosSemana,
                'gastos_semana' => $gastosSemana,
                'neto_semana' => $ingresosSemana - $gastosSemana,
                'inscritos_semana' => Cliente::where('created_at', '>=', $inicioSemana)->count(),
                'pagos_pendientes' => Venta::deSucursal()->where('estado', 'pendiente')->count(),
                'asistencias_hoy' => Asistencia::deSucursal()->whereDate('fecha', $hoy)->count(),
                'en_gym' => Asistencia::deSucursal()->whereDate('fecha', $hoy)->whereNull('hora_salida')->count(),
                'membresias_mes' => (clone $membresias)->where('created_at', '>=', $inicioMes)->count(),
                'por_vencer' => (clone $membresias)->where('estado', 'activo')->whereBetween('fecha_fin', [$hoy->toDateString(), $hoy->copy()->addDays(7)->toDateString()])->count(),
            ],
            'graficoAsistencias' => $graficoAsistencias,
            'ultimasVentas' => Venta::deSucursal()->with('cliente')->where('estado', '!=', 'anulado')->orderByDesc('id_venta')->limit(6)->get(),
            'clasesHoy' => Clase::deSucursal()->with('entrenador')->withCount('clientes')->where('dia_semana', $hoy->dayOfWeekIso)->orderBy('hora_inicio')->get(),
        ]);
    }
}
