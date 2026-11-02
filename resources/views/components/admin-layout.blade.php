@props([
    'titulo',
    'icono' => 'fa-gauge',
    'subtitulo' => null,
    'modulo' => null,
])

@php
    $usuario = Auth::user();
    $empresaActual = \App\Support\Tenant::empresa();
    $sucursales = \App\Support\Tenant::sucursales();
    $sucursalActual = \App\Support\Tenant::sucursalId();
    $puede = fn (string $m) => \App\Support\Permisos::puede($usuario, $m);
    $modoPlataforma = $usuario->isSuperAdmin() && ! $empresaActual;
    // Solo ve "sus" alumnos quien no tiene acceso a todos los clientes.
    $soloAsignados = ! $puede('clients.view') && $puede('clients.view_assigned');
    $esCoach = $soloAsignados && $usuario->entrenador;

    $menuPlataforma = [
        'Plataforma' => [
            ['plataforma', 'plataforma.index', 'fa-gauge-high', 'Panel de plataforma'],
            ['empresas', 'empresas.index', 'fa-building', 'Empresas'],
            ['usuarios-globales', 'plataforma.usuarios.index', 'fa-users-gear', 'Usuarios globales'],
            ['roles', 'plataforma.roles', 'fa-shield-halved', 'Roles y permisos'],
            ['auditoria-global', 'plataforma.auditoria', 'fa-clock-rotate-left', 'Auditoría'],
            ['plataforma-config', 'plataforma.configuracion', 'fa-sliders', 'Configuración'],
        ],
    ];

    // Menú de la empresa: [clave, ruta, icono, texto, permisos que lo muestran (cualquiera)].
    $menu = [
        'Inicio' => [
            ['resumen', 'dashboard', 'fa-chart-line', $esCoach ? 'Mi panel' : 'Panel', 'dashboard.view'],
        ],
        'Clientes' => [
            ['clientes', 'clientes.index', 'fa-users', $soloAsignados ? 'Mis alumnos' : 'Clientes', 'clients.view|clients.view_assigned'],
            ['planes', 'planes.index', 'fa-id-card', 'Membresías y planes', 'memberships.view'],
            ['asistencias', 'asistencias.index', 'fa-calendar-check', 'Asistencias', 'attendance.view|attendance.create'],
        ],
        'Deportivo' => [
            ['entrenamiento', 'entrenamiento.index', 'fa-dumbbell', $esCoach ? 'Mis clases' : 'Clases y coaches', 'classes.view|classes.manage_own'],
            ['nutricion', 'nutricion.index', 'fa-apple-whole', 'Nutrición', 'nutrition.view'],
        ],
        'Comercial' => [
            ['caja', 'caja.index', 'fa-cash-register', $puede('cash.view_all') ? 'Cajas' : 'Mi caja', 'cash.open|cash.view_all|cash.manage'],
            ['ventas', 'ventas.index', 'fa-cart-shopping', $puede('sales.view') ? 'Ventas' : 'Mis ventas', 'sales.view|sales.view_own|sales.create'],
            ['gastos', 'gastos.index', 'fa-file-invoice-dollar', 'Gastos', 'expenses.view|expenses.create'],
            ['catalogo', 'productos.index', 'fa-box-open', 'Productos', 'products.view'],
            ['kardex', 'inventario.movimientos', 'fa-boxes-stacked', 'Inventario', 'inventory.view'],
        ],
        'Administración' => [
            ['usuarios', 'usuarios.index', 'fa-user-tie', 'Empleados', 'users.view'],
            ['roles', 'roles.index', 'fa-shield-halved', 'Roles y permisos', 'roles.manage'],
            ['sucursales', 'sucursales.index', 'fa-store', 'Sucursales', 'branches.manage'],
            ['auditoria', 'auditoria.index', 'fa-clock-rotate-left', 'Historial de actividades', 'audit.view'],
            ['configuracion', 'configuracion.index', 'fa-gear', 'Configuración', 'settings.manage'],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $titulo }} · {{ $config->nombre_gym }}</title>
    <link rel="icon" href="{{ $config->favicon_url }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root { {!! \App\Support\Tema::panel($config, \App\Support\Tenant::sucursal()) !!} }

        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; background: var(--bg-color); color: #1e293b; margin: 0; }
    </style>

    @include('admin.styles')
</head>

