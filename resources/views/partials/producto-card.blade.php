{{-- Tarjeta de producto del sitio público. Requiere $producto (con stock_total y categoria_slug). --}}
@php
    $imagenes = ['img/img1.jpg', 'img/img2.jpg', 'img/img3.jpg'];
    $img = asset($imagenes[$producto->id_producto % 3]);
    $agotado = $producto->stock_total <= 0;
@endphp

<div class="col-md-6 col-lg-3 fade-in product-item" data-category="{{ $producto->categoria_slug }}">
    <div class="product-card h-100">
        <div class="product-image position-relative">
            <img src="{{ $img }}" class="img-fluid" alt="{{ $producto->nombre }}">
            @if ($agotado)
                <span class="position-absolute top-0 start-0 m-2 badge bg-secondary">Agotado</span>
            @elseif ($producto->stock_total <= $producto->stock_minimo)
                <span class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark">Últimas unidades</span>
            @endif
        </div>
        <div class="product-info p-4">
            <small class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 1px;">{{ $producto->tipo?->nombre ?? 'Producto' }}</small>
            <h5 class="fw-bold mb-2">{{ $producto->nombre }}</h5>
            <p class="text-muted mb-3">{{ $producto->descripcion ?: 'Disponible en recepción.' }}</p>
            <div class="d-flex justify-content-between align-items-center">
                <span class="fs-4 fw-bold text-warning">{{ dinero($producto->precio) }}</span>
                <button type="button" class="btn btn-dark btn-sm" @disabled($agotado)
                    data-agregar data-tipo="producto" data-ref="{{ $producto->id_producto }}"
                    data-nombre="{{ $producto->nombre }}" data-precio="{{ (float) $producto->precio }}"
                    data-img="{{ $img }}" data-stock="{{ $producto->stock_total }}">
                    {{ $agotado ? 'Agotado' : 'Agregar' }}
                </button>
            </div>
        </div>
    </div>
</div>
