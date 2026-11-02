<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotente: se puede ejecutar varias veces sin duplicar roles.
        foreach (array_keys(Role::ETIQUETAS) as $nombre) {
            Role::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
