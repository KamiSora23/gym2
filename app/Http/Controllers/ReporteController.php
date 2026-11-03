<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Cliente;
use App\Models\Gasto;
use App\Models\Membresia;
use App\Models\Venta;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reportes de gestión: dinero (ingresos, gastos, utilidad), ventas, membresías,
 * retención de socios y uso del gimnasio en un rango de fechas.
 */
class ReporteController extends Controller
{
    public function index(Request $request): View
    {
        [$desde, $hasta] = $this->rango($request);
        $rango = [$desde->toDateString(), $hasta->toDateString()];

        $ventas = Venta::deSucursal()->where('estado', 'completado')->entreFechas($rango);
        $ingresos = (float) (clone $ventas)->sum('total');
        $gastos = (float) Gasto::deSucursal()->entreFechas($rango)->sum('monto');

        // Ventas y gastos por día para el gráfico.
        $ventasDia = (clone $ventas)->selectRaw('fecha as dia, sum(total) as total')->groupBy('fecha')->pluck('total', 'dia')
            ->mapWithKeys(fn ($v, $k) => [Carbon::parse($k)->toDateString() => (float) $v]);
        $gastosDia = Gasto::deSucursal()->entreFechas($rango)->selectRaw('fecha as dia, sum(monto) as total')->groupBy('fecha')->pluck('total', 'dia')
            ->mapWithKeys(fn ($v, $k) => [Carbon::parse($k)->toDateString() => (float) $v]);
        $dias = collect(CarbonPeriod::create($desde, $hasta))->map(fn ($d) => $d->toDateString());

        $idsVentas = (clone $ventas)->pluck('id_venta');

        // Membresías vendidas en el periodo y socios.
        $membresiasPeriodo = Membresia::with('plan')->whereHas('cliente')
            ->whereIn('id_venta', $idsVentas)->get();

        $hoy = today();
        $vigentes = Membresia::whereHas('cliente')->where('estado', 'activo')->whereDate('fecha_inicio', '<=', $hoy)->whereDate('fecha_fin', '>=', $hoy);

        // Retención: socios cuya membresía venció en los últimos 30 días y no renovaron.
        $vencidosSinRenovar = Cliente::where('activo', true)
            ->whereHas('membresias', fn ($q) => $q->whereBetween('fecha_fin', [$hoy->copy()->subDays(30)->toDateString(), $hoy->copy()->subDay()->toDateString()]))
            ->whereDoesntHave('membresias', fn ($q) => $q->where('estado', 'activo')->whereDate('fecha_fin', '>=', $hoy))
            ->with(['membresias' => fn ($q) => $q->with('plan')->orderByDesc('fecha_fin')])
            ->orderBy('nombre')
            ->get();

        $asistencias = Asistencia::deSucursal()->entreFechas($rango);
        $porHora = (clone $asistencias)->get(['hora_entrada'])
            ->countBy(fn ($a) => (int) substr((string) $a->hora_entrada, 0, 2))
            ->sortKeys();
        $porDiaSemana = (clone $asistencias)->get(['fecha'])
            ->countBy(fn ($a) => Carbon::parse($a->fecha)->dayOfWeekIso);

        return view('admin.reportes', [
            'desde' => $desde,
            'hasta' => $hasta,
            'resumen' => [
                'ingresos' => $ingresos,
                'gastos' => $gastos,
                'utilidad' => $ingresos - $gastos,
                'ventas' => $idsVentas->count(),
                'ticket' => $idsVentas->count() ? $ingresos / $idsVentas->count() : 0,
                'por_cobrar' => (float) Venta::deSucursal()->where('estado', 'pendiente')->sum('total'),
                'anuladas' => Venta::deSucursal()->where('estado', 'anulado')->entreFechas($rango)->count(),
            ],
            'grafico' => [
                'dias' => $dias->map(fn ($d) => Carbon::parse($d)->format('d/m'))->values(),
                'ventas' => $dias->map(fn ($d) => $ventasDia[$d] ?? 0)->values(),
                'gastos' => $dias->map(fn ($d) => $gastosDia[$d] ?? 0)->values(),
            ],
            'porMetodo' => (clone $ventas)->selectRaw('metodo_pago, count(*) as cantidad, sum(total) as total')->groupBy('metodo_pago')->orderByDesc('total')->get(),
            'gastosCategoria' => Gasto::deSucursal()->entreFechas($rango)->selectRaw('categoria, sum(monto) as total')->groupBy('categoria')->orderByDesc('total')->get(),
            'topProductos' => DB::table('venta_detalle')
                ->join('productos', 'productos.id_producto', '=', 'venta_detalle.id_producto')
                ->whereIn('venta_detalle.id_venta', $idsVentas)
                ->selectRaw('productos.nombre, sum(venta_detalle.cantidad) as cantidad, sum(venta_detalle.subtotal) as total')
                ->groupBy('productos.id_producto', 'productos.nombre')
                ->orderByDesc('total')
                ->limit(8)
                ->get(),
            'planes' => $membresiasPeriodo->groupBy(fn ($m) => $m->plan?->nombre ?? 'Plan eliminado')
                ->map(fn ($grupo, $nombre) => ['nombre' => $nombre, 'cantidad' => $grupo->count(), 'total' => (float) $grupo->sum(fn ($m) => $m->plan?->precio)])
                ->sortByDesc('cantidad')->values(),
            'socios' => [
                'vigentes' => (clone $vigentes)->distinct()->count('id_cliente'),
                'nuevos' => Cliente::whereBetween('created_at', [$desde->copy()->startOfDay(), $hasta->copy()->endOfDay()])->count(),
                'por_vencer' => (clone $vigentes)->whereDate('fecha_fin', '<=', $hoy->copy()->addDays(7))->distinct()->count('id_cliente'),
                'congelados' => Membresia::whereHas('cliente')->where('estado', 'congelada')->count(),
                'vencidos' => $vencidosSinRenovar->count(),
            ],
            'vencidosSinRenovar' => $vencidosSinRenovar->take(15),
            'asistencia' => [
                'total' => (clone $asistencias)->count(),
                'promedio' => $dias->count() ? round((clone $asistencias)->count() / $dias->count(), 1) : 0,
                'por_hora' => $porHora,
                'por_dia' => collect(range(1, 7))->mapWithKeys(fn ($d) => [$d => $porDiaSemana[$d] ?? 0]),
            ],
        ]);
    }

