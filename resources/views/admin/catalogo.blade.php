<x-admin-layout titulo="Catálogo" icono="fa-box-open" modulo="catalogo"
    subtitulo="Productos a la venta y stock {{ \App\Support\Tenant::sucursal() ? 'de '.\App\Support\Tenant::sucursal()->nombre : 'de todas las sucursales' }}.">

    <x-slot name="acciones">
        <div class="search-box"><i class="fas fa-search"></i><input type="search" placeholder="Buscar producto…" data-filter-cards="catalog-grid"></div>
        @if ($puedeKardex)
            <a href="{{ route('inventario.movimientos') }}" class="btn btn-light"><i class="fas fa-boxes-stacked"></i> Kardex</a>
        @endif
        @if ($puedeEditar)
            <a href="{{ route('categorias.index') }}" class="btn btn-light"><i class="fas fa-tags"></i> Categorías</a>
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

        <form id="form-producto" method="POST" action="{{ route('productos.store') }}" enctype="multipart/form-data" class="form-card {{ $creando ? '' : 'hidden' }}">
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
                        @foreach ($categorias->where('activo', true) as $c)
                            <option value="{{ $c->id_tipo }}" @selected($creando && old('id_tipo') == $c->id_tipo)>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Precio de venta *</label><input type="number" step="0.01" min="0" name="precio" value="{{ $creando ? old('precio') : '' }}" required></div>
                <div class="form-group"><label>Precio de compra</label><input type="number" step="0.01" min="0" name="precio_compra" value="{{ $creando ? old('precio_compra') : '' }}"></div>
                <div class="form-group"><label>SKU / código</label><input type="text" name="sku" value="{{ $creando ? old('sku') : '' }}" placeholder="PROT-WHEY-2KG"></div>
                <div class="form-group"><label>Stock mínimo</label><input type="number" min="0" name="stock_minimo" value="{{ $creando ? old('stock_minimo', 5) : 5 }}"></div>
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
                <div class="form-group" style="grid-column: span 2;"><label>Foto del producto</label><input type="file" name="imagen" accept="image/png,image/jpeg,image/webp"><small class="muted">JPG, PNG o WEBP · máx. 2 MB. Se muestra en la tienda web.</small></div>
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
                    <div class="catalog-image">
                        @if ($producto->imagen_url)
                            <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <i class="fas fa-dumbbell"></i>
                        @endif
                    </div>
                    <div class="catalog-body">
                        <div class="catalog-type">{{ $producto->tipo?->nombre ?? 'Sin categoría' }} @unless ($producto->activo) · inactivo @endunless</div>
                        <h3 class="catalog-title">{{ $producto->nombre }}</h3>
                        <p class="catalog-price">{{ dinero($producto->precio) }}</p>
                        <p class="catalog-stock">
                            <i class="fas fa-box" style="margin-right:5px;"></i> Stock: <strong>{{ $stock }}</strong> <span class="muted">/ mín. {{ $producto->stock_minimo }}</span>
                            @if ($stock <= $producto->stock_minimo)
                                <span class="status-badge {{ $stock === 0 ? 'status-cancelled' : 'status-pending' }}" style="margin-left:4px;">{{ $stock === 0 ? 'Agotado' : 'Bajo' }}</span>
                            @endif
                        </p>
                        @if (! $sucursalFija && $sucursales->count() > 1)
                            <p class="muted" style="margin:.35rem 0 0;">
                                @foreach ($sucursales as $s) {{ $s->nombre }}: {{ $producto->stockEn($s->id_sucursal) }}@if (! $loop->last) · @endif @endforeach
                            </p>
                        @endif

                        @if ($puedeEntrada || $puedeSalida)
                        <form method="POST" action="{{ route('productos.stock', $producto) }}" style="display:flex; flex-wrap:wrap; gap:.4rem; margin-top:.8rem;">
                            @csrf
                            <input type="number" name="cantidad" placeholder="{{ $puedeSalida && $puedeEntrada ? '+/- unid.' : ($puedeEntrada ? '+ unid.' : '- unid.') }}" @unless ($puedeSalida) min="1" @endunless @unless ($puedeEntrada) max="-1" @endunless required style="width:90px; padding:.4rem .5rem; border:1px solid #cbd5e1; border-radius:9px; font-size:.85rem;">
                            @if ($puedeSalida)
                                <select name="tipo" title="Tipo de salida" style="padding:.4rem; border:1px solid #cbd5e1; border-radius:9px; font-size:.8rem;">
                                    <option value="ajuste">Ajuste</option><option value="merma">Merma</option><option value="salida">Salida</option>
                                </select>
                                <input type="text" name="motivo" placeholder="Motivo (para salidas)" style="flex:1 1 100%; padding:.4rem .5rem; border:1px solid #cbd5e1; border-radius:9px; font-size:.8rem;">
                            @endif
                            @unless ($sucursalFija)
                                <select name="id_sucursal" style="flex:1; min-width:0; padding:.4rem; border:1px solid #cbd5e1; border-radius:9px; font-size:.8rem;">
                                    @foreach ($sucursales as $s)<option value="{{ $s->id_sucursal }}">{{ $s->nombre }}</option>@endforeach
                                </select>
                            @endunless
                            <button type="submit" class="btn btn-light btn-sm" title="{{ $puedeSalida ? 'Ajustar stock' : 'Registrar entrada de stock' }}"><i class="fas fa-boxes-stacked"></i></button>
                        </form>
                        @endif

                        @if ($puedeEditar)
                            <div class="company-actions" style="margin-top:.6rem;">
                                <button type="button" class="btn btn-light btn-sm" data-toggle-target="editar-producto-{{ $producto->id_producto }}"><i class="fas fa-pen"></i> Editar</button>
                                <form method="POST" action="{{ route('productos.estado', $producto) }}" @if ($producto->activo) data-confirm="¿Desactivar {{ $producto->nombre }}? Dejará de venderse, pero conserva su stock e historial." @endif>
                                    @csrf
                                    @method('PATCH')
                                    @if ($producto->activo)
                                        <button type="submit" class="btn btn-light btn-sm"><i class="fas fa-eye-slash"></i> Desactivar</button>
                                    @else
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> Activar</button>
                                    @endif
                                </form>
                            </div>
                            <form id="editar-producto-{{ $producto->id_producto }}" method="POST" action="{{ route('productos.update', $producto) }}" enctype="multipart/form-data" class="{{ $editando ? '' : 'hidden' }}" style="margin-top:.8rem;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="_id" value="{{ $producto->id_producto }}">
                                <div class="form-group"><label>Nombre</label><input type="text" name="nombre" value="{{ $editando ? old('nombre') : $producto->nombre }}" required></div>
                                <div class="form-group">
                                    <label>Categoría</label>
                                    <select name="id_tipo">
                                        @foreach ($categorias->filter(fn ($c) => $c->activo || $c->id_tipo === $producto->id_tipo) as $c)<option value="{{ $c->id_tipo }}" @selected(($editando ? old('id_tipo') : $producto->id_tipo) == $c->id_tipo)>{{ $c->nombre }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Precio de venta @unless ($puedePrecio)<span class="muted">(sin permiso para cambiarlo)</span>@endunless</label>
                                    <input type="number" step="0.01" min="0" name="precio" value="{{ $editando ? old('precio') : $producto->precio }}" required @unless ($puedePrecio) readonly style="background:#f1f5f9;" @endunless>
                                </div>
                                <div class="form-group"><label>Precio de compra</label><input type="number" step="0.01" min="0" name="precio_compra" value="{{ $editando ? old('precio_compra') : $producto->precio_compra }}"></div>
                                <div class="form-group"><label>SKU</label><input type="text" name="sku" value="{{ $editando ? old('sku') : $producto->sku }}"></div>
                                <div class="form-group"><label>Stock mínimo</label><input type="number" min="0" name="stock_minimo" value="{{ $editando ? old('stock_minimo') : $producto->stock_minimo }}"></div>
                                <div class="form-group"><label>Descripción</label><input type="text" name="descripcion" value="{{ $editando ? old('descripcion') : $producto->descripcion }}"></div>
                                <div class="form-group">
                                    <label>{{ $producto->imagen ? 'Cambiar foto' : 'Foto' }}</label>
                                    <input type="file" name="imagen" accept="image/png,image/jpeg,image/webp">
                                    @if ($producto->imagen)
                                        <label class="form-check" style="margin-top:.35rem;"><input type="checkbox" name="quitar_imagen" value="1"> Quitar la foto actual</label>
                                    @endif
                                </div>
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
