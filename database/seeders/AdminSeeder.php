<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gym.com'],
            [
                'name' => 'Administrador',
                'password' => '12345678',
                'role_id' => Role::idDe(Role::ADMIN),
                'email_verified_at' => now(),
            ],
        );
    }
}
