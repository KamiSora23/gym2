<x-admin-layout titulo="Sesión de caja #{{ $sesion->id_sesion }}" icono="fa-receipt" modulo="caja"
    subtitulo="{{ $sesion->caja?->nombre }} · {{ $sesion->usuario?->name }} · {{ $sesion->abierta_en->format('d/m/Y H:i') }}{{ $sesion->cerrada_en ? ' → '.$sesion->cerrada_en->format('H:i') : '' }}">

    <x-slot name="acciones">
        <a href="{{ route('caja.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Caja</a>
        <button type="button" class="btn btn-light" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
    </x-slot>

    @php
        $metodos = \App\Models\Venta::METODOS_PAGO;
        [$texto, $clase] = \App\Models\CajaSesion::ESTADOS[$sesion->estado] ?? [$sesion->estado, 'status-info'];
    @endphp

    <div class="pos-grid">
        <div class="table-card">
            <div class="table-header"><h3>Movimientos</h3><span class="status-badge {{ $clase }}">{{ $texto }}</span></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Hora</th><th>Concepto</th><th>Por</th><th>Método</th><th class="text-right">Monto</th></tr></thead>
                    <tbody>
                        <tr><td>{{ $sesion->abierta_en->format('H:i') }}</td><td>Fondo inicial</td><td>{{ $sesion->usuario?->name }}</td><td>Efectivo</td><td class="text-right">{{ dinero($sesion->monto_inicial) }}</td></tr>
                        @foreach ($sesion->movimientos as $m)
                            <tr>
                                <td>{{ $m->created_at->format('H:i') }}</td>
                                <td style="white-space:normal;">{{ $m->concepto }} <span class="chip">{{ $m->origen }}</span></td>
                                <td>{{ $m->usuario?->name }}</td>
                                <td>{{ $metodos[$m->metodo_pago] ?? $m->metodo_pago }}</td>
                                <td class="text-right {{ $m->tipo === 'ingreso' ? 'money-in' : 'money-out' }}">{{ $m->tipo === 'ingreso' ? '+' : '-' }}{{ dinero($m->monto) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="form-card">
                <h3><i class="fas fa-calculator"></i> Arqueo</h3>
                <table class="data-table">
                    <thead><tr><th>Método</th><th class="text-right">Esperado</th><th class="text-right">Contado</th><th class="text-right">Dif.</th></tr></thead>
                    <tbody>
                        @foreach ($metodos as $k => $v)
                            @php
                                $esp = (float) ($esperadoActual[$k] ?? 0);
                                $con = $sesion->contado ? (float) ($sesion->contado[$k] ?? 0) : null;
                            @endphp
                            <tr>
                                <td>{{ $v }}</td>
                                <td class="text-right">{{ dinero($esp) }}</td>
                                <td class="text-right">{{ $con === null ? '—' : dinero($con) }}</td>
                                <td class="text-right {{ $con !== null && abs($con - $esp) >= 0.005 ? 'money-out' : '' }}">{{ $con === null ? '—' : dinero($con - $esp) }}</td>
                            </tr>
                        @endforeach
                        <tr><td><strong>Total</strong></td><td class="text-right"><strong>{{ dinero(array_sum($esperadoActual ?? [])) }}</strong></td><td class="text-right"><strong>{{ $sesion->contado ? dinero(array_sum($sesion->contado)) : '—' }}</strong></td><td class="text-right"><strong>{{ $sesion->diferencia !== null ? dinero($sesion->diferencia) : '—' }}</strong></td></tr>
                    </tbody>
                </table>
                @if ($sesion->observaciones)<p class="muted" style="margin:.75rem 0 0;"><strong>Observaciones:</strong> {{ $sesion->observaciones }}</p>@endif
                @if ($sesion->revisado_por)
                    <p class="muted" style="margin:.75rem 0 0;"><i class="fas fa-user-check"></i> Revisado por {{ $sesion->revisor?->name }} el {{ $sesion->revisado_en?->format('d/m/Y H:i') }}: {{ $sesion->nota_revision }}</p>
                @endif
            </div>

            @if ($puedeAprobar)
                <form method="POST" action="{{ route('caja.aprobar', $sesion) }}" class="form-card">
                    @csrf
                    @method('PATCH')
                    <h3><i class="fas fa-user-check"></i> Aprobar diferencia</h3>
                    <div class="form-group"><label>Nota de revisión *</label><input type="text" name="nota_revision" placeholder="Se verificó el faltante con el empleado…" required></div>
                    @include('admin.partials.errores')
                    <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Aprobar</button></div>
                </form>
            @endif
        </div>
    </div>
</x-admin-layout>
