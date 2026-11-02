<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\User;
use App\Models\Venta;
use App\Support\CajaService;
use App\Support\Permisos;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Caja por sesiones: cada empleado abre su turno, registra movimientos,
 * hace el arqueo por método de pago y cierra. Las diferencias las aprueba un supervisor.
 */
class CajaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $sesion = CajaService::sesionAbierta($user)?->load(['movimientos.usuario', 'caja']);
        $veTodas = Permisos::puede($user, 'cash.view_all');

        $historial = CajaSesion::with(['usuario', 'caja', 'revisor'])
            ->deSucursal()
            ->when(! $veTodas, fn ($q) => $q->where('user_id', $user->id))
            ->where('estado', '!=', 'abierta')
            ->orderByDesc('cerrada_en')
            ->paginate(15);

        return view('admin.caja', [
            'sesion' => $sesion,
            'esperado' => $sesion?->saldoEsperado(),
            'cajas' => Caja::with('sesionAbierta.usuario', 'sucursal')->deSucursal()->where('activo', true)->orderBy('nombre')->get(),
            'abiertas' => $veTodas
                ? CajaSesion::with(['usuario', 'caja'])->deSucursal()->where('estado', 'abierta')->where('user_id', '!=', $user->id)->get()
                : collect(),
            'pendientes' => Permisos::puede($user, 'cash.approve')
                ? CajaSesion::with(['usuario', 'caja'])->deSucursal()->where('estado', 'observada')->where('user_id', '!=', $user->id)->get()
                : collect(),
            'historial' => $historial,
            'veTodas' => $veTodas,
            'puedeOperar' => Permisos::puede($user, 'cash.open'),
            'puedeMovimientos' => Permisos::puede($user, 'cash.movements'),
            'sucursales' => Tenant::sucursales(),
        ]);
    }

    public function abrir(Request $request): RedirectResponse
    {
        Permisos::exigir('cash.open');
        $user = $request->user();

        $datos = $request->validateWithBag('caja', [
            'id_caja' => ['required', Rule::exists('cajas', 'id_caja')->where('id_empresa', Tenant::empresaId())->where('activo', true)],
            'monto_inicial' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ]);

        if (CajaService::sesionAbierta($user)) {
            return back()->with('error', 'Ya tienes una caja abierta. Ciérrala antes de abrir otra.');
        }

        $caja = Caja::findOrFail($datos['id_caja']);

        // El personal de una sucursal solo opera las cajas de su sucursal.
        if ($user->id_sucursal && (int) $caja->id_sucursal !== (int) $user->id_sucursal) {
            abort(403, 'Esa caja pertenece a otra sucursal.');
        }

        $sesion = DB::transaction(function () use ($caja, $user, $datos) {
            if (CajaSesion::where('id_caja', $caja->id_caja)->where('estado', 'abierta')->lockForUpdate()->exists()) {
                return null;
            }

            return CajaSesion::create([
                'id_empresa' => $caja->id_empresa,
                'id_sucursal' => $caja->id_sucursal,
                'id_caja' => $caja->id_caja,
                'user_id' => $user->id,
                'monto_inicial' => $datos['monto_inicial'],
                'abierta_en' => now(),
                'estado' => 'abierta',
            ]);
        });

        if (! $sesion) {
            return back()->with('error', "La {$caja->nombre} ya está siendo usada por otro empleado.");
        }

        Auditoria::registrar('caja.abierta', "Abrió {$caja->nombre} con ".dinero($datos['monto_inicial']), $sesion);

        return redirect()->route('caja.index')->with('success', 'Caja abierta con un fondo de '.dinero($datos['monto_inicial']).'.');
    }

    public function movimiento(Request $request): RedirectResponse
    {
        Permisos::exigir('cash.movements');
        $sesion = CajaService::exigirSesion($request->user());

        $datos = $request->validateWithBag('movimiento', [
            'tipo' => ['required', Rule::in(['ingreso', 'egreso'])],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'metodo_pago' => ['required', Rule::in(array_keys(Venta::METODOS_PAGO))],
            'concepto' => ['required', 'string', 'max:150'],
        ]);

        if ($datos['tipo'] === 'egreso' && $sesion->load('movimientos')->saldoEsperado()[$datos['metodo_pago']] < $datos['monto']) {
            return back()->with('error', 'No hay saldo suficiente en caja para ese egreso.');
        }

        $metodo = $datos['tipo'] === 'ingreso' ? 'ingreso' : 'egreso';
        CajaService::$metodo($sesion, (float) $datos['monto'], $datos['metodo_pago'], 'manual', $datos['concepto']);

        return back()->with('success', ucfirst($datos['tipo']).' registrado.');
    }

    /**
     * Arqueo y cierre: el empleado declara lo que contó por cada método de pago.
     */
    public function cerrar(Request $request): RedirectResponse
    {
        Permisos::exigir('cash.open');
        $sesion = CajaService::exigirSesion($request->user())->load('movimientos');

        $reglas = ['observaciones' => ['nullable', 'string', 'max:500']];
        foreach (array_keys(Venta::METODOS_PAGO) as $metodo) {
            $reglas["contado.{$metodo}"] = ['required', 'numeric', 'min:0', 'max:10000000'];
        }
        $datos = $request->validateWithBag('cierre', $reglas);

        $esperado = $sesion->saldoEsperado();
        $contado = array_map(fn ($v) => round((float) $v, 2), $datos['contado']);
        $diferencia = round(array_sum($contado) - array_sum($esperado), 2);
        $cuadra = collect($esperado)->every(fn ($valor, $metodo) => abs(($contado[$metodo] ?? 0) - $valor) < 0.005);

        $sesion->update([
            'cerrada_en' => now(),
            'esperado' => $esperado,
            'contado' => $contado,
            'diferencia' => $diferencia,
            'estado' => $cuadra ? 'cuadrada' : 'observada',
            'observaciones' => $datos['observaciones'] ?? null,
        ]);

        Auditoria::registrar('caja.cerrada', 'Cerró caja: esperado '.dinero(array_sum($esperado)).', contado '.dinero(array_sum($contado)).', diferencia '.dinero($diferencia), $sesion, compact('esperado', 'contado'));

        return redirect()->route('caja.sesion', $sesion)->with(
            $cuadra ? 'success' : 'info',
            $cuadra ? 'Caja cerrada sin diferencias.' : 'Caja cerrada con una diferencia de '.dinero($diferencia).'. Queda pendiente de revisión de un supervisor.'
        );
    }

    public function show(Request $request, CajaSesion $sesion): View
    {
        $this->autorizarVer($request->user(), $sesion);

        return view('admin.caja-sesion', [
            'sesion' => $sesion->load(['movimientos.usuario', 'usuario', 'caja', 'revisor']),
            'esperadoActual' => $sesion->estaAbierta() ? $sesion->saldoEsperado() : $sesion->esperado,
            'puedeAprobar' => $sesion->estado === 'observada'
                && Permisos::puede($request->user(), 'cash.approve')
                && (int) $sesion->user_id !== $request->user()->id,
        ]);
    }

    /**
     * Un supervisor revisa y aprueba la diferencia. Nadie aprueba su propia caja.
     */
    public function aprobar(Request $request, CajaSesion $sesion): RedirectResponse
    {
        Permisos::exigir('cash.approve');
        Tenant::exigirSucursal($sesion->id_sucursal);

        abort_if((int) $sesion->user_id === $request->user()->id, 403, 'No puedes aprobar las diferencias de tu propia caja.');

        if ($sesion->estado !== 'observada') {
            return back()->with('error', 'Esta sesión no tiene diferencias pendientes.');
        }

        $datos = $request->validate(['nota_revision' => ['required', 'string', 'max:500']], [
            'nota_revision.required' => 'Explica brevemente la revisión de la diferencia.',
        ]);

        $sesion->update([
            'estado' => 'aprobada',
            'revisado_por' => $request->user()->id,
            'revisado_en' => now(),
            'nota_revision' => $datos['nota_revision'],
        ]);

        Auditoria::registrar('caja.aprobada', 'Aprobó diferencia de '.dinero($sesion->diferencia)." de {$sesion->usuario?->name}: {$datos['nota_revision']}", $sesion);

        return back()->with('success', 'Diferencia aprobada.');
    }

    public function crearCaja(Request $request): RedirectResponse
    {
        Permisos::exigir('cash.manage');

        $datos = $request->validateWithBag('nuevaCaja', [
            'nombre' => ['required', 'string', 'max:60'],
            'id_sucursal' => ['required', Rule::in(Tenant::sucursales()->pluck('id_sucursal'))],
        ]);

        $caja = Caja::create($datos + ['activo' => true]);
        Auditoria::registrar('caja.creada', "Creó la caja {$caja->nombre}", $caja);

        return back()->with('success', "Caja «{$caja->nombre}» creada.");
    }

    private function autorizarVer(User $user, CajaSesion $sesion): void
    {
        Tenant::exigirSucursal($sesion->id_sucursal);

        abort_unless((int) $sesion->user_id === $user->id || Permisos::puede($user, 'cash.view_all'), 403, 'Solo puedes ver tu propia caja.');
    }
}
