<x-admin-layout titulo="Asistencias" icono="fa-calendar-check" modulo="asistencias"
    subtitulo="Check-in y salida de clientes · {{ $fecha->locale('es')->translatedFormat('l j \d\e F') }}">

    <x-slot name="acciones">
        <form method="GET" style="display:flex; gap:.5rem; align-items:center;">
            <input type="date" name="fecha" value="{{ $fecha->toDateString() }}" max="{{ today()->toDateString() }}" onchange="this.form.submit()"
                style="padding:.55rem .7rem; border:1px solid #cbd5e1; border-radius:12px;">
        </form>
    </x-slot>

    <div class="stats-grid">
        <div class="stat-card cyan"><div class="stat-title">Check-ins</div><div class="stat-value">{{ $asistencias->count() }}</div><div class="stat-icon"><i class="fas fa-right-to-bracket"></i></div></div>
        <div class="stat-card green"><div class="stat-title">En el gym</div><div class="stat-value">{{ $enGym }}</div><div class="stat-icon"><i class="fas fa-person-running"></i></div></div>
        <div class="stat-card purple"><div class="stat-title">Finalizadas</div><div class="stat-value">{{ $asistencias->count() - $enGym }}</div><div class="stat-icon"><i class="fas fa-right-from-bracket"></i></div></div>
        <div class="stat-card orange"><div class="stat-title">Sin membresía</div><div class="stat-value">{{ $asistencias->filter(fn ($a) => ! $a->cliente?->membresiaActiva)->count() }}</div><div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div></div>
    </div>

    @if ($fecha->isToday())
        <form method="POST" action="{{ route('asistencias.store') }}" class="form-card">
            @csrf
            <h3><i class="fas fa-user-check"></i> Registrar check-in</h3>
            @if ($clientes->isEmpty())
                <p class="muted" style="margin:0;">Aún no hay clientes. Regístralos en <a href="{{ route('clientes.index') }}">Clientes</a>.</p>
            @else
                <div style="display:flex; gap:.75rem; flex-wrap:wrap; align-items:end;">
                    <div class="form-group" style="flex:1; min-width:240px; margin:0;">
                        <label>Cliente</label>
                        <select name="id_cliente" required>
                            <option value="">Selecciona un cliente…</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id_cliente }}">{{ $c->nombre }} — {{ $c->dni }}{{ $c->membresiaActiva ? '' : ' (sin membresía)' }}</option>
                            @endforeach
                        </select>
                    </div>
                    @unless (\App\Support\Tenant::sucursalId())
                        <div class="form-group" style="margin:0;">
                            <label>Sucursal</label>
                            <select name="id_sucursal">
                                @foreach ($sucursales as $s)<option value="{{ $s->id_sucursal }}">{{ $s->nombre }}</option>@endforeach
                            </select>
                        </div>
                    @endunless
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Registrar entrada</button>
                </div>
                @include('admin.partials.errores', ['bag' => 'asistencia'])
            @endif
        </form>
    @endif

    <div class="table-card">
        <div class="table-header">
            <h3>Registro del día</h3>
            <div class="search-box"><i class="fas fa-search"></i><input type="search" placeholder="Buscar cliente…" data-filter-table="tabla-asistencias"></div>
        </div>
        <div class="table-scroll">
            <table class="data-table" id="tabla-asistencias">
                <thead><tr><th>Cliente</th><th>Sucursal</th><th>Entrada</th><th>Salida</th><th>Membresía</th><th class="text-right">Acción</th></tr></thead>
                <tbody>
                    @forelse ($asistencias as $a)
                        <tr>
                            <td>{{ $a->cliente?->nombre }}</td>
                            <td>{{ $a->sucursal?->nombre ?? '—' }}</td>
                            <td>{{ substr($a->hora_entrada, 0, 5) }}</td>
                            <td>{{ $a->hora_salida ? substr($a->hora_salida, 0, 5) : '—' }}</td>
                            <td>
                                @if ($a->cliente?->membresiaActiva)
                                    <span class="status-badge status-active">Vigente</span>
                                @else
                                    <span class="status-badge status-cancelled">Sin membresía</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if (! $a->hora_salida)
                                    <form method="POST" action="{{ route('asistencias.salida', $a) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-light btn-sm"><i class="fas fa-right-from-bracket"></i> Salida</button>
                                    </form>
                                @else
                                    <span class="status-badge status-info">Finalizada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state"><i class="fas fa-calendar-xmark"></i> No hay asistencias registradas este día.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
