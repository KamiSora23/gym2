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
 * Usuarios de la empresa actual. El super admin además puede crear super admins.
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $usuarios = User::with(['role', 'sucursal'])
            ->where(function ($q) use ($request) {
                $q->where('id_empresa', Tenant::empresaId());

                if ($request->user()->isSuperAdmin()) {
                    $q->orWhereHas('role', fn ($r) => $r->where('nombre', Role::SUPER_ADMIN));
                }
            })
            ->orderBy('name')
            ->get();

        return view('admin.usuarios', [
            'usuarios' => $usuarios,
            'roles' => $this->rolesAsignables($request->user()),
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
        $this->autorizar($request->user(), $usuario);

        $datos = $this->validar($request, $usuario);

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
        $this->autorizar($request->user(), $usuario);

        if ($usuario->is($request->user())) {
            return $this->volver('No puedes eliminar tu propia cuenta desde aquí.', 'error');
        }

        if ($this->quitariaUltimoSuperAdmin($usuario, null)) {
            return $this->volver('Debe existir al menos un Super Administrador.', 'error');
        }

        $usuario->delete();

        return $this->volver('Usuario eliminado correctamente.');
    }

    private function validar(Request $request, ?User $usuario = null): array
    {
        $rolesValidos = $this->rolesAsignables($request->user())->pluck('id');
        $superAdminId = Role::idDe(Role::SUPER_ADMIN);

        $datos = $request->validateWithBag('usuario', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($usuario?->id)],
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'role_id' => ['required', Rule::in($rolesValidos)],
            'id_sucursal' => ['nullable', Rule::in(Tenant::sucursales()->pluck('id_sucursal'))],
        ]);

        // Los super admin no pertenecen a ninguna empresa; el resto, a la empresa actual.
        if ((int) $datos['role_id'] === $superAdminId) {
            $datos['id_empresa'] = null;
            $datos['id_sucursal'] = null;
        } else {
            $datos['id_empresa'] = Tenant::empresaId();
        }

        return $datos;
    }

    /**
     * Un admin no puede tocar usuarios de otras empresas ni a super admins.
     */
    private function autorizar(User $actor, User $usuario): void
    {
        if ($actor->isSuperAdmin()) {
            return;
        }

        abort_if($usuario->isSuperAdmin() || (int) $usuario->id_empresa !== Tenant::empresaId(), 403);
    }

    private function rolesAsignables(User $actor)
    {
        return Role::orderBy('id')->get()
            ->when(! $actor->isSuperAdmin(), fn ($roles) => $roles->where('nombre', '!=', Role::SUPER_ADMIN))
            ->values();
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
