<x-admin-layout titulo="Inventario" icono="fa-boxes-stacked" modulo="kardex"
    subtitulo="Kardex: cada entrada, venta, anulación y ajuste de stock con su responsable y motivo.">

    <x-slot name="acciones">
        <a href="{{ route('productos.index') }}" class="btn btn-light"><i class="fas fa-box-open"></i> Productos</a>
    </x-slot>

    <form method="GET" class="form-card">
        <div class="form-grid cols-4" style="align-items:end;">
            <div class="form-group">
                <label>Producto</label>
                <select name="producto">
                    <option value="">Todos</option>
                    @foreach ($productos as $p)<option value="{{ $p->id_producto }}" @selected(request('producto') == $p->id_producto)>{{ $p->nombre }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    @foreach (\App\Models\InventarioMovimiento::TIPOS as $k => $v)<option value="{{ $k }}" @selected(request('tipo') === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="form-group" style="flex-direction:row; gap:.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="{{ route('inventario.movimientos') }}" class="btn btn-light">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="table-card">
        <div class="table-header"><h3>Movimientos <span class="count">({{ $movimientos->total() }})</span></h3></div>
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th class="text-right">Cantidad</th><th class="text-right">Stock</th><th>Sucursal</th><th>Responsable</th><th>Motivo</th></tr></thead>
                <tbody>
                    @forelse ($movimientos as $m)
                        <tr>
                            <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $m->producto?->nombre }}</td>
                            <td><span class="chip">{{ \App\Models\InventarioMovimiento::TIPOS[$m->tipo] ?? $m->tipo }}</span></td>
                            <td class="text-right {{ $m->cantidad > 0 ? 'money-in' : 'money-out' }}">{{ $m->cantidad > 0 ? '+' : '' }}{{ $m->cantidad }}</td>
                            <td class="text-right">{{ $m->stock_resultante }}</td>
                            <td>{{ $m->sucursal?->nombre }}</td>
                            <td>{{ $m->usuario?->name ?? 'Sistema' }}</td>
                            <td style="white-space:normal;">{{ $m->motivo ?? ($m->id_venta ? 'Venta #'.str_pad($m->id_venta, 5, '0', STR_PAD_LEFT) : '—') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty-state"><i class="fas fa-boxes-stacked"></i> Aún no hay movimientos de inventario.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @include('admin.partials.paginacion', ['items' => $movimientos])
</x-admin-layout>