<body>
    <header class="topbar">
        <div style="display:flex; align-items:center; gap:.5rem; min-width:0;">
            <button type="button" id="sidebarToggle" class="icon-btn lg:hidden" aria-label="Abrir menú">
                <i class="fas fa-bars"></i>
            </button>

            <a href="{{ $usuario->homeRoute() }}" class="topbar-brand">
                <img src="{{ $config->logo_url }}" alt="Logo">
                <span style="min-width:0;">
                    <strong>{{ $modoPlataforma ? $config->nombre_gym : ($empresaActual?->nombre ?? $config->nombre_gym) }}</strong>
                    <small>{{ $modoPlataforma ? 'Plataforma · Super administración' : ($usuario->sucursal?->nombre ?? 'Sistema administrativo') }}</small>
                </span>
            </a>
        </div>

        <div class="topbar-right">
            @if ($usuario->isSuperAdmin())
                <form method="POST" action="{{ route('contexto.empresa') }}" class="context-select" title="Empresa que estás administrando">
                    @csrf
                    <span><i class="fas fa-building"></i></span>
                    <select name="id_empresa" onchange="this.form.submit()">
                        <option value="">Plataforma (todas)</option>
                        @foreach (\App\Models\Empresa::orderBy('nombre')->get(['id_empresa', 'nombre']) as $e)
                            <option value="{{ $e->id_empresa }}" @selected($empresaActual?->id_empresa === $e->id_empresa)>{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                </form>
            @endif

            @if ($empresaActual && \App\Support\Tenant::puedeCambiarSucursal($usuario) && $sucursales->isNotEmpty())
                <form method="POST" action="{{ route('contexto.sucursal') }}" class="context-select" title="Sucursal">
                    @csrf
                    <span><i class="fas fa-store"></i></span>
                    <select name="id_sucursal" onchange="this.form.submit()">
                        <option value="">Todas las sucursales</option>
                        @foreach ($sucursales as $s)
                            <option value="{{ $s->id_sucursal }}" @selected($sucursalActual === $s->id_sucursal)>{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                </form>
            @elseif ($usuario->sucursal)
                <span class="chip"><i class="fas fa-store"></i> {{ $usuario->sucursal->nombre }}</span>
            @endif

            <div style="position:relative;" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" class="user-chip" @click="open = !open">
                    <span class="avatar">{{ strtoupper(mb_substr($usuario->name, 0, 1)) }}</span>
                    <span class="who">
                        <strong>{{ $usuario->name }}</strong>
                        <small>{{ $usuario->role?->etiqueta }}</small>
                    </span>
                    <i class="fas fa-chevron-down" style="font-size:.7rem; color:#94a3b8;"></i>
                </button>
                <div class="dropdown-panel" x-show="open" x-transition style="display:none;">
                    <a href="{{ route('profile.edit') }}"><i class="fas fa-user-gear"></i> Mi perfil</a>
                    <a href="{{ url('/') }}" target="_blank"><i class="fas fa-arrow-up-right-from-square"></i> Ver sitio público</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"><i class="fas fa-right-from-bracket"></i> Cerrar sesión</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div id="dashboard-view">
        <aside class="sidebar" id="sidebar">
            <div class="profile-section">
                <div class="profile-avatar">{{ strtoupper(mb_substr($usuario->name, 0, 1)) }}</div>
                <div class="profile-info">
                    <h4 title="{{ $usuario->name }}">{{ $usuario->name }}</h4>
                    <p>{{ $usuario->role?->etiqueta }}</p>
                </div>
            </div>

            <nav class="sidebar-menu">
                @if ($modoPlataforma)
                    @foreach ($menuPlataforma as $categoria => $items)
                        <div class="menu-category">{{ $categoria }}</div>
                        @foreach ($items as [$clave, $ruta, $iconoItem, $texto])
                            <a href="{{ route($ruta) }}" class="menu-item {{ $modulo === $clave ? 'active' : '' }}">
                                <i class="fas {{ $iconoItem }}"></i> {{ $texto }}
                            </a>
                        @endforeach
                    @endforeach
                @elseif ($empresaActual)
                    @if ($usuario->isSuperAdmin())
                        <form method="POST" action="{{ route('contexto.empresa') }}">
                            @csrf
                            <input type="hidden" name="id_empresa" value="">
                            <button type="submit" class="menu-item menu-back">
                                <i class="fas fa-arrow-left"></i> Volver a la plataforma
                            </button>
                        </form>
                    @endif

                    @foreach ($menu as $categoria => $items)
                        @php $visibles = array_filter($items, fn ($i) => $puede($i[4])); @endphp
                        @if ($visibles)
                            <div class="menu-category">{{ $categoria }}</div>
                            @foreach ($visibles as [$clave, $ruta, $iconoItem, $texto])
                                <a href="{{ route($ruta) }}" class="menu-item {{ $modulo === $clave ? 'active' : '' }}">
                                    <i class="fas {{ $iconoItem }}"></i> {{ $texto }}
                                </a>
                            @endforeach
                        @endif
                    @endforeach
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

        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <main class="main-content">
            @if ($usuario->isSuperAdmin() && $empresaActual)
                <div class="mode-banner">
                    <i class="fas fa-user-shield"></i>
                    <span>Modo empresa: estás administrando <strong>{{ $empresaActual->nombre }}</strong> como super administrador. Todo lo que hagas aquí afecta solo a esta empresa.</span>
                    <form method="POST" action="{{ route('contexto.empresa') }}">
                        @csrf
                        <input type="hidden" name="id_empresa" value="">
                        <button type="submit" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> Volver a la plataforma</button>
                    </form>
                </div>
            @endif

            @foreach (['success' => 'fa-circle-check', 'error' => 'fa-circle-exclamation', 'info' => 'fa-circle-info'] as $tipo => $iconoAlerta)
                @if (session($tipo))
                    <div class="alert alert-{{ $tipo }} flash">
                        <i class="fas {{ $iconoAlerta }}"></i> {{ session($tipo) }}
                        <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
                    </div>
                @endif
            @endforeach

            <div class="page-header">
                <div>
                    <h2><span class="icon"><i class="fas {{ $icono }}"></i></span> {{ $titulo }}</h2>
                    @if ($subtitulo)
                        <p>{{ $subtitulo }}</p>
                    @endif
                </div>
                @isset($acciones)
                    <div style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">{{ $acciones }}</div>
                @endisset
            </div>

            {{ $slot }}
        </main>
    </div>

    <script>
        (function () {
            const body = document.body;
            document.getElementById('sidebarToggle')?.addEventListener('click', () => body.classList.toggle('sidebar-open'));
            document.getElementById('sidebarBackdrop')?.addEventListener('click', () => body.classList.remove('sidebar-open'));

            // Botones que muestran/ocultan formularios o filas de edición.
            document.querySelectorAll('[data-toggle-target]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const objetivo = document.getElementById(btn.dataset.toggleTarget);
                    if (!objetivo) return;
                    objetivo.classList.toggle('hidden');
                    if (!objetivo.classList.contains('hidden')) {
                        objetivo.querySelector('input:not([type=hidden]):not([disabled]), select, textarea')?.focus();
                    }
                });
            });

            // Acciones que requieren un motivo (anulaciones): se pide antes de enviar.
            document.querySelectorAll('form[data-motivo]').forEach(form => {
                form.addEventListener('submit', e => {
                    if (form.querySelector('input[name=motivo]')?.value) return;
                    const motivo = prompt(form.dataset.motivo);
                    if (!motivo || motivo.trim().length < 5) {
                        e.preventDefault();
                        if (motivo !== null) alert('Escribe un motivo de al menos 5 caracteres.');
                        return;
                    }
                    const campo = form.querySelector('input[name=motivo]') ?? Object.assign(document.createElement('input'), { type: 'hidden', name: 'motivo' });
                    campo.value = motivo.trim();
                    form.appendChild(campo);
                });
            });

            // Confirmación antes de acciones destructivas.
            document.querySelectorAll('form[data-confirm]').forEach(form => {
                form.addEventListener('submit', e => {
                    if (!confirm(form.dataset.confirm)) e.preventDefault();
                });
            });

            // Búsqueda en tablas y tarjetas.
            document.querySelectorAll('[data-filter-table]').forEach(input => {
                input.addEventListener('input', () => {
                    const texto = input.value.toLowerCase().trim();
                    document.querySelectorAll('#' + input.dataset.filterTable + ' tbody tr:not(.edit-row)').forEach(fila => {
                        if (fila.querySelector('.empty-state')) return;
                        fila.style.display = fila.textContent.toLowerCase().includes(texto) ? '' : 'none';
                    });
                });
            });

            document.querySelectorAll('[data-filter-cards]').forEach(input => {
                input.addEventListener('input', () => {
                    const texto = input.value.toLowerCase().trim();
                    document.querySelectorAll('#' + input.dataset.filterCards + ' [data-search]').forEach(card => {
                        card.style.display = card.dataset.search.includes(texto) ? '' : 'none';
                    });
                });
            });

            // Las alertas se ocultan solas.
            setTimeout(() => {
                document.querySelectorAll('.alert.flash').forEach(a => {
                    a.style.transition = 'opacity .4s';
                    a.style.opacity = '0';
                    setTimeout(() => a.remove(), 400);
                });
            }, 6000);
        })();
    </script>

    {{ $scripts ?? '' }}
</body>

</html>
