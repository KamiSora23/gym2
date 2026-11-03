<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\Entrenador;
use App\Models\Membresia;
use App\Models\Role;
use App\Models\User;
use App\Support\Alcance;
use App\Support\Permisos;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $soloAsignados = ! Alcance::veTodosLosClientes($user);

        // Con "clients.view_assigned" solo aparecen sus alumnos o pacientes asignados.
        $clientes = Alcance::clientes($user, Cliente::query())
            ->with(['membresiaActiva.plan', 'entrenador', 'nutricionista'])
            ->withCount('clases')
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('nombre', 'like', $texto)->orWhere('dni', 'like', $texto)->orWhere('correo', 'like', $texto));
            })
            ->when($request->input('estado') === 'inactivos', fn ($q) => $q->where('activo', false), fn ($q) => $q->where('activo', true))
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        return view('admin.clientes', [
            'clientes' => $clientes,
            'puedeCrear' => $user->permite('clients.create'),
            'puedeEditar' => $user->permite('clients.edit'),
            'puedeEliminar' => $user->permite('clients.delete'),
            'puedeAsignarCoach' => $user->permite('clients.assign'),
            'esCoach' => $soloAsignados,
            'entrenadores' => Entrenador::where('activo', true)->orderBy('nombre')->get(),
            'nutricionistas' => $this->nutricionistas(),
            'resumen' => [
                'total' => Cliente::where('activo', true)->count(),
                'inactivos' => Cliente::where('activo', false)->count(),
                'activos' => Cliente::whereHas('membresiaActiva')->count(),
                'nuevos' => Cliente::where('created_at', '>=', today()->startOfMonth())->count(),
                'con_coach' => Cliente::whereNotNull('id_entrenador')->count(),
            ],
        ]);
    }

    /**
     * Ficha del cliente: membresía, clases, rutinas y progreso.
     */
    public function show(Request $request, Cliente $cliente): View
    {
        $user = $request->user();
        Alcance::exigirCliente($user, $cliente);

        $cliente->load(['entrenador', 'nutricionista', 'membresiaActiva.plan', 'clases.entrenador', 'rutinas.ejercicios', 'rutinas.entrenador', 'progresos']);

        // Los datos nutricionales son sensibles: solo los ve quien tiene "nutrition.view".
        $veNutricion = $user->permite('nutrition.view');

        return view('admin.cliente-ficha', [
            'cliente' => $cliente,
            'membresias' => $cliente->membresias()->with('plan')->orderByDesc('fecha_fin')->limit(6)->get(),
            'asistencias' => Asistencia::where('id_cliente', $cliente->id_cliente)->orderByDesc('fecha')->limit(10)->get(),
            'puedeRutinas' => $user->permite('routines.manage'),
            'puedeEvaluar' => $user->permite('evaluations.manage'),
            'puedeEditar' => $user->permite('clients.edit'),
            'puedeCongelar' => $user->permite('memberships.freeze'),
            'veNutricion' => $veNutricion,
            'puedeNutricion' => $user->permite('nutrition.manage'),
            'planesNutricionales' => $veNutricion ? $cliente->planesNutricionales()->with('nutricionista')->get() : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Permisos::exigir('clients.create');

        $cliente = Cliente::create($this->validar($request));
        Auditoria::registrar('cliente.creado', "Registró al cliente {$cliente->nombre}", $cliente);

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado correctamente.');
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        Permisos::exigir('clients.edit');
        Alcance::exigirCliente($request->user(), $cliente);

        $cliente->update($this->validar($request, $cliente));

        return redirect()->route('clientes.index', $request->only('q', 'page'))->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Desactiva o reactiva al cliente. Un cliente inactivo no aparece en caja, asistencias ni clases.
     */
    public function estado(Request $request, Cliente $cliente): RedirectResponse
    {
        Permisos::exigir('clients.delete');

        $cliente->update(['activo' => ! $cliente->activo]);
        Auditoria::registrar($cliente->activo ? 'cliente.reactivado' : 'cliente.desactivado', ($cliente->activo ? 'Reactivó' : 'Desactivó')." al cliente {$cliente->nombre}", $cliente);

        return back()->with('success', $cliente->activo ? "{$cliente->nombre} fue reactivado." : "{$cliente->nombre} fue desactivado. Su historial se conserva.");
    }

    public function destroy(Request $request, Cliente $cliente): RedirectResponse
    {
        Permisos::exigir('clients.delete');

        // Con historial (ventas, membresías, asistencias) solo se desactiva.
        if ($cliente->tieneHistorial()) {
            $cliente->update(['activo' => false]);
            Auditoria::registrar('cliente.desactivado', "Desactivó al cliente {$cliente->nombre}", $cliente);

            return back()->with('info', "{$cliente->nombre} tiene historial: se desactivó en lugar de eliminarse.");
        }

        $cliente->delete();
        Auditoria::registrar('cliente.eliminado', "Eliminó al cliente {$cliente->nombre}", $cliente);

        return back()->with('success', 'Cliente eliminado.');
    }

    /**
     * Congela la membresía vigente: deja de contar el tiempo hasta que se reanude.
     */
    public function congelar(Request $request, Membresia $membresia): RedirectResponse
    {
        Permisos::exigir('memberships.freeze');
        $cliente = Cliente::findOrFail($membresia->id_cliente);

        if ($membresia->estado !== 'activo' || $membresia->fecha_fin->lt(today())) {
            return back()->with('error', 'Solo se puede congelar una membresía vigente.');
        }

        $membresia->update(['estado' => 'congelada', 'congelada_desde' => today()]);
        Auditoria::registrar('membresia.congelada', "Congeló la membresía de {$cliente->nombre}", $cliente);

        return back()->with('success', 'Membresía congelada. Los días no se descuentan hasta que se reanude.');
    }

    /**
     * Reanuda la membresía y alarga su vencimiento los días que estuvo congelada.
     */
    public function reanudar(Request $request, Membresia $membresia): RedirectResponse
    {
        Permisos::exigir('memberships.freeze');
        $cliente = Cliente::findOrFail($membresia->id_cliente);

        if ($membresia->estado !== 'congelada') {
            return back()->with('error', 'La membresía no está congelada.');
        }

        $dias = (int) $membresia->congelada_desde->diffInDays(today());

        $membresia->update([
            'estado' => 'activo',
            'fecha_fin' => $membresia->fecha_fin->copy()->addDays($dias),
            'dias_congelados' => $membresia->dias_congelados + $dias,
            'congelada_desde' => null,
        ]);

        Auditoria::registrar('membresia.reanudada', "Reanudó la membresía de {$cliente->nombre} (+{$dias} días)", $cliente);

        return back()->with('success', "Membresía reanudada. Se sumaron {$dias} días a su vencimiento.");
    }

    private function nutricionistas()
    {
        return User::where('id_empresa', Tenant::empresaId())
            ->whereHas('role', fn ($q) => $q->where('nombre', Role::NUTRICIONISTA))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function noCoach(Request $request): void
    {
        Permisos::exigir('clients.edit');
    }

    private function validar(Request $request, ?Cliente $cliente = null): array
    {
        $datos = $request->validateWithBag('cliente', [
            'nombre' => ['required', 'string', 'max:150'],
            'dni' => [
                'required', 'string', 'max:20',
                Rule::unique('clientes', 'dni')
                    ->where('id_empresa', Tenant::empresaId())
                    ->ignore($cliente?->id_cliente, 'id_cliente'),
            ],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:150'],
            'id_entrenador' => ['nullable', Rule::exists('entrenadores', 'id_entrenador')->where('id_empresa', Tenant::empresaId())],
            'id_nutricionista' => ['nullable', Rule::in($this->nutricionistas()->pluck('id'))],
        ]);

        // Asignar coach o nutricionista es un permiso aparte.
        if (! $request->user()->permite('clients.assign')) {
            unset($datos['id_entrenador'], $datos['id_nutricionista']);
        }

        return $datos;
    }
}
