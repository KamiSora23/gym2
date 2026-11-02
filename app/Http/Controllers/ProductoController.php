<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Support\Inventario;
use App\Support\Permisos;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Catálogo de productos e inventario por sucursal.
 * Todo cambio de stock pasa por App\Support\Inventario y queda en el kardex.
 */
class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $productos = Producto::with(['tipo', 'inventario'])->orderBy('nombre')->get();
        $sucursal = Tenant::sucursalId();

        return view('admin.catalogo', [
            'productos' => $productos,
            'stockBajo' => $productos->filter(fn ($p) => $p->activo && $p->stockEn($sucursal) <= $p->stock_minimo)->count(),
            'categorias' => TipoProducto::orderBy('nombre')->get(),
            'sucursales' => Tenant::sucursales(),
            'puedeEditar' => Permisos::puede($user, 'products.create'),
            'puedePrecio' => Permisos::puede($user, 'products.edit_price'),
            'puedeEntrada' => Permisos::puede($user, 'inventory.entries'),
            'puedeSalida' => Permisos::puede($user, 'inventory.adjust'),
            'puedeKardex' => Permisos::puede($user, 'inventory.view'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Permisos::exigir('products.create');

        $datos = $this->validar($request);
        $request->validateWithBag('producto', ['stock_inicial' => ['nullable', 'integer', 'min:0', 'max:100000']]);
        $stock = $request->integer('stock_inicial');

        DB::transaction(function () use ($datos, $stock, $request) {
            $producto = Producto::create($datos + ['activo' => true]);

            if ($stock > 0 && ($sucursal = Tenant::sucursalParaRegistro((int) $request->id_sucursal))) {
                Inventario::mover($producto, $sucursal, $stock, 'entrada', 'Stock inicial');
            }

            Auditoria::registrar('producto.creado', "Creó el producto {$producto->nombre} a ".dinero($producto->precio), $producto);
        });

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        Permisos::exigir('products.create');

        $datos = $this->validar($request, $producto);
        $datos['activo'] = $request->boolean('activo');

        // Cambiar el precio de venta es un permiso aparte.
        if (round((float) $datos['precio'], 2) !== round((float) $producto->precio, 2)) {
            if (! Permisos::puede($request->user(), 'products.edit_price')) {
                return back()->with('error', 'No tienes permiso para modificar precios de venta.');
            }

            Auditoria::registrar('producto.precio', "Cambió el precio de {$producto->nombre} de ".dinero($producto->precio).' a '.dinero($datos['precio']), $producto);
        }

        $producto->update($datos);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        Permisos::exigir('products.create');

        if ($producto->inventario()->where('cantidad', '>', 0)->exists() || DB::table('venta_detalle')->where('id_producto', $producto->id_producto)->exists()) {
            $producto->update(['activo' => false]);

            return back()->with('info', 'El producto tiene stock o ventas: se desactivó en lugar de eliminarse.');
        }

        $producto->delete();

        return back()->with('success', 'Producto eliminado.');
    }

    /**
     * Entrada de mercadería (positiva) o salida/merma/ajuste (negativa, con motivo).
     */
    public function stock(Request $request, Producto $producto): RedirectResponse
    {
        $datos = $request->validateWithBag('stock', [
            'cantidad' => ['required', 'integer', 'not_in:0', 'min:-100000', 'max:100000'],
            'id_sucursal' => ['nullable', 'integer'],
            'tipo' => ['nullable', Rule::in(['merma', 'ajuste', 'salida'])],
            'motivo' => ['nullable', 'string', 'max:150'],
        ]);

        $entrada = $datos['cantidad'] > 0;
        Permisos::exigir($entrada ? 'inventory.entries' : 'inventory.adjust');

        if (! $entrada && blank($datos['motivo'] ?? null)) {
            return back()->with('error', 'Indica el motivo de la salida o ajuste de stock.');
        }

        $sucursal = Tenant::sucursalParaRegistro($datos['id_sucursal'] ?? null);

        if (! $sucursal) {
            return back()->with('error', 'La empresa no tiene sucursales activas.');
        }

        $actual = $producto->stockEn($sucursal);

        if ($actual + $datos['cantidad'] < 0) {
            return back()->with('error', "No puedes retirar más de lo que hay en stock ({$actual}).");
        }

        $tipo = $entrada ? 'entrada' : ($datos['tipo'] ?? 'ajuste');

        DB::transaction(fn () => Inventario::mover($producto, $sucursal, (int) $datos['cantidad'], $tipo, $datos['motivo'] ?? null));

        if (! $entrada) {
            Auditoria::registrar('inventario.ajuste', ucfirst($tipo)." de {$datos['cantidad']} unid. de {$producto->nombre}: {$datos['motivo']}", $producto);
        }

        return back()->with('success', "Stock de {$producto->nombre} actualizado.");
    }

    /**
     * Kardex: historial de movimientos de inventario.
     */
    public function movimientos(Request $request): View
    {
        Permisos::exigir('inventory.view');

        return view('admin.kardex', [
            'movimientos' => InventarioMovimiento::with(['producto', 'usuario', 'sucursal'])
                ->deSucursal()
                ->when($request->filled('producto'), fn ($q) => $q->where('id_producto', $request->producto))
                ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
                ->orderByDesc('id')
                ->paginate(30)
                ->withQueryString(),
            'productos' => Producto::orderBy('nombre')->get(['id_producto', 'nombre']),
        ]);
    }

    public function categoria(Request $request): RedirectResponse
    {
        Permisos::exigir('products.create');

        $datos = $request->validateWithBag('categoria', [
            'nombre' => ['required', 'string', 'max:80', Rule::unique('tipo_producto', 'nombre')->where('id_empresa', Tenant::empresaId())],
        ]);

        TipoProducto::create($datos);

        return back()->with('success', 'Categoría creada.');
    }

    private function validar(Request $request, ?Producto $producto = null): array
    {
        $datos = $request->validateWithBag('producto', [
            'nombre' => ['required', 'string', 'max:150'],
            'sku' => ['nullable', 'string', 'max:40', Rule::unique('productos', 'sku')->where('id_empresa', Tenant::empresaId())->ignore($producto?->id_producto, 'id_producto')],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio' => ['required', 'numeric', 'min:0', 'max:999999'],
            'precio_compra' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'stock_minimo' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'id_tipo' => ['required', Rule::exists('tipo_producto', 'id_tipo')->where('id_empresa', Tenant::empresaId())],
        ]);

        $datos['precio_compra'] ??= $producto->precio_compra ?? 0;
        $datos['stock_minimo'] ??= $producto->stock_minimo ?? 5;

        return $datos;
    }
}
