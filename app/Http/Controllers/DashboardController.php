<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Cliente;
use App\Models\Gasto;
use App\Models\Ingreso;
use App\Models\Membresia;
use App\Models\Producto;
use App\Models\Role;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Panel de inicio. Cada rol ve lo que necesita para su trabajo:
 * admin (resumen completo), recepción, caja y entrenador.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return match (true) {
            $user->isAdmin() => $this->administracion(),
            $user->hasRole(Role::RECEPCIONISTA) => $this->recepcion(),
            $user->hasRole(Role::CAJERA) => $this->caja(),
            $user->hasRole(Role::ENTRENADOR) => $this->coach($user),
        };
    }

    /**
     * Recepción: check-in rápido, quién está en el gym, clases de hoy y membresías por vencer.
     */
    private function recepcion(): View
    {
        $hoy = today();

        $asistenciasHoy = Asistencia::deSucursal()->with('cliente.membresiaActiva')->whereDate('fecha', $hoy)->orderByDesc('hora_entrada')->get();

        return view('admin.paneles.recepcion', [
            'asistenciasHoy' => $asistenciasHoy,
            'clasesHoy' => Clase::deSucursal()->with('entrenador')->withCount('clientes')->where('dia_semana', $hoy->dayOfWeekIso)->orderBy('hora_inicio')->get(),
            'porVencer' => Membresia::with(['cliente', 'plan'])->whereHas('cliente')
                ->where('estado', 'activo')
                ->whereBetween('fecha_fin', [$hoy->toDateString(), $hoy->copy()->addDays(7)->toDateString()])
                ->orderBy('fecha_fin')->get(),
            'clientes' => Cliente::with('membresiaActiva')->orderBy('nombre')->get(['id_cliente', 'nombre', 'dni']),
            'nuevosHoy' => Cliente::whereDate('created_at', $hoy)->count(),
        ]);
    }

    /**
     * Caja: ventas del día por método de pago, cobros pendientes, gastos y stock bajo.
     */
    private function caja(): View
    {
        $hoy = today();
        $ventasHoy = Venta::deSucursal()->with('cliente')->whereDate('fecha', $hoy)->where('estado', '!=', 'anulado')->orderByDesc('id_venta')->get();

        $productos = Producto::with(['inventario', 'tipo'])->where('activo', true)->get()
            ->map(fn ($p) => tap($p, fn ($p) => $p->stock_actual = $p->stockEn()))
            ->filter(fn ($p) => $p->stock_actual <= 5)
            ->sortBy('stock_actual');

        return view('admin.paneles.caja', [
            'ventasHoy' => $ventasHoy,
            'cobradoHoy' => (float) $ventasHoy->where('estado', 'completado')->sum('total'),
            'porMetodo' => $ventasHoy->where('estado', 'completado')->groupBy('metodo_pago')->map->sum('total'),
            'pendientes' => Venta::deSucursal()->with('cliente')->where('estado', 'pendiente')->orderBy('fecha')->get(),
            'gastosHoy' => (float) Gasto::deSucursal()->whereDate('fecha', $hoy)->sum('monto'),
            'stockBajo' => $productos->values(),
        ]);
    }

    /**
     * Entrenador: sus clases, su semana y sus alumnos. Sin información financiera.
     */
    private function coach($user): View
    {
        $perfil = $user->entrenador;
        $hoy = today();

        $clases = $perfil
            ? Clase::with('clientes')->where('id_entrenador', $perfil->id_entrenador)->orderBy('dia_semana')->orderBy('hora_inicio')->get()
            : collect();

        $alumnos = $perfil
            ? Cliente::with('membresiaActiva.plan')
                ->where(fn ($q) => $q->where('id_entrenador', $perfil->id_entrenador)
                    ->orWhereHas('clases', fn ($c) => $c->where('clases.id_entrenador', $perfil->id_entrenador)))
                ->orderBy('nombre')->get()
            : collect();

        $idsAlumnos = $alumnos->pluck('id_cliente');

        return view('admin.paneles.coach', [
            'perfil' => $perfil,
            'clases' => $clases,
            'clasesHoy' => $clases->where('dia_semana', $hoy->dayOfWeekIso)->values(),
            'alumnos' => $alumnos,
            'personales' => $perfil ? $alumnos->where('id_entrenador', $perfil->id_entrenador)->count() : 0,
            'asistieronHoy' => Asistencia::with('cliente')->whereIn('id_cliente', $idsAlumnos)->whereDate('fecha', $hoy)->orderByDesc('hora_entrada')->get(),
        ]);
    }

    /**
     * Administración: resumen completo de la empresa o sucursal.
     */
    private function administracion(): View
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
