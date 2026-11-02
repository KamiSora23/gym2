<x-admin-layout titulo="Roles y permisos" icono="fa-shield-halved" modulo="roles"
    subtitulo="Qué puede hacer cada rol en el sistema. Los permisos se aplican igual en todas las empresas.">

    @php
        $colores = ['super_admin' => 'purple', 'admin' => 'red', 'recepcionista' => 'blue', 'cajera' => 'orange', 'entrenador' => 'cyan', 'cliente' => 'green'];
        $iconos = ['super_admin' => 'fa-user-shield', 'admin' => 'fa-user-tie', 'recepcionista' => 'fa-bell-concierge', 'cajera' => 'fa-cash-register', 'entrenador' => 'fa-dumbbell', 'cliente' => 'fa-user'];
    @endphp

    <div class="catalog-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); margin-bottom:1.5rem;">
        @foreach ($roles as $rol)
            <div class="stat-card {{ $colores[$rol->nombre] ?? 'blue' }}" style="padding-bottom:1.25rem;">
                <div class="stat-title">{{ $rol->etiqueta }}</div>
                <div class="stat-value">{{ $rol->usuarios_count }} <span style="font-size:.85rem; font-weight:600; color:var(--text-soft);">usuarios</span></div>
                <div class="stat-icon"><i class="fas {{ $iconos[$rol->nombre] ?? 'fa-user' }}"></i></div>
                <p style="margin:.6rem 0 0; font-size:.83rem; color:var(--text-gray); line-height:1.45;">{{ \App\Support\Permisos::DESCRIPCIONES[$rol->nombre] ?? '' }}</p>
            </div>
        @endforeach
    </div>

    <div class="table-card">
        <div class="table-header"><h3>Matriz de permisos por módulo</h3><span class="muted">El super admin tiene acceso a todo</span></div>
        <div class="table-scroll">
            <table class="data-table matrix">
                <thead>
                    <tr>
                        <th>Módulo</th>
                        @foreach ($roles->where('nombre', '!=', 'cliente') as $rol)
                            <th>{{ $rol->etiqueta }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($modulos as $modulo => $permitidos)
                        <tr>
                            <td><strong>{{ $nombresModulos[$modulo] ?? $modulo }}</strong></td>
                            @foreach ($roles->where('nombre', '!=', 'cliente') as $rol)
                                @php $si = $rol->nombre === 'super_admin' || in_array($rol->nombre, $permitidos, true); @endphp
                                <td><i class="fas {{ $si ? 'fa-circle-check si' : 'fa-circle-minus no' }}"></i></td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>Mi cuenta (área del cliente)</strong></td>
                        @foreach ($roles->where('nombre', '!=', 'cliente') as $rol)
                            <td><i class="fas fa-circle-minus no"></i></td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header"><h3>Acciones permitidas dentro de los módulos</h3><span class="muted">Se validan en el servidor, no solo se ocultan los botones</span></div>
        <div class="table-scroll">
            <table class="data-table matrix">
                <thead>
                    <tr>
                        <th>Acción</th>
                        @foreach ($roles->where('nombre', '!=', 'cliente') as $rol)
                            <th>{{ $rol->etiqueta }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach (\App\Support\Permisos::ACCIONES as $accion => [$descripcion, $permitidos])
                        <tr>
                            <td style="white-space:normal;">{{ $descripcion }}</td>
                            @foreach ($roles->where('nombre', '!=', 'cliente') as $rol)
                                @php $si = $rol->nombre === 'super_admin' || in_array($rol->nombre, $permitidos, true); @endphp
                                <td><i class="fas {{ $si ? 'fa-circle-check si' : 'fa-circle-minus no' }}"></i></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-info" style="margin:0;">
        <i class="fas fa-circle-info"></i>
        «Propias» significa que el coach solo actúa sobre sus clases y sus alumnos. El cliente tiene su propia área («Mi cuenta») para ver su rutina y progreso e inscribirse en clases.
    </div>
</x-admin-layout>
