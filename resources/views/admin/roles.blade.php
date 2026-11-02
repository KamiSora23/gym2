<x-admin-layout titulo="Roles y permisos" icono="fa-shield-halved" modulo="roles"
    subtitulo="Los roles agrupan permisos. Las plantillas del sistema no se editan: crea un rol propio a partir de una de ellas.">

    <x-slot name="acciones">
        <button type="button" class="btn btn-primary" data-toggle-target="form-rol"><i class="fas fa-plus"></i> Nuevo rol</button>
    </x-slot>

    <form id="form-rol" method="POST" action="{{ route('roles.store') }}" class="form-card {{ $errors->rol->any() ? '' : 'hidden' }}">
        @csrf
        <h3><i class="fas fa-shield-halved"></i> Nuevo rol de la empresa</h3>
        <div class="form-grid" style="grid-template-columns: 1fr 2fr 1fr;">
            <div class="form-group"><label>Nombre *</label><input type="text" name="etiqueta" value="{{ old('etiqueta') }}" placeholder="Supervisor de ventas" required></div>
            <div class="form-group"><label>Descripción</label><input type="text" name="descripcion" value="{{ old('descripcion') }}" placeholder="Supervisa ventas y caja de la sede"></div>
            <div class="form-group">
                <label>Partir de la plantilla</label>
                <select name="plantilla">
                    <option value="">Sin permisos</option>
                    @foreach ($roles->where('es_sistema', true)->where('nombre', '!=', 'cliente') as $r)
                        <option value="{{ $r->id }}">{{ $r->etiqueta }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @include('admin.partials.errores', ['bag' => 'rol'])
        <div class="form-actions">
            <button type="button" class="btn btn-light" data-toggle-target="form-rol">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear rol</button>
        </div>
    </form>

    <div class="alert alert-info">
        <i class="fas fa-circle-info"></i>
        Solo puedes conceder permisos que tú mismo tienes. Además de los roles, a cada empleado se le pueden conceder o restringir permisos individuales desde su ficha en <a href="{{ route('usuarios.index') }}">Empleados</a>.
    </div>

    @foreach ($roles->where('nombre', '!=', 'cliente') as $rol)
        @php
            $suyos = $rol->permisos->pluck('codigo');
            $editable = ! $rol->es_sistema;
        @endphp
        <details class="table-card role-card" id="rol-{{ $rol->id }}" @if ($loop->first || request()->fullUrlIs('*rol-'.$rol->id)) open @endif>
            <summary class="table-header" style="cursor:pointer; list-style:none;">
                <h3 style="display:flex; align-items:center; gap:.6rem;">
                    <i class="fas fa-chevron-right role-chevron" style="font-size:.75rem; color:var(--text-soft);"></i>
                    {{ $rol->etiqueta }}
                    <span class="status-badge {{ $rol->es_sistema ? 'status-dark' : 'status-info' }}">{{ $rol->es_sistema ? 'Plantilla del sistema' : 'Rol de la empresa' }}</span>
                </h3>
                <span class="muted">{{ $suyos->count() }} permisos · {{ $rol->users_count }} usuarios</span>
            </summary>

            <form method="POST" action="{{ $editable ? route('roles.update', $rol) : '#' }}" style="padding:1.25rem 1.5rem;">
                @csrf
                @method('PUT')
                @if ($editable)
                    <div class="form-grid" style="margin-bottom:1rem;">
                        <div class="form-group"><label>Nombre</label><input type="text" name="etiqueta" value="{{ $rol->etiqueta }}" required></div>
                        <div class="form-group"><label>Descripción</label><input type="text" name="descripcion" value="{{ $rol->descripcion }}"></div>
                    </div>
                @elseif ($rol->descripcion)
                    <p class="muted" style="margin:0 0 1rem;">{{ $rol->descripcion }}</p>
                @endif

                <div class="perm-groups">
                    @foreach ($grupos as $grupo => $nombreGrupo)
                        <fieldset class="perm-group">
                            <legend>{{ $nombreGrupo }}</legend>
                            @foreach (($permisos[$grupo] ?? collect())->groupBy('modulo') as $modulo => $items)
                                <div class="perm-modulo">
                                    <strong>{{ $modulo }}</strong>
                                    @foreach ($items as $p)
                                        @php $concedible = $misPermisos->contains($p->codigo); @endphp
                                        <label class="perm-item {{ $editable && ! $concedible ? 'bloqueado' : '' }}" title="{{ $p->codigo }}{{ $editable && ! $concedible ? ' · no lo tienes, no puedes concederlo' : '' }}">
                                            <input type="checkbox" name="permisos[]" value="{{ $p->codigo }}" @checked($suyos->contains($p->codigo)) @disabled(! $editable || ! $concedible)>
                                            {{ $p->descripcion }}
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </fieldset>
                    @endforeach
                </div>

                @if ($editable)
                    <div class="form-actions">
                        @if ($rol->users_count === 0)
                            <button type="submit" form="eliminar-rol-{{ $rol->id }}" class="btn btn-danger"><i class="fas fa-trash"></i> Eliminar rol</button>
                        @endif
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar permisos</button>
                    </div>
                @endif
            </form>
            @if ($editable)
                <form id="eliminar-rol-{{ $rol->id }}" method="POST" action="{{ route('roles.destroy', $rol) }}" data-confirm="¿Eliminar el rol {{ $rol->etiqueta }}?">@csrf @method('DELETE')</form>
            @endif
        </details>
    @endforeach

    <style>
        .role-card[open] .role-chevron { transform: rotate(90deg); }
        .role-card summary::-webkit-details-marker { display: none; }
        .perm-groups { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; }
        .perm-group { border: 1px solid var(--line); border-radius: 14px; padding: .75rem 1rem 1rem; margin: 0; }
        .perm-group legend { font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--primary); padding: 0 .4rem; }
        .perm-modulo { margin-top: .65rem; }
        .perm-modulo strong { display: block; font-size: .8rem; color: var(--text-dark); margin-bottom: .25rem; }
        .perm-item { display: flex; gap: .5rem; align-items: flex-start; font-size: .82rem; color: var(--text-gray); padding: .15rem 0; cursor: pointer; }
        .perm-item input { margin-top: .15rem; }
        .perm-item.bloqueado { opacity: .5; cursor: not-allowed; }
    </style>
</x-admin-layout>
