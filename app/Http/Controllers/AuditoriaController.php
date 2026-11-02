<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Empresa;
use App\Models\User;
use App\Support\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Historial de actividades: de la empresa actual o, para el super admin, de toda la plataforma.
 */
class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        return $this->listado($request, Tenant::empresaId(), false);
    }

    public function plataforma(Request $request): View
    {
        $empresa = $request->filled('empresa') ? (int) $request->empresa : null;

        return $this->listado($request, $empresa, true);
    }

    private function listado(Request $request, ?int $empresaId, bool $global): View
    {
        $registros = Auditoria::with(['usuario', 'empresa'])
            ->when($empresaId, fn ($q) => $q->where('id_empresa', $empresaId))
            ->when($request->filled('usuario'), fn ($q) => $q->where('user_id', $request->usuario))
            ->when($request->filled('accion'), fn ($q) => $q->where('accion', 'like', $request->accion.'%'))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->orderByDesc('id')
            ->paginate(40)
            ->withQueryString();

        return view('admin.auditoria', [
            'registros' => $registros,
            'global' => $global,
            'usuarios' => User::when($empresaId, fn ($q) => $q->where('id_empresa', $empresaId))->orderBy('name')->get(['id', 'name']),
            'empresas' => $global ? Empresa::orderBy('nombre')->get(['id_empresa', 'nombre']) : collect(),
            'acciones' => [
                'venta' => 'Ventas', 'caja' => 'Caja', 'inventario' => 'Inventario', 'producto' => 'Productos',
                'usuario' => 'Usuarios', 'permisos' => 'Permisos y roles', 'cliente' => 'Clientes',
                'membresia' => 'Membresías', 'sesion' => 'Inicios de sesión', 'empresa' => 'Empresas', 'nutricion' => 'Nutrición',
            ],
        ]);
    }
}
