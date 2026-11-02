<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutinaEjercicio extends Model
{
    protected $table = 'rutina_ejercicios';

    protected $fillable = ['id_rutina', 'dia', 'ejercicio', 'series', 'repeticiones', 'descanso', 'orden'];
}
