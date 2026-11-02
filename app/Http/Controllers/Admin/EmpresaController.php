<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Empresas (gimnasios) de la plataforma. Solo super admin.
 */
class EmpresaController extends Controller
{
    public function index(): View
    {
        $empresas = Empresa::query()
            ->withCount(['sucursales' => fn ($q) => $q->withoutGlobalScopes(), 'usuarios', 'clientes' => fn ($q) => $q->withoutGlobalScopes()])
            ->orderBy('nombre')
            ->get();

        return view('admin.empresas', [
            'empresas' => $empresas,
            'totales' => [
                'empresas' => $empresas->count(),
                'activas' => $empresas->where('activo', true)->count(),
                'sucursales' => Sucursal::withoutGlobalScopes()->count(),
                'usuarios' => User::whereNotNull('id_empresa')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validateWithBag('empresa', $this->reglas() + [
            'sucursal' => ['required', 'string', 'max:120'],
            'admin_nombre' => ['nullable', 'required_with:admin_email', 'string', 'max:255'],
            'admin_email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['nullable', 'required_with:admin_email', Password::defaults()],
        ]);

        $empresa = DB::transaction(function () use ($datos) {
            $empresa = Empresa::create($this->soloEmpresa($datos) + ['activo' => true]);

            $sucursal = Sucursal::create([
                'id_empresa' => $empresa->id_empresa,
                'nombre' => $datos['sucursal'],
                'direccion' => $datos['direccion'] ?? null,
                'telefono' => $datos['telefono'] ?? null,
            ]);

            Configuracion::create(Configuracion::VALORES_POR_DEFECTO + [
                'id_empresa' => $empresa->id_empresa,
                'nombre_gym' => $empresa->nombre,
                'email' => $empresa->email,
                'telefono' => $empresa->telefono,
                'direccion' => $empresa->direccion,
            ]);

            if (! empty($datos['admin_email'])) {
                User::create([
                    'name' => $datos['admin_nombre'],
                    'email' => $datos['admin_email'],
                    'password' => $datos['admin_password'],
                    'role_id' => Role::idDe(Role::ADMIN),
                    'id_empresa' => $empresa->id_empresa,
                    'id_sucursal' => $sucursal->id_sucursal,
                    'email_verified_at' => now(),
                ]);
            }

            return $empresa;
        });

        return redirect()->route('empresas.index')->with('success', "Empresa {$empresa->nombre} creada correctamente.");
    }

    public function update(Request $request, Empresa $empresa): RedirectResponse
    {
        $datos = $request->validateWithBag('empresa', $this->reglas($empresa));
        $datos['activo'] = $request->boolean('activo');

        $empresa->update($datos);

        return redirect()->route('empresas.index')->with('success', 'Empresa actualizada correctamente.');
    }

    public function destroy(Empresa $empresa): RedirectResponse
    {
        $tieneDatos = $empresa->usuarios()->exists()
            || $empresa->clientes()->withoutGlobalScopes()->exists()
            || DB::table('ventas')->where('id_empresa', $empresa->id_empresa)->exists();

        if ($tieneDatos) {
            return back()->with('error', 'La empresa tiene usuarios, clientes o ventas. Desactívala en lugar de eliminarla.');
        }

        $empresa->delete();

        if (Tenant::empresaId() === $empresa->id_empresa) {
            Tenant::olvidar();
        }

        return redirect()->route('empresas.index')->with('success', 'Empresa eliminada.');
    }

    private function reglas(?Empresa $empresa = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'ruc' => ['nullable', 'string', 'max:20', Rule::unique('empresa', 'ruc')->ignore($empresa?->id_empresa, 'id_empresa')],
            'email' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:200'],
        ];
    }

    private function soloEmpresa(array $datos): array
    {
        return array_intersect_key($datos, array_flip(['nombre', 'ruc', 'email', 'telefono', 'direccion']));
    }
}
