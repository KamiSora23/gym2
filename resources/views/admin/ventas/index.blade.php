<x-admin-layout titulo="Ventas" icono="fa-cart-shopping" modulo="ventas"
    subtitulo="Historial de ventas de productos y membresías.">

    <x-slot name="acciones">
        <a href="{{ route('ventas.create') }}" class="btn btn-primary"><i class="fas fa-cash-register"></i> Nueva venta</a>
    </x-slot>

    @php
        $estados = ['completado' => ['status-active', 'Pagada'], 'pendiente' => ['status-pending', 'Pendiente'], 'anulado' => ['status-cancelled', 'Anulada']];
        $puedeAnular = Auth::user()->permite('ventas.anular');
    @endphp

    <div class="stats-grid">
        <div class="stat-card green"><div class="stat-title">Ventas hoy</div><div class="stat-value">{{ dinero($stats['hoy']) }}</div><div class="stat-icon"><i class="fas fa-cash-register"></i></div></div>
        <div class="stat-card cyan"><div class="stat-title">Ventas semana</div><div class="stat-value">{{ dinero($stats['semana']) }}</div><div class="stat-icon"><i class="fas fa-chart-line"></i></div><div class="stat-hint">{{ $stats['transacciones'] }} transacciones</div></div>
        <div class="stat-card purple"><div class="stat-title">Ticket promedio</div><div class="stat-value">{{ dinero($stats['ticket']) }}</div><div class="stat-icon"><i class="fas fa-ticket"></i></div></div>
        <div class="stat-card orange"><div class="stat-title">Por cobrar</div><div class="stat-value">{{ $stats['pendientes'] }}</div><div class="stat-icon"><i class="fas fa-clock"></i></div></div>
    </div>

    <form method="GET" class="form-card">
        <div class="form-grid cols-4" style="align-items:end;">
            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    @foreach ($estados as $valor => [, $texto])
                        <option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Desde</label><input type="date" name="desde" value="{{ request('desde') }}"></div>
            <div class="form-group"><label>Hasta</label><input type="date" name="hasta" value="{{ request('hasta') }}"></div>
            <div class="form-group" style="flex-direction:row; gap:.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="{{ route('ventas.index') }}" class="btn btn-light">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="table-card">
        <div class="table-header"><h3>Historial <span class="count">({{ $ventas->total() }})</span></h3></div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr><th>Folio</th><th>Cliente</th><th>Fecha</th><th>Sucursal</th><th>Pago</th><th>Total</th><th>Estado</th><th class="text-right">Acciones</th></tr>
                </thead>
                <tbody>
                    @forelse ($ventas as $venta)
                        <tr>
                            <td><a href="{{ route('ventas.show', $venta) }}" style="font-weight:700; color:var(--primary);">{{ $venta->folio }}</a></td>
                            <td>{{ $venta->cliente?->nombre ?? 'Mostrador' }}</td>
                            <td>{{ $venta->fecha->format('d/m/Y') }}</td>
                            <td>{{ $venta->sucursal?->nombre ?? '—' }}</td>
                            <td>{{ \App\Models\Venta::METODOS_PAGO[$venta->metodo_pago] ?? $venta->metodo_pago }}</td>
                            <td class="{{ $venta->estado === 'anulado' ? '' : 'money-in' }}" style="{{ $venta->estado === 'anulado' ? 'text-decoration:line-through; color:#94a3b8;' : '' }}">{{ dinero($venta->total) }}</td>
                            <td><span class="status-badge {{ $estados[$venta->estado][0] ?? 'status-info' }}">{{ $estados[$venta->estado][1] ?? $venta->estado }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('ventas.show', $venta) }}" class="btn btn-light btn-sm btn-icon" title="Ver detalle"><i class="fas fa-eye"></i></a>
                                    @if ($venta->estado === 'pendiente')
                                        <form method="POST" action="{{ route('ventas.cobrar', $venta) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-light btn-sm btn-icon" title="Marcar como pagada" style="color:#059669;"><i class="fas fa-check"></i></button>
                                        </form>
                                    @endif
                                    @if ($puedeAnular && $venta->estado !== 'anulado')
                                        <form method="POST" action="{{ route('ventas.anular', $venta) }}" data-confirm="¿Anular la venta {{ $venta->folio }}? Se devolverá el stock y se cancelará la membresía.">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Anular"><i class="fas fa-ban"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty-state"><i class="fas fa-basket-shopping"></i> No hay ventas para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.partials.paginacion', ['items' => $ventas])
</x-admin-layout>
