<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Cliente;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AsistenciaController extends Controller
{
    public function index(Request $request): View
    {
        $fecha = $request->date('fecha') ?? today();

        $asistencias = Asistencia::with(['cliente.membresiaActiva', 'sucursal'])
            ->deSucursal()
            ->whereDate('fecha', $fecha)
            ->orderByDesc('hora_entrada')
            ->get();

        return view('admin.asistencias', [
            'fecha' => Carbon::parse($fecha),
            'asistencias' => $asistencias,
            'enGym' => $asistencias->whereNull('hora_salida')->count(),
            'clientes' => Cliente::with('membresiaActiva')->orderBy('nombre')->get(['id_cliente', 'nombre', 'dni']),
            'sucursales' => Tenant::sucursales(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validateWithBag('asistencia', [
            'id_cliente' => ['required', Rule::exists('clientes', 'id_cliente')->where('id_empresa', Tenant::empresaId())],
            'id_sucursal' => ['nullable', 'integer'],
        ]);

        $abierta = Asistencia::where('id_cliente', $datos['id_cliente'])
            ->whereDate('fecha', today())
            ->whereNull('hora_salida')
            ->exists();

        if ($abierta) {
            return back()->with('error', 'Ese cliente ya tiene un check-in abierto hoy.');
        }

        $cliente = Cliente::with('membresiaActiva')->find($datos['id_cliente']);

        Asistencia::create([
            'id_sucursal' => Tenant::sucursalParaRegistro($datos['id_sucursal'] ?? null),
            'id_cliente' => $cliente->id_cliente,
            'fecha' => today(),
            'hora_entrada' => now()->format('H:i:s'),
        ]);

        $mensaje = "Check-in de {$cliente->nombre} registrado.";

        return $cliente->membresiaActiva
            ? back()->with('success', $mensaje)
            : back()->with('info', $mensaje.' Atención: el cliente no tiene una membresía vigente.');
    }

    public function salida(Asistencia $asistencia): RedirectResponse
    {
        if ($asistencia->hora_salida) {
            return back()->with('error', 'La salida ya estaba registrada.');
        }

        $asistencia->update(['hora_salida' => now()->format('H:i:s')]);

        return back()->with('success', 'Salida registrada.');
    }
}
