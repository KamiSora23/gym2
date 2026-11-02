<?php

namespace App\Http\Controllers;

use App\Models\Entrenador;
use App\Models\Role;
use App\Models\User;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Entrenadores (coaches) de la empresa.
 */
class EntrenadorController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->soloGestion($request);

        Entrenador::create($this->validar($request) + ['activo' => true]);

        return redirect()->route('entrenamiento.index')->with('success', 'Entrenador registrado correctamente.');
    }

    public function update(Request $request, Entrenador $entrenador): RedirectResponse
    {
        $this->soloGestion($request);

        $datos = $this->validar($request, $entrenador);
        $datos['activo'] = $request->boolean('activo');
        $entrenador->update($datos);

        return redirect()->route('entrenamiento.index')->with('success', 'Entrenador actualizado correctamente.');
    }

    public function destroy(Request $request, Entrenador $entrenador): RedirectResponse
    {
        $this->soloGestion($request);

        if ($entrenador->clases()->exists() || $entrenador->clientes()->exists()) {
            $entrenador->update(['activo' => false]);

            return back()->with('info', 'El entrenador tiene clases o clientes asignados: se desactivó en lugar de eliminarse.');
        }

        $entrenador->delete();

        return back()->with('success', 'Entrenador eliminado.');
    }

    private function validar(Request $request, ?Entrenador $entrenador = null): array
    {
        $empresaId = Tenant::empresaId();

        $usuariosEntrenador = User::where('id_empresa', $empresaId)
            ->whereHas('role', fn ($q) => $q->where('nombre', Role::ENTRENADOR))
            ->pluck('id');

        return $request->validateWithBag('entrenador', [
            'nombre' => ['required', 'string', 'max:150'],
            'especialidad' => ['nullable', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'id_sucursal' => ['nullable', Rule::in(Tenant::sucursales()->pluck('id_sucursal'))],
            'user_id' => [
                'nullable',
                Rule::in($usuariosEntrenador),
                Rule::unique('entrenadores', 'user_id')->ignore($entrenador?->id_entrenador, 'id_entrenador'),
            ],
        ], [
            'user_id.unique' => 'Ese usuario ya está vinculado a otro entrenador.',
        ]);
    }

    private function soloGestion(Request $request): void
    {
        abort_unless($request->user()->isAdmin() || $request->user()->hasRole(Role::RECEPCIONISTA), 403);
    }
}
