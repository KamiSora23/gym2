<x-admin-layout titulo="Categorías" icono="fa-tags" modulo="categorias"
    subtitulo="Organiza los productos de la tienda y los gastos. Una categoría en uso no se elimina: se desactiva.">

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 460px), 1fr)); gap:1.5rem;">
        {{-- Categorías de productos --}}
        <div class="table-card">
            <div class="table-header">
                <h3><i class="fas fa-box-open" style="color:var(--primary)"></i> Productos <span class="count">({{ $productos->count() }})</span></h3>
            </div>

            @if ($puedeProductos)
                <form method="POST" action="{{ route('categorias.productos.store') }}" style="display:flex; gap:.5rem; padding:0 1.25rem 1rem;">
                    @csrf
                    <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Nueva categoría (ej. Suplementos)" required maxlength="80" style="flex:1; padding:.55rem .75rem; border:1px solid #cbd5e1; border-radius:10px;">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Agregar</button>
                </form>
                <div style="padding:0 1.25rem;">@include('admin.partials.errores', ['bag' => 'categoria'])</div>
            @endif

            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Categoría</th><th>Productos</th><th>Estado</th>@if ($puedeProductos)<th class="text-right">Acciones</th>@endif</tr></thead>
                    <tbody>
                        @forelse ($productos as $c)
                            <tr>
                                <td style="font-weight:600;">{{ $c->nombre }}</td>
                                <td><a href="{{ route('productos.index') }}" class="muted">{{ $c->productos_count }}</a></td>
                                <td><span class="status-badge {{ $c->activo ? 'status-active' : 'status-cancelled' }}">{{ $c->activo ? 'Activa' : 'Inactiva' }}</span></td>
                                @if ($puedeProductos)
                                    <td>
                                        <div class="row-actions">
                                            <button type="button" class="btn btn-light btn-sm btn-icon" title="Renombrar" data-toggle-target="cat-p-{{ $c->id_tipo }}"><i class="fas fa-pen"></i></button>
                                            <form method="POST" action="{{ route('categorias.productos.estado', $c) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-light btn-sm btn-icon" title="{{ $c->activo ? 'Desactivar' : 'Activar' }}"><i class="fas {{ $c->activo ? 'fa-eye-slash' : 'fa-eye' }}"></i></button>
                                            </form>
                                            @if ($c->productos_count === 0)
                                                <form method="POST" action="{{ route('categorias.productos.destroy', $c) }}" data-confirm="¿Eliminar la categoría {{ $c->nombre }}?">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar"><i class="fas fa-trash"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                            @if ($puedeProductos)
                                @php $bolsa = 'categoria_'.$c->id_tipo; @endphp
                                <tr id="cat-p-{{ $c->id_tipo }}" class="edit-row {{ $errors->{$bolsa}->any() ? '' : 'hidden' }}">
                                    <td colspan="4">
                                        <form method="POST" action="{{ route('categorias.productos.update', $c) }}" style="display:flex; gap:.5rem;">
                                            @csrf @method('PUT')
                                            <input type="text" name="nombre" value="{{ $c->nombre }}" required maxlength="80" style="flex:1; padding:.5rem .75rem; border:1px solid #cbd5e1; border-radius:10px;">
                                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button>
                                        </form>
                                        @include('admin.partials.errores', ['bag' => $bolsa])
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="4" class="empty-state"><i class="fas fa-tags"></i> Aún no hay categorías de productos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Categorías de gastos --}}
        <div class="table-card">
            <div class="table-header">
                <h3><i class="fas fa-file-invoice-dollar" style="color:var(--primary)"></i> Gastos <span class="count">({{ $gastos->count() }})</span></h3>
            </div>

            @if ($puedeGastos)
                <form method="POST" action="{{ route('categorias.gastos.store') }}" style="display:flex; gap:.5rem; padding:0 1.25rem 1rem;">
                    @csrf
                    <input type="text" name="nombre" placeholder="Nueva categoría (ej. Limpieza)" required maxlength="60" style="flex:1; padding:.55rem .75rem; border:1px solid #cbd5e1; border-radius:10px;">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Agregar</button>
                </form>
                <div style="padding:0 1.25rem;">@include('admin.partials.errores', ['bag' => 'categoria_gasto'])</div>
            @endif

            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Categoría</th><th>Gastos</th><th>Estado</th>@if ($puedeGastos)<th class="text-right">Acciones</th>@endif</tr></thead>
                    <tbody>
                        @foreach ($gastos as $c)
                            <tr>
                                <td style="font-weight:600;">{{ $c->nombre }}</td>
                                <td>{{ $c->usos }} <span class="muted">· {{ dinero($c->monto) }}</span></td>
                                <td><span class="status-badge {{ $c->activo ? 'status-active' : 'status-cancelled' }}">{{ $c->activo ? 'Activa' : 'Inactiva' }}</span></td>
                                @if ($puedeGastos)
                                    <td>
                                        <div class="row-actions">
                                            <button type="button" class="btn btn-light btn-sm btn-icon" title="Renombrar" data-toggle-target="cat-g-{{ $c->id_categoria }}"><i class="fas fa-pen"></i></button>
                                            <form method="POST" action="{{ route('categorias.gastos.estado', $c) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-light btn-sm btn-icon" title="{{ $c->activo ? 'Desactivar' : 'Activar' }}"><i class="fas {{ $c->activo ? 'fa-eye-slash' : 'fa-eye' }}"></i></button>
                                            </form>
                                            @if ($c->usos === 0)
                                                <form method="POST" action="{{ route('categorias.gastos.destroy', $c) }}" data-confirm="¿Eliminar la categoría {{ $c->nombre }}?">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar"><i class="fas fa-trash"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                            @if ($puedeGastos)
                                @php $bolsa = 'categoria_gasto_'.$c->id_categoria; @endphp
                                <tr id="cat-g-{{ $c->id_categoria }}" class="edit-row {{ $errors->{$bolsa}->any() ? '' : 'hidden' }}">
                                    <td colspan="4">
                                        <form method="POST" action="{{ route('categorias.gastos.update', $c) }}" style="display:flex; gap:.5rem;">
                                            @csrf @method('PUT')
                                            <input type="text" name="nombre" value="{{ $c->nombre }}" required maxlength="60" style="flex:1; padding:.5rem .75rem; border:1px solid #cbd5e1; border-radius:10px;">
                                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button>
                                        </form>
                                        <p class="muted" style="margin:.4rem 0 0;">Los gastos ya registrados con esta categoría también se renombran.</p>
                                        @include('admin.partials.errores', ['bag' => $bolsa])
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
