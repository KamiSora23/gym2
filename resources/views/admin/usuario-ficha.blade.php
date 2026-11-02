<x-admin-layout :titulo="$usuario->name" icono="fa-user-tie" modulo="usuarios"
    subtitulo="{{ $usuario->email }} · {{ $usuario->role?->etiqueta }} · {{ $usuario->sucursal?->nombre ?? 'Todas las sucursales' }}">

    <x-slot name="acciones">
        <a href="{{ route('usuarios.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Empleados</a>
    </x-slot>

    @php
        $delRol = $detalle['rol'];
        $concedidos = $detalle['concedidos'];
        $restringidos = $detalle['restringidos'];
    @endphp

    @if ($usuario->isBloqueado())
        <div class="alert alert-error"><i class="fas fa-lock"></i> Usuario bloqueado desde {{ $usuario->bloqueado_en?->format('d/m/Y H:i') }}: {{ $usuario->motivo_bloqueo }}</div>
    @endif

    <div class="stats-grid">
        <div class="stat-card blue"><div class="stat-title">Rol</div><div class="stat-value" style="font-size:1.15rem;">{{ $usuario->role?->etiqueta }}</div><div class="stat-icon"><i class="fas fa-shield-halved"></i></div><div class="stat-hint">{{ $delRol->count() }} permisos heredados</div></div>
        <div class="stat-card green"><div class="stat-title">Permisos extra</div><div class="stat-value">{{ $concedidos->count() }}</div><div class="stat-icon"><i class="fas fa-plus"></i></div><div class="stat-hint">Concedidos a esta persona</div></div>
        <div class="stat-card red"><div class="stat-title">Restricciones</div><div class="stat-value">{{ $restringidos->count() }}</div><div class="stat-icon"><i class="fas fa-ban"></i></div><div class="stat-hint">Prevalecen sobre el rol</div></div>
        <div class="stat-card purple"><div class="stat-title">Permisos efectivos</div><div class="stat-value">{{ $usuario->isSuperAdmin() ? 'Todos' : $efectivos->count() }}</div><div class="stat-icon"><i class="fas fa-key"></i></div><div class="stat-hint">{{ $usuario->isBloqueado() ? 'Sin efecto: bloqueado' : 'Lo que realmente puede hacer' }}</div></div>
    </div>

    <div class="grid-2">
        <div>
            @if ($puedeGestionar)
                <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="form-card">
                    @csrf @method('PUT')
                    <h3><i class="fas fa-id-card"></i> Datos de acceso</h3>
                    <div class="form-grid">
                        <div class="form-group"><label>Nombre</label><input type="text" name="name" value="{{ old('name', $usuario->name) }}" required></div>
                        <div class="form-group"><label>Correo</label><input type="email" name="email" value="{{ old('email', $usuario->email) }}" required></div>
                        <div class="form-group">
                            <label>Rol</label>
                            <select name="role_id">
                                @foreach ($roles as $r)<option value="{{ $r->id }}" @selected($usuario->role_id == $r->id)>{{ $r->etiqueta }}{{ $r->es_sistema ? '' : ' (empresa)' }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Sucursal</label>
                            <select name="id_sucursal">
                                <option value="">Todas / sin asignar</option>
                                @foreach ($sucursales as $s)<option value="{{ $s->id_sucursal }}" @selected($usuario->id_sucursal == $s->id_sucursal)>{{ $s->nombre }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Nueva contraseña <small style="font-weight:400;">(opcional)</small></label><input type="password" name="password" autocomplete="new-password"></div>
                        <div class="form-group"><label>Confirmar contraseña</label><input type="password" name="password_confirmation" autocomplete="new-password"></div>
                    </div>
                    @include('admin.partials.errores', ['bag' => 'usuario'])
                    <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button></div>
                </form>
            @endif

            @if ($puedeBloquear)
                <form method="POST" action="{{ route('usuarios.bloqueo', $usuario) }}" class="form-card" @unless ($usuario->isBloqueado()) data-confirm="¿Bloquear a {{ $usuario->name }}? No podrá iniciar sesión." @endunless>
                    @csrf @method('PATCH')
                    <h3><i class="fas fa-lock"></i> {{ $usuario->isBloqueado() ? 'Desbloquear acceso' : 'Bloquear acceso' }}</h3>
                    @unless ($usuario->isBloqueado())
                        <div class="form-group"><label>Motivo *</label><input type="text" name="motivo" placeholder="Fin de contrato, investigación interna…" required></div>
                    @endunless
                    @include('admin.partials.errores')
                    <div class="form-actions">
                        <button type="submit" class="btn {{ $usuario->isBloqueado() ? 'btn-primary' : 'btn-danger' }}">
                            <i class="fas {{ $usuario->isBloqueado() ? 'fa-lock-open' : 'fa-lock' }}"></i> {{ $usuario->isBloqueado() ? 'Desbloquear' : 'Bloquear' }}
                        </button>
                    </div>
                </form>
            @endif

            <div class="panel-card">
                <h4>Actividad reciente</h4>
                <ul class="tx-list">
                    @forelse ($actividad as $a)
                        <li><span style="white-space:normal;">{{ $a->descripcion }}<small>{{ $a->created_at->format('d/m/Y H:i') }} · {{ $a->accion }}</small></span></li>
                    @empty
                        <li class="muted">Sin actividad registrada.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <form method="POST" action="{{ route('usuarios.permisos', $usuario) }}" class="form-card">
            @csrf @method('PUT')
            <h3><i class="fas fa-key"></i> Permisos individuales</h3>
            <p class="muted" style="margin:-.5rem 0 1rem;">
                <strong>Rol</strong>: lo que diga su rol. <strong>Conceder</strong>: agrega el permiso aunque el rol no lo tenga.
                <strong>Restringir</strong>: lo quita aunque el rol lo tenga (prevalece).
            </p>

            @foreach ($grupos as $grupo => $nombreGrupo)
                <div class="section-label" style="margin-top:1rem;">{{ $nombreGrupo }}</div>
                <table class="data-table perm-table">
                    <tbody>
                        @foreach ($permisos[$grupo] ?? [] as $p)
                            @php
                                $estado = $concedidos->contains($p->codigo) ? 'grant' : ($restringidos->contains($p->codigo) ? 'deny' : 'rol');
                                $enRol = $delRol->contains($p->codigo);
                                $activo = $efectivos->contains($p->codigo);
                                $controla = $puedePermisos && $misPermisos->contains($p->codigo);
                            @endphp
                            <tr>
                                <td style="white-space:normal;">
                                    <i class="fas {{ $activo ? 'fa-circle-check' : 'fa-circle-minus' }}" style="color: {{ $activo ? '#059669' : '#cbd5e1' }}"></i>
                                    {{ $p->descripcion }}
                                    <small class="muted" style="display:block;">{{ $p->modulo }} · {{ $enRol ? 'incluido en su rol' : 'no incluido en su rol' }}</small>
                                </td>
                                <td style="width:130px;">
                                    <select name="permisos[{{ $p->codigo }}]" @disabled(! $controla) class="perm-select perm-{{ $estado }}">
                                        <option value="rol" @selected($estado === 'rol')>Rol</option>
                                        <option value="grant" @selected($estado === 'grant')>Conceder</option>
                                        <option value="deny" @selected($estado === 'deny')>Restringir</option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach

            @if ($puedePermisos)
                <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar permisos</button></div>
            @endif
        </form>
    </div>

    <style>
        .perm-table td { padding: .55rem .5rem !important; font-size: .83rem; }
        .perm-select { width: 100%; padding: .35rem .4rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: .8rem; }
        .perm-grant { background: #ecfdf5; border-color: #6ee7b7; }
        .perm-deny { background: #fef2f2; border-color: #fca5a5; }
    </style>
    <x-slot name="scripts">
        <script>
            document.querySelectorAll('.perm-select').forEach(s => s.addEventListener('change', () => {
                s.classList.remove('perm-rol', 'perm-grant', 'perm-deny');
                s.classList.add('perm-' + s.value);
            }));
        </script>
    </x-slot>
</x-admin-layout>
