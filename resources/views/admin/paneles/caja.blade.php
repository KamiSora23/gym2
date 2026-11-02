<x-admin-layout titulo="Caja" icono="fa-cash-register" modulo="resumen"
    subtitulo="{{ now()->locale('es')->translatedFormat('l, j \d\e F') }} · {{ \App\Support\Tenant::sucursal()?->nombre ?? 'todas las sucursales' }}">

    <x-slot name="acciones">
        <a href="{{ route('gastos.index') }}" class="btn btn-light"><i class="fas fa-file-invoice-dollar"></i> Registrar gasto</a>
        <a href="{{ route('ventas.create') }}" class="btn btn-primary"><i class="fas fa-cash-register"></i> Nueva venta</a>
    </x-slot>

    <div class="stats-grid">
        <div class="stat-card green"><div class="stat-title">Cobrado hoy</div><div class="stat-value">{{ dinero($cobradoHoy) }}</div><div class="stat-icon"><i class="fas fa-sack-dollar"></i></div><div class="stat-hint">{{ $ventasHoy->where('estado', 'completado')->count() }} ventas</div></div>
        <div class="stat-card orange"><div class="stat-title">Por cobrar</div><div class="stat-value">{{ dinero($pendientes->sum('total')) }}</div><div class="stat-icon"><i class="fas fa-clock"></i></div><div class="stat-hint">{{ $pendientes->count() }} ventas pendientes</div></div>
        <div class="stat-card red"><div class="stat-title">Gastos hoy</div><div class="stat-value">{{ dinero($gastosHoy) }}</div><div class="stat-icon"><i class="fas fa-receipt"></i></div></div>
        <div class="stat-card purple"><div class="stat-title">Saldo del día</div><div class="stat-value">{{ dinero($cobradoHoy - $gastosHoy) }}</div><div class="stat-icon"><i class="fas fa-scale-balanced"></i></div></div>
    </div>

    <div class="grid-2">
        <div>
            <div class="panel-card" style="margin-bottom:1.5rem;">
                <h4>Cobrado hoy por método de pago</h4>
                <ul class="tx-list">
                    @foreach (\App\Models\Venta::METODOS_PAGO as $clave => $nombre)
                        <li><span>{{ $nombre }}</span><strong>{{ dinero($porMetodo[$clave] ?? 0) }}</strong></li>
                    @endforeach
                </ul>
            </div>

            <div class="table-card">
                <div class="table-header"><h3>Pendientes de cobro</h3></div>
                <div class="table-scroll">
                    <table class="data-table">
                        <tbody>
                            @forelse ($pendientes as $v)
                                <tr>
                                    <td><a href="{{ route('ventas.show', $v) }}" style="font-weight:700; color:var(--primary);">{{ $v->folio }}</a><div class="muted">{{ $v->cliente?->nombre ?? 'Mostrador' }} · {{ $v->fecha->format('d/m') }}</div></td>
                                    <td>{{ dinero($v->total) }}</td>
                                    <td class="text-right">
                                        <form method="POST" action="{{ route('ventas.cobrar', $v) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Cobrar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="empty-state"><i class="fas fa-circle-check"></i> No hay cobros pendientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <div class="table-card">
                <div class="table-header"><h3>Ventas de hoy</h3><a href="{{ route('ventas.index') }}" class="btn btn-light btn-sm">Historial</a></div>
                <div class="table-scroll">
                    <table class="data-table">
                        <tbody>
                            @forelse ($ventasHoy as $v)
                                <tr>
                                    <td><a href="{{ route('ventas.show', $v) }}" style="font-weight:700; color:var(--primary);">{{ $v->folio }}</a><div class="muted">{{ $v->cliente?->nombre ?? 'Mostrador' }}</div></td>
                                    <td>{{ \App\Models\Venta::METODOS_PAGO[$v->metodo_pago] ?? $v->metodo_pago }}</td>
                                    <td class="money-in text-right">{{ dinero($v->total) }}</td>
                                </tr>
                            @empty
                                <tr><td class="empty-state"><i class="fas fa-basket-shopping"></i> Aún no hay ventas hoy.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel-card">
                <h4>Stock bajo <span class="muted">(5 o menos)</span></h4>
                <ul class="tx-list">
                    @forelse ($stockBajo as $p)
                        <li><span>{{ $p->nombre }}<small>{{ $p->tipo?->nombre }}</small></span><span class="status-badge {{ $p->stock_actual === 0 ? 'status-cancelled' : 'status-pending' }}">{{ $p->stock_actual }} unid.</span></li>
                    @empty
                        <li class="muted">Todo el stock está en orden.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-admin-layout>
