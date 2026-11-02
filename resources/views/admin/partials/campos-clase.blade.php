@php
    $valor = fn (string $campo, $defecto = null) => $editando ? old($campo, $defecto) : ($clase?->{$campo} ?? $defecto);
    $hora = fn (string $campo, string $defecto) => substr((string) $valor($campo, $defecto), 0, 5);
@endphp

<div class="form-grid cols-4">
    <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" value="{{ $valor('nombre') }}" placeholder="Spinning" required></div>
    @if ($conCoach ?? true)
    <div class="form-group">
        <label>Coach</label>
        <select name="id_entrenador">
            <option value="">Sin asignar</option>
            @foreach ($entrenadores->where('activo', true) as $e)
                <option value="{{ $e->id_entrenador }}" @selected($valor('id_entrenador') == $e->id_entrenador)>{{ $e->nombre }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="form-group">
        <label>Día *</label>
        <select name="dia_semana" required>
            @foreach (\App\Models\Clase::DIAS as $num => $dia)
                <option value="{{ $num }}" @selected((int) $valor('dia_semana', 1) === $num)>{{ $dia }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group"><label>Cupo *</label><input type="number" min="1" name="cupo" value="{{ $valor('cupo', 20) }}" required></div>
    <div class="form-group"><label>Inicio *</label><input type="time" name="hora_inicio" value="{{ $hora('hora_inicio', '07:00') }}" required></div>
    <div class="form-group"><label>Fin *</label><input type="time" name="hora_fin" value="{{ $hora('hora_fin', '08:00') }}" required></div>
    @unless (\App\Support\Tenant::sucursalId() || ! ($conCoach ?? true))
        <div class="form-group">
            <label>Sucursal</label>
            <select name="id_sucursal">
                @foreach ($sucursales as $s)
                    <option value="{{ $s->id_sucursal }}" @selected($valor('id_sucursal') == $s->id_sucursal)>{{ $s->nombre }}</option>
                @endforeach
            </select>
        </div>
    @endunless
</div>
