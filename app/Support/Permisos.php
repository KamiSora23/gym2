<?php

namespace App\Support;

use App\Models\Role;
use App\Models\User;

/**
 * Permisos del sistema en dos niveles:
 *  - MODULOS: qué páginas del panel puede abrir cada rol.
 *  - ACCIONES: qué puede hacer dentro de ellas (crear, editar, anular…).
 * El super admin siempre puede todo.
 */
class Permisos
{
    public const MODULOS = [
        'resumen' => [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA, Role::ENTRENADOR],
        'clientes' => [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA, Role::ENTRENADOR],
        'asistencias' => [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA, Role::ENTRENADOR],
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
     * Acciones dentro de los módulos: [descripción, roles].
     */
    public const ACCIONES = [
        'clientes.crear' => ['Registrar y editar clientes', [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA]],
        'clientes.eliminar' => ['Eliminar clientes', [Role::ADMIN]],
        'clientes.asignar_coach' => ['Asignar coach personal a un cliente', [Role::ADMIN, Role::RECEPCIONISTA]],
        'ventas.vender' => ['Registrar ventas', [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA]],
        'ventas.cobrar' => ['Cobrar ventas pendientes', [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA]],
        'ventas.anular' => ['Anular ventas (devuelve stock)', [Role::ADMIN, Role::CAJERA]],
        'gastos.registrar' => ['Registrar gastos', [Role::ADMIN, Role::CAJERA]],
        'gastos.eliminar' => ['Eliminar gastos', [Role::ADMIN]],
        'catalogo.editar' => ['Crear y editar productos y categorías', [Role::ADMIN]],
        'stock.entrada' => ['Registrar entradas de stock', [Role::ADMIN, Role::CAJERA]],
        'stock.salida' => ['Retirar stock (mermas y ajustes)', [Role::ADMIN]],
        'planes.editar' => ['Crear y editar planes de membresía', [Role::ADMIN]],
        'coaches.gestionar' => ['Crear, editar y eliminar coaches', [Role::ADMIN]],
        'clases.todas' => ['Crear y editar cualquier clase del horario', [Role::ADMIN, Role::RECEPCIONISTA]],
        'clases.propias' => ['Crear y editar sus propias clases', [Role::ENTRENADOR]],
        'inscripciones.todas' => ['Inscribir clientes en cualquier clase', [Role::ADMIN, Role::RECEPCIONISTA]],
        'inscripciones.propias' => ['Inscribir alumnos en sus clases', [Role::ENTRENADOR]],
        'disponibilidad.propia' => ['Definir su disponibilidad semanal', [Role::ENTRENADOR]],
        'disponibilidad.todas' => ['Definir la disponibilidad de cualquier coach', [Role::ADMIN]],
        'rutinas.gestionar' => ['Crear rutinas y registrar progreso de alumnos', [Role::ADMIN, Role::ENTRENADOR]],
        'asistencias.registrar' => ['Registrar entradas y salidas', [Role::ADMIN, Role::RECEPCIONISTA, Role::CAJERA, Role::ENTRENADOR]],
    ];

    /**
     * Qué hace cada rol, para mostrarlo en la pantalla de roles.
     */
    public const DESCRIPCIONES = [
        Role::SUPER_ADMIN => 'Dueño de la plataforma: crea y supervisa empresas y usuarios globales, y puede entrar a administrar cualquier empresa.',
        Role::ADMIN => 'Administra su empresa: sucursales, personal, coaches, configuración, catálogo, planes y todas las operaciones.',
        Role::RECEPCIONISTA => 'Atiende la recepción de su sucursal: clientes, check-in, venta y cobro de membresías, horario de clases e inscripciones.',
        Role::CAJERA => 'Maneja la caja de su sucursal: ventas, cobros, anulaciones, gastos, entradas de stock y check-in.',
        Role::ENTRENADOR => 'Coach: crea y gestiona sus clases, define su disponibilidad, inscribe alumnos y les arma rutinas y seguimiento. No ve finanzas.',
        Role::CLIENTE => 'Socio: en "Mi cuenta" ve su membresía, su rutina y su progreso, y se inscribe o retira de clases.',
    ];

    public static function puede(?User $user, string $modulo): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasRole(...(self::MODULOS[$modulo] ?? []));
    }

    public static function permite(?User $user, string $accion): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasRole(...(self::ACCIONES[$accion][1] ?? []));
    }

    /**
     * Corta la petición con 403 si el usuario no puede realizar la acción.
     */
    public static function exigir(string $accion): void
    {
        abort_unless(static::permite(auth()->user(), $accion), 403, 'No tienes permiso para realizar esta acción.');
    }

    /**
     * Para usar en rutas: ->middleware(Permisos::middleware('ventas'))
     */
    public static function middleware(string $modulo): string
    {
        return 'role:'.implode(',', self::MODULOS[$modulo] ?: [Role::SUPER_ADMIN]);
    }
}
