<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $fillable = [
        'nombre_gym',
        'slogan',
        'logo',
        'favicon',
        'color_primario',
        'color_sidebar',
        'color_secundario',
        'telefono',
        'correo',
        'direccion',
        'facebook',
        'instagram',
        'tiktok',
        'imagen_login',

        // NUEVO
        'horario_lunes',
        'horario_martes',
        'horario_miercoles',
        'horario_jueves',
        'horario_viernes',
        'horario_sabado',
        'horario_domingo',
    ];
}
