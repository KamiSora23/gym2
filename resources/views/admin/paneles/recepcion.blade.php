<x-admin-layout titulo="Recepción" icono="fa-bell-concierge" modulo="resumen"
    subtitulo="{{ now()->locale('es')->translatedFormat('l, j \d\e F') }} · {{ \App\Support\Tenant::sucursal()?->nombre ?? 'todas las sucursales' }}">

    <x-slot name="acciones">
        <a href="{{ route('clientes.index') }}" class="btn btn-light"><i class="fas fa-user-plus"></i> Nuevo cliente</a>
        <a href="{{ route('ventas.create') }}" class="btn btn-primary"><i class="fas fa-id-card"></i> Vender membresía</a>
    </x-slot>

    @php $enGym = $asistenciasHoy->whereNull('hora_salida'); @endphp

    @include('admin.partials.estado-caja')

    <div class="stats-grid">
        <div class="stat-card green"><div class="stat-title">En el gym ahora</div><div class="stat-value">{{ $enGym->count() }}</div><div class="stat-icon"><i class="fas fa-person-running"></i></div></div>
        <div class="stat-card cyan"><div class="stat-title">Check-ins hoy</div><div class="stat-value">{{ $asistenciasHoy->count() }}</div><div class="stat-icon"><i class="fas fa-right-to-bracket"></i></div></div>
        <div class="stat-card orange"><div class="stat-title">Vencen en 7 días</div><div class="stat-value">{{ $porVencer->count() }}</div><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div></div>
        <div class="stat-card purple"><div class="stat-title">Clientes nuevos hoy</div><div class="stat-value">{{ $nuevosHoy }}</div><div class="stat-icon"><i class="fas fa-user-plus"></i></div></div>
    </div>

    @if ($pedidosWeb->isNotEmpty())
        <div class="table-card">
            <div class="table-header"><h3><i class="fas fa-globe" style="color:var(--primary)"></i> Pedidos web por cobrar <span class="count">({{ $pedidosWeb->count() }})</span></h3></div>
            <div class="table-scroll">
                <table class="data-table">
                    <tbody>
                        @foreach ($pedidosWeb as $pedido)
                            <tr>
                                <td><a href="{{ route('ventas.show', $pedido) }}" style="font-weight:700; color:var(--primary);">{{ $pedido->folio }}</a><div class="muted">{{ $pedido->cliente?->nombre }} · {{ $pedido->created_at->format('d/m H:i') }}</div></td>
                                <td>{{ \App\Models\Venta::METODOS_PAGO[$pedido->metodo_pago] ?? $pedido->metodo_pago }}</td>
                                <td class="money-in">{{ dinero($pedido->total) }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('ventas.cobrar', $pedido) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Cobrar y entregar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('asistencias.store') }}" class="form-card">
        @csrf
        <h3><i class="fas fa-user-check"></i> Check-in rápido</h3>
        <div style="display:flex; gap:.75rem; flex-wrap:wrap; align-items:end;">
            <div class="form-group" style="flex:1; min-width:240px; margin:0;">
                <select name="id_cliente" required>
                    <option value="">Busca o elige un cliente…</option>
                    @foreach ($clientes as $c)
                        <option value="{{ $c->id_cliente }}">{{ $c->nombre }} — {{ $c->dni }}{{ $c->membresiaActiva ? '' : ' (sin membresía)' }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Registrar entrada</button>
        </div>
    </form>

    <div class="grid-2">
        <div class="table-card">
            <div class="table-header"><h3>En el gym ahora</h3><a href="{{ route('asistencias.index') }}" class="btn btn-light btn-sm">Ver todo</a></div>
            <div class="table-scroll">
                <table class="data-table">
                    <tbody>
                        @forelse ($enGym as $a)
                            <tr>
                                <td>{{ $a->cliente?->nombre }} @unless ($a->cliente?->membresiaActiva)<span class="status-badge status-cancelled">Sin membresía</span>@endunless<div class="muted">Entró {{ substr($a->hora_entrada, 0, 5) }}</div></td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('asistencias.salida', $a) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-light btn-sm"><i class="fas fa-right-from-bracket"></i> Salida</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="empty-state"><i class="fas fa-door-open"></i> Nadie en el gym por ahora.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="panel-card" style="margin-bottom:1.5rem;">
                <h4>Clases de hoy</h4>
                <ul class="tx-list">
                    @forelse ($clasesHoy as $clase)
                        <li>
                            <span>{{ $clase->nombre }}<small>{{ $clase->horario }} · {{ $clase->entrenador?->nombre ?? 'Sin coach' }}</small></span>
                            <span class="status-badge {{ $clase->clientes_count >= $clase->cupo ? 'status-cancelled' : 'status-active' }}">{{ $clase->clientes_count }}/{{ $clase->cupo }}</span>
                        </li>
                    @empty
                        <li class="muted">No hay clases hoy.</li>
                    @endforelse
                </ul>
            </div>

            <div class="panel-card">
                <h4>Membresías por vencer</h4>
                <ul class="tx-list">
                    @forelse ($porVencer as $m)
                        <li>
                            <span>{{ $m->cliente?->nombre }}<small>{{ $m->plan?->nombre }} · vence {{ $m->fecha_fin->locale('es')->diffForHumans() }}</small></span>
                            <a href="{{ route('ventas.create', ['cliente' => $m->id_cliente]) }}" class="btn btn-light btn-sm">Renovar</a>
                        </li>
                    @empty
                        <li class="muted">Ninguna vence esta semana.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-admin-layout>
