<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Crea (o actualiza el rol de) la cuenta de super administrador.
     * Credenciales configurables en .env: SUPERADMIN_NAME, SUPERADMIN_EMAIL, SUPERADMIN_PASSWORD.
     */
    public function run(): void
    {
        $email = env('SUPERADMIN_EMAIL', 'superadmin@gym.com');

        $user = User::firstOrNew(['email' => $email]);

        $user->name = env('SUPERADMIN_NAME', 'Super Administrador');
        $user->role_id = Role::idDe(Role::SUPER_ADMIN);
        $user->email_verified_at ??= now();

        // Solo se fija la contraseña al crear la cuenta, para no pisar cambios posteriores.
        if (! $user->exists) {
            $user->password = env('SUPERADMIN_PASSWORD', 'SuperAdmin123!');
        }

        $user->save();
    }
}
