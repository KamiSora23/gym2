<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GastoController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validateWithBag('gasto', [
            'concepto' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
        ]);

        DB::table('gastos')->insert($datos + [
            'user_id' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard', ['seccion' => 'gastos'])
            ->with('success', 'Gasto registrado correctamente.');
    }
}
