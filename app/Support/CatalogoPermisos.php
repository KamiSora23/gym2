<?php

namespace App\Support;

use App\Models\Permiso;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo global de permisos y plantillas de rol iniciales.
 *
 * Los permisos se agrupan en tres áreas que no se mezclan:
 * administrativa, comercial y deportiva.
 */
class CatalogoPermisos
{
    public const GRUPOS = [
        'administrativo' => 'Administración y seguridad',
        'comercial' => 'Gestión comercial',
        'deportivo' => 'Gestión deportiva',
    ];

    /**
     * codigo => [módulo, descripción, grupo]
     */
    public const PERMISOS = [
        // ----- Administración -----
        'dashboard.view' => ['Panel', 'Ver su panel de inicio', 'administrativo'],
        'reports.financial' => ['Panel', 'Ver indicadores financieros y reportes', 'administrativo'],
        'branches.view' => ['Sucursales', 'Ver sucursales', 'administrativo'],
        'branches.manage' => ['Sucursales', 'Crear y editar sucursales', 'administrativo'],
        'branches.switch' => ['Sucursales', 'Trabajar con todas las sucursales de la empresa', 'administrativo'],
        'users.view' => ['Empleados', 'Ver empleados y usuarios', 'administrativo'],
        'users.manage' => ['Empleados', 'Crear y editar empleados', 'administrativo'],
        'users.block' => ['Empleados', 'Bloquear y desbloquear usuarios', 'administrativo'],
        'roles.manage' => ['Roles y permisos', 'Crear y editar roles', 'administrativo'],
        'permissions.assign' => ['Roles y permisos', 'Conceder o restringir permisos a un usuario', 'administrativo'],
        'settings.manage' => ['Configuración', 'Configuración de la empresa', 'administrativo'],
        'audit.view' => ['Auditoría', 'Ver el historial de actividades', 'administrativo'],

        // ----- Comercial -----
        'clients.view' => ['Clientes', 'Ver todos los clientes de su ámbito', 'comercial'],
        'clients.view_assigned' => ['Clientes', 'Ver solo los clientes asignados', 'comercial'],
        'clients.create' => ['Clientes', 'Registrar clientes', 'comercial'],
        'clients.edit' => ['Clientes', 'Editar clientes', 'comercial'],
        'clients.delete' => ['Clientes', 'Eliminar clientes', 'comercial'],
        'clients.assign' => ['Clientes', 'Asignar coach o nutricionista a un cliente', 'comercial'],
        'memberships.view' => ['Membresías', 'Ver planes y membresías', 'comercial'],
        'memberships.manage_plans' => ['Membresías', 'Crear planes y modificar sus precios', 'comercial'],
        'memberships.freeze' => ['Membresías', 'Congelar y reanudar membresías', 'comercial'],
        'attendance.view' => ['Asistencias', 'Ver asistencias', 'comercial'],
        'attendance.create' => ['Asistencias', 'Registrar entradas y salidas', 'comercial'],
        'sales.view' => ['Ventas', 'Ver todas las ventas de su ámbito', 'comercial'],
        'sales.view_own' => ['Ventas', 'Ver solo sus propias ventas', 'comercial'],
        'sales.create' => ['Ventas', 'Registrar ventas y vender membresías', 'comercial'],
        'sales.cancel' => ['Ventas', 'Anular ventas', 'comercial'],
        'payments.create' => ['Pagos', 'Cobrar ventas pendientes', 'comercial'],
        'cash.open' => ['Caja', 'Abrir, operar y cerrar su caja', 'comercial'],
        'cash.movements' => ['Caja', 'Registrar ingresos y egresos manuales en su caja', 'comercial'],
        'cash.view_all' => ['Caja', 'Ver las cajas de otros empleados', 'comercial'],
        'cash.approve' => ['Caja', 'Aprobar diferencias de cierre', 'comercial'],
        'cash.manage' => ['Caja', 'Crear y configurar cajas', 'comercial'],
        'expenses.view' => ['Gastos', 'Ver gastos', 'comercial'],
        'expenses.create' => ['Gastos', 'Registrar gastos', 'comercial'],
        'expenses.delete' => ['Gastos', 'Eliminar gastos', 'comercial'],
        'products.view' => ['Productos', 'Ver catálogo de productos', 'comercial'],
        'products.create' => ['Productos', 'Crear y editar productos y categorías', 'comercial'],
        'products.edit_price' => ['Productos', 'Modificar precios de venta', 'comercial'],
        'inventory.view' => ['Inventario', 'Ver stock y movimientos', 'comercial'],
        'inventory.entries' => ['Inventario', 'Registrar entradas de mercadería', 'comercial'],
        'inventory.adjust' => ['Inventario', 'Registrar salidas, mermas y ajustes', 'comercial'],

        // ----- Deportivo -----
        'trainers.manage' => ['Entrenadores', 'Crear y editar coaches', 'deportivo'],
        'trainers.availability' => ['Entrenadores', 'Definir la disponibilidad de cualquier coach', 'deportivo'],
        'trainers.availability_own' => ['Entrenadores', 'Definir su propia disponibilidad', 'deportivo'],
        'classes.view' => ['Clases', 'Ver horario de clases', 'deportivo'],
        'classes.manage' => ['Clases', 'Crear y editar cualquier clase', 'deportivo'],
        'classes.manage_own' => ['Clases', 'Crear y editar sus propias clases', 'deportivo'],
        'enrollments.manage' => ['Clases', 'Inscribir clientes en cualquier clase', 'deportivo'],
        'enrollments.manage_own' => ['Clases', 'Inscribir alumnos en sus clases', 'deportivo'],
        'routines.manage' => ['Rutinas', 'Crear y asignar rutinas', 'deportivo'],
        'evaluations.manage' => ['Seguimiento', 'Registrar evaluaciones y progreso físico', 'deportivo'],
        'nutrition.view' => ['Nutrición', 'Ver planes nutricionales', 'deportivo'],
        'nutrition.manage' => ['Nutrición', 'Crear y editar planes nutricionales', 'deportivo'],
    ];

