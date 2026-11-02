<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use App\Support\Autorizacion;
use App\Support\CatalogoPermisos;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Roles de la empresa. Las plantillas del sistema se ven pero no se editan:
 * para personalizarlas se crea un rol propio a partir de una plantilla.
 */
class RolController extends Controller
{
    public function index(Request $request): View
    {
        $empresaId = Tenant::empresaId();

        $roles = Role::disponiblesPara($empresaId)
            ->where('nombre', '!=', Role::SUPER_ADMIN)
            ->with('permisos:id,codigo')
            ->withCount(['users' => fn ($q) => $q->where('id_empresa', $empresaId)])
            ->orderByRaw('id_empresa is null desc')
            ->orderBy('id')
            ->get();

        return view('admin.roles', [
            'roles' => $roles,
            'permisos' => Permiso::orderBy('id')->get()->groupBy('grupo'),
            'grupos' => CatalogoPermisos::GRUPOS,
            'misPermisos' => $request->user()->isSuperAdmin()
                ? collect(array_keys(CatalogoPermisos::PERMISOS))
                : Autorizacion::efectivos($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = Tenant::empresaId();

        $datos = $request->validateWithBag('rol', [
            'etiqueta' => ['required', 'string', 'max:60'],
            'descripcion' => ['nullable', 'string', 'max:150'],
            'plantilla' => ['nullable', 'exists:roles,id'],
        ]);

        $nombre = Str::slug($datos['etiqueta'], '_');

        if (Role::disponiblesPara($empresaId)->where('nombre', $nombre)->exists()) {
            return back()->withErrors(['etiqueta' => 'Ya existe un rol con ese nombre.'], 'rol')->withInput();
        }

        $rol = Role::create([
            'nombre' => $nombre,
            'etiqueta' => $datos['etiqueta'],
            'descripcion' => $datos['descripcion'] ?? null,
            'id_empresa' => $empresaId,
            'es_sistema' => false,
        ]);

        // Copia los permisos de la plantilla, pero solo los que quien lo crea puede conceder.
        if (! empty($datos['plantilla'])) {
            $base = Role::disponiblesPara($empresaId)->findOrFail($datos['plantilla']);
            $codigos = $this->concedibles($request->user(), $base->permisos->pluck('codigo')->all());
            $rol->permisos()->sync(Permiso::whereIn('codigo', $codigos)->pluck('id'));
        }

        Auditoria::registrar('permisos.rol_creado', "Creó el rol {$rol->etiqueta}", $rol);

        return redirect()->to(route('roles.index').'#rol-'.$rol->id)->with('success', "Rol «{$rol->etiqueta}» creado. Ajusta sus permisos.");
    }

    public function update(Request $request, Role $rol): RedirectResponse
    {
        $this->editable($rol);

        $datos = $request->validateWithBag('rol', [
            'etiqueta' => ['required', 'string', 'max:60'],
            'descripcion' => ['nullable', 'string', 'max:150'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string'],
        ]);

        $actor = $request->user();
        $actuales = $rol->permisos()->pluck('codigo')->all();
        $solicitados = array_values(array_intersect($datos['permisos'] ?? [], array_keys(CatalogoPermisos::PERMISOS)));

        // Solo se cambia lo que el actor controla; los permisos que él no tiene se conservan tal cual.
        $concedibles = array_values(array_unique(array_merge(
            $this->concedibles($actor, $solicitados),
            array_filter($actuales, fn ($c) => ! Autorizacion::puedeConceder($actor, $c)),
        )));
        $rechazados = array_diff($solicitados, $concedibles);

        $rol->update(['etiqueta' => $datos['etiqueta'], 'descripcion' => $datos['descripcion'] ?? null]);
        $rol->permisos()->sync(Permiso::whereIn('codigo', $concedibles)->pluck('id'));
        Autorizacion::olvidar();

        Auditoria::registrar('permisos.rol_actualizado', "Actualizó los permisos del rol {$rol->etiqueta} (".count($concedibles).' permisos)', $rol, ['permisos' => $concedibles]);

        $respuesta = back()->with('success', "Permisos del rol «{$rol->etiqueta}» guardados.");

        return $rechazados
            ? $respuesta->with('info', 'No puedes conceder permisos que tú no tienes: '.implode(', ', $rechazados).'.')
            : $respuesta;
    }

    public function destroy(Role $rol): RedirectResponse
    {
        $this->editable($rol);

        if ($rol->users()->exists()) {
            return back()->with('error', 'El rol tiene usuarios asignados. Cámbialos de rol antes de eliminarlo.');
        }

        $rol->delete();
        Auditoria::registrar('permisos.rol_eliminado', "Eliminó el rol {$rol->etiqueta}");

        return back()->with('success', 'Rol eliminado.');
    }

    /**
     * Solo se editan los roles propios de la empresa actual.
     */
    private function editable(Role $rol): void
    {
        abort_if($rol->es_sistema || (int) $rol->id_empresa !== Tenant::empresaId(), 403, 'Las plantillas del sistema no se editan: crea un rol propio a partir de ellas.');
    }

    /**
     * Permisos que el usuario puede conceder (los que él mismo tiene).
     */
    private function concedibles(User $user, array $codigos): array
    {
        return array_values(array_filter($codigos, fn ($c) => Autorizacion::puedeConceder($user, $c)));
    }
}
