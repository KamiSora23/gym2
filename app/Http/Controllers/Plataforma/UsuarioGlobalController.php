<?php

namespace App\Http\Controllers\Plataforma;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Todos los usuarios de la plataforma (de todas las empresas). Solo super admin.
 */
class UsuarioGlobalController extends Controller
{
    public function index(Request $request): View
    {
        $usuarios = User::with(['role', 'empresa', 'sucursal'])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$request->q}%")->orWhere('email', 'like', "%{$request->q}%")))
            ->when($request->filled('rol'), fn ($q) => $q->where('role_id', $request->rol))
            ->when($request->input('empresa') === 'plataforma', fn ($q) => $q->whereNull('id_empresa'))
            ->when(is_numeric($request->input('empresa')), fn ($q) => $q->where('id_empresa', $request->empresa))
            ->orderByRaw('id_empresa is not null')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('plataforma.usuarios', [
            'usuarios' => $usuarios,
            'roles' => Role::orderBy('id')->get(),
            'empresas' => Empresa::orderBy('nombre')->get(),
            'sucursales' => Sucursal::withoutGlobalScopes()->where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($datos) {
            $user = User::create($datos);

            if ($user->isCliente()) {
                Cliente::withoutGlobalScopes()->create([
                    'id_empresa' => $user->id_empresa,
                    'nombre' => $user->name,
                    'correo' => $user->email,
                    'dni' => 'TEMP-'.$user->id,
                    'user_id' => $user->id,
                ]);
            }
        });

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $datos = $this->validar($request, $usuario);

        if ($this->quitariaUltimoSuperAdmin($usuario, (int) $datos['role_id'])) {
            return back()->with('error', 'Debe existir al menos un Super Administrador.');
        }

        if (blank($datos['password'] ?? null)) {
            unset($datos['password']);
        }

        $usuario->update($datos);

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        if ($this->quitariaUltimoSuperAdmin($usuario, null)) {
            return back()->with('error', 'Debe existir al menos un Super Administrador.');
        }

        $usuario->delete();

        return back()->with('success', 'Usuario eliminado.');
    }

    private function validar(Request $request, ?User $usuario = null): array
    {
        $superAdminId = Role::idDe(Role::SUPER_ADMIN);
        $esSuper = (int) $request->input('role_id') === $superAdminId;

        $datos = $request->validateWithBag('usuario', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($usuario?->id)],
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
            'id_empresa' => [$esSuper ? 'nullable' : 'required', 'nullable', 'exists:empresa,id_empresa'],
            'id_sucursal' => ['nullable', Rule::exists('sucursales', 'id_sucursal')->where('id_empresa', $request->input('id_empresa'))],
        ], [
            'id_empresa.required' => 'Elige la empresa a la que pertenece el usuario.',
            'id_sucursal.exists' => 'La sucursal no pertenece a la empresa elegida.',
        ]);

        if ($esSuper) {
            $datos['id_empresa'] = null;
            $datos['id_sucursal'] = null;
        }

        return $datos;
    }

    private function quitariaUltimoSuperAdmin(User $usuario, ?int $nuevoRol): bool
    {
        $superAdminId = Role::idDe(Role::SUPER_ADMIN);

        return (int) $usuario->role_id === $superAdminId
            && $nuevoRol !== $superAdminId
            && User::where('role_id', $superAdminId)->count() <= 1;
    }
}
