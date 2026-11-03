<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Support\Permisos;
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
            'clientes' => Cliente::with('membresiaActiva')->where('activo', true)->orderBy('nombre')->get(['id_cliente', 'nombre', 'dni']),
            'sucursales' => Tenant::sucursales(),
            'checkinQr' => Configuracion::actual()->checkin_qr,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Permisos::exigir('attendance.create');

        $datos = $request->validateWithBag('asistencia', [
            'id_cliente' => ['required', Rule::exists('clientes', 'id_cliente')->where('id_empresa', Tenant::empresaId())->where('activo', true)],
            'id_sucursal' => ['nullable', 'integer'],
            'cortesia' => ['nullable', 'boolean'],
            'motivo' => ['nullable', 'required_if:cortesia,1', 'string', 'max:150'],
        ], [
            'motivo.required_if' => 'Indica el motivo del pase (pase diario, clase de prueba, cortesía…).',
        ]);

        if ($this->entradaAbierta($datos['id_cliente'])) {
            return back()->with('error', 'Ese cliente ya tiene un check-in abierto hoy.');
        }

        return $this->registrarEntrada(
            Cliente::with('membresiaActiva')->find($datos['id_cliente']),
            $datos['id_sucursal'] ?? null,
            (bool) ($datos['cortesia'] ?? false),
            $datos['motivo'] ?? null,
        );
    }

    /**
     * Check-in con el código QR del socio (cámara o lector USB). Si ya está dentro, registra su salida.
     */
    public function qr(Request $request): RedirectResponse
    {
        Permisos::exigir('attendance.create');
        abort_unless(Configuracion::actual()->checkin_qr, 404);

        $datos = $request->validate(['codigo' => ['required', 'string', 'max:120']], ['codigo.required' => 'Escanea o escribe el código del socio.']);

        // El lector puede enviar la URL completa o solo el código.
        $codigo = strtoupper(trim(basename(trim($datos['codigo']))));
        $cliente = Cliente::with('membresiaActiva')->where('codigo_acceso', $codigo)->first();

        if (! $cliente) {
            return back()->with('error', 'Código QR no reconocido. Pide al socio que muestre el pase de su cuenta o búscalo por nombre.');
        }

        if (! $cliente->activo) {
            return back()->with('error', "{$cliente->nombre} está dado de baja. Reactívalo desde Clientes para registrar su entrada.");
        }

        if ($abierta = $this->entradaAbierta($cliente->id_cliente)) {
            $abierta->update(['hora_salida' => now()->format('H:i:s')]);

            return back()->with('success', "Salida de {$cliente->nombre} registrada.");
        }

        return $this->registrarEntrada($cliente, $request->integer('id_sucursal') ?: null, false, null);
    }

    private function entradaAbierta(int $idCliente): ?Asistencia
    {
        return Asistencia::where('id_cliente', $idCliente)
            ->whereDate('fecha', today())
            ->whereNull('hora_salida')
            ->first();
    }

    private function registrarEntrada(Cliente $cliente, ?int $sucursal, bool $cortesia, ?string $motivo): RedirectResponse
    {
        // Control de acceso: sin membresía vigente no entra, salvo un pase autorizado y registrado.
        if (! $cliente->membresiaActiva && ! $cortesia) {
            $congelada = $cliente->membresias()->where('estado', 'congelada')->exists();

            return back()
                ->with('error', $congelada
                    ? "Acceso denegado: la membresía de {$cliente->nombre} está congelada. Reanúdala desde su ficha."
                    : "Acceso denegado: {$cliente->nombre} no tiene una membresía vigente.")
                ->with('sin_membresia', ['id' => $cliente->id_cliente, 'nombre' => $cliente->nombre]);
        }

        Asistencia::create([
            'id_sucursal' => Tenant::sucursalParaRegistro($sucursal),
            'id_cliente' => $cliente->id_cliente,
            'fecha' => today(),
            'hora_entrada' => now()->format('H:i:s'),
        ]);

        if (! $cliente->membresiaActiva) {
            Auditoria::registrar('asistencia.pase', "Dejó pasar a {$cliente->nombre} sin membresía vigente: {$motivo}", $cliente);

            return back()->with('info', "Entrada de {$cliente->nombre} registrada como pase: {$motivo}.");
        }

        $dias = (int) today()->diffInDays($cliente->membresiaActiva->fecha_fin, false);
        $mensaje = "Check-in de {$cliente->nombre} registrado.";

        return $dias <= 7
            ? back()->with('success', $mensaje)->with('info', 'Su membresía vence '.($dias === 0 ? 'hoy' : "en {$dias} día(s)").': ofrécele renovar.')
            : back()->with('success', $mensaje);
    }

    public function salida(Asistencia $asistencia): RedirectResponse
    {
        Permisos::exigir('attendance.create');

        if ($asistencia->hora_salida) {
            return back()->with('error', 'La salida ya estaba registrada.');
        }

        $asistencia->update(['hora_salida' => now()->format('H:i:s')]);

        return back()->with('success', 'Salida registrada.');
    }
}
