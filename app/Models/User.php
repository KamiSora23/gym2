<?php

namespace App\Models;

use App\Support\Permisos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'id_empresa',
        'id_sucursal',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class)->withoutGlobalScopes();
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal')->withoutGlobalScopes();
    }

    public function entrenador()
    {
        return $this->hasOne(Entrenador::class, 'user_id')->withoutGlobalScopes();
    }

    public function rolNombre(): ?string
    {
        return $this->role?->nombre;
    }

    /**
     * Verifica si el usuario tiene alguno de los roles indicados.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->rolNombre(), $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN, Role::ADMIN);
    }

    public function isStaff(): bool
    {
        return $this->hasRole(...Role::STAFF);
    }

    /**
     * ¿Puede realizar esta acción? Ver App\Support\Permisos::ACCIONES.
     */
    public function permite(string $accion): bool
    {
        return Permisos::permite($this, $accion);
    }

    public function isCliente(): bool
    {
        return $this->hasRole(Role::CLIENTE);
    }

    /**
     * Ruta a la que debe ir el usuario después de iniciar sesión.
     */
    public function homeRoute(): string
    {
        if ($this->isSuperAdmin()) {
            return session('empresa_id') ? route('dashboard') : route('plataforma.index');
        }

        return $this->isStaff() ? route('dashboard') : route('mi-cuenta');
    }
}
