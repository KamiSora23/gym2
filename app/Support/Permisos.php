<?php

namespace App\Support;

use App\Models\Role;
use App\Models\User;

/**
 * Qué roles pueden usar cada módulo del panel. El super admin siempre puede.
 */
class Permisos
{
    public const MODULOS = [
        'resumen' => [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA, Role::ENTRENADOR],
        'clientes' => [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA, Role::ENTRENADOR],
        'asistencias' => [Role::ADMIN, Role::RECEPCIONISTA, Role::ENTRENADOR],
        'ventas' => [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA],
        'gastos' => [Role::ADMIN, Role::CAJERA],
        'catalogo' => [Role::ADMIN, Role::CAJERA],
        'planes' => [Role::ADMIN, Role::RECEPCIONISTA],
        'entrenamiento' => [Role::ADMIN, Role::RECEPCIONISTA, Role::ENTRENADOR],
        'sucursales' => [Role::ADMIN],
        'usuarios' => [Role::ADMIN],
        'configuracion' => [Role::ADMIN],
        'empresas' => [],
    ];

    public const NOMBRES = [
        'resumen' => 'Resumen',
        'clientes' => 'Clientes',
        'asistencias' => 'Asistencias',
        'ventas' => 'Ventas',
        'gastos' => 'Gastos',
        'catalogo' => 'Catálogo',
        'planes' => 'Planes y membresías',
        'entrenamiento' => 'Entrenamiento',
        'sucursales' => 'Sucursales',
        'usuarios' => 'Usuarios y roles',
        'configuracion' => 'Configuración',
        'empresas' => 'Empresas (plataforma)',
    ];

    /**
     * Qué hace cada rol, para mostrarlo en la pantalla de roles.
     */
    public const DESCRIPCIONES = [
        Role::SUPER_ADMIN => 'Dueño de la plataforma: crea y supervisa empresas y usuarios globales, y puede entrar a administrar cualquier empresa.',
        Role::ADMIN => 'Administra su empresa: sucursales, personal, configuración, catálogo, planes y todas las operaciones.',
        Role::RECEPCIONISTA => 'Atiende la recepción de su sucursal: clientes, check-in, venta de membresías y horario de clases.',
        Role::CAJERA => 'Maneja la caja de su sucursal: ventas, cobros pendientes, anulaciones, gastos y stock.',
        Role::ENTRENADOR => 'Coach: ve sus clases y sus alumnos, y registra asistencias. No ve información financiera.',
        Role::CLIENTE => 'Socio del gimnasio: entra a "Mi cuenta" para ver su membresía, sus clases y sus compras.',
    ];

    public static function puede(?User $user, string $modulo): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasRole(...(self::MODULOS[$modulo] ?? []));
    }

    /**
     * Para usar en rutas: ->middleware(Permisos::middleware('ventas'))
     */
    public static function middleware(string $modulo): string
    {
        return 'role:'.implode(',', self::MODULOS[$modulo] ?: [Role::SUPER_ADMIN]);
    }
}
