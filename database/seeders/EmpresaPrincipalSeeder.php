<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Empresa inicial con su sucursal, configuración y administrador (admin@gym.com).
 */
class EmpresaPrincipalSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::firstOrCreate(
            ['nombre' => 'GYM IMPULSO'],
            [
                'email' => 'info@gymimpulso.com',
                'telefono' => '+51 961 179 515',
                'direccion' => 'Av. Principal 123, Lima',
                'activo' => true,
            ],
        );

        $sucursal = Sucursal::withoutGlobalScopes()->firstOrCreate(
            ['id_empresa' => $empresa->id_empresa, 'nombre' => 'Sede principal'],
            ['direccion' => $empresa->direccion, 'telefono' => $empresa->telefono, 'activo' => true],
        );

        Configuracion::firstOrCreate(
            ['id_empresa' => $empresa->id_empresa],
            Configuracion::VALORES_POR_DEFECTO + [
                'slogan' => 'Tu mejor versión empieza aquí',
                'email' => $empresa->email,
                'telefono' => $empresa->telefono,
                'direccion' => $empresa->direccion,
                'horario_lunes' => '6:00 AM - 10:00 PM',
                'horario_martes' => '6:00 AM - 10:00 PM',
                'horario_miercoles' => '6:00 AM - 10:00 PM',
                'horario_jueves' => '6:00 AM - 10:00 PM',
                'horario_viernes' => '6:00 AM - 10:00 PM',
                'horario_sabado' => '8:00 AM - 6:00 PM',
                'horario_domingo' => 'Cerrado',
            ],
        );

        // Credenciales configurables en .env: ADMIN_EMAIL y ADMIN_PASSWORD.
        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@gym.com')],
            [
                'name' => env('ADMIN_NAME', 'Administrador'),
                'password' => env('ADMIN_PASSWORD', '12345678'),
                'role_id' => Role::idDe(Role::ADMIN),
                'email_verified_at' => now(),
            ],
        );

        $admin->update([
            'id_empresa' => $admin->id_empresa ?? $empresa->id_empresa,
            'id_sucursal' => $admin->id_sucursal ?? $sucursal->id_sucursal,
        ]);
    }
}
