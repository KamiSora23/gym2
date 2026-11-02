@php
    $usuario = Auth::user();
    $sucursal = \App\Support\Tenant::sucursal();
@endphp

<x-admin-layout titulo="Hola, {{ strtok($usuario->name, ' ') }}" icono="fa-chart-line" modulo="resumen"
    subtitulo="Resumen de la semana · {{ $sucursal?->nombre ?? 'todas las sucursales' }}">

    <x-slot name="acciones">
        <span class="date-display">{{ now()->locale('es')->translatedFormat('l, j \d\e F \d\e Y') }}</span>
        @if (\App\Support\Permisos::puede($usuario, 'ventas'))
            <a href="{{ route('ventas.create') }}" class="btn btn-primary"><i class="fas fa-cash-register"></i> Nueva venta</a>
        @endif
    </x-slot>

    <div class="dashboard-layout">
        <div>
            <div class="stats-grid">
                <div class="stat-card cyan">
                    <div class="stat-title">Clientes activos</div>
                    <div class="stat-value">{{ $stats['clientes_activos'] }}</div>
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-hint">{{ $stats['clientes_total'] }} registrados</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-title">Ingresos semana</div>
                    <div class="stat-value">{{ dinero($stats['ingresos_semana']) }}</div>
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-hint">Ventas cobradas</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-title">Gastos semana</div>
                    <div class="stat-value">{{ dinero($stats['gastos_semana']) }}</div>
                    <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                    <div class="stat-hint">Egresos</div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-title">Ganancia neta</div>
                    <div class="stat-value">{{ dinero($stats['neto_semana']) }}</div>
                    <div class="stat-icon"><i class="fas fa-chart-area"></i></div>
                    <div class="stat-hint">Ingresos − gastos</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-title">Inscritos semana</div>
                    <div class="stat-value">{{ $stats['inscritos_semana'] }}</div>
                    <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="stat-hint">Nuevos clientes</div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-title">Pagos pendientes</div>
                    <div class="stat-value">{{ $stats['pagos_pendientes'] }}</div>
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-hint">Ventas por cobrar</div>
                </div>
                <div class="stat-card cyan">
                    <div class="stat-title">Asistencias hoy</div>
                    <div class="stat-value">{{ $stats['asistencias_hoy'] }}</div>
                    <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-hint">{{ $stats['en_gym'] }} en el gym ahora</div>
                </div>
                <div class="stat-card pink">
                    <div class="stat-title">Membresías del mes</div>
                    <div class="stat-value">{{ $stats['membresias_mes'] }}</div>
                    <div class="stat-icon"><i class="fas fa-rotate"></i></div>
                    <div class="stat-hint">{{ $stats['por_vencer'] }} vencen en 7 días</div>
                </div>
            </div>

            <div class="charts-section">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Asistencias de la semana</h3>
                        @if (\App\Support\Permisos::puede($usuario, 'asistencias'))
                            <a href="{{ route('asistencias.index') }}" class="btn btn-light btn-sm">Ver detalle</a>
                        @endif
                    </div>
                    <div class="chart-box"><canvas id="barChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-header"><h3>Balance semanal</h3></div>
                    <div class="donut-box">
                        <canvas id="donutChart"></canvas>
                        <div class="donut-center">
                            <small>NETO</small>
                            <strong style="color: {{ $stats['neto_semana'] >= 0 ? '#059669' : '#dc2626' }}">{{ dinero($stats['neto_semana']) }}</strong>
                        </div>
                    </div>
                    <ul class="chart-legend">
                        <li><span><i class="dot" style="background:#10b981"></i>Ingresos</span> <span class="val">{{ dinero($stats['ingresos_semana']) }}</span></li>
                        <li><span><i class="dot" style="background:#ef4444"></i>Gastos</span> <span class="val">{{ dinero($stats['gastos_semana']) }}</span></li>
                        <li class="total">Ganancia neta <span class="val">{{ dinero($stats['neto_semana']) }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <div class="summary-card">
                <div class="summary-title">RESUMEN OPERATIVO</div>
                <div class="summary-grid">
                    <div class="summary-item"><h2>{{ $stats['en_gym'] }}</h2><p>En el gym</p></div>
                    <div class="summary-item"><h2>{{ $clasesHoy->count() }}</h2><p>Clases hoy</p></div>
                </div>
            </div>

            <div class="panel-card">
                <h4>Clases de hoy</h4>
                @forelse ($clasesHoy as $clase)
                    <ul class="tx-list">
                        <li>
                            <span>{{ $clase->nombre }}<small>{{ $clase->horario }} · {{ $clase->entrenador?->nombre ?? 'Sin coach' }}</small></span>
                            <span class="chip">{{ $clase->clientes_count }}/{{ $clase->cupo }}</span>
                        </li>
                    </ul>
                @empty
                    <p class="muted" style="margin:0;">No hay clases programadas hoy.</p>
                @endforelse
            </div>

            <div class="panel-card">
                <h4>Últimas ventas</h4>
                @forelse ($ultimasVentas as $venta)
                    <ul class="tx-list">
                        <li>
                            <span>
                                {{ $venta->cliente?->nombre ?? 'Venta mostrador' }}
                                <small>{{ $venta->folio }} · {{ $venta->fecha->format('d/m/Y') }} @if ($venta->estado === 'pendiente') · <b style="color:#b45309">pendiente</b> @endif</small>
                            </span>
                            <strong>{{ dinero($venta->total) }}</strong>
                        </li>
                    </ul>
                @empty
                    <p class="muted" style="margin:0;">Sin ventas recientes.</p>
                @endforelse
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            (function () {
                if (typeof Chart === 'undefined') return;

                const primario = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#ff4d4f';
                Chart.defaults.font.family = 'Inter, sans-serif';
                Chart.defaults.color = '#94a3b8';

                const asistencias = @json($graficoAsistencias);

                new Chart(document.getElementById('barChart'), {
                    type: 'bar',
                    data: {
                        labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                        datasets: [{ label: 'Asistencias', data: asistencias, backgroundColor: primario, borderRadius: 8, maxBarThickness: 36 }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true, suggestedMax: Math.max(...asistencias) < 5 ? 5 : undefined, ticks: { precision: 0 }, grid: { color: '#f1f5f9' }, border: { display: false } },
                            x: { grid: { display: false }, border: { display: false } },
                        },
                        plugins: { legend: { display: false } },
                    },
                });

                const ingresos = {{ (float) $stats['ingresos_semana'] }};
                const gastos = {{ (float) $stats['gastos_semana'] }};
                const sinDatos = ingresos === 0 && gastos === 0;

                new Chart(document.getElementById('donutChart'), {
                    type: 'doughnut',
                    data: {
                        labels: sinDatos ? ['Sin movimientos'] : ['Ingresos', 'Gastos'],
                        datasets: [{ data: sinDatos ? [1] : [ingresos, gastos], backgroundColor: sinDatos ? ['#e2e8f0'] : ['#10b981', '#ef4444'], borderWidth: 0 }],
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false }, tooltip: { enabled: !sinDatos } } },
                });
            })();
        </script>
    </x-slot>
</x-admin-layout>
