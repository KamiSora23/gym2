<x-admin-layout titulo="Gastos" icono="fa-file-invoice-dollar" modulo="gastos"
    subtitulo="Control de egresos del gimnasio.">

    <x-slot name="acciones">
        <button type="button" class="btn btn-primary" data-toggle-target="form-gasto"><i class="fas fa-plus"></i> Nuevo gasto</button>
    </x-slot>

    <div class="stats-grid">
        <div class="stat-card red"><div class="stat-title">Gastos hoy</div><div class="stat-value">{{ dinero($stats['hoy']) }}</div><div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div></div>
        <div class="stat-card orange"><div class="stat-title">Gastos semana</div><div class="stat-value">{{ dinero($stats['semana']) }}</div><div class="stat-icon"><i class="fas fa-chart-bar"></i></div></div>
        <div class="stat-card blue"><div class="stat-title">Gastos mes</div><div class="stat-value">{{ dinero($stats['mes']) }}</div><div class="stat-icon"><i class="fas fa-calendar"></i></div></div>
        <div class="stat-card purple"><div class="stat-title">Registros</div><div class="stat-value">{{ $stats['registros'] }}</div><div class="stat-icon"><i class="fas fa-list"></i></div></div>
    </div>

    <form id="form-gasto" method="POST" action="{{ route('gastos.store') }}" class="form-card {{ $errors->gasto->any() ? '' : 'hidden' }}">
        @csrf
        <h3><i class="fas fa-file-circle-plus"></i> Registrar gasto</h3>
        <div class="form-grid cols-4">
            <div class="form-group"><label>Concepto *</label><input type="text" name="concepto" value="{{ old('concepto') }}" placeholder="Pago de luz" required></div>
            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria">
                    @foreach (\App\Models\Gasto::CATEGORIAS as $c)<option value="{{ $c }}" @selected(old('categoria') === $c)>{{ $c }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label>Monto *</label><input type="number" step="0.01" min="0.01" name="monto" value="{{ old('monto') }}" required></div>
            <div class="form-group"><label>Fecha *</label><input type="date" name="fecha" value="{{ old('fecha', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required></div>
            @unless (\App\Support\Tenant::sucursalId())
                <div class="form-group">
                    <label>Sucursal</label>
                    <select name="id_sucursal">
                        @foreach ($sucursales as $s)<option value="{{ $s->id_sucursal }}" @selected(old('id_sucursal') == $s->id_sucursal)>{{ $s->nombre }}</option>@endforeach
                    </select>
                </div>
            @endunless
        </div>
        @include('admin.partials.errores', ['bag' => 'gasto'])
        <div class="form-actions">
            <button type="button" class="btn btn-light" data-toggle-target="form-gasto">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar gasto</button>
        </div>
    </form>

    <form method="GET" class="form-card">
        <div class="form-grid cols-4" style="align-items:end;">
            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria">
                    <option value="">Todas</option>
                    @foreach (\App\Models\Gasto::CATEGORIAS as $c)<option value="{{ $c }}" @selected(request('categoria') === $c)>{{ $c }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label>Mes</label><input type="month" name="mes" value="{{ request('mes') }}"></div>
            <div class="form-group" style="flex-direction:row; gap:.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="{{ route('gastos.index') }}" class="btn btn-light">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="table-card">
        <div class="table-header"><h3>Registro de gastos <span class="count">({{ $gastos->total() }})</span></h3></div>
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr><th>Concepto</th><th>Categoría</th><th>Monto</th><th>Fecha</th><th>Sucursal</th><th>Registrado por</th><th></th></tr></thead>
                <tbody>
                    @forelse ($gastos as $gasto)
                        <tr>
                            <td>{{ $gasto->concepto }}</td>
                            <td><span class="chip">{{ $gasto->categoria ?? 'Otros' }}</span></td>
                            <td class="money-out">-{{ dinero($gasto->monto) }}</td>
                            <td>{{ $gasto->fecha->format('d/m/Y') }}</td>
                            <td>{{ $gasto->sucursal?->nombre ?? '—' }}</td>
                            <td>{{ $gasto->usuario?->name ?? '—' }}</td>
                            <td class="text-right">
                                @if (Auth::user()->permite('expenses.delete'))
                                    <form method="POST" action="{{ route('gastos.destroy', $gasto) }}" data-confirm="¿Eliminar el gasto «{{ $gasto->concepto }}»?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-state"><i class="fas fa-file-invoice"></i> No hay gastos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.partials.paginacion', ['items' => $gastos])
</x-admin-layout>
