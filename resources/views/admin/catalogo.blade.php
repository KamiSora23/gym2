<x-admin-layout titulo="Catálogo" icono="fa-box-open" modulo="catalogo"
    subtitulo="Productos a la venta y stock {{ \App\Support\Tenant::sucursal() ? 'de '.\App\Support\Tenant::sucursal()->nombre : 'de todas las sucursales' }}.">

    <x-slot name="acciones">
        <div class="search-box"><i class="fas fa-search"></i><input type="search" placeholder="Buscar producto…" data-filter-cards="catalog-grid"></div>
        @if ($puedeEditar)
            <button type="button" class="btn btn-light" data-toggle-target="form-categoria"><i class="fas fa-tags"></i> Categoría</button>
            <button type="button" class="btn btn-primary" data-toggle-target="form-producto"><i class="fas fa-plus"></i> Nuevo producto</button>
        @endif
    </x-slot>

    @php
        $sucursalFija = \App\Support\Tenant::sucursalId();
        $creando = $errors->producto->any() && ! old('_id');
    @endphp

    @if ($puedeEditar)
        <form id="form-categoria" method="POST" action="{{ route('productos.categoria') }}" class="form-card {{ $errors->categoria->any() ? '' : 'hidden' }}">
            @csrf
            <h3><i class="fas fa-tags"></i> Nueva categoría</h3>
            <div class="form-grid">
                <div class="form-group"><label>Nombre</label><input type="text" name="nombre" placeholder="Suplementos" required></div>
                <div class="form-group">
                    <label>Categorías actuales</label>
                    <div style="display:flex; gap:.4rem; flex-wrap:wrap; padding-top:.35rem;">
                        @forelse ($categorias as $c) <span class="chip">{{ $c->nombre }}</span> @empty <span class="muted">Ninguna todavía</span> @endforelse
                    </div>
                </div>
            </div>
            @include('admin.partials.errores', ['bag' => 'categoria'])
            <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear categoría</button></div>
        </form>

        <form id="form-producto" method="POST" action="{{ route('productos.store') }}" class="form-card {{ $creando ? '' : 'hidden' }}">
            @csrf
            <h3><i class="fas fa-box"></i> Nuevo producto</h3>
            @if ($categorias->isEmpty())
                <p class="muted">Primero crea una categoría.</p>
            @endif
            <div class="form-grid cols-4">
                <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" value="{{ $creando ? old('nombre') : '' }}" required></div>
                <div class="form-group">
                    <label>Categoría *</label>
                    <select name="id_tipo" required>
                        @foreach ($categorias as $c)
                            <option value="{{ $c->id_tipo }}" @selected($creando && old('id_tipo') == $c->id_tipo)>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Precio de venta *</label><input type="number" step="0.01" min="0" name="precio" value="{{ $creando ? old('precio') : '' }}" required></div>
                <div class="form-group"><label>Stock inicial</label><input type="number" min="0" name="stock_inicial" value="{{ $creando ? old('stock_inicial') : 0 }}"></div>
                @unless ($sucursalFija)
                    <div class="form-group">
                        <label>Sucursal del stock</label>
                        <select name="id_sucursal">
                            @foreach ($sucursales as $s)<option value="{{ $s->id_sucursal }}">{{ $s->nombre }}</option>@endforeach
                        </select>
                    </div>
                @endunless
                <div class="form-group" style="grid-column: span 2;"><label>Descripción</label><input type="text" name="descripcion" value="{{ $creando ? old('descripcion') : '' }}"></div>
            </div>
            @if ($creando)
                @include('admin.partials.errores', ['bag' => 'producto'])
            @endif
            <div class="form-actions">
                <button type="button" class="btn btn-light" data-toggle-target="form-producto">Cancelar</button>
                <button type="submit" class="btn btn-primary" @disabled($categorias->isEmpty())><i class="fas fa-save"></i> Crear producto</button>
            </div>
        </form>
    @endif

    @include('admin.partials.errores', ['bag' => 'stock'])

    @if ($productos->isEmpty())
        <div class="table-card"><p class="empty-state" style="margin:0;"><i class="fas fa-box-open"></i> Aún no hay productos en el catálogo.</p></div>
    @else
        <div class="catalog-grid" id="catalog-grid">
            @foreach ($productos as $producto)
                @php
                    $stock = $producto->stockEn();
                    $editando = $errors->producto->any() && (int) old('_id') === $producto->id_producto;
                @endphp
                <div class="catalog-card" data-search="{{ \Illuminate\Support\Str::lower($producto->nombre.' '.$producto->tipo?->nombre) }}" style="{{ $producto->activo ? '' : 'opacity:.6' }}">
                    <div class="catalog-image"><i class="fas fa-dumbbell"></i></div>
                    <div class="catalog-body">
                        <div class="catalog-type">{{ $producto->tipo?->nombre ?? 'Sin categoría' }} @unless ($producto->activo) · inactivo @endunless</div>
                        <h3 class="catalog-title">{{ $producto->nombre }}</h3>
                        <p class="catalog-price">{{ dinero($producto->precio) }}</p>
                        <p class="catalog-stock">
                            <i class="fas fa-box" style="margin-right:5px;"></i> Stock: <strong>{{ $stock }}</strong>
                            @if ($stock <= 5)
                                <span class="status-badge {{ $stock === 0 ? 'status-cancelled' : 'status-pending' }}" style="margin-left:4px;">{{ $stock === 0 ? 'Agotado' : 'Bajo' }}</span>
                            @endif
                        </p>
                        @if (! $sucursalFija && $sucursales->count() > 1)
                            <p class="muted" style="margin:.35rem 0 0;">
                                @foreach ($sucursales as $s) {{ $s->nombre }}: {{ $producto->stockEn($s->id_sucursal) }}@if (! $loop->last) · @endif @endforeach
                            </p>
                        @endif

                        <form method="POST" action="{{ route('productos.stock', $producto) }}" style="display:flex; gap:.4rem; margin-top:.8rem;">
                            @csrf
                            <input type="number" name="cantidad" placeholder="+/- unid." required style="width:90px; padding:.4rem .5rem; border:1px solid #cbd5e1; border-radius:9px; font-size:.85rem;">
                            @unless ($sucursalFija)
                                <select name="id_sucursal" style="flex:1; min-width:0; padding:.4rem; border:1px solid #cbd5e1; border-radius:9px; font-size:.8rem;">
                                    @foreach ($sucursales as $s)<option value="{{ $s->id_sucursal }}">{{ $s->nombre }}</option>@endforeach
                                </select>
                            @endunless
                            <button type="submit" class="btn btn-light btn-sm" title="Ajustar stock"><i class="fas fa-boxes-stacked"></i></button>
                        </form>

                        @if ($puedeEditar)
                            <div class="company-actions" style="margin-top:.6rem;">
                                <button type="button" class="btn btn-light btn-sm" data-toggle-target="editar-producto-{{ $producto->id_producto }}"><i class="fas fa-pen"></i> Editar</button>
                                <form method="POST" action="{{ route('productos.destroy', $producto) }}" data-confirm="¿Eliminar {{ $producto->nombre }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                            <form id="editar-producto-{{ $producto->id_producto }}" method="POST" action="{{ route('productos.update', $producto) }}" class="{{ $editando ? '' : 'hidden' }}" style="margin-top:.8rem;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_id" value="{{ $producto->id_producto }}">
                                <div class="form-group"><label>Nombre</label><input type="text" name="nombre" value="{{ $editando ? old('nombre') : $producto->nombre }}" required></div>
                                <div class="form-group">
                                    <label>Categoría</label>
                                    <select name="id_tipo">
                                        @foreach ($categorias as $c)<option value="{{ $c->id_tipo }}" @selected(($editando ? old('id_tipo') : $producto->id_tipo) == $c->id_tipo)>{{ $c->nombre }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="form-group"><label>Precio</label><input type="number" step="0.01" min="0" name="precio" value="{{ $editando ? old('precio') : $producto->precio }}" required></div>
                                <div class="form-group"><label>Descripción</label><input type="text" name="descripcion" value="{{ $editando ? old('descripcion') : $producto->descripcion }}"></div>
                                <label class="form-check"><input type="checkbox" name="activo" value="1" @checked($editando ? old('activo') : $producto->activo)> A la venta</label>
                                @if ($editando)
                                    @include('admin.partials.errores', ['bag' => 'producto'])
                                @endif
                                <div class="form-actions"><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button></div>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-admin-layout>
