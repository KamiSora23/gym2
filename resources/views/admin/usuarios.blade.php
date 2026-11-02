<x-admin-layout titulo="Usuarios y roles" icono="fa-users-gear" modulo="usuarios"
    subtitulo="Cuentas del personal de {{ \App\Support\Tenant::empresa()?->nombre }} y sus permisos.">

    <x-slot name="acciones">
        <button type="button" class="btn btn-primary" data-toggle-target="form-usuario">
            <i class="fas fa-user-plus"></i> Nuevo usuario
        </button>
    </x-slot>

    @php
        $yo = Auth::user();
        $creando = $errors->usuario->any() && ! old('_id');
        $colorRol = ['super_admin' => 'purple', 'admin' => 'red', 'recepcionista' => 'blue', 'cajera' => 'orange', 'entrenador' => 'cyan', 'cliente' => 'green'];
    @endphp

    <div class="stats-grid">
        @foreach ($roles->whereNotIn('nombre', ['cliente', 'super_admin']) as $rol)
            <div class="stat-card {{ $colorRol[$rol->nombre] ?? 'blue' }}">
                <div class="stat-title">{{ $rol->etiqueta }}</div>
                <div class="stat-value">{{ $usuarios->where('role_id', $rol->id)->count() }}</div>
                <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
            </div>
        @endforeach
    </div>

    <form id="form-usuario" method="POST" action="{{ route('usuarios.store') }}" class="form-card {{ $creando ? '' : 'hidden' }}">
        @csrf
        <h3><i class="fas fa-user-plus"></i> Nuevo usuario</h3>
        <div class="form-grid cols-4">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="name" value="{{ $creando ? old('name') : '' }}" required>
            </div>
            <div class="form-group">
                <label>Correo *</label>
                <input type="email" name="email" value="{{ $creando ? old('email') : '' }}" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Contraseña *</label>
                <input type="password" name="password" autocomplete="new-password" required>
            </div>
            <div class="form-group">
                <label>Confirmar contraseña *</label>
                <input type="password" name="password_confirmation" autocomplete="new-password" required>
            </div>
            <div class="form-group">
                <label>Rol *</label>
                <select name="role_id" required>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id }}" @selected($creando ? old('role_id') == $rol->id : $rol->nombre === 'recepcionista')>{{ $rol->etiqueta }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Sucursal</label>
                <select name="id_sucursal">
                    <option value="">Todas / sin asignar</option>
                    @foreach ($sucursales as $s)
                        <option value="{{ $s->id_sucursal }}" @selected($creando && old('id_sucursal') == $s->id_sucursal)>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <p class="muted" style="margin:.75rem 0 0;">El personal que no es administrador trabaja solo en su sucursal. Los super administradores no pertenecen a ninguna empresa.</p>
        @if ($creando)
            @include('admin.partials.errores', ['bag' => 'usuario'])
        @endif
        <div class="form-actions">
            <button type="button" class="btn btn-light" data-toggle-target="form-usuario">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear usuario</button>
        </div>
    </form>

    <div class="table-card">
        <div class="table-header">
            <h3>Usuarios <span class="count">({{ $usuarios->count() }})</span></h3>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="search" placeholder="Buscar usuario…" data-filter-table="tabla-usuarios">
            </div>
        </div>
        <div class="table-scroll">
            <table class="data-table" id="tabla-usuarios">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Sucursal</th>
                        <th>Registrado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $u)
                        @php
                            $editando = $errors->usuario->any() && (int) old('_id') === $u->id;
                            $editable = $yo->isSuperAdmin() || ! $u->isSuperAdmin();
                        @endphp
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</div>
                                    <div>
                                        {{ $u->name }} @if ($u->is($yo)) <span class="status-badge status-info">Tú</span> @endif
                                        <small>{{ $u->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge {{ $u->isSuperAdmin() ? 'status-dark' : ($u->isStaff() ? 'status-info' : 'status-active') }}">
                                    {{ $u->role?->etiqueta ?? 'Sin rol' }}
                                </span>
                            </td>
                            <td>{{ $u->isSuperAdmin() ? 'Plataforma' : ($u->sucursal?->nombre ?? 'Todas') }}</td>
                            <td>{{ $u->created_at?->format('d/m/Y') }}</td>
                            <td>
                                @if ($editable)
                                    <div class="row-actions">
                                        <button type="button" class="btn btn-light btn-sm btn-icon" title="Editar" data-toggle-target="editar-{{ $u->id }}"><i class="fas fa-pen"></i></button>
                                        @unless ($u->is($yo))
                                            <form method="POST" action="{{ route('usuarios.destroy', $u) }}" data-confirm="¿Eliminar a {{ $u->name }}? Esta acción no se puede deshacer.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endunless
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @if ($editable)
                            <tr id="editar-{{ $u->id }}" class="edit-row {{ $editando ? '' : 'hidden' }}">
                                <td colspan="5">
                                    <form method="POST" action="{{ route('usuarios.update', $u) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="_id" value="{{ $u->id }}">
                                        <div class="form-grid cols-4">
                                            <div class="form-group"><label>Nombre</label><input type="text" name="name" value="{{ $editando ? old('name') : $u->name }}" required></div>
                                            <div class="form-group"><label>Correo</label><input type="email" name="email" value="{{ $editando ? old('email') : $u->email }}" required></div>
                                            <div class="form-group">
                                                <label>Rol</label>
                                                <select name="role_id" required>
                                                    @foreach ($roles as $rol)
                                                        <option value="{{ $rol->id }}" @selected(($editando ? old('role_id') : $u->role_id) == $rol->id)>{{ $rol->etiqueta }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Sucursal</label>
                                                <select name="id_sucursal">
                                                    <option value="">Todas / sin asignar</option>
                                                    @foreach ($sucursales as $s)
                                                        <option value="{{ $s->id_sucursal }}" @selected(($editando ? old('id_sucursal') : $u->id_sucursal) == $s->id_sucursal)>{{ $s->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group"><label>Nueva contraseña <small style="font-weight:400;">(opcional)</small></label><input type="password" name="password" autocomplete="new-password"></div>
                                            <div class="form-group"><label>Confirmar contraseña</label><input type="password" name="password_confirmation" autocomplete="new-password"></div>
                                        </div>
                                        @if ($editando)
                                            @include('admin.partials.errores', ['bag' => 'usuario'])
                                        @endif
                                        <div class="form-actions">
                                            <button type="button" class="btn btn-light btn-sm" data-toggle-target="editar-{{ $u->id }}">Cancelar</button>
                                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar cambios</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