    /**
     * Exporta las ventas del periodo a CSV (se abre en Excel).
     */
    public function exportar(Request $request): StreamedResponse
    {
        [$desde, $hasta] = $this->rango($request);

        $ventas = Venta::deSucursal()->with(['cliente', 'detalles', 'sucursal'])
            ->entreFechas([$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('fecha')->orderBy('id_venta')
            ->get();

        $nombre = 'ventas_'.$desde->format('Ymd').'_'.$hasta->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($ventas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF"); // BOM para que Excel lea las tildes
            fputcsv($salida, ['Folio', 'Fecha', 'Sucursal', 'Cliente', 'Detalle', 'Método', 'Estado', 'Origen', 'Total'], ';');

            foreach ($ventas as $v) {
                fputcsv($salida, [
                    $v->folio,
                    $v->fecha->format('d/m/Y'),
                    $v->sucursal?->nombre,
                    $v->cliente?->nombre ?? 'Venta rápida',
                    $v->detalles->map(fn ($d) => $d->cantidad.' x '.$d->descripcion)->implode(', '),
                    Venta::METODOS_PAGO[$v->metodo_pago] ?? $v->metodo_pago,
                    $v->estado,
                    $v->origen,
                    number_format((float) $v->total, 2, '.', ''),
                ], ';');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function rango(Request $request): array
    {
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $desde = $request->filled('desde') ? Carbon::parse($request->desde)->startOfDay() : today()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta)->startOfDay() : today();

        // Máximo un año por consulta.
        if ($desde->diffInDays($hasta) > 366) {
            $desde = $hasta->copy()->subYear();
        }

        return [$desde, $hasta];
    }
}