    /**
     * Un permiso amplio incluye a su versión reducida (ver todas las ventas ⇒ ver las propias).
     */
    public const IMPLICA = [
        'clients.view' => ['clients.view_assigned'],
        'sales.view' => ['sales.view_own'],
        'classes.manage' => ['classes.manage_own', 'classes.view'],
        'enrollments.manage' => ['enrollments.manage_own'],
        'trainers.availability' => ['trainers.availability_own'],
        'nutrition.manage' => ['nutrition.view'],
        'users.manage' => ['users.view'],
        'branches.manage' => ['branches.view'],
        'cash.view_all' => ['cash.open'],
    ];

    /**
     * Plantillas de rol del sistema: nombre => [etiqueta, descripción, permisos].
     * '*' = todos los permisos. El super admin no necesita permisos (tiene todo).
     */
    public static function plantillas(): array
    {
        $todos = array_keys(self::PERMISOS);

        return [
            Role::SUPER_ADMIN => ['Super Administrador', 'Control global de la plataforma.', []],
            Role::ADMIN => ['Administrador', 'Administración completa de su empresa.', $todos],
            Role::GERENTE => ['Gerente', 'Supervisa operaciones, cajas y resultados.', [
                'dashboard.view', 'reports.financial', 'branches.view', 'branches.switch', 'users.view', 'audit.view',
                'clients.view', 'clients.create', 'clients.edit', 'clients.assign',
                'memberships.view', 'memberships.freeze', 'attendance.view', 'attendance.create',
                'sales.view', 'sales.create', 'sales.cancel', 'payments.create',
                'cash.open', 'cash.movements', 'cash.view_all', 'cash.approve',
                'expenses.view', 'expenses.create', 'products.view', 'inventory.view',
                'classes.view', 'classes.manage', 'enrollments.manage',
            ]],
            Role::RECEPCIONISTA => ['Recepcionista', 'Atención, inscripciones, cobros y caja de su sucursal.', [
                'dashboard.view', 'clients.view', 'clients.create', 'clients.edit', 'clients.assign',
                'memberships.view', 'attendance.view', 'attendance.create',
                'sales.view_own', 'sales.create', 'payments.create', 'cash.open', 'cash.movements',
                'products.view', 'inventory.view', 'classes.view', 'enrollments.manage',
            ]],
            Role::CAJERA => ['Cajero', 'Cobros, ventas y caja de su sucursal.', [
                'dashboard.view', 'clients.view', 'clients.create', 'clients.edit',
                'memberships.view', 'attendance.view', 'attendance.create',
                'sales.view_own', 'sales.create', 'payments.create', 'cash.open', 'cash.movements',
                'expenses.view', 'expenses.create', 'products.view', 'inventory.view',
            ]],
            Role::ENTRENADOR => ['Entrenador', 'Clases, rutinas y seguimiento de sus alumnos.', [
                'dashboard.view', 'clients.view_assigned', 'memberships.view', 'attendance.view', 'attendance.create',
                'classes.view', 'classes.manage_own', 'enrollments.manage_own', 'trainers.availability_own',
                'routines.manage', 'evaluations.manage',
            ]],
            Role::NUTRICIONISTA => ['Nutricionista', 'Evaluaciones y planes nutricionales de sus pacientes.', [
                'dashboard.view', 'clients.view_assigned', 'evaluations.manage', 'nutrition.view', 'nutrition.manage',
            ]],
            Role::ALMACENERO => ['Almacenero', 'Productos, stock y movimientos de inventario.', [
                'dashboard.view', 'products.view', 'products.create', 'inventory.view', 'inventory.entries', 'inventory.adjust', 'sales.view',
            ]],
            Role::CLIENTE => ['Cliente', 'Portal personal del socio.', []],
        ];
    }

    /**
     * Crea o actualiza el catálogo y las plantillas. Seguro de ejecutar varias veces:
     * los roles propios de cada empresa no se tocan.
     */
    public static function sincronizar(bool $reiniciarPlantillas = false): void
    {
        if (! Schema::hasTable('permisos')) {
            return;
        }

        foreach (self::PERMISOS as $codigo => [$modulo, $descripcion, $grupo]) {
            Permiso::updateOrCreate(['codigo' => $codigo], compact('modulo', 'descripcion', 'grupo'));
        }

        $ids = Permiso::pluck('id', 'codigo');

        foreach (self::plantillas() as $nombre => [$etiqueta, $descripcion, $permisos]) {
            $rol = Role::whereNull('id_empresa')->firstOrNew(['nombre' => $nombre]);
            $nuevo = ! $rol->exists;
            $rol->fill(['etiqueta' => $etiqueta, 'descripcion' => $descripcion, 'es_sistema' => true])->save();

            // El administrador siempre recibe todos los permisos, incluidos los nuevos del catálogo.
            if ($nombre === Role::ADMIN || $nuevo || $reiniciarPlantillas || ! DB::table('role_permiso')->where('role_id', $rol->id)->exists()) {
                $rol->permisos()->sync($ids->only($permisos)->values());
            }
        }

        Autorizacion::olvidar();
    }
}
