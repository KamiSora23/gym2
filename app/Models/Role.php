<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const SUPER_ADMIN = 'super_admin';

    public const ADMIN = 'admin';

    public const CLIENTE = 'cliente';

    public const RECEPCIONISTA = 'recepcionista';

    public const CAJERA = 'cajera';

    public const ENTRENADOR = 'entrenador';

    /**
     * Roles con acceso al panel administrativo.
     */
    public const STAFF = [
        self::SUPER_ADMIN,
        self::ADMIN,
        self::RECEPCIONISTA,
        self::CAJERA,
        self::ENTRENADOR,
    ];

    /**
     * Nombre legible de cada rol.
     */
    public const ETIQUETAS = [
        self::SUPER_ADMIN => 'Super Administrador',
        self::ADMIN => 'Administrador',
        self::CLIENTE => 'Cliente',
        self::RECEPCIONISTA => 'Recepcionista',
        self::CAJERA => 'Cajera',
        self::ENTRENADOR => 'Entrenador',
    ];

    protected $fillable = ['nombre'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getEtiquetaAttribute(): string
    {
        return self::ETIQUETAS[$this->nombre] ?? ucfirst($this->nombre);
    }

    public static function idDe(string $nombre): int
    {
        return static::firstOrCreate(['nombre' => $nombre])->id;
    }
}
