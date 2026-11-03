<x-admin-layout titulo="Venta {{ $venta->folio }}" icono="fa-receipt" modulo="ventas"
    subtitulo="{{ $venta->fecha->format('d/m/Y') }} · {{ $venta->sucursal?->nombre }} · atendido por {{ $venta->vendedor?->name ?? '—' }}">

    <x-slot name="acciones">
        <a href="{{ route('ventas.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Ventas</a>
        <button type="button" class="btn btn-light" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
        <a href="{{ route('ventas.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva venta</a>
    </x-slot>

    @php
        $estados = ['completado' => ['status-active', 'Pagada'], 'pendiente' => ['status-pending', 'Pendiente de cobro'], 'anulado' => ['status-cancelled', 'Anulada']];
    @endphp

    <div class="pos-grid">
        <div class="table-card">
            <div class="table-header">
                <h3>Detalle</h3>
                <span class="status-badge {{ $estados[$venta->estado][0] }}">{{ $estados[$venta->estado][1] }}</span>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Concepto</th><th>Cant.</th><th>Precio</th><th class="text-right">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($venta->detalles as $d)
                            <tr>
                                <td>@if ($d->id_plan)<i class="fas fa-id-card" style="color:var(--primary)"></i>@else<i class="fas fa-box" style="color:#94a3b8"></i>@endif {{ $d->descripcion }}</td>
                                <td>{{ $d->cantidad }}</td>
                                <td>{{ dinero($d->precio_unitario) }}</td>
                                <td class="text-right">{{ dinero($d->subtotal) }}</td>
                            </tr>
                        @endforeach
                        <tr><td colspan="3" class="text-right"><strong>TOTAL</strong></td><td class="text-right"><strong style="font-size:1.2rem;">{{ dinero($venta->total) }}</strong></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="form-card">
                <h3><i class="fas fa-user"></i> Cliente</h3>
                @if ($venta->cliente)
                    <p style="margin:0; font-weight:700;">{{ $venta->cliente->nombre }}</p>
                    <p class="muted" style="margin:.2rem 0 0;">DNI {{ $venta->cliente->dni }} · {{ $venta->cliente->telefono ?? $venta->cliente->correo }}</p>
                @else
                    <p class="muted" style="margin:0;">Venta de mostrador</p>
                @endif
                @if ($venta->estado === 'anulado' && $venta->anulada_en)
                    <div class="alert alert-error" style="margin:.75rem 0 0;"><i class="fas fa-ban"></i> Anulada por {{ $venta->anuladaPor?->name ?? '—' }} el {{ $venta->anulada_en->format('d/m/Y H:i') }}: {{ $venta->motivo_anulacion }}</div>
                @endif
                @if ($venta->origen === 'web')
                    <div class="alert alert-info" style="margin:.75rem 0 0;"><i class="fas fa-globe"></i> Pedido del sitio web: el cliente paga y recoge en recepción.{{ $venta->estado === 'pendiente' ? ' La membresía se activa al cobrar.' : '' }}</div>
                @endif
                <div class="summary-row" style="margin-top:.75rem;"><span>Método de pago</span><strong>{{ \App\Models\Venta::METODOS_PAGO[$venta->metodo_pago] ?? $venta->metodo_pago }}</strong></div>
                @foreach ($venta->membresias as $m)
                    <div class="summary-row"><span>Membresía {{ $m->plan?->nombre }}</span><strong>{{ $m->fecha_inicio->format('d/m/Y') }} → {{ $m->fecha_fin->format('d/m/Y') }}</strong></div>
                @endforeach
            </div>

            @if ($venta->estado !== 'anulado')
                <div class="form-card" style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    @if ($venta->estado === 'pendiente')
                        <form method="POST" action="{{ route('ventas.cobrar', $venta) }}" style="flex:1;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-primary" style="width:100%;"><i class="fas fa-check"></i> Marcar como pagada</button>
                        </form>
                    @endif
                    @if (Auth::user()->permite('sales.cancel'))
                        <form method="POST" action="{{ route('ventas.anular', $venta) }}" style="flex:1;" data-motivo="Motivo de la anulación de la venta {{ $venta->folio }} (se devolverá el stock, el dinero saldrá de tu caja y se cancelará la membresía):">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-danger" style="width:100%;"><i class="fas fa-ban"></i> Anular venta</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
