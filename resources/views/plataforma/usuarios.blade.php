<x-admin-layout titulo="Usuarios globales" icono="fa-users-gear" modulo="usuarios-globales"
    subtitulo="Todas las cuentas de la plataforma: super administradores y personal de cada empresa.">

    <x-slot name="acciones">
        <button type="button" class="btn btn-primary" data-toggle-target="form-usuario"><i class="fas fa-user-plus"></i> Nuevo usuario</button>
    </x-slot>

    @php
        $yo = Auth::user();
        $creando = $errors->usuario->any() && ! old('_id');
        $sucursalesJson = $sucursales->map(fn ($s) => ['id' => $s->id_sucursal, 'empresa' => $s->id_empresa, 'nombre' => $s->nombre])->values();
    @endphp

    <form id="form-usuario" method="POST" action="{{ route('plataforma.usuarios.store') }}" class="form-card {{ $creando ? '' : 'hidden' }}" data-form-usuario>
        @csrf
        <h3><i class="fas fa-user-plus"></i> Nuevo usuario</h3>
        @include('plataforma.partials.campos-usuario', ['u' => null, 'editando' => $creando])
        @if ($creando)
            @include('admin.partials.errores', ['bag' => 'usuario'])
        @endif
        <div class="form-actions">
            <button type="button" class="btn btn-light" data-toggle-target="form-usuario">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear usuario</button>
        </div>
    </form>

    <form method="GET" class="form-card">
        <div class="form-grid cols-4" style="align-items:end;">
            <div class="form-group"><label>Buscar</label><input type="search" name="q" value="{{ request('q') }}" placeholder="Nombre o correo"></div>
            <div class="form-group">
                <label>Empresa</label>
                <select name="empresa">
                    <option value="">Todas</option>
                    <option value="plataforma" @selected(request('empresa') === 'plataforma')>Solo plataforma (super admins)</option>
                    @foreach ($empresas as $e)<option value="{{ $e->id_empresa }}" @selected(request('empresa') == $e->id_empresa)>{{ $e->nombre }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Rol</label>
                <select name="rol">
                    <option value="">Todos</option>
                    @foreach ($roles as $r)<option value="{{ $r->id }}" @selected(request('rol') == $r->id)>{{ $r->etiqueta }}</option>@endforeach
                </select>
            </div>
            <div class="form-group" style="flex-direction:row; gap:.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="{{ route('plataforma.usuarios.index') }}" class="btn btn-light">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="table-card">
        <div class="table-header"><h3>Usuarios <span class="count">({{ $usuarios->total() }})</span></h3></div>
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr><th>Usuario</th><th>Rol</th><th>Empresa</th><th>Sucursal</th><th>Registrado</th><th class="text-right">Acciones</th></tr></thead>
                <tbody>
                    @forelse ($usuarios as $u)
                        @php $editando = $errors->usuario->any() && (int) old('_id') === $u->id; @endphp
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</div>
                                    <div>{{ $u->name }} @if ($u->is($yo)) <span class="status-badge status-info">Tú</span> @endif<small>{{ $u->email }}</small></div>
                                </div>
                            </td>
                            <td><span class="status-badge {{ $u->isSuperAdmin() ? 'status-dark' : ($u->isStaff() ? 'status-info' : 'status-active') }}">{{ $u->role?->etiqueta }}</span></td>
                            <td>{{ $u->isSuperAdmin() ? 'Plataforma' : ($u->empresa?->nombre ?? '—') }}</td>
                            <td>{{ $u->sucursal?->nombre ?? '—' }}</td>
                            <td>{{ $u->created_at?->format('d/m/Y') }}</td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="btn btn-light btn-sm btn-icon" title="Editar" data-toggle-target="editar-{{ $u->id }}"><i class="fas fa-pen"></i></button>
                                    @unless ($u->is($yo))
                                        <form method="POST" action="{{ route('plataforma.usuarios.destroy', $u) }}" data-confirm="¿Eliminar a {{ $u->name }}?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                        <tr id="editar-{{ $u->id }}" class="edit-row {{ $editando ? '' : 'hidden' }}">
                            <td colspan="6">
                                <form method="POST" action="{{ route('plataforma.usuarios.update', $u) }}" data-form-usuario>
                                    @csrf @method('PUT')
                                    <input type="hidden" name="_id" value="{{ $u->id }}">
                                    @include('plataforma.partials.campos-usuario', ['u' => $u, 'editando' => $editando])
                                    @if ($editando)
                                        @include('admin.partials.errores', ['bag' => 'usuario'])
                                    @endif
                                    <div class="form-actions">
                                        <button type="button" class="btn btn-light btn-sm" data-toggle-target="editar-{{ $u->id }}">Cancelar</button>
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state"><i class="fas fa-users"></i> No hay usuarios con esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.partials.paginacion', ['items' => $usuarios])

    <x-slot name="scripts">
        <script>
            (function () {
                const sucursales = @json($sucursalesJson);
                const superId = '{{ $roles->firstWhere('nombre', 'super_admin')?->id }}';

                // En cada formulario: la sucursal depende de la empresa y el super admin no lleva empresa.
                document.querySelectorAll('[data-form-usuario]').forEach(form => {
                    const rol = form.querySelector('[name=role_id]');
                    const empresa = form.querySelector('[name=id_empresa]');
                    const sucursal = form.querySelector('[name=id_sucursal]');
                    const elegida = sucursal.dataset.valor;

                    function actualizar() {
                        const esSuper = rol.value === superId;
                        empresa.disabled = sucursal.disabled = esSuper;
                        empresa.required = !esSuper;
                        const actual = sucursal.value || elegida;
                        sucursal.querySelectorAll('option:not(:first-child)').forEach(o => o.remove());
                        sucursales.filter(s => String(s.empresa) === empresa.value).forEach(s => sucursal.add(new Option(s.nombre, s.id)));
                        sucursal.value = [...sucursal.options].some(o => o.value === actual) ? actual : '';
                    }

                    rol.addEventListener('change', actualizar);
                    empresa.addEventListener('change', actualizar);
                    actualizar();
                });
            })();
        </script>
    </x-slot>
</x-admin-layout>
