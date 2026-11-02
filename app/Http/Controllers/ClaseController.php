<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Cliente;
use App\Models\Entrenador;
use App\Models\Role;
use App\Models\User;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Horario de clases, entrenadores e inscripciones.
 */
class ClaseController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $miPerfil = $user->hasRole(Role::ENTRENADOR) ? $user->entrenador : null;

        // Un entrenador ve por defecto solo sus clases.
        $soloMias = $miPerfil && ! $request->boolean('todas');

        $clases = Clase::with(['entrenador', 'clientes'])
            ->deSucursal()
            ->when($soloMias, fn ($q) => $q->where('id_entrenador', $miPerfil->id_entrenador))
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        $entrenadores = Entrenador::withCount(['clases', 'clientes'])
            ->with(['usuario', 'sucursal'])
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->get();

        return view('admin.entrenamiento', [
            'clases' => $clases,
            'porDia' => $clases->groupBy('dia_semana'),
            'entrenadores' => $entrenadores,
            'clientes' => Cliente::orderBy('nombre')->get(['id_cliente', 'nombre', 'dni']),
            'sucursales' => Tenant::sucursales(),
            'usuariosEntrenador' => User::where('id_empresa', Tenant::empresaId())
                ->whereHas('role', fn ($q) => $q->where('nombre', Role::ENTRENADOR))
                ->orderBy('name')
                ->get(),
            'miPerfil' => $miPerfil,
            'soloMias' => $soloMias,
            'puedeGestionar' => $user->isAdmin() || $user->hasRole(Role::RECEPCIONISTA),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->soloGestion($request);

        $datos = $this->validar($request);
        $datos['id_sucursal'] = Tenant::sucursalParaRegistro($datos['id_sucursal'] ?? null);

        Clase::create($datos);

        return redirect()->route('entrenamiento.index')->with('success', 'Clase agregada al horario.');
    }

    public function update(Request $request, Clase $clase): RedirectResponse
    {
        $this->soloGestion($request);

        $datos = $this->validar($request);

        if ($datos['cupo'] < $clase->clientes()->count()) {
            return back()->with('error', 'El cupo no puede ser menor que la cantidad de inscritos.');
        }

        $datos['id_sucursal'] = Tenant::sucursalParaRegistro($datos['id_sucursal'] ?? $clase->id_sucursal);
        $clase->update($datos);

        return redirect()->route('entrenamiento.index')->with('success', 'Clase actualizada.');
    }

    public function destroy(Request $request, Clase $clase): RedirectResponse
    {
        $this->soloGestion($request);

        $clase->delete();

        return back()->with('success', 'Clase eliminada del horario.');
    }

    public function inscribir(Request $request, Clase $clase): RedirectResponse
    {
        $datos = $request->validateWithBag('inscripcion', [
            'id_cliente' => ['required', Rule::exists('clientes', 'id_cliente')->where('id_empresa', Tenant::empresaId())],
        ]);

        if ($clase->clientes()->where('clientes.id_cliente', $datos['id_cliente'])->exists()) {
            return back()->with('error', 'El cliente ya está inscrito en esta clase.');
        }

        if ($clase->clientes()->count() >= $clase->cupo) {
            return back()->with('error', "La clase {$clase->nombre} ya no tiene cupos disponibles.");
        }

        $clase->clientes()->attach($datos['id_cliente']);

        return back()->with('success', 'Cliente inscrito en la clase.');
    }

    public function desinscribir(Clase $clase, Cliente $cliente): RedirectResponse
    {
        $clase->clientes()->detach($cliente->id_cliente);

        return back()->with('success', "{$cliente->nombre} fue retirado de la clase.");
    }

    private function validar(Request $request): array
    {
        return $request->validateWithBag('clase', [
            'nombre' => ['required', 'string', 'max:100'],
            'id_entrenador' => ['nullable', Rule::exists('entrenadores', 'id_entrenador')->where('id_empresa', Tenant::empresaId())],
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'cupo' => ['required', 'integer', 'min:1', 'max:500'],
            'id_sucursal' => ['nullable', 'integer'],
        ]);
    }

    private function soloGestion(Request $request): void
    {
        abort_unless($request->user()->isAdmin() || $request->user()->hasRole(Role::RECEPCIONISTA), 403);
    }
}
