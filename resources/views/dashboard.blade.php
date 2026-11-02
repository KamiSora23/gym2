<x-app-layout>

    @include('dashboard.styles')

    @php
        $usuario = Auth::user();
        $puede = [
            'asistencias' => $usuario->isSuperAdmin() || $usuario->hasRole('admin', 'recepcionista', 'entrenador'),
            'gastos' => $usuario->isSuperAdmin() || $usuario->hasRole('admin', 'cajera'),
            'config' => $usuario->isAdmin(),
            'usuarios' => $usuario->isSuperAdmin(),
        ];

        // Sección inicial: la que venga en la URL o la que tenga errores de validación.
        $seccion = match (true) {
            $errors->usuario->any() => 'usuarios',
            $errors->gasto->any() => 'gastos',
            $errors->asistencia->any() => 'asistencias',
            $errors->default->any() => 'config',
            default => request('seccion', 'reportes'),
        };
        $secciones = ['reportes', 'asistencias', 'ventas', 'gastos', 'catalogo', 'entrenamiento', 'marketing', 'usuarios', 'config'];
        if (! in_array($seccion, $secciones, true) || (in_array($seccion, ['config', 'usuarios'], true) && ! $puede[$seccion])) {
            $seccion = 'reportes';
        }
    @endphp

    <div id="dashboard-view">

        @include('dashboard.sidebar')

        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <div class="main-content">

            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-circle-check"></i> {{ session('success') }}
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
                </div>
            @endif

            @include('dashboard.main')

        </div>
    </div>

    @include('dashboard.scripts')

</x-app-layout>
