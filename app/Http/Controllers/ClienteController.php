<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Cliente;
use App\Models\Entrenador;
use App\Models\Role;
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
        $esCoach = $user->hasRole(Role::ENTRENADOR);
        $perfil = $esCoach ? $user->entrenador : null;

        $clientes = Cliente::with(['membresiaActiva.plan', 'entrenador'])
            ->withCount('clases')
            // El coach solo ve a sus alumnos: los que entrena en persona o los inscritos en sus clases.
            ->when($esCoach, fn ($q) => $q->where(function ($w) use ($perfil) {
                $id = $perfil?->id_entrenador ?? 0;
                $w->where('id_entrenador', $id)->orWhereHas('clases', fn ($c) => $c->where('clases.id_entrenador', $id));
            }))
            ->when($request->filled('q'), function ($q) use ($request) {
                $texto = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('nombre', 'like', $texto)->orWhere('dni', 'like', $texto)->orWhere('correo', 'like', $texto));
            })
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        return view('admin.clientes', [
            'clientes' => $clientes,
            'puedeEditar' => $user->permite('clientes.crear'),
            'puedeEliminar' => $user->permite('clientes.eliminar'),
            'puedeAsignarCoach' => $user->permite('clientes.asignar_coach'),
            'esCoach' => $esCoach,
            'entrenadores' => Entrenador::where('activo', true)->orderBy('nombre')->get(),
            'resumen' => [
                'total' => Cliente::count(),
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

        if ($user->hasRole(Role::ENTRENADOR)) {
            abort_unless($user->entrenador && $user->entrenador->esAlumno($cliente), 403, 'Solo puedes ver a tus alumnos.');
        }

        $cliente->load(['entrenador', 'membresiaActiva.plan', 'clases.entrenador', 'rutinas.ejercicios', 'rutinas.entrenador', 'progresos']);

        return view('admin.cliente-ficha', [
            'cliente' => $cliente,
            'membresias' => $cliente->membresias()->with('plan')->orderByDesc('fecha_fin')->limit(6)->get(),
            'asistencias' => Asistencia::where('id_cliente', $cliente->id_cliente)->orderByDesc('fecha')->limit(10)->get(),
            'puedeRutinas' => $user->permite('rutinas.gestionar'),
            'puedeEditar' => $user->permite('clientes.crear'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->noCoach($request);

        Cliente::create($this->validar($request));

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado correctamente.');
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $this->noCoach($request);

        $cliente->update($this->validar($request, $cliente));

        return redirect()->route('clientes.index', $request->only('q', 'page'))->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Request $request, Cliente $cliente): RedirectResponse
    {
        Permisos::exigir('clientes.eliminar');

        if ($cliente->ventas()->exists()) {
            return back()->with('error', 'El cliente tiene ventas registradas y no se puede eliminar.');
        }

        $cliente->delete();

        return back()->with('success', 'Cliente eliminado.');
    }

    private function noCoach(Request $request): void
    {
        Permisos::exigir('clientes.crear');
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
        ]);

        // Solo admin y recepción asignan el coach personal.
        if (! $request->user()->permite('clientes.asignar_coach')) {
            unset($datos['id_entrenador']);
        }

        return $datos;
    }
}
