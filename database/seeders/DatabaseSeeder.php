<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Datos de demostración (planes, productos, coaches, clases, clientes, ventas):
     *   php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            SuperAdminSeeder::class,
            EmpresaPrincipalSeeder::class,
        ]);
    }
}
