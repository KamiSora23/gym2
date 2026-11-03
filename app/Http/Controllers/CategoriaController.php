<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\CategoriaGasto;
use App\Models\Gasto;
use App\Models\TipoProducto;
use App\Support\Permisos;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Categorías de productos (tienda y caja) y de gastos.
 * Una categoría en uso no se elimina: se desactiva y deja de ofrecerse en los formularios.
 */
class CategoriaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $gastos = CategoriaGasto::deLaEmpresa();
        $usoGastos = Gasto::select('categoria', DB::raw('count(*) as total'), DB::raw('sum(monto) as monto'))
            ->groupBy('categoria')->get()->keyBy('categoria');

        return view('admin.categorias', [
            'productos' => TipoProducto::withCount('productos')->orderBy('nombre')->get(),
            'gastos' => $gastos->each(function ($c) use ($usoGastos) {
                $c->usos = (int) ($usoGastos[$c->nombre]->total ?? 0);
                $c->monto = (float) ($usoGastos[$c->nombre]->monto ?? 0);
            }),
            'puedeProductos' => Permisos::puede($user, 'products.create'),
            'puedeGastos' => Permisos::puede($user, 'expenses.create'),
        ]);
    }

    // ----- Categorías de productos -----

    public function storeProducto(Request $request): RedirectResponse
    {
        Permisos::exigir('products.create');

        $datos = $request->validateWithBag('categoria', [
            'nombre' => ['required', 'string', 'max:80', Rule::unique('tipo_producto', 'nombre')->where('id_empresa', Tenant::empresaId())],
        ]);

        TipoProducto::create($datos + ['activo' => true]);

        return back()->with('success', "Categoría «{$datos['nombre']}» creada.");
    }

    public function updateProducto(Request $request, TipoProducto $categoria): RedirectResponse
    {
        Permisos::exigir('products.create');

        $datos = $request->validateWithBag('categoria_'.$categoria->id_tipo, [
            'nombre' => ['required', 'string', 'max:80', Rule::unique('tipo_producto', 'nombre')->where('id_empresa', Tenant::empresaId())->ignore($categoria->id_tipo, 'id_tipo')],
        ]);

        $categoria->update($datos);

        return back()->with('success', 'Categoría actualizada.');
    }

    public function estadoProducto(TipoProducto $categoria): RedirectResponse
    {
        Permisos::exigir('products.create');

        $categoria->update(['activo' => ! $categoria->activo]);
        Auditoria::registrar('categoria.estado', ($categoria->activo ? 'Activó' : 'Desactivó')." la categoría de productos {$categoria->nombre}", $categoria);

        return back()->with('success', $categoria->activo ? "«{$categoria->nombre}» vuelve a estar disponible." : "«{$categoria->nombre}» se desactivó: no se ofrece para productos nuevos ni aparece como filtro en la tienda.");
    }

    public function destroyProducto(TipoProducto $categoria): RedirectResponse
    {
        Permisos::exigir('products.create');

        if ($categoria->productos()->exists()) {
            return back()->with('error', "«{$categoria->nombre}» tiene productos. Muévelos a otra categoría o desactívala.");
        }

        $categoria->delete();

        return back()->with('success', 'Categoría eliminada.');
    }

    // ----- Categorías de gastos -----

    public function storeGasto(Request $request): RedirectResponse
    {
        Permisos::exigir('expenses.create');
        CategoriaGasto::deLaEmpresa();

        $datos = $request->validateWithBag('categoria_gasto', [
            'nombre' => ['required', 'string', 'max:60', Rule::unique('categorias_gasto', 'nombre')->where('id_empresa', Tenant::empresaId())],
        ]);

        CategoriaGasto::create($datos);

        return back()->with('success', "Categoría de gasto «{$datos['nombre']}» creada.");
    }

    public function updateGasto(Request $request, CategoriaGasto $categoria): RedirectResponse
    {
        Permisos::exigir('expenses.create');

        $datos = $request->validateWithBag('categoria_gasto_'.$categoria->id_categoria, [
            'nombre' => ['required', 'string', 'max:60', Rule::unique('categorias_gasto', 'nombre')->where('id_empresa', Tenant::empresaId())->ignore($categoria->id_categoria, 'id_categoria')],
        ]);

        // Los gastos guardan el nombre: se renombran también para no perder el historial.
        DB::transaction(function () use ($categoria, $datos) {
            Gasto::where('categoria', $categoria->nombre)->update(['categoria' => $datos['nombre']]);
            $categoria->update($datos);
        });

        return back()->with('success', 'Categoría de gasto actualizada.');
    }

    public function estadoGasto(CategoriaGasto $categoria): RedirectResponse
    {
        Permisos::exigir('expenses.create');

        $categoria->update(['activo' => ! $categoria->activo]);

        return back()->with('success', $categoria->activo ? "«{$categoria->nombre}» vuelve a estar disponible." : "«{$categoria->nombre}» se desactivó: ya no se ofrece al registrar gastos.");
    }

    public function destroyGasto(CategoriaGasto $categoria): RedirectResponse
    {
        Permisos::exigir('expenses.create');

        if ($categoria->gastos()->exists()) {
            return back()->with('error', "«{$categoria->nombre}» tiene gastos registrados. Desactívala en lugar de eliminarla.");
        }

        $categoria->delete();

        return back()->with('success', 'Categoría de gasto eliminada.');
    }
}
