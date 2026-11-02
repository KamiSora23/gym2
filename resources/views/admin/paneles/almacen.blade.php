<x-admin-layout titulo="Almacén" icono="fa-warehouse" modulo="resumen"
    subtitulo="Stock {{ \App\Support\Tenant::sucursal() ? 'de '.\App\Support\Tenant::sucursal()->nombre : 'de todas las sucursales' }} y movimientos recientes.">

    <x-slot name="acciones">
        <a href="{{ route('inventario.movimientos') }}" class="btn btn-light"><i class="fas fa-boxes-stacked"></i> Kardex</a>
        <a href="{{ route('productos.index') }}" class="btn btn-primary"><i class="fas fa-box-open"></i> Productos</a>
    </x-slot>

    <div class="stats-grid">
        <div class="stat-card blue"><div class="stat-title">Productos activos</div><div class="stat-value">{{ $totalProductos }}</div><div class="stat-icon"><i class="fas fa-box"></i></div></div>
        <div class="stat-card cyan"><div class="stat-title">Unidades en stock</div><div class="stat-value">{{ $unidades }}</div><div class="stat-icon"><i class="fas fa-cubes"></i></div></div>
        <div class="stat-card orange"><div class="stat-title">Stock bajo</div><div class="stat-value">{{ $stockBajo->count() }}</div><div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div><div class="stat-hint">En o por debajo del mínimo</div></div>
        <div class="stat-card green"><div class="stat-title">Inventario valorizado</div><div class="stat-value">{{ dinero($valorizado) }}</div><div class="stat-icon"><i class="fas fa-coins"></i></div><div class="stat-hint">A precio de compra · {{ $entradasHoy }} unid. entraron hoy</div></div>
    </div>

    <div class="grid-2">
        <div class="table-card">
            <div class="table-header"><h3>Por reponer</h3></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Producto</th><th class="text-right">Stock</th><th class="text-right">Mínimo</th></tr></thead>
                    <tbody>
                        @forelse ($stockBajo as $p)
                            @php $stock = $p->stockEn(); @endphp
                            <tr><td>{{ $p->nombre }}<div class="muted">{{ $p->tipo?->nombre }}{{ $p->sku ? ' · '.$p->sku : '' }}</div></td><td class="text-right"><span class="status-badge {{ $stock === 0 ? 'status-cancelled' : 'status-pending' }}">{{ $stock }}</span></td><td class="text-right">{{ $p->stock_minimo }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="empty-state"><i class="fas fa-circle-check"></i> Ningún producto bajo el mínimo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="panel-card">
            <h4>Últimos movimientos</h4>
            <ul class="tx-list">
                @forelse ($movimientos as $m)
                    <li><span>{{ $m->producto?->nombre }}<small>{{ \App\Models\InventarioMovimiento::TIPOS[$m->tipo] ?? $m->tipo }} · {{ $m->usuario?->name ?? 'Sistema' }} · {{ $m->created_at->format('d/m H:i') }}</small></span><strong style="color: {{ $m->cantidad > 0 ? '#059669' : '#dc2626' }}">{{ $m->cantidad > 0 ? '+' : '' }}{{ $m->cantidad }}</strong></li>
                @empty
                    <li class="muted">Sin movimientos todavía.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-admin-layout>
