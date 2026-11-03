<x-admin-layout titulo="Reportes" icono="fa-chart-pie" modulo="reportes"
    subtitulo="Dinero, ventas, socios y uso del gimnasio del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}.">

    <x-slot name="acciones">
        <a href="{{ route('reportes.exportar', request()->only('desde', 'hasta')) }}" class="btn btn-light"><i class="fas fa-file-csv"></i> Exportar ventas</a>
        <button type="button" class="btn btn-light" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
    </x-slot>

    @php
        $dias = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];
        $atajos = [
            'Este mes' => [today()->startOfMonth(), today()],
            'Mes anterior' => [today()->subMonthNoOverflow()->startOfMonth(), today()->subMonthNoOverflow()->endOfMonth()],
            'Últimos 7 días' => [today()->subDays(6), today()],
            'Este año' => [today()->startOfYear(), today()],
        ];
        $maxHora = max(1, $asistencia['por_hora']->max() ?? 1);
        $maxDia = max(1, $asistencia['por_dia']->max());
    @endphp

    <form method="GET" class="form-card" style="display:flex; flex-wrap:wrap; gap:.75rem; align-items:flex-end;">
        <div class="form-group" style="margin:0;"><label>Desde</label><input type="date" name="desde" value="{{ $desde->toDateString() }}" max="{{ today()->toDateString() }}"></div>
        <div class="form-group" style="margin:0;"><label>Hasta</label><input type="date" name="hasta" value="{{ $hasta->toDateString() }}" max="{{ today()->toDateString() }}"></div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Ver</button>
        <div style="display:flex; flex-wrap:wrap; gap:.4rem; margin-left:auto;">
            @foreach ($atajos as $texto => [$a, $b])
                <a href="{{ route('reportes.index', ['desde' => $a->toDateString(), 'hasta' => $b->toDateString()]) }}"
                   class="btn btn-sm {{ $desde->isSameDay($a) && $hasta->isSameDay($b) ? 'btn-primary' : 'btn-light' }}">{{ $texto }}</a>
            @endforeach
        </div>
    </form>

    {{-- Dinero --}}
    <div class="stats-grid">
        <div class="stat-card green"><div class="stat-title">Ingresos</div><div class="stat-value">{{ dinero($resumen['ingresos']) }}</div><div class="stat-icon"><i class="fas fa-arrow-trend-up"></i></div></div>
        <div class="stat-card red"><div class="stat-title">Gastos</div><div class="stat-value">{{ dinero($resumen['gastos']) }}</div><div class="stat-icon"><i class="fas fa-arrow-trend-down"></i></div></div>
        <div class="stat-card {{ $resumen['utilidad'] >= 0 ? 'blue' : 'orange' }}"><div class="stat-title">Utilidad</div><div class="stat-value">{{ dinero($resumen['utilidad']) }}</div><div class="stat-icon"><i class="fas fa-scale-balanced"></i></div></div>
        <div class="stat-card purple"><div class="stat-title">Ventas · ticket promedio</div><div class="stat-value">{{ $resumen['ventas'] }} <small style="font-size:.9rem; font-weight:600;">· {{ dinero($resumen['ticket']) }}</small></div><div class="stat-icon"><i class="fas fa-receipt"></i></div></div>
    </div>

    <div class="chart-card" style="margin-bottom:1.5rem;">
        <div class="chart-header">
            <h3>Ingresos y gastos por día</h3>
            <span class="muted">Por cobrar: <strong>{{ dinero($resumen['por_cobrar']) }}</strong> · Anuladas: {{ $resumen['anuladas'] }}</span>
        </div>
        <div class="chart-box"><canvas id="graficoDinero"></canvas></div>
    </div>

    <div class="grid-2" style="margin-bottom:1.5rem;">
        <div class="table-card">
            <div class="table-header"><h3>Ingresos por método de pago</h3></div>
            <table class="data-table">
                <tbody>
                    @forelse ($porMetodo as $m)
                        <tr>
                            <td>{{ \App\Models\Venta::METODOS_PAGO[$m->metodo_pago] ?? ucfirst((string) $m->metodo_pago) }}</td>
                            <td class="muted">{{ $m->cantidad }} ventas</td>
                            <td class="text-right money-in">{{ dinero($m->total) }}</td>
                        </tr>
                    @empty
                        <tr><td class="empty-state">Sin ventas en el periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-card">
            <div class="table-header"><h3>Gastos por categoría</h3></div>
            <table class="data-table">
                <tbody>
                    @forelse ($gastosCategoria as $g)
                        <tr>
                            <td>{{ $g->categoria ?? 'Sin categoría' }}</td>
                            <td style="width:45%;"><div style="height:8px; border-radius:6px; background:#fee2e2;"><div style="height:8px; border-radius:6px; background:#ef4444; width:{{ $resumen['gastos'] > 0 ? round($g->total / $resumen['gastos'] * 100) : 0 }}%;"></div></div></td>
                            <td class="text-right money-out">{{ dinero($g->total) }}</td>
                        </tr>
                    @empty
                        <tr><td class="empty-state">Sin gastos en el periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid-2" style="margin-bottom:1.5rem;">
        <div class="table-card">
            <div class="table-header"><h3>Membresías vendidas por plan</h3></div>
            <table class="data-table">
                <tbody>
                    @forelse ($planes as $p)
                        <tr><td>{{ $p['nombre'] }}</td><td class="muted">{{ $p['cantidad'] }} vendidas</td><td class="text-right money-in">{{ dinero($p['total']) }}</td></tr>
                    @empty
                        <tr><td class="empty-state">No se vendieron membresías en el periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-card">
            <div class="table-header"><h3>Productos más vendidos</h3></div>
            <table class="data-table">
                <tbody>
                    @forelse ($topProductos as $p)
                        <tr><td>{{ $p->nombre }}</td><td class="muted">{{ (int) $p->cantidad }} unid.</td><td class="text-right money-in">{{ dinero($p->total) }}</td></tr>
                    @empty
                        <tr><td class="empty-state">No se vendieron productos en el periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Socios y retención --}}
    <div class="stats-grid">
        <div class="stat-card green"><div class="stat-title">Socios con membresía vigente</div><div class="stat-value">{{ $socios['vigentes'] }}</div><div class="stat-icon"><i class="fas fa-id-card"></i></div></div>
        <div class="stat-card blue"><div class="stat-title">Clientes nuevos (periodo)</div><div class="stat-value">{{ $socios['nuevos'] }}</div><div class="stat-icon"><i class="fas fa-user-plus"></i></div></div>
        <div class="stat-card orange"><div class="stat-title">Vencen en 7 días</div><div class="stat-value">{{ $socios['por_vencer'] }}</div><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div></div>
        <div class="stat-card red"><div class="stat-title">Vencidos sin renovar (30 días)</div><div class="stat-value">{{ $socios['vencidos'] }}</div><div class="stat-icon"><i class="fas fa-user-clock"></i></div></div>
    </div>

    <div class="grid-2" style="margin-bottom:1.5rem;">
        <div class="table-card">
            <div class="table-header">
                <h3>Para recuperar: vencieron y no renovaron</h3>
                @if ($socios['congelados'])<span class="muted">{{ $socios['congelados'] }} membresías congeladas</span>@endif
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <tbody>
                        @forelse ($vencidosSinRenovar as $c)
                            @php $ultima = $c->membresias->first(); @endphp
                            <tr>
                                <td><a href="{{ route('clientes.show', $c) }}" style="font-weight:600; color:var(--text-dark); text-decoration:none;">{{ $c->nombre }}</a><div class="muted">{{ $c->telefono ?? $c->correo ?? 'Sin contacto' }}</div></td>
                                <td class="muted">{{ $ultima?->plan?->nombre }} · venció {{ $ultima?->fecha_fin?->format('d/m') }}</td>
                                <td class="text-right">
                                    @if (\App\Support\Permisos::puede(Auth::user(), 'sales.create'))
                                        <a href="{{ route('ventas.create', ['cliente' => $c->id_cliente]) }}" class="btn btn-light btn-sm"><i class="fas fa-rotate"></i> Renovar</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td class="empty-state"><i class="fas fa-circle-check"></i> Todos los socios recientes renovaron.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header"><h3>Uso del gimnasio</h3><span class="muted">{{ $asistencia['total'] }} visitas · {{ $asistencia['promedio'] }} por día</span></div>
            <div style="padding:0 1.25rem 1.25rem;">
                <p class="muted" style="margin:0 0 .5rem; font-weight:600;">Por día de la semana</p>
                @foreach ($asistencia['por_dia'] as $d => $total)
                    <div style="display:flex; align-items:center; gap:.6rem; margin-bottom:.3rem;">
                        <span style="width:34px; font-size:.8rem;">{{ $dias[$d] }}</span>
                        <div style="flex:1; height:10px; border-radius:6px; background:#f1f5f9;"><div style="height:10px; border-radius:6px; background:var(--primary-color); width:{{ round($total / $maxDia * 100) }}%;"></div></div>
                        <span style="width:32px; text-align:right; font-size:.8rem;">{{ $total }}</span>
                    </div>
                @endforeach

                <p class="muted" style="margin:1rem 0 .5rem; font-weight:600;">Horas de mayor afluencia</p>
                @forelse ($asistencia['por_hora'] as $hora => $total)
                    <div style="display:flex; align-items:center; gap:.6rem; margin-bottom:.3rem;">
                        <span style="width:46px; font-size:.8rem;">{{ sprintf('%02d:00', $hora) }}</span>
                        <div style="flex:1; height:10px; border-radius:6px; background:#f1f5f9;"><div style="height:10px; border-radius:6px; background:{{ $total === $asistencia['por_hora']->max() ? '#f59e0b' : 'var(--secondary-color)' }}; width:{{ round($total / $maxHora * 100) }}%;"></div></div>
                        <span style="width:32px; text-align:right; font-size:.8rem;">{{ $total }}</span>
                    </div>
                @empty
                    <p class="muted">Sin asistencias en el periodo.</p>
                @endforelse
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            (function () {
                if (typeof Chart === 'undefined') return;
                Chart.defaults.font.family = 'Inter, sans-serif';
                Chart.defaults.color = '#94a3b8';
                const moneda = window.GYM_MONEDA || 'S/';

                new Chart(document.getElementById('graficoDinero'), {
                    type: 'bar',
                    data: {
                        labels: @json($grafico['dias']),
                        datasets: [
                            { label: 'Ingresos', data: @json($grafico['ventas']), backgroundColor: '#10b981', borderRadius: 6, maxBarThickness: 22 },
                            { label: 'Gastos', data: @json($grafico['gastos']), backgroundColor: '#ef4444', borderRadius: 6, maxBarThickness: 22 },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { tooltip: { callbacks: { label: c => `${c.dataset.label}: ${moneda} ${Number(c.raw).toFixed(2)}` } } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, border: { display: false } },
                            x: { grid: { display: false }, border: { display: false } },
                        },
                    },
                });
            })();
        </script>
    </x-slot>
</x-admin-layout>
