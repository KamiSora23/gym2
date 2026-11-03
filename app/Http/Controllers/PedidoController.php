<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\Plan;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Venta;
use App\Support\Inventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Pedidos del sitio web: el cliente reserva productos o una membresía y paga al recoger en recepción.
 *
 * El pedido se registra como venta pendiente de cobro. Los precios y el stock los calcula
 * el servidor (nunca se confía en lo que envía el navegador). El stock queda reservado y
 * la membresía se activa cuando recepción cobra.
 */
class PedidoController extends Controller
{
    public const METODOS = ['efectivo', 'tarjeta', 'yape', 'transferencia'];

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $cliente = Cliente::withoutGlobalScopes()->where('user_id', $user->id)->first();

        if (! $cliente) {
            return back()->with('error', 'Tu cuenta no tiene ficha de cliente. Acércate a recepción para completar tu registro.');
        }

        $empresaId = $cliente->id_empresa;

        $datos = $request->validate([
            'items' => ['required', 'string', 'max:5000'],
            'metodo_pago' => ['required', Rule::in(self::METODOS)],
            'id_sucursal' => ['required', Rule::exists('sucursales', 'id_sucursal')->where('id_empresa', $empresaId)->where('activo', true)],
        ], [
            'id_sucursal.required' => 'Elige la sede donde recogerás tu pedido.',
        ]);

        $items = collect(json_decode($datos['items'], true) ?: [])
            ->filter(fn ($i) => is_array($i) && in_array($i['tipo'] ?? null, ['producto', 'plan'], true) && (int) ($i['ref'] ?? 0) > 0)
            ->map(fn ($i) => ['tipo' => $i['tipo'], 'ref' => (int) $i['ref'], 'qty' => max(1, min(50, (int) ($i['qty'] ?? 1)))]);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'Tu carrito está vacío.']);
        }

        if ($items->where('tipo', 'plan')->count() > 1 || $items->where('tipo', 'plan')->sum('qty') > 1) {
            throw ValidationException::withMessages(['items' => 'Solo puedes pedir una membresía por pedido.']);
        }

        if ($items->contains('tipo', 'plan') && ($pendiente = $cliente->situacionPlanes()['pendiente'])) {
            throw ValidationException::withMessages(['items' => "Ya tienes una membresía por pagar en el pedido {$pendiente->folio}. Págala en recepción o cancélala desde «Mi cuenta» antes de pedir otra."]);
        }

        $sucursal = Sucursal::withoutGlobalScopes()->findOrFail($datos['id_sucursal']);

        $venta = DB::transaction(function () use ($items, $cliente, $user, $sucursal, $datos) {
            $lineas = [];
            $productos = [];

            foreach ($items as $item) {
                if ($item['tipo'] === 'producto') {
                    $producto = Producto::withoutGlobalScopes()->where('id_empresa', $cliente->id_empresa)->where('activo', true)->find($item['ref']);

                    if (! $producto) {
                        throw ValidationException::withMessages(['items' => 'Uno de los productos ya no está disponible. Revisa tu carrito.']);
                    }

                    $productos[] = [$producto, $item['qty']];
                    $lineas[] = ['id_producto' => $producto->id_producto, 'descripcion' => $producto->nombre, 'cantidad' => $item['qty'], 'precio_unitario' => $producto->precio, 'subtotal' => round($producto->precio * $item['qty'], 2)];
                } else {
                    $plan = Plan::withoutGlobalScopes()->where('id_empresa', $cliente->id_empresa)->where('activo', true)->find($item['ref']);

                    if (! $plan) {
                        throw ValidationException::withMessages(['items' => 'La membresía elegida ya no está disponible.']);
                    }

                    $lineas[] = ['id_plan' => $plan->id_plan, 'descripcion' => "Membresía {$plan->nombre} ({$plan->duracion_dias} días)", 'cantidad' => 1, 'precio_unitario' => $plan->precio, 'subtotal' => $plan->precio];
                }
            }

            $venta = Venta::withoutGlobalScopes()->create([
                'id_empresa' => $cliente->id_empresa,
                'id_sucursal' => $sucursal->id_sucursal,
                'id_cliente' => $cliente->id_cliente,
                'user_id' => $user->id,
                'fecha' => today(),
                'total' => collect($lineas)->sum('subtotal'),
                'estado' => 'pendiente',
                'metodo_pago' => $datos['metodo_pago'],
                'origen' => 'web',
            ]);

            $venta->detalles()->createMany($lineas);

            // Reserva el stock en la sede de recojo (falla si no alcanza).
            foreach ($productos as [$producto, $cantidad]) {
                Inventario::mover($producto, $sucursal->id_sucursal, -$cantidad, 'venta', 'Pedido web', $venta);
            }

            return $venta;
        });

        Auditoria::registrar('venta.pedido_web', "Pedido web {$venta->folio} de {$cliente->nombre} por ".dinero($venta->total), $venta, [], $cliente->id_empresa);

        return redirect()->route('mi-cuenta')
            ->with('success', "¡Pedido {$venta->folio} registrado! Págalo y recógelo en {$sucursal->nombre}.")
            ->with('pedido_ok', true);
    }

    /**
     * El cliente cancela su propio pedido mientras siga pendiente: se libera el stock.
     */
    public function cancelar(Request $request, int $venta): RedirectResponse
    {
        $cliente = Cliente::withoutGlobalScopes()->where('user_id', $request->user()->id)->firstOrFail();

        $pedido = Venta::withoutGlobalScopes()
            ->where('id_venta', $venta)
            ->where('id_cliente', $cliente->id_cliente)
            ->where('origen', 'web')
            ->firstOrFail();

        if ($pedido->estado !== 'pendiente') {
            return back()->with('error', 'Solo puedes cancelar pedidos pendientes de pago.');
        }

        DB::transaction(function () use ($pedido, $request) {
            foreach ($pedido->detalles()->whereNotNull('id_producto')->get() as $detalle) {
                $producto = Producto::withoutGlobalScopes()->find($detalle->id_producto);

                if ($producto) {
                    Inventario::mover($producto, $pedido->id_sucursal, $detalle->cantidad, 'anulacion', 'Pedido web cancelado por el cliente', $pedido);
                }
            }

            $pedido->update([
                'estado' => 'anulado',
                'anulada_por' => $request->user()->id,
                'anulada_en' => now(),
                'motivo_anulacion' => 'Cancelado por el cliente desde la web',
            ]);
        });

        Auditoria::registrar('venta.pedido_cancelado', "El cliente canceló el pedido web {$pedido->folio}", $pedido, [], $pedido->id_empresa);

        return back()->with('success', "Pedido {$pedido->folio} cancelado.");
    }
}
