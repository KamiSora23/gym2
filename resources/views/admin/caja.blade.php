<x-admin-layout :titulo="$veTodas ? 'Cajas' : 'Mi caja'" icono="fa-cash-register" modulo="caja"
    subtitulo="Cada cobro y devolución queda registrado en la sesión de caja de quien lo realiza.">

    @if (Auth::user()->permite('cash.manage'))
        <x-slot name="acciones">
            <a href="{{ route('cajas.index') }}" class="btn btn-light"><i class="fas fa-sliders"></i> Gestionar cajas</a>
        </x-slot>
    @endif

    @php
        $metodos = \App\Models\Venta::METODOS_PAGO;
    @endphp

    @if ($puedeOperar && ! $sesion)
        <div class="grid-2" style="margin-bottom:1.5rem;">
            <form method="POST" action="{{ route('caja.abrir') }}" class="form-card" style="margin:0;">
                @csrf
                <h3><i class="fas fa-lock-open"></i> Abrir mi caja</h3>
                <p class="muted" style="margin:-.5rem 0 1rem;">Registra el fondo inicial en efectivo con el que empiezas tu turno.</p>
                @if ($cajas->isEmpty())
                    <div class="alert alert-error" style="margin:0;"><i class="fas fa-circle-exclamation"></i> No tienes ninguna caja asignada. Pide al administrador que te asigne una.</div>
                @else
                <div class="form-grid">
                    <div class="form-group">
                        <label>Caja</label>
                        <select name="id_caja" required>
                            @foreach ($cajas as $caja)
                                @php $mia = $caja->responsables->contains('id', Auth::id()); @endphp
                                <option value="{{ $caja->id_caja }}" @disabled($caja->sesionAbierta) @selected($sugerida?->is($caja))>
                                    {{ $caja->nombre }} · {{ $caja->sucursal?->nombre }}{{ $mia ? ' · asignada a ti' : '' }}{{ $caja->sesionAbierta ? ' (en uso por '.$caja->sesionAbierta->usuario?->name.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @unless ($sugerida)
                            <span class="field-error">Todas tus cajas están en uso en este momento.</span>
                        @endunless
                    </div>
                    <div class="form-group">
                        <label>Fondo inicial (efectivo)</label>
                        <input type="number" step="0.01" min="0" name="monto_inicial" value="{{ old('monto_inicial', 100) }}" required>
                    </div>
                </div>
                @include('admin.partials.errores', ['bag' => 'caja'])
                <div class="form-actions"><button type="submit" class="btn btn-primary" @disabled(! $sugerida)><i class="fas fa-lock-open"></i> Abrir caja</button></div>
                @endif
            </form>

            <div class="panel-card">
                <h4><i class="fas fa-circle-info" style="color:var(--primary)"></i> Cómo funciona</h4>
                <ul class="tx-list">
                    <li><span>1. Abres tu caja con el fondo inicial.</span></li>
                    <li><span>2. Las ventas y cobros entran solos a tu caja.</span></li>
                    <li><span>3. Al terminar cuentas el dinero por método de pago.</span></li>
                    <li><span>4. Si hay diferencia, un supervisor la revisa y aprueba.</span></li>
                </ul>
            </div>
        </div>
    @endif

    @if ($sesion)
        @php $totalEsperado = array_sum($esperado); @endphp
        <div class="stats-grid">
            <div class="stat-card green"><div class="stat-title">Caja abierta</div><div class="stat-value" style="font-size:1.15rem;">{{ $sesion->caja?->nombre }}</div><div class="stat-icon"><i class="fas fa-lock-open"></i></div><div class="stat-hint">Desde {{ $sesion->abierta_en->format('H:i') }} · fondo {{ dinero($sesion->monto_inicial) }}</div></div>
            <div class="stat-card cyan"><div class="stat-title">Ingresos</div><div class="stat-value">{{ dinero($sesion->movimientos->where('tipo', 'ingreso')->sum('monto')) }}</div><div class="stat-icon"><i class="fas fa-arrow-down"></i></div></div>
            <div class="stat-card red"><div class="stat-title">Egresos</div><div class="stat-value">{{ dinero($sesion->movimientos->where('tipo', 'egreso')->sum('monto')) }}</div><div class="stat-icon"><i class="fas fa-arrow-up"></i></div></div>
            <div class="stat-card purple"><div class="stat-title">Saldo esperado</div><div class="stat-value">{{ dinero($totalEsperado) }}</div><div class="stat-icon"><i class="fas fa-scale-balanced"></i></div><div class="stat-hint">Efectivo: {{ dinero($esperado['efectivo']) }}</div></div>
        </div>

        <div class="grid-2">
            <div>
                <div class="table-card">
                    <div class="table-header"><h3>Movimientos del turno</h3><span class="count">{{ $sesion->movimientos->count() }}</span></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>Hora</th><th>Concepto</th><th>Método</th><th class="text-right">Monto</th></tr></thead>
                            <tbody>
                                @forelse ($sesion->movimientos->sortByDesc('id_movimiento') as $m)
                                    <tr>
                                        <td>{{ $m->created_at->format('H:i') }}</td>
                                        <td style="white-space:normal;">{{ $m->concepto }} <span class="chip">{{ $m->origen }}</span></td>
                                        <td>{{ $metodos[$m->metodo_pago] ?? $m->metodo_pago }}</td>
                                        <td class="text-right {{ $m->tipo === 'ingreso' ? 'money-in' : 'money-out' }}">{{ $m->tipo === 'ingreso' ? '+' : '-' }}{{ dinero($m->monto) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="empty-state"><i class="fas fa-receipt"></i> Aún no hay movimientos en este turno.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($puedeMovimientos)
                    <form method="POST" action="{{ route('caja.movimiento') }}" class="form-card">
                        @csrf
                        <h3><i class="fas fa-right-left"></i> Ingreso o egreso manual</h3>
                        <div class="form-grid cols-4">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select name="tipo"><option value="ingreso">Ingreso</option><option value="egreso">Egreso</option></select>
                            </div>
                            <div class="form-group"><label>Monto</label><input type="number" step="0.01" min="0.01" name="monto" required></div>
                            <div class="form-group">
                                <label>Método</label>
                                <select name="metodo_pago">@foreach ($metodos as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                            </div>
                            <div class="form-group"><label>Concepto</label><input type="text" name="concepto" placeholder="Compra de útiles de limpieza" required></div>
                        </div>
                        @include('admin.partials.errores', ['bag' => 'movimiento'])
                        <div class="form-actions"><button type="submit" class="btn btn-light"><i class="fas fa-plus"></i> Registrar</button></div>
                    </form>
                @endif
            </div>

            <form method="POST" action="{{ route('caja.cerrar') }}" class="form-card" data-confirm="¿Cerrar la caja? Después no podrás modificar el arqueo.">
                @csrf
                <h3><i class="fas fa-calculator"></i> Arqueo y cierre</h3>
                <p class="muted" style="margin:-.5rem 0 1rem;">Cuenta lo que tienes realmente por cada método de pago. El sistema compara con lo esperado.</p>
                <table class="data-table" style="margin-bottom:1rem;">
                    <thead><tr><th>Método</th><th class="text-right">Esperado</th><th style="width:150px;">Contado</th></tr></thead>
                    <tbody>
                        @foreach ($metodos as $clave => $nombre)
                            <tr>
                                <td>{{ $nombre }}</td>
                                <td class="text-right">{{ dinero($esperado[$clave] ?? 0) }}</td>
                                <td><input type="number" step="0.01" min="0" name="contado[{{ $clave }}]" value="{{ old('contado.'.$clave, number_format($esperado[$clave] ?? 0, 2, '.', '')) }}" required style="width:100%; padding:.45rem; border:1px solid #cbd5e1; border-radius:9px;"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="form-group"><label>Observaciones</label><input type="text" name="observaciones" placeholder="Opcional"></div>
                @include('admin.partials.errores', ['bag' => 'cierre'])
                <div class="form-actions"><button type="submit" class="btn btn-primary"><i class="fas fa-lock"></i> Cerrar caja</button></div>
            </form>
        </div>
    @endif

    @if ($pendientes->isNotEmpty())
        <div class="table-card">
            <div class="table-header"><h3><i class="fas fa-triangle-exclamation" style="color:#b45309"></i> Diferencias por aprobar</h3></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Empleado</th><th>Caja</th><th>Cierre</th><th class="text-right">Diferencia</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($pendientes as $p)
                            <tr>
                                <td>{{ $p->usuario?->name }}</td>
                                <td>{{ $p->caja?->nombre }}</td>
                                <td>{{ $p->cerrada_en?->format('d/m/Y H:i') }}</td>
                                <td class="text-right money-out">{{ dinero($p->diferencia) }}</td>
                                <td class="text-right"><a href="{{ route('caja.sesion', $p) }}" class="btn btn-light btn-sm">Revisar</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($veTodas)
        <div class="table-card">
            <div class="table-header"><h3>Estado de las cajas ahora</h3><span class="muted">{{ $todasLasCajas->filter->sesionAbierta->count() }} de {{ $todasLasCajas->count() }} abiertas</span></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Caja</th><th>Sucursal</th><th>Responsables</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($todasLasCajas as $c)
                            <tr>
                                <td><strong>{{ $c->nombre }}</strong></td>
                                <td>{{ $c->sucursal?->nombre }}</td>
                                <td style="white-space:normal;">{{ $c->responsables->pluck('name')->implode(', ') ?: 'Cualquiera de la sucursal' }}</td>
                                <td>
                                    @if ($c->sesionAbierta)
                                        <span class="status-badge status-active"><i class="fas fa-lock-open"></i> {{ $c->sesionAbierta->usuario?->name }} · desde {{ $c->sesionAbierta->abierta_en->format('H:i') }}</span>
                                    @else
                                        <span class="status-badge status-info">Cerrada</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($c->sesionAbierta && ! $c->sesionAbierta->usuario?->is(Auth::user()))
                                        <a href="{{ route('caja.sesion', $c->sesionAbierta) }}" class="btn btn-light btn-sm">Ver turno</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="table-card">
        <div class="table-header"><h3>{{ $veTodas ? 'Historial de cierres' : 'Mis cierres anteriores' }}</h3></div>
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr><th>Fecha</th>@if ($veTodas)<th>Empleado</th>@endif<th>Caja</th><th class="text-right">Esperado</th><th class="text-right">Contado</th><th class="text-right">Diferencia</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                    @forelse ($historial as $h)
                        @php [$texto, $clase] = \App\Models\CajaSesion::ESTADOS[$h->estado] ?? [$h->estado, 'status-info']; @endphp
                        <tr>
                            <td>{{ $h->cerrada_en?->format('d/m/Y H:i') }}</td>
                            @if ($veTodas)<td>{{ $h->usuario?->name }}</td>@endif
                            <td>{{ $h->caja?->nombre }}</td>
                            <td class="text-right">{{ dinero(array_sum($h->esperado ?? [])) }}</td>
                            <td class="text-right">{{ dinero(array_sum($h->contado ?? [])) }}</td>
                            <td class="text-right {{ (float) $h->diferencia < 0 ? 'money-out' : '' }}">{{ dinero($h->diferencia) }}</td>
                            <td><span class="status-badge {{ $clase }}">{{ $texto }}</span></td>
                            <td class="text-right"><a href="{{ route('caja.sesion', $h) }}" class="btn btn-light btn-sm btn-icon"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty-state"><i class="fas fa-cash-register"></i> Aún no hay cierres de caja.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @include('admin.partials.paginacion', ['items' => $historial])

</x-admin-layout>
