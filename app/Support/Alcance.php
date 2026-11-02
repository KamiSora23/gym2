<?php

namespace App\Support;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ámbito de datos (data scope) sobre los clientes.
 *
 * - clients.view: todos los clientes de su empresa.
 * - clients.view_assigned: solo los asignados (coach personal, alumnos de sus clases
 *   o pacientes del nutricionista).
 * La empresa ya la filtra el modelo; aquí se resuelve la asignación.
 */
class Alcance
{
    public static function veTodosLosClientes(User $user): bool
    {
        return Permisos::puede($user, 'clients.view');
    }

    public static function clientes(User $user, Builder $query): Builder
    {
        if (static::veTodosLosClientes($user)) {
            return $query;
        }

        if (! Permisos::puede($user, 'clients.view_assigned')) {
            return $query->whereRaw('1 = 0');
        }

        $idCoach = $user->entrenador?->id_entrenador ?? 0;

        return $query->where(function ($q) use ($user, $idCoach) {
            $q->where('clientes.id_nutricionista', $user->id)
                ->orWhere('clientes.id_entrenador', $idCoach)
                ->orWhereHas('clases', fn ($c) => $c->where('clases.id_entrenador', $idCoach));
        });
    }

    public static function puedeVerCliente(User $user, Cliente $cliente): bool
    {
        if (static::veTodosLosClientes($user)) {
            return true;
        }

        if (! Permisos::puede($user, 'clients.view_assigned')) {
            return false;
        }

        if ((int) $cliente->id_nutricionista === $user->id) {
            return true;
        }

        return (bool) $user->entrenador?->esAlumno($cliente);
    }

    public static function exigirCliente(User $user, Cliente $cliente): void
    {
        abort_unless(static::puedeVerCliente($user, $cliente), 403, 'Este cliente no está asignado a ti.');
    }
}
