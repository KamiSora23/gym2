@if (Auth::user()->permite('cash.open'))
    @if ($sesion)
        @php $esperado = $sesion->saldoEsperado(); @endphp
        <div class="alert alert-success" style="justify-content:space-between; flex-wrap:wrap;">
            <span><i class="fas fa-lock-open"></i> Tu caja está abierta desde las {{ $sesion->abierta_en->format('H:i') }} · saldo esperado <strong>{{ dinero(array_sum($esperado)) }}</strong> (efectivo {{ dinero($esperado['efectivo']) }})</span>
            <a href="{{ route('caja.index') }}" class="btn btn-light btn-sm">Ver mi caja</a>
        </div>
    @else
        <div class="alert alert-info" style="justify-content:space-between; flex-wrap:wrap;">
            <span><i class="fas fa-cash-register"></i> Tu caja está cerrada. Ábrela para poder cobrar ventas y membresías.</span>
            <a href="{{ route('caja.index') }}" class="btn btn-primary btn-sm">Abrir caja</a>
        </div>
    @endif
@endif
