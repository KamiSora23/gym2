<x-admin-layout titulo="Panel de plataforma" icono="fa-gauge-high" modulo="plataforma"
    subtitulo="Visión global de todas las empresas. Aquí no se mezclan los datos de ninguna empresa en particular.">

    <x-slot name="acciones">
        <a href="{{ route('empresas.index') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva empresa</a>
    </x-slot>

    <div class="stats-grid">
        <div class="stat-card purple"><div class="stat-title">Empresas</div><div class="stat-value">{{ $totales['empresas'] }}</div><div class="stat-icon"><i class="fas fa-building"></i></div><div class="stat-hint">{{ $totales['activas'] }} activas</div></div>
        <div class="stat-card blue"><div class="stat-title">Sucursales</div><div class="stat-value">{{ $totales['sucursales'] }}</div><div class="stat-icon"><i class="fas fa-store"></i></div></div>
        <div class="stat-card cyan"><div class="stat-title">Personal</div><div class="stat-value">{{ $totales['personal'] }}</div><div class="stat-icon"><i class="fas fa-user-tie"></i></div><div class="stat-hint">{{ $totales['super_admins'] }} super admins</div></div>
        <div class="stat-card green"><div class="stat-title">Ventas del mes</div><div class="stat-value">{{ dinero($totales['ventas_mes']) }}</div><div class="stat-icon"><i class="fas fa-sack-dollar"></i></div><div class="stat-hint">{{ $totales['clientes'] }} clientes en total</div></div>
    </div>

    <div class="charts-section" style="margin-bottom:1.5rem;">
        <div class="chart-card">
            <div class="chart-header"><h3>Ventas de la plataforma (6 meses)</h3></div>
            <div class="chart-box"><canvas id="ventasPlataforma"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-header"><h3>Ventas del mes por empresa</h3></div>
            <div class="chart-box" style="height:240px;"><canvas id="ventasEmpresa"></canvas></div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Empresas <span class="count">({{ $empresas->count() }})</span></h3>
            <div class="search-box"><i class="fas fa-search"></i><input type="search" placeholder="Buscar empresa…" data-filter-table="tabla-empresas"></div>
        </div>
        <div class="table-scroll">
            <table class="data-table" id="tabla-empresas">
                <thead>
                    <tr><th>Empresa</th><th>Sucursales</th><th>Personal</th><th>Clientes</th><th>Membresías activas</th><th>Ventas del mes</th><th>Última venta</th><th>Estado</th><th class="text-right">Acción</th></tr>
                </thead>
                <tbody>
                    @forelse ($empresas as $e)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">{{ strtoupper(mb_substr($e->nombre, 0, 1)) }}</div>
                                    <div>{{ $e->nombre }}<small>{{ $e->ruc ? 'RUC '.$e->ruc : ($e->email ?? 'Sin datos de contacto') }}</small></div>
                                </div>
                            </td>
                            <td>{{ $e->num_sucursales }}</td>
                            <td>{{ $e->num_personal }}</td>
                            <td>{{ $e->num_clientes }}</td>
                            <td>{{ $e->membresias_activas }}</td>
                            <td class="money-in">{{ dinero($e->ventas_mes) }}</td>
                            <td>{{ $e->ultima_venta?->locale('es')->diffForHumans() ?? 'Nunca' }}</td>
                            <td><span class="status-badge {{ $e->activo ? 'status-active' : 'status-cancelled' }}">{{ $e->activo ? 'Activa' : 'Inactiva' }}</span></td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('contexto.empresa') }}">
                                    @csrf
                                    <input type="hidden" name="id_empresa" value="{{ $e->id_empresa }}">
                                    <button type="submit" class="btn btn-light btn-sm" @disabled(! $e->activo)><i class="fas fa-right-to-bracket"></i> Administrar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="empty-state"><i class="fas fa-building"></i> Aún no hay empresas. <a href="{{ route('empresas.index') }}">Crea la primera</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            (function () {
                if (typeof Chart === 'undefined') return;
                const primario = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
                const acento = getComputedStyle(document.documentElement).getPropertyValue('--secondary-color').trim();
                Chart.defaults.font.family = 'Inter, sans-serif';
                Chart.defaults.color = '#94a3b8';

                new Chart(document.getElementById('ventasPlataforma'), {
                    type: 'line',
                    data: {
                        labels: @json($grafico['labels']),
                        datasets: [{ data: @json($grafico['datos']), borderColor: primario, backgroundColor: primario + '22', fill: true, tension: .35, pointRadius: 4, pointBackgroundColor: primario }],
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } },
                });

                const empresas = @json($empresas->map(fn ($e) => ['nombre' => $e->nombre, 'ventas' => round($e->ventas_mes, 2)])->values());
                new Chart(document.getElementById('ventasEmpresa'), {
                    type: 'bar',
                    data: { labels: empresas.map(e => e.nombre), datasets: [{ data: empresas.map(e => e.ventas), backgroundColor: acento, borderRadius: 8, maxBarThickness: 28 }] },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } } },
                });
            })();
        </script>
    </x-slot>
</x-admin-layout>
