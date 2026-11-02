<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => fn () => Role::idDe(Role::CLIENTE),
            'id_empresa' => fn () => static::empresaDePrueba()->id_empresa,
            'id_sucursal' => fn (array $attributes) => Sucursal::withoutGlobalScopes()
                ->where('id_empresa', $attributes['id_empresa'])
                ->value('id_sucursal'),
        ];
    }

    /**
     * Empresa (con una sucursal) a la que pertenecen por defecto los usuarios de prueba.
     */
    public static function empresaDePrueba(): Empresa
    {
        $empresa = Empresa::firstOrCreate(['nombre' => 'Empresa de prueba'], ['activo' => true]);

        Sucursal::withoutGlobalScopes()->firstOrCreate(
            ['id_empresa' => $empresa->id_empresa, 'nombre' => 'Sede de prueba'],
            ['activo' => true],
        );

        return $empresa;
    }

    public function deEmpresa(Empresa $empresa, ?Sucursal $sucursal = null): static
    {
        return $this->state(fn () => [
            'id_empresa' => $empresa->id_empresa,
            'id_sucursal' => $sucursal?->id_sucursal,
        ]);
    }

    public function conRol(string $rol): static
    {
        return $this->state(fn () => ['role_id' => Role::idDe($rol)]);
    }

    public function admin(): static
    {
        return $this->conRol(Role::ADMIN);
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::idDe(Role::SUPER_ADMIN),
            'id_empresa' => null,
            'id_sucursal' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
