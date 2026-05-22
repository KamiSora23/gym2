<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([

            [
                'nombre' => 'admin'
            ],

            [
                'nombre' => 'cliente'
            ],

            [
                'nombre' => 'recepcionista'
            ],

            [
                'nombre' => 'cajera'
            ],

            [
                'nombre' => 'entrenador'
            ]

        ]);
    }
}
