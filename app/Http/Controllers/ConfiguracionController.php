<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Sucursal;
use App\Support\Tema;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Identidad, colores, contacto y horarios de la empresa actual.
 */
class ConfiguracionController extends Controller
{
    public function index(): View
    {
        return view('admin.configuracion', [
            'c' => Configuracion::paraEmpresa(Tenant::empresaId()),
            'presets' => Tema::PRESETS,
            'sucursales' => Sucursal::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $hex = ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'];

        $datos = $request->validate([
            'nombre_gym' => ['required', 'string', 'max:100'],
            'slogan' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'tiktok' => ['nullable', 'url', 'max:255'],
            'tema' => ['nullable', 'string', 'max:30'],
            'moneda' => ['nullable', Rule::in(array_keys(Configuracion::MONEDAS))],
            'color_primario' => $hex,
            'color_sidebar' => $hex,
            'color_secundario' => $hex,
            'color_fondo' => $hex,
            'web_color_primario' => $hex,
            'web_color_oscuro' => $hex,
            'horario_lunes' => ['nullable', 'string', 'max:60'],
            'horario_martes' => ['nullable', 'string', 'max:60'],
            'horario_miercoles' => ['nullable', 'string', 'max:60'],
            'horario_jueves' => ['nullable', 'string', 'max:60'],
            'horario_viernes' => ['nullable', 'string', 'max:60'],
            'horario_sabado' => ['nullable', 'string', 'max:60'],
            'horario_domingo' => ['nullable', 'string', 'max:60'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,svg', 'max:512'],
            'quitar_logo' => ['nullable', 'boolean'],
            'sucursal_colores' => ['nullable', 'array'],
            'sucursal_colores.*.propio' => ['nullable', 'boolean'],
            'sucursal_colores.*.color_primario' => $hex,
            'sucursal_colores.*.color_sidebar' => $hex,
        ]);

        $empresaId = Tenant::empresaId();
        $config = Configuracion::firstOrNew(['id_empresa' => $empresaId], Configuracion::VALORES_POR_DEFECTO);

        if ($request->boolean('quitar_logo') && $config->logo) {
            Storage::disk('public')->delete($config->logo);
            $config->logo = null;
        }

        foreach (['logo', 'favicon'] as $archivo) {
            if ($request->hasFile($archivo)) {
                if ($config->{$archivo}) {
                    Storage::disk('public')->delete($config->{$archivo});
                }

                $datos[$archivo] = $request->file($archivo)->store("branding/{$empresaId}", 'public');
            } else {
                unset($datos[$archivo]);
            }
        }

        // Los colores vacíos conservan el valor actual.
        foreach (['color_primario', 'color_sidebar', 'color_secundario', 'color_fondo', 'web_color_primario', 'web_color_oscuro'] as $color) {
            $datos[$color] = isset($datos[$color]) ? strtolower($datos[$color]) : $config->{$color};
        }

        $datos['web_usa_colores_panel'] = $request->boolean('web_usa_colores_panel');
        $datos['moneda'] = $datos['moneda'] ?? ($config->moneda ?: 'S/');
        $coloresSucursal = $datos['sucursal_colores'] ?? [];
        unset($datos['sucursal_colores'], $datos['quitar_logo']);

        $config->fill($datos)->save();

        // Colores propios de cada sucursal (solo de esta empresa: el modelo filtra por empresa).
        foreach (Sucursal::whereIn('id_sucursal', array_keys($coloresSucursal))->get() as $sucursal) {
            $c = $coloresSucursal[$sucursal->id_sucursal];
            $propio = ! empty($c['propio']);

            $sucursal->update([
                'color_primario' => $propio ? ($c['color_primario'] ?? null) : null,
                'color_sidebar' => $propio ? ($c['color_sidebar'] ?? null) : null,
            ]);
        }

        $seccion = preg_replace('/[^a-z-]/', '', (string) $request->input('_seccion', 'identidad'));

        return redirect()->to(route('configuracion.index').'#'.$seccion)
            ->with('success', 'Configuración guardada. Los nuevos colores ya se aplican al panel y al sitio web.');
    }
}
