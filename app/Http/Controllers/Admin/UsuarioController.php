<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Gestión de usuarios del sistema. Solo accesible por el super administrador.
 */
class UsuarioController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validateWithBag('usuario', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        DB::transaction(function () use ($datos) {
            $user = User::create($datos);

            if ($user->isCliente()) {
                $this->crearFichaCliente($user);
            }
        });

        return $this->volver('Usuario creado correctamente.');
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $datos = $request->validateWithBag('usuario', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        if ($this->quitariaUltimoSuperAdmin($usuario, (int) $datos['role_id'])) {
            return $this->volver('Debe existir al menos un Super Administrador.', 'error');
        }

        if (blank($datos['password'] ?? null)) {
            unset($datos['password']);
        }

        DB::transaction(function () use ($usuario, $datos) {
            $usuario->update($datos);
            $usuario->load('role');

            if ($usuario->isCliente() && ! $usuario->cliente()->exists()) {
                $this->crearFichaCliente($usuario);
            }
        });

        return $this->volver('Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return $this->volver('No puedes eliminar tu propia cuenta desde aquí.', 'error');
        }

        if ($this->quitariaUltimoSuperAdmin($usuario, null)) {
            return $this->volver('Debe existir al menos un Super Administrador.', 'error');
        }

        $usuario->delete();

        return $this->volver('Usuario eliminado correctamente.');
    }

    private function quitariaUltimoSuperAdmin(User $usuario, ?int $nuevoRol): bool
    {
        $superAdminId = Role::idDe(Role::SUPER_ADMIN);

        return (int) $usuario->role_id === $superAdminId
            && $nuevoRol !== $superAdminId
            && User::where('role_id', $superAdminId)->count() <= 1;
    }

    private function crearFichaCliente(User $user): void
    {
        Cliente::create([
            'nombre' => $user->name,
            'correo' => $user->email,
            'dni' => 'TEMP-'.$user->id,
            'user_id' => $user->id,
        ]);
    }

    private function volver(string $mensaje, string $tipo = 'success'): RedirectResponse
    {
        return redirect()->route('dashboard', ['seccion' => 'usuarios'])->with($tipo, $mensaje);
    }
}
