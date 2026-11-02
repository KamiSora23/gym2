<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Progreso;
use App\Models\Role;
use App\Models\Rutina;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Rutinas de entrenamiento y seguimiento del progreso de un alumno.
 * Las gestiona el admin o el coach del alumno.
 */
class RutinaController extends Controller
{
    public function store(Request $request, Cliente $cliente): RedirectResponse
    {
        $this->autorizar($request->user(), $cliente);

        $datos = $this->validar($request);

        DB::transaction(function () use ($request, $cliente, $datos) {
            // Solo una rutina activa por alumno: la nueva reemplaza a la anterior.
            Rutina::where('id_cliente', $cliente->id_cliente)->update(['activa' => false]);

            $rutina = Rutina::create([
                'id_cliente' => $cliente->id_cliente,
                'id_empresa' => $cliente->id_empresa,
                'id_entrenador' => $request->user()->entrenador?->id_entrenador ?? $cliente->id_entrenador,
                'nombre' => $datos['nombre'],
                'objetivo' => $datos['objetivo'] ?? null,
                'notas' => $datos['notas'] ?? null,
                'activa' => true,
            ]);

            $rutina->ejercicios()->createMany($this->ejercicios($datos));
        });

        return redirect()->route('clientes.show', $cliente)->with('success', 'Rutina asignada al alumno.');
    }

    public function update(Request $request, Rutina $rutina): RedirectResponse
    {
        $this->autorizar($request->user(), $rutina->cliente);

        $datos = $this->validar($request);

        DB::transaction(function () use ($rutina, $datos) {
            $rutina->update(['nombre' => $datos['nombre'], 'objetivo' => $datos['objetivo'] ?? null, 'notas' => $datos['notas'] ?? null]);
            $rutina->ejercicios()->delete();
            $rutina->ejercicios()->createMany($this->ejercicios($datos));
        });

        return redirect()->route('clientes.show', $rutina->id_cliente)->with('success', 'Rutina actualizada.');
    }

    public function activar(Request $request, Rutina $rutina): RedirectResponse
    {
        $this->autorizar($request->user(), $rutina->cliente);

        Rutina::where('id_cliente', $rutina->id_cliente)->update(['activa' => false]);
        $rutina->update(['activa' => true]);

        return back()->with('success', "La rutina «{$rutina->nombre}» ahora es la activa.");
    }

    public function destroy(Request $request, Rutina $rutina): RedirectResponse
    {
        $this->autorizar($request->user(), $rutina->cliente);

        $rutina->delete();

        return back()->with('success', 'Rutina eliminada.');
    }

    public function progreso(Request $request, Cliente $cliente): RedirectResponse
    {
        $this->autorizar($request->user(), $cliente);

        $datos = $request->validateWithBag('progreso', [
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'peso' => ['nullable', 'numeric', 'between:20,400'],
            'grasa' => ['nullable', 'numeric', 'between:1,80'],
            'cintura' => ['nullable', 'numeric', 'between:30,250'],
            'notas' => ['nullable', 'string', 'max:500'],
        ]);

        if (blank($datos['peso'] ?? null) && blank($datos['grasa'] ?? null) && blank($datos['cintura'] ?? null) && blank($datos['notas'] ?? null)) {
            return back()->with('error', 'Registra al menos una medida o una nota.');
        }

        Progreso::create($datos + [
            'id_empresa' => $cliente->id_empresa,
            'id_cliente' => $cliente->id_cliente,
            'id_entrenador' => $request->user()->entrenador?->id_entrenador,
        ]);

        return back()->with('success', 'Medición registrada.');
    }

    public function eliminarProgreso(Request $request, Progreso $progreso): RedirectResponse
    {
        $this->autorizar($request->user(), Cliente::findOrFail($progreso->id_cliente));

        $progreso->delete();

        return back()->with('success', 'Medición eliminada.');
    }

    private function validar(Request $request): array
    {
        return $request->validateWithBag('rutina', [
            'nombre' => ['required', 'string', 'max:120'],
            'objetivo' => ['nullable', 'string', 'max:150'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'ejercicios' => ['required', 'array', 'min:1', 'max:60'],
            'ejercicios.*.dia' => ['nullable', 'string', 'max:40'],
            'ejercicios.*.ejercicio' => ['nullable', 'string', 'max:120'],
            'ejercicios.*.series' => ['nullable', 'integer', 'min:1', 'max:50'],
            'ejercicios.*.repeticiones' => ['nullable', 'string', 'max:30'],
            'ejercicios.*.descanso' => ['nullable', 'string', 'max:30'],
        ], [
            'ejercicios.required' => 'Agrega al menos un ejercicio.',
        ]);
    }

    private function ejercicios(array $datos): array
    {
        $lista = collect($datos['ejercicios'])
            ->filter(fn ($e) => filled($e['ejercicio'] ?? null))
            ->values()
            ->map(fn ($e, $i) => [
                'dia' => $e['dia'] ?? null,
                'ejercicio' => $e['ejercicio'],
                'series' => $e['series'] ?? null,
                'repeticiones' => $e['repeticiones'] ?? null,
                'descanso' => $e['descanso'] ?? null,
                'orden' => $i,
            ]);

        if ($lista->isEmpty()) {
            throw ValidationException::withMessages(['ejercicios' => 'Agrega al menos un ejercicio.'])->errorBag('rutina');
        }

        return $lista->all();
    }

    /**
     * Admin: cualquier cliente. Coach: solo sus alumnos.
     */
    private function autorizar(User $user, Cliente $cliente): void
    {
        abort_unless($user->permite('rutinas.gestionar'), 403);

        if ($user->hasRole(Role::ENTRENADOR)) {
            abort_unless($user->entrenador && $user->entrenador->esAlumno($cliente), 403, 'Solo puedes gestionar a tus alumnos.');
        }
    }
}
