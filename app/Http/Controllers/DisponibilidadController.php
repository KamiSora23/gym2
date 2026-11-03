<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Entrenador;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Perfil del coach y su disponibilidad semanal.
 * El coach edita los suyos; el admin, los de cualquier coach.
 */
class DisponibilidadController extends Controller
{
    /**
     * Acceso directo del coach a su propio perfil.
     */
    public function miPerfil(Request $request): RedirectResponse
    {
        $perfil = $request->user()->entrenador;

        if (! $perfil) {
            return redirect()->route('entrenamiento.index')
                ->with('info', 'Tu usuario no está vinculado a un perfil de coach. Pide al administrador que te vincule.');
        }

        return redirect()->route('entrenadores.disponibilidad', $perfil);
    }

    public function edit(Request $request, Entrenador $entrenador): View
    {
        $this->autorizar($request->user(), $entrenador);

        return view('admin.disponibilidad', [
            'coach' => $entrenador->load('disponibilidad'),
            'clases' => Clase::where('id_entrenador', $entrenador->id_entrenador)->orderBy('dia_semana')->orderBy('hora_inicio')->get(),
            'esPropio' => $request->user()->entrenador?->is($entrenador),
        ]);
    }

    public function update(Request $request, Entrenador $entrenador): RedirectResponse
    {
        $this->autorizar($request->user(), $entrenador);

        $datos = $request->validate([
            'especialidad' => ['nullable', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'franjas' => ['nullable', 'array', 'max:50'],
            'franjas.*.dia_semana' => ['required', 'integer', 'between:1,7'],
            'franjas.*.hora_inicio' => ['required', 'date_format:H:i'],
            'franjas.*.hora_fin' => ['required', 'date_format:H:i'],
        ], [
            'franjas.*.hora_inicio.date_format' => 'Revisa las horas: usa el formato HH:MM.',
            'franjas.*.hora_fin.date_format' => 'Revisa las horas: usa el formato HH:MM.',
        ]);

        $franjas = collect($datos['franjas'] ?? [])->map(fn ($f) => [
            'dia_semana' => (int) $f['dia_semana'],
            'hora_inicio' => $f['hora_inicio'],
            'hora_fin' => $f['hora_fin'],
        ]);

        $this->validarFranjas($franjas);

        DB::transaction(function () use ($entrenador, $datos, $franjas) {
            $entrenador->update(array_intersect_key($datos, array_flip(['especialidad', 'telefono', 'bio'])));
            $entrenador->disponibilidad()->delete();
            $entrenador->disponibilidad()->createMany($franjas->all());
        });

        // Avisar si alguna clase ya programada queda fuera de la nueva disponibilidad.
        $fuera = Clase::where('id_entrenador', $entrenador->id_entrenador)->get()
            ->reject(fn ($c) => $entrenador->disponibleEn($c->dia_semana, substr($c->hora_inicio, 0, 5), substr($c->hora_fin, 0, 5)));

        $respuesta = redirect()->route('entrenadores.disponibilidad', $entrenador)->with('success', 'Perfil y disponibilidad guardados.');

        return $fuera->isEmpty()
            ? $respuesta
            : $respuesta->with('info', 'Atención: estas clases quedan fuera de la disponibilidad: '.$fuera->map(fn ($c) => "{$c->nombre} (".Clase::DIAS[$c->dia_semana]." {$c->horario})")->implode(', ').'.');
    }

    private function validarFranjas($franjas): void
    {
        foreach ($franjas as $i => $f) {
            if ($f['hora_fin'] <= $f['hora_inicio']) {
                throw ValidationException::withMessages(["franjas.{$i}.hora_fin" => Clase::DIAS[$f['dia_semana']].': la hora de fin debe ser posterior a la de inicio.']);
            }
        }

        foreach ($franjas->groupBy('dia_semana') as $dia => $delDia) {
            $ordenadas = $delDia->sortBy('hora_inicio')->values();

            for ($i = 1; $i < $ordenadas->count(); $i++) {
                if ($ordenadas[$i]['hora_inicio'] < $ordenadas[$i - 1]['hora_fin']) {
                    throw ValidationException::withMessages(['franjas' => Clase::DIAS[$dia].': hay franjas que se cruzan.']);
                }
            }
        }
    }

    private function autorizar(User $user, Entrenador $entrenador): void
    {
        $propio = $user->entrenador && $user->entrenador->is($entrenador);

        abort_unless(
            $user->permite('trainers.availability') || ($propio && $user->permite('trainers.availability_own')),
            403,
            'Solo puedes editar tu propia disponibilidad.'
        );
    }
}
