<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsistenciaController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validateWithBag('asistencia', [
            'id_cliente' => ['required', 'exists:clientes,id_cliente'],
        ]);

        $yaRegistrado = DB::table('asistencias')
            ->where('id_cliente', $datos['id_cliente'])
            ->whereDate('fecha', today())
            ->whereNull('hora_salida')
            ->exists();

        if ($yaRegistrado) {
            return redirect()->route('dashboard', ['seccion' => 'asistencias'])
                ->with('error', 'Ese cliente ya tiene un check-in abierto hoy.');
        }

        DB::table('asistencias')->insert([
            'id_cliente' => $datos['id_cliente'],
            'fecha' => today()->toDateString(),
            'hora_entrada' => now()->format('H:i:s'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard', ['seccion' => 'asistencias'])
            ->with('success', 'Asistencia registrada correctamente.');
    }
}
