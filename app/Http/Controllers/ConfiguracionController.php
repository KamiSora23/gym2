<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function store(Request $request)
    {
        $config = Configuracion::first();

        if (!$config) {
            $config = new Configuracion();
        }

        $config->nombre_gym = $request->nombre_gym ?? $config->nombre_gym;
        $config->email = $request->email;
        $config->telefono = $request->telefono;
        $config->direccion = $request->direccion;
        $config->update([
            'color_primario' => $request->color_primario ?? $config->color_primario,
            'color_sidebar' => $request->color_sidebar ?? $config->color_sidebar,
            'color_secundario' => $request->color_secundario ?? $config->color_secundario,
        ]);
        $config->horario_lunes = $request->horario_lunes;
        $config->horario_martes = $request->horario_martes;
        $config->horario_miercoles = $request->horario_miercoles;
        $config->horario_jueves = $request->horario_jueves;
        $config->horario_viernes = $request->horario_viernes;
        $config->horario_sabado = $request->horario_sabado;
        $config->horario_domingo = $request->horario_domingo;


        $config->save();

        return back()->with('success', 'Configuración actualizada');
    }
}
