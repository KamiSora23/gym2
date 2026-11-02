<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $hoy = Carbon::today();
        $inicioSemana = $hoy->copy()->startOfWeek(Carbon::MONDAY);
        $finSemana = $hoy->copy()->endOfWeek(Carbon::SUNDAY);
        $inicioMes = $hoy->copy()->startOfMonth();
        $rangoSemana = [$inicioSemana->toDateString(), $finSemana->toDateString()];

        $ingresosSemana = (float) DB::table('ingresos')->whereBetween('fecha', $rangoSemana)->sum('monto');
        $gastosSemana = (float) DB::table('gastos')->whereBetween('fecha', $rangoSemana)->sum('monto');

        $ventasSemana = DB::table('ventas')->whereBetween('fecha', $rangoSemana);
        $totalVentasSemana = (float) (clone $ventasSemana)->sum('total');
        $numVentasSemana = (clone $ventasSemana)->count();

        // Asistencias por día de la semana actual (Lun..Dom).
        $asistenciasPorDia = DB::table('asistencias')
            ->whereBetween('fecha', $rangoSemana)
            ->select('fecha', DB::raw('count(*) as total'))
            ->groupBy('fecha')
            ->pluck('total', 'fecha');

        $graficoAsistencias = collect(range(0, 6))->map(function ($i) use ($inicioSemana, $asistenciasPorDia) {
            $fecha = $inicioSemana->copy()->addDays($i)->toDateString();

            return (int) ($asistenciasPorDia[$fecha] ?? 0);
        });

        $stats = [
            'clientes_total' => DB::table('clientes')->count(),
            'clientes_activos' => DB::table('membresias')
                ->where('estado', 'activo')
                ->whereDate('fecha_fin', '>=', $hoy)
                ->distinct()
                ->count('id_cliente'),
            'ingresos_semana' => $ingresosSemana,
            'gastos_semana' => $gastosSemana,
            'neto_semana' => $ingresosSemana - $gastosSemana,
            'inscritos_semana' => DB::table('clientes')->where('created_at', '>=', $inicioSemana)->count(),
            'pagos_pendientes' => DB::table('ventas')->where('estado', 'pendiente')->count(),
            'asistencias_hoy' => DB::table('asistencias')->whereDate('fecha', $hoy)->count(),
            'renovaciones_mes' => DB::table('membresias')->where('created_at', '>=', $inicioMes)->count(),
            'ventas_hoy' => (float) DB::table('ventas')->whereDate('fecha', $hoy)->sum('total'),
            'ventas_semana' => $totalVentasSemana,
            'num_ventas_semana' => $numVentasSemana,
            'ticket_promedio' => $numVentasSemana ? $totalVentasSemana / $numVentasSemana : 0,
            'gastos_hoy' => (float) DB::table('gastos')->whereDate('fecha', $hoy)->sum('monto'),
            'gastos_mes' => (float) DB::table('gastos')->where('fecha', '>=', $inicioMes->toDateString())->sum('monto'),
        ];

        $ventas = DB::table('ventas')
            ->leftJoin('clientes', 'clientes.id_cliente', '=', 'ventas.id_cliente')
            ->select('ventas.*', 'clientes.nombre as cliente')
            ->orderByDesc('ventas.fecha')
            ->orderByDesc('ventas.id_venta')
            ->limit(25)
            ->get();

        $asistencias = DB::table('asistencias')
            ->join('clientes', 'clientes.id_cliente', '=', 'asistencias.id_cliente')
            ->select('asistencias.*', 'clientes.nombre as cliente')
            ->whereDate('asistencias.fecha', $hoy)
            ->orderByDesc('asistencias.hora_entrada')
            ->get();

        $gastos = DB::table('gastos')
            ->leftJoin('users', 'users.id', '=', 'gastos.user_id')
            ->select('gastos.*', 'users.name as registrado_por')
            ->orderByDesc('gastos.fecha')
            ->orderByDesc('gastos.id_gasto')
            ->limit(25)
            ->get();

        $productos = DB::table('productos')
            ->leftJoin('tipo_producto', 'tipo_producto.id_tipo', '=', 'productos.id_tipo')
            ->leftJoin('inventario', 'inventario.id_producto', '=', 'productos.id_producto')
            ->select(
                'productos.id_producto',
                'productos.nombre',
                'productos.precio',
                'tipo_producto.nombre as tipo',
                DB::raw('coalesce(sum(inventario.cantidad), 0) as stock'),
            )
            ->groupBy('productos.id_producto', 'productos.nombre', 'productos.precio', 'tipo_producto.nombre')
            ->orderBy('productos.nombre')
            ->get();

        $clientes = DB::table('clientes')->orderBy('nombre')->get(['id_cliente', 'nombre', 'dni']);

        $usuarios = $request->user()->isSuperAdmin()
            ? User::with('role')->orderBy('name')->get()
            : collect();

        return view('dashboard', [
            'stats' => $stats,
            'graficoAsistencias' => $graficoAsistencias,
            'ventas' => $ventas,
            'ultimasVentas' => $ventas->take(5),
            'asistencias' => $asistencias,
            'gastos' => $gastos,
            'productos' => $productos,
            'clientes' => $clientes,
            'usuarios' => $usuarios,
            'roles' => Role::orderBy('id')->get(),
        ]);
    }
}
