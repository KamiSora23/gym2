@php
    $v = fn (string $campo, $defecto = null) => $editando ? old($campo, $defecto) : ($u?->{$campo} ?? $defecto);
@endphp

<div class="form-grid cols-4">
    <div class="form-group"><label>Nombre *</label><input type="text" name="name" value="{{ $v('name') }}" required></div>
    <div class="form-group"><label>Correo *</label><input type="email" name="email" value="{{ $v('email') }}" required autocomplete="off"></div>
    <div class="form-group">
        <label>Rol *</label>
        <select name="role_id" required>
            @foreach ($roles as $r)
                <option value="{{ $r->id }}" @selected($v('role_id', $roles->firstWhere('nombre', 'admin')?->id) == $r->id)>{{ $r->etiqueta }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Empresa</label>
        <select name="id_empresa">
            <option value="">Elige una empresa…</option>
            @foreach ($empresas as $e)<option value="{{ $e->id_empresa }}" @selected($v('id_empresa') == $e->id_empresa)>{{ $e->nombre }}</option>@endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Sucursal</label>
        <select name="id_sucursal" data-valor="{{ $v('id_sucursal') }}">
            <option value="">Todas / sin asignar</option>
        </select>
    </div>
    <div class="form-group"><label>{{ $u ? 'Nueva contraseña (opcional)' : 'Contraseña *' }}</label><input type="password" name="password" autocomplete="new-password" @required(! $u)></div>
    <div class="form-group"><label>Confirmar contraseña</label><input type="password" name="password_confirmation" autocomplete="new-password" @required(! $u)></div>
</div>
