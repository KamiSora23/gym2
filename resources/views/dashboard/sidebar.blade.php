@php
    $item = fn (string $pagina) => 'menu-item'.($seccion === $pagina ? ' active' : '');
@endphp

<aside class="sidebar" id="sidebar">

    <div class="profile-section">
        <div class="profile-avatar">
            {{ strtoupper(mb_substr($usuario->name, 0, 1)) }}
        </div>

        <div class="profile-info">
            <h4 title="{{ $usuario->name }}">{{ $usuario->name }}</h4>
            <p>{{ $usuario->role?->etiqueta }}</p>
        </div>
    </div>

    <nav class="sidebar-menu">

        <div class="menu-category">Operaciones</div>

        <button type="button" class="{{ $item('reportes') }}" data-page="reportes">
            <i class="fas fa-chart-line"></i> Resumen
        </button>

        <button type="button" class="{{ $item('asistencias') }}" data-page="asistencias">
            <i class="fas fa-calendar-check"></i> Asistencias
        </button>

        <button type="button" class="{{ $item('ventas') }}" data-page="ventas">
            <i class="fas fa-cart-shopping"></i> Ventas
        </button>

        <button type="button" class="{{ $item('gastos') }}" data-page="gastos">
            <i class="fas fa-file-invoice-dollar"></i> Gastos
        </button>

        <div class="menu-category">Gestión</div>

        <button type="button" class="{{ $item('catalogo') }}" data-page="catalogo">
            <i class="fas fa-box-open"></i> Catálogo
        </button>

        <button type="button" class="{{ $item('entrenamiento') }}" data-page="entrenamiento">
            <i class="fas fa-dumbbell"></i> Entrenamiento <span class="menu-tag">DEMO</span>
        </button>

        <button type="button" class="{{ $item('marketing') }}" data-page="marketing">
            <i class="fas fa-bullhorn"></i> Marketing <span class="menu-tag">DEMO</span>
        </button>

        @if ($puede['usuarios'] || $puede['config'])
            <div class="menu-category">Administración</div>
        @endif

        @if ($puede['usuarios'])
            <button type="button" class="{{ $item('usuarios') }}" data-page="usuarios">
                <i class="fas fa-users-gear"></i> Usuarios y roles
            </button>
        @endif

        @if ($puede['config'])
            <button type="button" class="{{ $item('config') }}" data-page="config">
                <i class="fas fa-gear"></i> Configuración
            </button>
        @endif

    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="menu-item">
                <i class="fas fa-right-from-bracket"></i> Cerrar sesión
            </button>
        </form>
    </div>

</aside>
