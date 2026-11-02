<?php

namespace Database\Seeders;

use App\Support\CatalogoPermisos;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Catálogo de permisos y roles plantilla. Idempotente: se puede ejecutar varias veces.
     */
    public function run(): void
    {
        CatalogoPermisos::sincronizar();
    }
}
