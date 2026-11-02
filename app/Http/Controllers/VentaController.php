<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Ingreso;
use App\Models\Inventario;
use App\Models\Membresia;
use App\Models\Plan;
use App\Models\Producto;
use App\Models\Venta;
use App\Support\Permisos;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Punto de venta: productos (descuentan stock) y membresías (crean o renuevan la membresía del cliente).
 */
class VentaController extends Controller
{
    public function index(Request $request): View
    {
        $filtro = fn ($q) => $q->deSucursal()
            ->when($request->filled('estado'), fn ($w) => $w->where('estado', $request->estado))
            ->when($request->filled('desde'), fn ($w) => $w->whereDate('fecha', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($w) => $w->whereDate('fecha', '<=', $request->hasta));

        $ventas = $filtro(Venta::query())
            ->with(['cliente', 'vendedor', 'sucursal'])
            ->orderByDesc('fecha')
            ->orderByDesc('id_venta')
            ->paginate(20)
            ->withQueryString();

        $validas = fn () => Venta::deSucursal()->where('estado', '!=', 'anulado');
        $semana = [today()->startOfWeek()->toDateString(), today()->endOfWeek()->toDateString()];
        $numSemana = $validas()->whereBetween('fecha', $semana)->count();
        $totalSemana = (float) $validas()->whereBetween('fecha', $semana)->sum('total');

        return view('admin.ventas.index', [
            'ventas' => $ventas,
            'stats' => [
                'hoy' => (float) $validas()->whereDate('fecha', today())->sum('total'),
                'semana' => $totalSemana,
                'transacciones' => $numSemana,
                'ticket' => $numSemana ? $totalSemana / $numSemana : 0,
                'pendientes' => Venta::deSucursal()->where('estado', 'pendiente')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.ventas.create', [
            'clientes' => Cliente::with('membresiaActiva.plan')->orderBy('nombre')->get(),
            'productos' => Producto::with('inventario')->where('activo', true)->orderBy('nombre')->get(),
            'planes' => Plan::where('activo', true)->orderBy('precio')->get(),
            'sucursales' => Tenant::sucursales(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Permisos::exigir('ventas.vender');

        $empresaId = Tenant::empresaId();

        $datos = $request->validate([
            'id_cliente' => ['nullable', Rule::exists('clientes', 'id_cliente')->where('id_empresa', $empresaId)],
            'id_plan' => ['nullable', Rule::exists('planes', 'id_plan')->where('id_empresa', $empresaId)->where('activo', true)],
            'metodo_pago' => ['required', Rule::in(array_keys(Venta::METODOS_PAGO))],
            'estado' => ['required', Rule::in(['completado', 'pendiente'])],
            'id_sucursal' => ['nullable', 'integer'],
            'items' => ['nullable', 'array'],
            'items.*.id_producto' => ['nullable', Rule::exists('productos', 'id_producto')->where('id_empresa', $empresaId)],
            'items.*.cantidad' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ], [
            'id_plan.exists' => 'El plan seleccionado no está disponible.',
        ]);

        $items = collect($datos['items'] ?? [])
            ->filter(fn ($i) => ! empty($i['id_producto']) && ! empty($i['cantidad']))
            ->groupBy('id_producto')
            ->map(fn ($grupo, $id) => ['id_producto' => (int) $id, 'cantidad' => (int) $grupo->sum('cantidad')])
            ->values();

        if ($items->isEmpty() && empty($datos['id_plan'])) {
            throw ValidationException::withMessages(['items' => 'Agrega al menos un producto o una membresía.']);
        }

        if (! empty($datos['id_plan']) && empty($datos['id_cliente'])) {
            throw ValidationException::withMessages(['id_cliente' => 'Para vender una membresía debes elegir un cliente.']);
        }

        $sucursalId = Tenant::sucursalParaRegistro($datos['id_sucursal'] ?? null);

        if (! $sucursalId) {
            throw ValidationException::withMessages(['id_sucursal' => 'La empresa no tiene sucursales activas.']);
        }

        $venta = DB::transaction(function () use ($datos, $items, $sucursalId, $request) {
            $productos = Producto::whereIn('id_producto', $items->pluck('id_producto'))->get()->keyBy('id_producto');
            $lineas = [];

            foreach ($items as $item) {
                $producto = $productos[$item['id_producto']];

                // Se bloquea la fila de stock para evitar vender dos veces la misma unidad.
                $stock = Inventario::where('id_producto', $producto->id_producto)
                    ->where('id_sucursal', $sucursalId)
                    ->lockForUpdate()
                    ->first();

                if (! $stock || $stock->cantidad < $item['cantidad']) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente de {$producto->nombre} (disponible: ".($stock->cantidad ?? 0).').',
                    ]);
                }

                $stock->decrement('cantidad', $item['cantidad']);

                $lineas[] = [
                    'id_producto' => $producto->id_producto,
                    'descripcion' => $producto->nombre,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'subtotal' => round($producto->precio * $item['cantidad'], 2),
                ];
            }

            $plan = ! empty($datos['id_plan']) ? Plan::find($datos['id_plan']) : null;

            if ($plan) {
                $lineas[] = [
                    'id_plan' => $plan->id_plan,
                    'descripcion' => "Membresía {$plan->nombre} ({$plan->duracion_dias} días)",
                    'cantidad' => 1,
                    'precio_unitario' => $plan->precio,
                    'subtotal' => $plan->precio,
                ];
            }

            $venta = Venta::create([
                'id_sucursal' => $sucursalId,
                'id_cliente' => $datos['id_cliente'] ?? null,
                'user_id' => $request->user()->id,
                'fecha' => today(),
                'total' => collect($lineas)->sum('subtotal'),
                'estado' => $datos['estado'],
                'metodo_pago' => $datos['metodo_pago'],
            ]);

            $venta->detalles()->createMany($lineas);

            if ($plan) {
                $this->crearMembresia($venta, $plan);
            }

            if ($venta->estado === 'completado') {
                $this->registrarIngreso($venta);
            }

            return $venta;
        });

        return redirect()->route('ventas.show', $venta)->with('success', "Venta {$venta->folio} registrada correctamente.");
    }

    public function show(Venta $venta): View
    {
        $venta->load(['detalles', 'cliente', 'vendedor', 'sucursal', 'membresias.plan']);

        return view('admin.ventas.show', compact('venta'));
    }

    /**
     * Marca como pagada una venta pendiente.
     */
    public function cobrar(Venta $venta): RedirectResponse
    {
        Permisos::exigir('ventas.cobrar');

        if ($venta->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden cobrar ventas pendientes.');
        }

        DB::transaction(function () use ($venta) {
            $venta->update(['estado' => 'completado']);
            $this->registrarIngreso($venta);
        });

        return back()->with('success', "Venta {$venta->folio} cobrada.");
    }

    /**
     * Anula la venta: devuelve el stock, elimina el ingreso y cancela la membresía.
     */
    public function anular(Request $request, Venta $venta): RedirectResponse
    {
        Permisos::exigir('ventas.anular');

        if ($venta->estado === 'anulado') {
            return back()->with('error', 'La venta ya estaba anulada.');
        }

        DB::transaction(function () use ($venta) {
            foreach ($venta->detalles()->whereNotNull('id_producto')->get() as $detalle) {
                $producto = Producto::withoutGlobalScopes()->find($detalle->id_producto);

                if ($producto) {
                    ProductoController::ajustarStock($producto, $venta->id_sucursal, $detalle->cantidad);
                }
            }

            $venta->ingreso()->delete();
            $venta->membresias()->update(['estado' => 'anulado']);
            $venta->update(['estado' => 'anulado']);
        });

        return back()->with('success', "Venta {$venta->folio} anulada. El stock fue devuelto.");
    }

    /**
     * La nueva membresía empieza hoy o, si el cliente ya tiene una vigente, cuando esa termine.
     */
    private function crearMembresia(Venta $venta, Plan $plan): void
    {
        $vigente = Membresia::where('id_cliente', $venta->id_cliente)
            ->where('estado', 'activo')
            ->whereDate('fecha_fin', '>=', today())
            ->orderByDesc('fecha_fin')
            ->first();

        $inicio = $vigente ? $vigente->fecha_fin->copy()->addDay() : today();

        Membresia::create([
            'id_cliente' => $venta->id_cliente,
            'id_plan' => $plan->id_plan,
            'id_venta' => $venta->id_venta,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $inicio->copy()->addDays($plan->duracion_dias - 1),
            'estado' => 'activo',
        ]);
    }

    private function registrarIngreso(Venta $venta): void
    {
        Ingreso::create([
            'id_empresa' => $venta->id_empresa,
            'id_sucursal' => $venta->id_sucursal,
            'concepto' => "Venta {$venta->folio}",
            'monto' => $venta->total,
            'fecha' => today(),
            'user_id' => auth()->id(),
            'id_venta' => $venta->id_venta,
        ]);
    }
}
