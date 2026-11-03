<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('gym:superadmin {email?} {--name=}', function (?string $email = null) {
    $email ??= $this->ask('Correo del super administrador');
    $name = $this->option('name') ?: $this->ask('Nombre', 'Super Administrador');
    $password = $this->secret('Contraseña (mínimo 8 caracteres)');

    $validator = Validator::make(
        ['email' => $email, 'password' => $password],
        ['email' => ['required', 'email'], 'password' => ['required', Password::defaults()]],
    );

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

        return 1;
    }

    $user = User::updateOrCreate(['email' => $email], [
        'name' => $name,
        'password' => $password,
        'role_id' => Role::idDe(Role::SUPER_ADMIN),
        'email_verified_at' => now(),
    ]);

    $this->info("Super administrador listo: {$user->email}");

    return 0;
})->purpose('Crea o promueve un usuario a super administrador');

Artisan::command('gym:preparar {--demo : Carga también los datos de demostración}', function () {
    // Se ejecuta al arrancar el contenedor: deja la base de datos y la app listas para producción.
    $this->call('migrate', ['--force' => true]);

    if (User::count() === 0) {
        $this->info('Base de datos nueva: creando roles, super administrador y empresa principal…');
        $this->call('db:seed', ['--force' => true]);

        if ($this->option('demo')) {
            $this->call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
        }
    } else {
        // Mantiene al día el catálogo de permisos y los roles del sistema.
        $this->call('db:seed', ['--class' => 'RolesSeeder', '--force' => true]);
    }

    if (! file_exists(public_path('storage'))) {
        $this->call('storage:link');
    }

    $this->call('optimize');

    return 0;
})->purpose('Migra, siembra la primera vez y optimiza la aplicación (usado por Docker)');
