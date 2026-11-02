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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Horario de clases, coaches e inscripciones.
 *
 * - Admin y recepción gestionan cualquier clase.
 * - El coach crea, edita y elimina solo sus propias clases, e inscribe alumnos en ellas.
 */
class ClaseController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        // Quien gestiona "sus propias" clases y está vinculado a un coach trabaja con su perfil.
        $miPerfil = ! $user->permite('classes.manage') && $user->permite('classes.manage_own')
            ? $user->entrenador?->load('disponibilidad')
            : null;

        // Un entrenador ve por defecto solo sus clases.
        $soloMias = $miPerfil && ! $request->boolean('todas');

        $clases = Clase::with(['entrenador', 'clientes'])
            ->deSucursal()
            ->when($soloMias, fn ($q) => $q->where('id_entrenador', $miPerfil->id_entrenador))
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        $entrenadores = Entrenador::withCount(['clases', 'clientes'])
            ->with(['usuario', 'sucursal', 'disponibilidad'])
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
            'puedeTodas' => $user->permite('classes.manage'),
            'puedePropias' => $user->permite('classes.manage_own') && $miPerfil,
            'puedeCoaches' => $user->permite('trainers.manage'),
            'puedeEditarClase' => fn (Clase $c) => $this->puedeGestionar($user, $c),
            'puedeInscribir' => fn (Clase $c) => $this->puedeInscribir($user, $c),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $datos = $this->validar($request);

        if (! $user->permite('classes.manage')) {
            abort_unless($user->permite('classes.manage_own') && $user->entrenador, 403, 'No puedes crear clases.');

            // El coach siempre crea la clase a su nombre y en su sucursal.
            $datos['id_entrenador'] = $user->entrenador->id_entrenador;
            $datos['id_sucursal'] = $user->id_sucursal ?? $user->entrenador->id_sucursal;
        }

        $datos['id_sucursal'] = Tenant::sucursalParaRegistro($datos['id_sucursal'] ?? null);
        $this->validarHorario($datos);

        Clase::create($datos);

        return redirect()->route('entrenamiento.index')->with('success', 'Clase agregada al horario.');
    }

    public function update(Request $request, Clase $clase): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->puedeGestionar($user, $clase), 403, 'Solo puedes editar tus propias clases.');

        $datos = $this->validar($request);

        if (! $user->permite('classes.manage')) {
            $datos['id_entrenador'] = $clase->id_entrenador;
            $datos['id_sucursal'] = $clase->id_sucursal;
        }

        if ($datos['cupo'] < $clase->clientes()->count()) {
            return back()->with('error', 'El cupo no puede ser menor que la cantidad de inscritos.');
        }

        $datos['id_sucursal'] = Tenant::sucursalParaRegistro($datos['id_sucursal'] ?? $clase->id_sucursal);
        $this->validarHorario($datos, $clase);

        $clase->update($datos);

        return redirect()->route('entrenamiento.index')->with('success', 'Clase actualizada.');
    }

    public function destroy(Request $request, Clase $clase): RedirectResponse
    {
        abort_unless($this->puedeGestionar($request->user(), $clase), 403, 'Solo puedes eliminar tus propias clases.');

        $clase->delete();

        return back()->with('success', 'Clase eliminada del horario.');
    }

    public function inscribir(Request $request, Clase $clase): RedirectResponse
    {
        abort_unless($this->puedeInscribir($request->user(), $clase), 403, 'Solo puedes inscribir alumnos en tus clases.');

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

    public function desinscribir(Request $request, Clase $clase, Cliente $cliente): RedirectResponse
    {
        abort_unless($this->puedeInscribir($request->user(), $clase), 403, 'Solo puedes retirar alumnos de tus clases.');

        $clase->clientes()->detach($cliente->id_cliente);

        return back()->with('success', "{$cliente->nombre} fue retirado de la clase.");
    }

    private function puedeGestionar(User $user, Clase $clase): bool
    {
        return $user->permite('classes.manage')
            || ($user->permite('classes.manage_own') && $user->entrenador && (int) $clase->id_entrenador === $user->entrenador->id_entrenador);
    }

    private function puedeInscribir(User $user, Clase $clase): bool
    {
        return $user->permite('enrollments.manage')
            || ($user->permite('enrollments.manage_own') && $user->entrenador && (int) $clase->id_entrenador === $user->entrenador->id_entrenador);
    }

    /**
     * Un coach no puede tener dos clases a la misma hora, y la clase debe caber en su disponibilidad.
     */
    private function validarHorario(array $datos, ?Clase $actual = null): void
    {
        if (empty($datos['id_entrenador'])) {
            return;
        }

        $coach = Entrenador::find($datos['id_entrenador']);

        $cruce = Clase::where('id_entrenador', $coach->id_entrenador)
            ->where('dia_semana', $datos['dia_semana'])
            ->when($actual, fn ($q) => $q->where('id_clase', '!=', $actual->id_clase))
            // Se comparan con segundos para que SQLite y MySQL den el mismo resultado.
            ->where('hora_inicio', '<', substr($datos['hora_fin'], 0, 5).':00')
            ->where('hora_fin', '>', substr($datos['hora_inicio'], 0, 5).':00')
            ->first();

        if ($cruce) {
            throw ValidationException::withMessages([
                'hora_inicio' => "{$coach->nombre} ya tiene la clase {$cruce->nombre} ({$cruce->horario}) ese día.",
            ])->errorBag('clase');
        }

        if (! $coach->disponibleEn((int) $datos['dia_semana'], $datos['hora_inicio'], $datos['hora_fin'])) {
            $franjas = $coach->disponibilidad->where('dia_semana', (int) $datos['dia_semana'])->map->horario->implode(', ');

            throw ValidationException::withMessages([
                'hora_inicio' => "{$coach->nombre} no está disponible ese horario. Disponibilidad ese día: ".($franjas ?: 'ninguna').'.',
            ])->errorBag('clase');
        }
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
}
