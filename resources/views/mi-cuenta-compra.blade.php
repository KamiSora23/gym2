<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra {{ $venta->folio }} · {{ $config->nombre_gym }}</title>
    <link rel="icon" href="{{ $config->favicon_url }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    @include('partials.tema-web')
    <style>
        body { background: #f4f6fa; }
        .comprobante { max-width: 720px; margin: 2rem auto; background: #fff; border-radius: 18px; box-shadow: 0 6px 24px rgba(15, 23, 42, .06); overflow: hidden; }
        .comprobante header { background: linear-gradient(135deg, var(--dark), var(--dark-light)); color: #fff; padding: 1.75rem 2rem; }
        .comprobante .cuerpo { padding: 1.75rem 2rem; }
        .comprobante table td, .comprobante table th { padding: .65rem .25rem; }
        .dato small { display: block; color: #94a3b8; font-size: .75rem; text-transform: uppercase; font-weight: 700; letter-spacing: .03em; }
        .chip-estado { font-size: .75rem; font-weight: 700; padding: .3rem .75rem; border-radius: 99px; }
        .chip-estado.pagado { background: #ecfdf5; color: #047857; }
        .chip-estado.pendiente { background: #fffbeb; color: #b45309; }
        .chip-estado.anulado { background: #f1f5f9; color: #64748b; }
        @media print { .no-print { display: none !important; } body { background: #fff; } .comprobante { box-shadow: none; margin: 0; } }
    </style>
</head>

<body>
    @php
        $estado = ['completado' => ['pagado', 'Pagado'], 'pendiente' => ['pendiente', 'Por pagar en recepción'], 'anulado' => ['anulado', $venta->origen === 'web' ? 'Cancelado' : 'Anulado']][$venta->estado] ?? ['anulado', $venta->estado];
    @endphp

    <div class="container px-3">
        <div class="d-flex justify-content-between align-items-center mt-4 no-print" style="max-width:720px; margin:0 auto;">
            <a href="{{ route('mi-cuenta') }}#compras" class="btn btn-sm btn-outline-dark"><i class="bi bi-arrow-left"></i> Mis compras</a>
            <button type="button" class="btn btn-sm btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
        </div>

        <article class="comprobante">
            <header class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $config->logo_url }}" alt="" style="height:44px; width:auto; background:#fff; border-radius:10px; padding:4px;">
                    <div>
                        <div class="fw-bold fs-5">{{ $config->nombre_gym }}</div>
                        <div class="small text-white-50">{{ $venta->sucursal?->nombre }}{{ $venta->sucursal?->direccion ? ' · '.$venta->sucursal->direccion : '' }}</div>
                    </div>
                </div>
                <div class="text-end">
                    <div class="small text-white-50">Comprobante de compra</div>
                    <div class="fw-bold fs-4">{{ $venta->folio }}</div>
                </div>
            </header>

            <div class="cuerpo">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3 dato"><small>Fecha</small>{{ $venta->fecha->format('d/m/Y') }}</div>
                    <div class="col-6 col-md-3 dato"><small>Cliente</small>{{ $cliente->nombre }}</div>
                    <div class="col-6 col-md-3 dato"><small>Pago</small>{{ \App\Models\Venta::METODOS_PAGO[$venta->metodo_pago] ?? ucfirst((string) $venta->metodo_pago) }}</div>
                    <div class="col-6 col-md-3 dato"><small>Estado</small><span class="chip-estado {{ $estado[0] }}">{{ $estado[1] }}</span></div>
                </div>

                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="small text-muted"><th>Concepto</th><th class="text-center">Cant.</th><th class="text-end">Precio</th><th class="text-end">Importe</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($venta->detalles as $d)
                            <tr>
                                <td>{{ $d->descripcion }}</td>
                                <td class="text-center">{{ $d->cantidad }}</td>
                                <td class="text-end">{{ dinero($d->precio_unitario) }}</td>
                                <td class="text-end fw-semibold">{{ dinero($d->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-end fw-bold border-0 pt-3">Total</td><td class="text-end fw-bold fs-5 border-0 pt-3">{{ dinero($venta->total) }}</td></tr>
                    </tfoot>
                </table>

                @foreach ($venta->membresias as $mem)
                    <div class="alert alert-light border mt-3 mb-0 small">
                        <i class="bi bi-card-checklist text-primary"></i>
                        Membresía <strong>{{ $mem->plan?->nombre }}</strong>: del {{ $mem->fecha_inicio->format('d/m/Y') }} al {{ $mem->fecha_fin->format('d/m/Y') }}.
                    </div>
                @endforeach

                @if ($venta->estado === 'pendiente')
                    <div class="alert alert-warning mt-3 mb-0 small"><i class="bi bi-shop"></i> Paga y recoge tu pedido en recepción de {{ $venta->sucursal?->nombre ?? 'tu sede' }}. {{ $venta->detalles->whereNotNull('id_plan')->isNotEmpty() ? 'La membresía se activa al pagar.' : '' }}</div>
                @elseif ($venta->estado === 'anulado')
                    <div class="alert alert-secondary mt-3 mb-0 small">Esta compra fue {{ $venta->origen === 'web' ? 'cancelada' : 'anulada' }}{{ $venta->anulada_en ? ' el '.$venta->anulada_en->format('d/m/Y') : '' }}.</div>
                @endif

                <p class="small text-muted mt-4 mb-0">Documento interno de {{ $config->nombre_gym }}. No es un comprobante tributario.</p>
            </div>
        </article>
    </div>
</body>

</html>
