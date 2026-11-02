<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Usuarios de la empresa actual. Los super admins se gestionan aparte, en la plataforma.
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $usuarios = User::with(['role', 'sucursal'])
            ->where('id_empresa', Tenant::empresaId())
            ->orderBy('name')
            ->get();

        return view('admin.usuarios', [
            'usuarios' => $usuarios,
            'roles' => $this->rolesAsignables(),
            'sucursales' => Tenant::sucursales(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

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
        $this->autorizar($usuario);

        $datos = $this->validar($request, $usuario);

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
        $this->autorizar($usuario);

        if ($usuario->is($request->user())) {
            return $this->volver('No puedes eliminar tu propia cuenta desde aquí.', 'error');
        }

        $usuario->delete();

        return $this->volver('Usuario eliminado correctamente.');
    }

    private function validar(Request $request, ?User $usuario = null): array
    {
        $rolesValidos = $this->rolesAsignables()->pluck('id');

        $datos = $request->validateWithBag('usuario', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($usuario?->id)],
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'role_id' => ['required', Rule::in($rolesValidos)],
            'id_sucursal' => ['nullable', Rule::in(Tenant::sucursales()->pluck('id_sucursal'))],
        ]);

        $datos['id_empresa'] = Tenant::empresaId();

        return $datos;
    }

    /**
     * Desde una empresa solo se gestionan los usuarios de esa empresa (nunca super admins).
     */
    private function autorizar(User $usuario): void
    {
        abort_if($usuario->isSuperAdmin() || (int) $usuario->id_empresa !== Tenant::empresaId(), 403);
    }

    private function rolesAsignables()
    {
        return Role::where('nombre', '!=', Role::SUPER_ADMIN)->orderBy('id')->get();
    }

    private function crearFichaCliente(User $user): void
    {
        Cliente::create([
            'id_empresa' => $user->id_empresa,
            'nombre' => $user->name,
            'correo' => $user->email,
            'dni' => 'TEMP-'.$user->id,
            'user_id' => $user->id,
        ]);
    }

    private function volver(string $mensaje, string $tipo = 'success'): RedirectResponse
    {
        return redirect()->route('usuarios.index')->with($tipo, $mensaje);
    }
}
