<x-admin-layout titulo="Mi panel de coach" icono="fa-dumbbell" modulo="resumen"
    subtitulo="Hola, {{ strtok(Auth::user()->name, ' ') }}. Tus clases y alumnos de {{ \App\Support\Tenant::empresa()?->nombre }}.">

    <x-slot name="acciones">
        @if ($perfil)
            <a href="{{ route('entrenadores.disponibilidad', $perfil) }}" class="btn btn-light"><i class="fas fa-user-clock"></i> Mi disponibilidad</a>
        @endif
        <a href="{{ route('entrenamiento.index') }}" class="btn btn-primary"><i class="fas fa-calendar-plus"></i> Mis clases</a>
    </x-slot>

    @if (! $perfil)
        <div class="alert alert-info">
            <i class="fas fa-circle-info"></i>
            Tu usuario aún no está vinculado a un perfil de coach. Pide al administrador que te vincule en <strong>Entrenamiento → Coaches</strong>.
        </div>
    @else
        <div class="stats-grid">
            <div class="stat-card cyan"><div class="stat-title">Clases hoy</div><div class="stat-value">{{ $clasesHoy->count() }}</div><div class="stat-icon"><i class="fas fa-calendar-day"></i></div></div>
            <div class="stat-card purple"><div class="stat-title">Clases por semana</div><div class="stat-value">{{ $clases->count() }}</div><div class="stat-icon"><i class="fas fa-calendar-week"></i></div></div>
            <div class="stat-card green"><div class="stat-title">Mis alumnos</div><div class="stat-value">{{ $alumnos->count() }}</div><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-hint">{{ $personales }} con coaching personal</div></div>
            <div class="stat-card orange"><div class="stat-title">Vinieron hoy</div><div class="stat-value">{{ $asistieronHoy->count() }}</div><div class="stat-icon"><i class="fas fa-person-running"></i></div></div>
        </div>

        <h3 style="color:var(--text-dark); margin:0 0 1rem; font-size:1.05rem; font-weight:700;">Mis clases de hoy</h3>
        <div class="catalog-grid" style="margin-bottom:1.5rem;">
            @forelse ($clasesHoy as $clase)
                <div class="campaign-card class-card">
                    <div class="campaign-header">
                        <div><h3 class="campaign-title">{{ $clase->nombre }}</h3><p>{{ $clase->horario }}</p></div>
                        <span class="status-badge {{ $clase->clientes->count() >= $clase->cupo ? 'status-cancelled' : 'status-active' }}">{{ $clase->clientes->count() }}/{{ $clase->cupo }}</span>
                    </div>
                    <div style="display:flex; flex-wrap:wrap; gap:.35rem;">
                        @forelse ($clase->clientes as $inscrito)
                            <span class="chip">{{ $inscrito->nombre }}</span>
                        @empty
                            <span class="muted">Sin inscritos.</span>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="panel-card" style="grid-column:1/-1;"><p class="muted" style="margin:0;">Hoy no tienes clases programadas.</p></div>
            @endforelse
        </div>

        <div class="grid-2">
            <div class="table-card">
                <div class="table-header"><h3>Mis alumnos</h3></div>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>Alumno</th><th>Tipo</th><th>Membresía</th></tr></thead>
                        <tbody>
                            @forelse ($alumnos as $al)
                                <tr>
                                    <td><a href="{{ route('clientes.show', $al) }}" style="font-weight:600; color:var(--text-dark); text-decoration:none;">{{ $al->nombre }}</a><div class="muted">{{ $al->telefono ?? '—' }} · <a href="{{ route('clientes.show', $al) }}">rutina y progreso</a></div></td>
                                    <td><span class="chip">{{ $al->id_entrenador === $perfil->id_entrenador ? 'Personal' : 'Clase grupal' }}</span></td>
                                    <td>
                                        @if ($al->membresiaActiva)
                                            <span class="status-badge status-active">Vigente</span><div class="muted">hasta {{ $al->membresiaActiva->fecha_fin->format('d/m/Y') }}</div>
                                        @else
                                            <span class="status-badge status-cancelled">Vencida</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="empty-state"><i class="fas fa-users"></i> Aún no tienes alumnos asignados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel-card">
                <h4>Alumnos que vinieron hoy</h4>
                <ul class="tx-list">
                    @forelse ($asistieronHoy as $a)
                        <li><span>{{ $a->cliente?->nombre }}<small>Entrada {{ substr($a->hora_entrada, 0, 5) }}{{ $a->hora_salida ? ' · salida '.substr($a->hora_salida, 0, 5) : '' }}</small></span>
                            <span class="status-badge {{ $a->hora_salida ? 'status-info' : 'status-active' }}">{{ $a->hora_salida ? 'Se fue' : 'En el gym' }}</span></li>
                    @empty
                        <li class="muted">Ninguno de tus alumnos ha venido hoy.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @endif
</x-admin-layout>
