<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Identidad, contacto y horarios de la empresa actual.
 */
class ConfiguracionController extends Controller
{
    public function index(): View
    {
        return view('admin.configuracion');
    }

    public function store(Request $request): RedirectResponse
    {
        $hexColor = ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'];

        $datos = $request->validate([
            'nombre_gym' => ['required', 'string', 'max:100'],
            'slogan' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'tiktok' => ['nullable', 'url', 'max:255'],
            'color_primario' => $hexColor,
            'color_sidebar' => $hexColor,
            'color_secundario' => $hexColor,
            'horario_lunes' => ['nullable', 'string', 'max:60'],
            'horario_martes' => ['nullable', 'string', 'max:60'],
            'horario_miercoles' => ['nullable', 'string', 'max:60'],
            'horario_jueves' => ['nullable', 'string', 'max:60'],
            'horario_viernes' => ['nullable', 'string', 'max:60'],
            'horario_sabado' => ['nullable', 'string', 'max:60'],
            'horario_domingo' => ['nullable', 'string', 'max:60'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,svg', 'max:512'],
        ]);

        $empresaId = Tenant::empresaId();
        $config = Configuracion::firstOrNew(['id_empresa' => $empresaId], Configuracion::VALORES_POR_DEFECTO);

        foreach (['logo', 'favicon'] as $archivo) {
            unset($datos[$archivo]);

            if ($request->hasFile($archivo)) {
                if ($config->{$archivo}) {
                    Storage::disk('public')->delete($config->{$archivo});
                }

                $datos[$archivo] = $request->file($archivo)->store("branding/{$empresaId}", 'public');
            }
        }

        // Los colores vacíos conservan el valor actual.
        foreach (['color_primario', 'color_sidebar', 'color_secundario'] as $color) {
            $datos[$color] = $datos[$color] ?? $config->{$color};
        }

        $config->fill($datos)->save();

        return redirect()->route('configuracion.index')->with('success', 'Configuración actualizada correctamente.');
    }
}
