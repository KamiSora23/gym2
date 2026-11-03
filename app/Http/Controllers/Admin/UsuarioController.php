<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\Entrenador;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use App\Support\Autorizacion;
use App\Support\CatalogoPermisos;
use App\Support\Permisos;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Empleados de la empresa actual: cuentas, rol, permisos individuales y bloqueo.
 *
 * Regla de autoridad: nadie puede asignar un rol, conceder un permiso ni editar a una persona
 * que tenga más permisos que él mismo. Los super admins se gestionan en la plataforma.
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $usuarios = User::with(['role', 'sucursal', 'entrenador'])
            ->where('id_empresa', Tenant::empresaId())
            ->where(fn ($q) => $q->whereNull('role_id')->orWhereHas('role', fn ($r) => $r->where('nombre', '!=', Role::CLIENTE)))
            ->orderByRaw("estado = 'bloqueado'")
            ->orderBy('name')
            ->get();

        return view('admin.usuarios', [
            'usuarios' => $usuarios,
            'roles' => $this->rolesAsignables($request->user()),
            'sucursales' => Tenant::sucursales(),
            'puedeGestionar' => Permisos::puede($request->user(), 'users.manage'),
        ]);
    }

    public function show(Request $request, User $usuario): View
    {
        $this->delaEmpresa($usuario);
        $actor = $request->user();

        return view('admin.usuario-ficha', [
            'usuario' => $usuario->load(['role', 'sucursal']),
            'detalle' => Autorizacion::detalle($usuario),
            'efectivos' => Autorizacion::efectivos($usuario),
            'permisos' => Permiso::orderBy('id')->get()->groupBy('grupo'),
            'grupos' => CatalogoPermisos::GRUPOS,
            'roles' => $this->rolesAsignables($actor),
            'sucursales' => Tenant::sucursales(),
            'actividad' => Auditoria::where('user_id', $usuario->id)->orderByDesc('id')->limit(15)->get(),
            'puedeGestionar' => Permisos::puede($actor, 'users.manage') && $this->tieneAutoridadSobre($actor, $usuario),
            'puedePermisos' => Permisos::puede($actor, 'permissions.assign') && $this->tieneAutoridadSobre($actor, $usuario),
            'puedeBloquear' => Permisos::puede($actor, 'users.block') && $this->tieneAutoridadSobre($actor, $usuario) && ! $usuario->is($actor),
            'misPermisos' => $actor->isSuperAdmin() ? collect(array_keys(CatalogoPermisos::PERMISOS)) : Autorizacion::efectivos($actor),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Permisos::exigir('users.manage');
        $datos = $this->validar($request);

        $user = DB::transaction(function () use ($datos) {
            $user = User::create($datos);

            if ($user->isCliente()) {
                $this->crearFichaCliente($user);
            }

            $this->vincularPerfilCoach($user);

            return $user;
        });

        Auditoria::registrar('usuario.creado', "Creó al usuario {$user->name} ({$user->role?->etiqueta})", $user);

        return $this->volver('Usuario creado correctamente.');
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        Permisos::exigir('users.manage');
        $this->autorizar($request->user(), $usuario);

        $datos = $this->validar($request, $usuario);

        if (blank($datos['password'] ?? null)) {
            unset($datos['password']);
        }

        $rolAnterior = $usuario->role?->etiqueta;

        DB::transaction(function () use ($usuario, $datos) {
            $usuario->update($datos);
            $usuario->load('role');

            if ($usuario->isCliente() && ! $usuario->cliente()->exists()) {
                $this->crearFichaCliente($usuario);
            }

            $this->vincularPerfilCoach($usuario);
        });

        Autorizacion::olvidar();
        Auditoria::registrar('usuario.actualizado', "Actualizó al usuario {$usuario->name}".($rolAnterior !== $usuario->role?->etiqueta ? " (rol: {$rolAnterior} → {$usuario->role?->etiqueta})" : ''), $usuario);

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        Permisos::exigir('users.manage');
        $this->autorizar($request->user(), $usuario);

        if ($usuario->is($request->user())) {
            return $this->volver('No puedes eliminar tu propia cuenta desde aquí.', 'error');
        }

        if ($this->darDeBajaSiTieneHistorial($usuario)) {
            Auditoria::registrar('usuario.baja', "Dio de baja al usuario {$usuario->name}", $usuario);

            return $this->volver("{$usuario->name} tiene historial: se bloqueó su acceso en lugar de eliminarlo.", 'info');
        }

        // Su perfil de coach se borra si está vacío; si tiene clases o alumnos, queda desactivado.
        if ($perfil = Entrenador::where('user_id', $usuario->id)->first()) {
            $perfil->clases()->exists() || $perfil->clientes()->exists()
                ? $perfil->update(['user_id' => null, 'activo' => false])
                : $perfil->delete();
        }

        $usuario->delete();
        Auditoria::registrar('usuario.eliminado', "Eliminó al usuario {$usuario->name}");

        return $this->volver('Usuario eliminado correctamente.');
    }

    /**
     * Permisos individuales: "conceder" agrega un permiso que el rol no tiene;
     * "restringir" quita uno que el rol sí tiene. La restricción prevalece.
     */
    public function permisos(Request $request, User $usuario): RedirectResponse
    {
        Permisos::exigir('permissions.assign');
        $actor = $request->user();
        $this->autorizar($actor, $usuario);

        $datos = $request->validate([
            'permisos' => ['nullable', 'array'],
            'permisos.*' => [Rule::in(['rol', 'grant', 'deny'])],
        ]);

        $catalogo = Permiso::pluck('id', 'codigo');
        $cambios = [];
        $rechazados = [];

        foreach ($datos['permisos'] ?? [] as $codigo => $tipo) {
            if (! $catalogo->has($codigo) || $tipo === 'rol') {
                continue;
            }

            // Solo se concede o restringe lo que uno mismo tiene.
            if (! Autorizacion::puedeConceder($actor, $codigo)) {
                $rechazados[] = $codigo;

                continue;
            }

            $cambios[$catalogo[$codigo]] = ['tipo' => $tipo, 'otorgado_por' => $actor->id];
        }

        // Las excepciones existentes sobre permisos que el actor no controla se conservan.
        $intocables = $usuario->permisosIndividuales()->get()
            ->reject(fn ($p) => Autorizacion::puedeConceder($actor, $p->codigo))
            ->mapWithKeys(fn ($p) => [$p->id => ['tipo' => $p->pivot->tipo, 'otorgado_por' => $p->pivot->otorgado_por]]);

        $usuario->permisosIndividuales()->sync($cambios + $intocables->all());
        Autorizacion::olvidar();

        Auditoria::registrar('permisos.usuario', "Actualizó los permisos individuales de {$usuario->name}", $usuario, [
            'conceder' => collect($cambios)->where('tipo', 'grant')->keys()->map(fn ($id) => $catalogo->search($id))->values(),
            'restringir' => collect($cambios)->where('tipo', 'deny')->keys()->map(fn ($id) => $catalogo->search($id))->values(),
        ]);

        $respuesta = back()->with('success', 'Permisos individuales guardados.');

        return $rechazados ? $respuesta->with('info', 'No puedes conceder ni restringir permisos que tú no tienes: '.implode(', ', $rechazados).'.') : $respuesta;
    }

    public function bloqueo(Request $request, User $usuario): RedirectResponse
    {
        Permisos::exigir('users.block');
        $this->autorizar($request->user(), $usuario);

        abort_if($usuario->is($request->user()), 403, 'No puedes bloquearte a ti mismo.');

        if ($usuario->isBloqueado()) {
            $usuario->update(['estado' => 'activo', 'motivo_bloqueo' => null, 'bloqueado_en' => null]);
            Auditoria::registrar('usuario.desbloqueado', "Desbloqueó al usuario {$usuario->name}", $usuario);

            return back()->with('success', "{$usuario->name} puede volver a iniciar sesión.");
        }

        $datos = $request->validate(['motivo' => ['required', 'string', 'max:200']], ['motivo.required' => 'Indica el motivo del bloqueo.']);

        $usuario->update(['estado' => 'bloqueado', 'motivo_bloqueo' => $datos['motivo'], 'bloqueado_en' => now()]);
        Auditoria::registrar('usuario.bloqueado', "Bloqueó al usuario {$usuario->name}: {$datos['motivo']}", $usuario);

        return back()->with('success', "{$usuario->name} fue bloqueado y su sesión se cerrará en su siguiente acción.");
    }

    private function validar(Request $request, ?User $usuario = null): array
    {
        $datos = $request->validateWithBag('usuario', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($usuario?->id)],
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'role_id' => ['required', Rule::in($this->rolesAsignables($request->user())->pluck('id'))],
            'id_sucursal' => ['nullable', Rule::in(Tenant::sucursales()->pluck('id_sucursal'))],
        ], [
            'role_id.in' => 'No puedes asignar ese rol: tiene permisos que tú no tienes.',
        ]);

        $datos['id_empresa'] = Tenant::empresaId();

        // Quien no puede trabajar con todas las sedes necesita una sucursal; si hay una sola, se asigna.
        $rol = Role::with('permisos:id,codigo')->find($datos['role_id']);
        $multisede = $rol->nombre === Role::ADMIN || $rol->permisos->contains('codigo', 'branches.switch');

        if (! $multisede && blank($datos['id_sucursal'] ?? null)) {
            $sucursales = Tenant::sucursales();

            if ($sucursales->count() !== 1) {
                throw ValidationException::withMessages([
                    'id_sucursal' => "El rol {$rol->etiqueta} trabaja en una sola sede: elige su sucursal.",
                ])->errorBag('usuario');
            }

            $datos['id_sucursal'] = $sucursales->first()->id_sucursal;
        }

        return $datos;
    }

    /**
     * Un usuario con rol de entrenador necesita su perfil de coach para tener clases, horarios y alumnos.
     */
    private function vincularPerfilCoach(User $user): void
    {
        $user->loadMissing('role');

        if ($user->role?->nombre !== Role::ENTRENADOR) {
            return;
        }

        $perfil = Entrenador::where('user_id', $user->id)->first();

        if ($perfil) {
            $perfil->update(['id_sucursal' => $user->id_sucursal ?? $perfil->id_sucursal, 'activo' => true]);

            return;
        }

        Entrenador::create([
            'id_empresa' => $user->id_empresa,
            'id_sucursal' => $user->id_sucursal,
            'user_id' => $user->id,
            'nombre' => $user->name,
            'email' => $user->email,
            'activo' => true,
        ]);
    }

    /**
     * Roles de la empresa cuyos permisos el actor tiene todos (no puede asignar más de lo que posee).
     */
    private function rolesAsignables(User $actor)
    {
        $mios = $actor->isSuperAdmin() ? null : Autorizacion::efectivos($actor);

        return Role::disponiblesPara(Tenant::empresaId())
            ->whereNotIn('nombre', [Role::SUPER_ADMIN, Role::CLIENTE])
            ->with('permisos:id,codigo')
            ->orderByRaw('id_empresa is null desc')
            ->orderBy('id')
            ->get()
            ->filter(fn ($rol) => $mios === null || $rol->permisos->pluck('codigo')->diff($mios)->isEmpty())
            ->values();
    }

    private function tieneAutoridadSobre(User $actor, User $usuario): bool
    {
        if ($actor->isSuperAdmin()) {
            return ! $usuario->isSuperAdmin() || $actor->is($usuario);
        }

        return ! $usuario->isSuperAdmin()
            && Autorizacion::efectivos($usuario)->diff(Autorizacion::efectivos($actor))->isEmpty();
    }

    private function autorizar(User $actor, User $usuario): void
    {
        $this->delaEmpresa($usuario);

        abort_unless($this->tieneAutoridadSobre($actor, $usuario), 403, 'No puedes modificar a un usuario con más permisos que tú.');
    }

    private function delaEmpresa(User $usuario): void
    {
        abort_if($usuario->isSuperAdmin() || (int) $usuario->id_empresa !== Tenant::empresaId(), 403);
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

    /**
     * Un usuario que ya operó (ventas, caja, inventario, auditoría) no se borra: se da de baja.
     */
    private function darDeBajaSiTieneHistorial(User $usuario): bool
    {
        $usado = DB::table('ventas')->where('user_id', $usuario->id)->exists()
            || DB::table('caja_sesiones')->where('user_id', $usuario->id)->exists()
            || DB::table('inventario_movimientos')->where('user_id', $usuario->id)->exists()
            || DB::table('gastos')->where('user_id', $usuario->id)->exists()
            || DB::table('auditoria')->where('user_id', $usuario->id)->where('accion', 'not like', 'sesion.%')->exists();

        if ($usado) {
            $usuario->update(['estado' => 'bloqueado', 'motivo_bloqueo' => 'Dado de baja (conserva su historial)', 'bloqueado_en' => now()]);
            Entrenador::where('user_id', $usuario->id)->update(['activo' => false]);
        }

        return $usado;
    }
}
