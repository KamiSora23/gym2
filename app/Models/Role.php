<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rol: agrupa permisos. Los roles del sistema (id_empresa nulo) son plantillas;
 * cada empresa puede crear roles propios.
 */
class Role extends Model
{
    public const SUPER_ADMIN = 'super_admin';

    public const ADMIN = 'admin';

    public const GERENTE = 'gerente';

    public const CLIENTE = 'cliente';

    public const RECEPCIONISTA = 'recepcionista';

    public const CAJERA = 'cajera';

    public const ENTRENADOR = 'entrenador';

    public const NUTRICIONISTA = 'nutricionista';

    public const ALMACENERO = 'almacenero';

    /**
     * Nombre legible de los roles del sistema (por si aún no se sincronizó la base de datos).
     */
    public const ETIQUETAS = [
        self::SUPER_ADMIN => 'Super Administrador',
        self::ADMIN => 'Administrador',
        self::GERENTE => 'Gerente',
        self::RECEPCIONISTA => 'Recepcionista',
        self::CAJERA => 'Cajero',
        self::ENTRENADOR => 'Entrenador',
        self::NUTRICIONISTA => 'Nutricionista',
        self::ALMACENERO => 'Almacenero',
        self::CLIENTE => 'Cliente',
    ];

    protected $fillable = ['nombre', 'id_empresa', 'etiqueta', 'descripcion', 'es_sistema'];

    protected function casts(): array
    {
        return ['es_sistema' => 'boolean'];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'role_permiso', 'role_id', 'permiso_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function getEtiquetaAttribute(?string $valor): string
    {
        return $valor ?: (self::ETIQUETAS[$this->nombre] ?? ucfirst(str_replace('_', ' ', $this->nombre)));
    }

    /**
     * Roles que puede usar una empresa: las plantillas del sistema y sus roles propios.
     */
    public function scopeDisponiblesPara($query, ?int $empresaId)
    {
        return $query->where(fn ($q) => $q->whereNull('id_empresa')->orWhere('id_empresa', $empresaId));
    }

    /**
     * Id de un rol del sistema por su nombre.
     */
    public static function idDe(string $nombre): int
    {
        return static::whereNull('id_empresa')->firstOrCreate(
            ['nombre' => $nombre],
            ['etiqueta' => self::ETIQUETAS[$nombre] ?? null, 'es_sistema' => true],
        )->id;
    }
}
