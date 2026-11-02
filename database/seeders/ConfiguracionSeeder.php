<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        if (Configuracion::query()->exists()) {
            return;
        }

        Configuracion::create(Configuracion::VALORES_POR_DEFECTO + [
            'slogan' => 'Tu mejor versión empieza aquí',
            'email' => 'info@gymimpulso.com',
            'telefono' => '+51 961 179 515',
            'direccion' => 'Av. Principal 123, Lima',
            'horario_lunes' => '6:00 AM - 10:00 PM',
            'horario_martes' => '6:00 AM - 10:00 PM',
            'horario_miercoles' => '6:00 AM - 10:00 PM',
            'horario_jueves' => '6:00 AM - 10:00 PM',
            'horario_viernes' => '6:00 AM - 10:00 PM',
            'horario_sabado' => '8:00 AM - 6:00 PM',
            'horario_domingo' => 'Cerrado',
        ]);
    }
}
