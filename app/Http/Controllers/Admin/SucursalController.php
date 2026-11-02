<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Sucursales (sedes) de la empresa actual.
 */
class SucursalController extends Controller
{
    public function index(): View
    {
        $sucursales = Sucursal::withCount('usuarios')->orderBy('nombre')->get();

        $ventas = DB::table('ventas')
            ->whereIn('id_sucursal', $sucursales->pluck('id_sucursal'))
            ->where('estado', '!=', 'anulado')
            ->where('fecha', '>=', today()->startOfMonth()->toDateString())
            ->groupBy('id_sucursal')
            ->pluck(DB::raw('sum(total)'), 'id_sucursal');

        return view('admin.sucursales', compact('sucursales', 'ventas'));
    }

    public function store(Request $request): RedirectResponse
    {
        Sucursal::create($this->validar($request) + ['activo' => true]);

        return redirect()->route('sucursales.index')->with('success', 'Sucursal creada correctamente.');
    }

    public function update(Request $request, Sucursal $sucursal): RedirectResponse
    {
        $datos = $this->validar($request);
        $datos['activo'] = $request->boolean('activo');

        if (! $datos['activo'] && Sucursal::where('activo', true)->count() <= 1 && $sucursal->activo) {
            return back()->with('error', 'La empresa debe tener al menos una sucursal activa.');
        }

        $sucursal->update($datos);

        return redirect()->route('sucursales.index')->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Sucursal $sucursal): RedirectResponse
    {
        if (Sucursal::count() <= 1) {
            return back()->with('error', 'La empresa debe tener al menos una sucursal.');
        }

        $enUso = $sucursal->usuarios()->exists()
            || DB::table('ventas')->where('id_sucursal', $sucursal->id_sucursal)->exists()
            || DB::table('asistencias')->where('id_sucursal', $sucursal->id_sucursal)->exists();

        if ($enUso) {
            return back()->with('error', 'La sucursal tiene usuarios, ventas o asistencias. Desactívala en lugar de eliminarla.');
        }

        $sucursal->delete();

        return redirect()->route('sucursales.index')->with('success', 'Sucursal eliminada.');
    }

    private function validar(Request $request): array
    {
        return $request->validateWithBag('sucursal', [
            'nombre' => ['required', 'string', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);
    }
}
