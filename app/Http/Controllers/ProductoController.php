<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Support\Permisos;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Catálogo de productos e inventario por sucursal.
 */
class ProductoController extends Controller
{
    public function index(): View
    {
        $productos = Producto::with(['tipo', 'inventario'])->orderBy('nombre')->get();

        return view('admin.catalogo', [
            'productos' => $productos,
            'categorias' => TipoProducto::orderBy('nombre')->get(),
            'sucursales' => Tenant::sucursales(),
            'puedeEditar' => auth()->user()->permite('catalogo.editar'),
            'puedeEntrada' => auth()->user()->permite('stock.entrada'),
            'puedeSalida' => auth()->user()->permite('stock.salida'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->soloAdmin();

        $datos = $this->validar($request);
        $request->validateWithBag('producto', ['stock_inicial' => ['nullable', 'integer', 'min:0', 'max:100000']]);
        $stock = $request->integer('stock_inicial');

        $producto = Producto::create($datos + ['activo' => true]);

        if ($stock > 0 && ($sucursal = Tenant::sucursalParaRegistro((int) $request->id_sucursal))) {
            $this->ajustarStock($producto, $sucursal, $stock);
        }

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $this->soloAdmin();

        $datos = $this->validar($request);
        $datos['activo'] = $request->boolean('activo');
        $producto->update($datos);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $this->soloAdmin();

        if ($producto->inventario()->where('cantidad', '>', 0)->exists() || DB::table('venta_detalle')->where('id_producto', $producto->id_producto)->exists()) {
            $producto->update(['activo' => false]);

            return back()->with('info', 'El producto tiene stock o ventas: se desactivó en lugar de eliminarse.');
        }

        $producto->delete();

        return back()->with('success', 'Producto eliminado.');
    }

    /**
     * Entrada (o ajuste negativo) de stock en una sucursal.
     */
    public function stock(Request $request, Producto $producto): RedirectResponse
    {
        $datos = $request->validateWithBag('stock', [
            'cantidad' => ['required', 'integer', 'not_in:0', 'min:-100000', 'max:100000'],
            'id_sucursal' => ['nullable', 'integer'],
        ]);

        Permisos::exigir($datos['cantidad'] > 0 ? 'stock.entrada' : 'stock.salida');

        $sucursal = Tenant::sucursalParaRegistro($datos['id_sucursal'] ?? null);

        if (! $sucursal) {
            return back()->with('error', 'La empresa no tiene sucursales activas.');
        }

        $actual = $producto->stockEn($sucursal);

        if ($actual + $datos['cantidad'] < 0) {
            return back()->with('error', "No puedes retirar más de lo que hay en stock ({$actual}).");
        }

        $this->ajustarStock($producto, $sucursal, (int) $datos['cantidad']);

        return back()->with('success', "Stock de {$producto->nombre} actualizado.");
    }

    public function categoria(Request $request): RedirectResponse
    {
        $this->soloAdmin();

        $datos = $request->validateWithBag('categoria', [
            'nombre' => ['required', 'string', 'max:80', Rule::unique('tipo_producto', 'nombre')->where('id_empresa', Tenant::empresaId())],
        ]);

        TipoProducto::create($datos);

        return back()->with('success', 'Categoría creada.');
    }

    public static function ajustarStock(Producto $producto, int $sucursalId, int $cantidad): void
    {
        $fila = Inventario::firstOrCreate(
            ['id_producto' => $producto->id_producto, 'id_sucursal' => $sucursalId],
            ['cantidad' => 0, 'precio_compra' => 0],
        );

        $fila->increment('cantidad', $cantidad);
    }

    private function validar(Request $request): array
    {
        return $request->validateWithBag('producto', [
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio' => ['required', 'numeric', 'min:0', 'max:999999'],
            'id_tipo' => ['required', Rule::exists('tipo_producto', 'id_tipo')->where('id_empresa', Tenant::empresaId())],
        ]);
    }

    private function soloAdmin(): void
    {
        Permisos::exigir('catalogo.editar');
    }
}
