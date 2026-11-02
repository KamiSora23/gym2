<?php

namespace App\Http\Controllers\Plataforma;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Empresa;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use App\Support\CatalogoPermisos;
use App\Support\Tema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Paneles exclusivos del super admin: visión global de la plataforma.
 */
class PlataformaController extends Controller
{
    public function index(): View
    {
        $inicioMes = today()->startOfMonth()->toDateString();

        $ventasMes = DB::table('ventas')->where('estado', '!=', 'anulado')->where('fecha', '>=', $inicioMes)
            ->groupBy('id_empresa')->selectRaw('id_empresa, sum(total) as valor')->pluck('valor', 'id_empresa');
        $clientes = DB::table('clientes')->groupBy('id_empresa')->selectRaw('id_empresa, count(*) as valor')->pluck('valor', 'id_empresa');
        $sucursales = DB::table('sucursales')->groupBy('id_empresa')->selectRaw('id_empresa, count(*) as valor')->pluck('valor', 'id_empresa');
        $personal = User::whereNotNull('id_empresa')->whereHas('role', fn ($q) => $q->where('nombre', '!=', Role::CLIENTE))
            ->groupBy('id_empresa')->selectRaw('id_empresa, count(*) as valor')->pluck('valor', 'id_empresa');
        $membresias = DB::table('membresias')->join('clientes', 'clientes.id_cliente', '=', 'membresias.id_cliente')
            ->where('membresias.estado', 'activo')->whereDate('membresias.fecha_fin', '>=', today())
            ->groupBy('clientes.id_empresa')
            ->selectRaw('clientes.id_empresa as empresa, count(distinct membresias.id_cliente) as valor')
            ->pluck('valor', 'empresa');
        $ultimaVenta = DB::table('ventas')->groupBy('id_empresa')->selectRaw('id_empresa, max(fecha) as valor')->pluck('valor', 'id_empresa');

        $empresas = Empresa::orderBy('nombre')->get()->map(function ($e) use ($ventasMes, $clientes, $sucursales, $personal, $membresias, $ultimaVenta) {
            $id = $e->id_empresa;
            $e->ventas_mes = (float) ($ventasMes[$id] ?? 0);
            $e->num_clientes = (int) ($clientes[$id] ?? 0);
            $e->num_sucursales = (int) ($sucursales[$id] ?? 0);
            $e->num_personal = (int) ($personal[$id] ?? 0);
            $e->membresias_activas = (int) ($membresias[$id] ?? 0);
            $e->ultima_venta = isset($ultimaVenta[$id]) ? Carbon::parse($ultimaVenta[$id]) : null;

            return $e;
        });

        // Ventas de toda la plataforma en los últimos 6 meses.
        $meses = collect(range(5, 0))->map(fn ($i) => today()->startOfMonth()->subMonths($i));
        $ventasPorMes = DB::table('ventas')->where('estado', '!=', 'anulado')
            ->where('fecha', '>=', $meses->first()->toDateString())
            ->get(['fecha', 'total'])
            ->groupBy(fn ($v) => substr((string) $v->fecha, 0, 7))
            ->map(fn ($grupo) => $grupo->sum('total'));

        return view('plataforma.index', [
            'empresas' => $empresas,
            'totales' => [
                'empresas' => $empresas->count(),
                'activas' => $empresas->where('activo', true)->count(),
                'sucursales' => $empresas->sum('num_sucursales'),
                'personal' => $empresas->sum('num_personal'),
                'clientes' => $empresas->sum('num_clientes'),
                'ventas_mes' => $empresas->sum('ventas_mes'),
                'super_admins' => User::whereHas('role', fn ($q) => $q->where('nombre', Role::SUPER_ADMIN))->count(),
            ],
            'grafico' => [
                'labels' => $meses->map(fn ($m) => ucfirst($m->locale('es')->translatedFormat('M Y')))->values(),
                'datos' => $meses->map(fn ($m) => round((float) ($ventasPorMes[$m->format('Y-m')] ?? 0), 2))->values(),
            ],
        ]);
    }

    public function roles(): View
    {
        $conteo = User::query()->groupBy('role_id')->selectRaw('role_id, count(*) as valor')->pluck('valor', 'role_id');

        return view('plataforma.roles', [
            'roles' => Role::whereNull('id_empresa')->with('permisos:id,codigo')->orderBy('id')->get()
                ->each(fn ($r) => $r->usuarios_count = (int) ($conteo[$r->id] ?? 0)),
            'rolesPropios' => Role::whereNotNull('id_empresa')->with('empresa')->withCount('permisos')->orderBy('id_empresa')->get(),
            'permisos' => Permiso::orderBy('id')->get()->groupBy('grupo'),
            'grupos' => CatalogoPermisos::GRUPOS,
        ]);
    }

    public function configuracion(): View
    {
        return view('plataforma.configuracion', ['plataforma' => Configuracion::plataforma(), 'presets' => Tema::PRESETS]);
    }

    public function guardarConfiguracion(Request $request): RedirectResponse
    {
        $hex = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];

        $datos = $request->validate([
            'nombre_gym' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'color_primario' => $hex,
            'color_sidebar' => $hex,
            'color_secundario' => $hex,
            'color_fondo' => $hex,
            'tema' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ]);

        $config = Configuracion::query()->whereNull('id_empresa')->first() ?? new Configuracion(Configuracion::VALORES_POR_DEFECTO);

        unset($datos['logo']);
        if ($request->hasFile('logo')) {
            if ($config->logo) {
                Storage::disk('public')->delete($config->logo);
            }
            $datos['logo'] = $request->file('logo')->store('branding/plataforma', 'public');
        }

        $config->fill($datos + ['id_empresa' => null])->save();

        return redirect()->route('plataforma.configuracion')->with('success', 'Configuración de la plataforma actualizada.');
    }
}
