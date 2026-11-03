{{-- Tras un check-in rechazado: renovar o dejar pasar con un pase registrado. --}}
@if ($sin = session('sin_membresia'))
    <div class="form-card" style="border:1px solid #fecaca; background:#fff7f7;">
        <h3 style="color:#b91c1c;"><i class="fas fa-ban"></i> {{ $sin['nombre'] }} no puede entrar</h3>
        <div style="display:flex; flex-wrap:wrap; gap:.75rem; align-items:flex-end;">
            @if (\App\Support\Permisos::puede(Auth::user(), 'sales.create'))
                <a href="{{ route('ventas.create', ['cliente' => $sin['id']]) }}" class="btn btn-primary"><i class="fas fa-rotate"></i> Renovar membresía</a>
            @endif
            <form method="POST" action="{{ route('asistencias.store') }}" style="display:flex; flex-wrap:wrap; gap:.5rem; align-items:flex-end; flex:1;">
                @csrf
                <input type="hidden" name="id_cliente" value="{{ $sin['id'] }}">
                <input type="hidden" name="cortesia" value="1">
                <div class="form-group" style="margin:0; flex:1; min-width:220px;">
                    <label>Dejar pasar con un pase (queda registrado)</label>
                    <input type="text" name="motivo" placeholder="Pase diario pagado, clase de prueba, cortesía…" required maxlength="150">
                </div>
                <button type="submit" class="btn btn-light"><i class="fas fa-door-open"></i> Registrar pase</button>
            </form>
        </div>
    </div>
@endif
