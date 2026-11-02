<x-admin-layout titulo="Roles y permisos" icono="fa-shield-halved" modulo="roles"
    subtitulo="Plantillas de rol del sistema y su matriz de permisos. Cada empresa puede crear roles propios a partir de ellas.">

    @php
        $colores = ['super_admin' => 'purple', 'admin' => 'red', 'gerente' => 'pink', 'recepcionista' => 'blue', 'cajera' => 'orange', 'entrenador' => 'cyan', 'nutricionista' => 'green', 'almacenero' => 'orange', 'cliente' => 'green'];
        $iconos = ['super_admin' => 'fa-user-shield', 'admin' => 'fa-user-tie', 'gerente' => 'fa-user-gear', 'recepcionista' => 'fa-bell-concierge', 'cajera' => 'fa-cash-register', 'entrenador' => 'fa-dumbbell', 'nutricionista' => 'fa-apple-whole', 'almacenero' => 'fa-warehouse', 'cliente' => 'fa-user'];
        $columnas = $roles->whereNotIn('nombre', ['super_admin', 'cliente']);
    @endphp

    <div class="catalog-grid" style="grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); margin-bottom:1.5rem;">
        @foreach ($roles as $rol)
            <div class="stat-card {{ $colores[$rol->nombre] ?? 'blue' }}" style="padding-bottom:1.25rem;">
                <div class="stat-title">{{ $rol->etiqueta }}</div>
                <div class="stat-value">{{ $rol->usuarios_count }} <span style="font-size:.85rem; font-weight:600; color:var(--text-soft);">usuarios</span></div>
                <div class="stat-icon"><i class="fas {{ $iconos[$rol->nombre] ?? 'fa-user' }}"></i></div>
                <p style="margin:.6rem 0 0; font-size:.83rem; color:var(--text-gray); line-height:1.45;">{{ $rol->descripcion }}</p>
                <div class="stat-hint">{{ $rol->nombre === 'super_admin' ? 'Todos los permisos' : $rol->permisos->count().' permisos' }}</div>
            </div>
        @endforeach
    </div>

    @foreach ($grupos as $grupo => $nombreGrupo)
        <div class="table-card">
            <div class="table-header"><h3>{{ $nombreGrupo }}</h3><span class="muted">El super admin tiene todos los permisos</span></div>
            <div class="table-scroll">
                <table class="data-table matrix">
                    <thead>
                        <tr>
                            <th>Permiso</th>
                            @foreach ($columnas as $rol)<th>{{ $rol->etiqueta }}</th>@endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permisos[$grupo] ?? [] as $p)
                            <tr>
                                <td style="white-space:normal;"><strong>{{ $p->modulo }}</strong> · {{ $p->descripcion }}<div class="muted">{{ $p->codigo }}</div></td>
                                @foreach ($columnas as $rol)
                                    @php $si = $rol->permisos->contains('codigo', $p->codigo); @endphp
                                    <td><i class="fas {{ $si ? 'fa-circle-check si' : 'fa-circle-minus no' }}"></i></td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div class="table-card">
        <div class="table-header"><h3>Roles propios de las empresas <span class="count">({{ $rolesPropios->count() }})</span></h3></div>
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr><th>Rol</th><th>Empresa</th><th>Permisos</th></tr></thead>
                <tbody>
                    @forelse ($rolesPropios as $r)
                        <tr><td>{{ $r->etiqueta }}<div class="muted">{{ $r->descripcion }}</div></td><td>{{ $r->empresa?->nombre }}</td><td>{{ $r->permisos_count }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="empty-state"><i class="fas fa-shield-halved"></i> Ninguna empresa ha creado roles propios todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-info" style="margin:0;">
        <i class="fas fa-circle-info"></i>
        Orden de evaluación: usuario activo → empresa activa → sucursal autorizada → permiso del rol o concedido → restricciones individuales (prevalecen) → el registro pertenece a su ámbito.
    </div>
</x-admin-layout>
