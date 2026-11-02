<x-admin-layout titulo="Entrenamiento" icono="fa-dumbbell" modulo="entrenamiento"
    subtitulo="Coaches, horario semanal de clases e inscripciones de clientes.">

    <x-slot name="acciones">
        @if ($miPerfil)
            <a href="{{ route('entrenamiento.index', $soloMias ? ['todas' => 1] : []) }}" class="btn btn-light">
                <i class="fas fa-filter"></i> {{ $soloMias ? 'Ver todas las clases' : 'Solo mis clases' }}
            </a>
        @endif
        @if ($puedeGestionar)
            <button type="button" class="btn btn-light" data-toggle-target="form-entrenador"><i class="fas fa-user-tie"></i> Nuevo coach</button>
            <button type="button" class="btn btn-primary" data-toggle-target="form-clase" @disabled($entrenadores->where('activo', true)->isEmpty())><i class="fas fa-plus"></i> Nueva clase</button>
        @endif
    </x-slot>

    @php
        $sucursalFija = \App\Support\Tenant::sucursalId();
        $creandoClase = $errors->clase->any() && ! old('_id');
        $creandoCoach = $errors->entrenador->any() && ! old('_id');
        $activos = $entrenadores->where('activo', true);
    @endphp

    <div class="stats-grid">
        <div class="stat-card cyan"><div class="stat-title">Clases semanales</div><div class="stat-value">{{ $clases->count() }}</div><div class="stat-icon"><i class="fas fa-calendar-alt"></i></div></div>
        <div class="stat-card green"><div class="stat-title">Coaches activos</div><div class="stat-value">{{ $activos->count() }}</div><div class="stat-icon"><i class="fas fa-user-tie"></i></div></div>
        <div class="stat-card purple"><div class="stat-title">Inscripciones</div><div class="stat-value">{{ $clases->sum(fn ($c) => $c->clientes->count()) }}</div><div class="stat-icon"><i class="fas fa-users"></i></div></div>
        <div class="stat-card orange">
            <div class="stat-title">Ocupación</div>
            <div class="stat-value">{{ $clases->sum('cupo') ? round($clases->sum(fn ($c) => $c->clientes->count()) * 100 / $clases->sum('cupo')) : 0 }}%</div>
            <div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
        </div>
    </div>

    @if ($puedeGestionar)
        <form id="form-entrenador" method="POST" action="{{ route('entrenadores.store') }}" class="form-card {{ $creandoCoach ? '' : 'hidden' }}">
            @csrf
            <h3><i class="fas fa-user-tie"></i> Nuevo coach</h3>
            <div class="form-grid cols-4">
                <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" value="{{ $creandoCoach ? old('nombre') : '' }}" required></div>
                <div class="form-group"><label>Especialidad</label><input type="text" name="especialidad" value="{{ $creandoCoach ? old('especialidad') : '' }}" placeholder="Crossfit, Yoga…"></div>
                <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" value="{{ $creandoCoach ? old('telefono') : '' }}"></div>
                <div class="form-group"><label>Correo</label><input type="email" name="email" value="{{ $creandoCoach ? old('email') : '' }}"></div>
                <div class="form-group">
                    <label>Sucursal</label>
                    <select name="id_sucursal">
                        <option value="">Todas</option>
                        @foreach ($sucursales as $s)<option value="{{ $s->id_sucursal }}" @selected($creandoCoach && old('id_sucursal') == $s->id_sucursal)>{{ $s->nombre }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Usuario de acceso <span class="muted">(rol entrenador)</span></label>
                    <select name="user_id">
                        <option value="">Sin acceso al sistema</option>
                        @foreach ($usuariosEntrenador as $u)<option value="{{ $u->id }}" @selected($creandoCoach && old('user_id') == $u->id)>{{ $u->name }} ({{ $u->email }})</option>@endforeach
                    </select>
                </div>
            </div>
            @if ($creandoCoach)
                @include('admin.partials.errores', ['bag' => 'entrenador'])
            @endif
            <div class="form-actions">
                <button type="button" class="btn btn-light" data-toggle-target="form-entrenador">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Registrar coach</button>
            </div>
        </form>

        <form id="form-clase" method="POST" action="{{ route('clases.store') }}" class="form-card {{ $creandoClase ? '' : 'hidden' }}">
            @csrf
            <h3><i class="fas fa-calendar-plus"></i> Nueva clase</h3>
            @include('admin.partials.campos-clase', ['clase' => null, 'editando' => $creandoClase])
            @if ($creandoClase)
                @include('admin.partials.errores', ['bag' => 'clase'])
            @endif
            <div class="form-actions">
                <button type="button" class="btn btn-light" data-toggle-target="form-clase">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Agregar clase</button>
            </div>
        </form>
    @endif

    <h3 style="color:var(--text-dark); margin:0 0 1rem; font-size:1.05rem; font-weight:700;">
        Horario semanal @if ($soloMias) <span class="chip">Mis clases</span> @endif
    </h3>
    <div class="schedule-grid">
        @foreach (\App\Models\Clase::DIAS as $num => $dia)
            <div class="schedule-day" style="{{ now()->dayOfWeekIso === $num ? 'border-color:var(--primary); box-shadow:0 0 0 2px var(--primary-soft);' : '' }}">
                <div class="schedule-day-name">{{ $dia }}</div>
                @forelse ($porDia[$num] ?? [] as $clase)
                    <a href="#clase-{{ $clase->id_clase }}" class="schedule-class {{ $clase->clientes->count() >= $clase->cupo ? '' : 'highlight' }}" style="display:block; text-decoration:none;">
                        <strong>{{ substr($clase->hora_inicio, 0, 5) }}</strong> · {{ $clase->nombre }}
                        <div class="class-meta">{{ $clase->entrenador?->nombre ?? 'Sin coach' }} · {{ $clase->clientes->count() }}/{{ $clase->cupo }}</div>
                    </a>
                @empty
                    <div class="muted" style="padding:.5rem 0;">—</div>
                @endforelse
            </div>
        @endforeach
    </div>

    <div class="grid-2">
        <div class="table-card">
            <div class="table-header"><h3>Clases e inscritos</h3></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Clase</th><th>Coach</th><th>Cupo</th><th class="text-right">Acciones</th></tr></thead>
                    <tbody>
                        @forelse ($clases as $clase)
                            @php $editando = $errors->clase->any() && (int) old('_id') === $clase->id_clase; @endphp
                            <tr id="clase-{{ $clase->id_clase }}">
                                <td><strong>{{ $clase->nombre }}</strong><div class="muted">{{ \App\Models\Clase::DIAS[$clase->dia_semana] }} · {{ $clase->horario }}</div></td>
                                <td>{{ $clase->entrenador?->nombre ?? '—' }}</td>
                                <td><span class="status-badge {{ $clase->clientes->count() >= $clase->cupo ? 'status-cancelled' : 'status-active' }}">{{ $clase->clientes->count() }}/{{ $clase->cupo }}</span></td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button" class="btn btn-light btn-sm btn-icon" title="Inscritos" data-toggle-target="inscritos-{{ $clase->id_clase }}"><i class="fas fa-users"></i></button>
                                        @if ($puedeGestionar)
                                            <button type="button" class="btn btn-light btn-sm btn-icon" title="Editar" data-toggle-target="editar-clase-{{ $clase->id_clase }}"><i class="fas fa-pen"></i></button>
                                            <form method="POST" action="{{ route('clases.destroy', $clase) }}" data-confirm="¿Eliminar la clase {{ $clase->nombre }}?">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <tr id="inscritos-{{ $clase->id_clase }}" class="edit-row hidden">
                                <td colspan="4">
                                    <div style="display:flex; flex-wrap:wrap; gap:.4rem; margin-bottom:.75rem;">
                                        @forelse ($clase->clientes as $inscrito)
                                            <form method="POST" action="{{ route('clases.desinscribir', [$clase, $inscrito]) }}" class="chip" data-confirm="¿Retirar a {{ $inscrito->nombre }} de la clase?">
                                                @csrf @method('DELETE')
                                                {{ $inscrito->nombre }}
                                                <button type="submit" style="border:0; background:none; cursor:pointer; color:#dc2626; padding:0;" title="Retirar"><i class="fas fa-xmark"></i></button>
                                            </form>
                                        @empty
                                            <span class="muted">Nadie inscrito todavía.</span>
                                        @endforelse
                                    </div>
                                    @if ($clase->clientes->count() < $clase->cupo)
                                        <form method="POST" action="{{ route('clases.inscribir', $clase) }}" style="display:flex; gap:.5rem;">
                                            @csrf
                                            <select name="id_cliente" required style="flex:1; padding:.5rem; border:1px solid #cbd5e1; border-radius:10px;">
                                                <option value="">Inscribir cliente…</option>
                                                @foreach ($clientes->whereNotIn('id_cliente', $clase->clientes->pluck('id_cliente')) as $c)
                                                    <option value="{{ $c->id_cliente }}">{{ $c->nombre }} — {{ $c->dni }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Inscribir</button>
                                        </form>
                                    @else
                                        <span class="status-badge status-cancelled">Clase llena</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($puedeGestionar)
                                <tr id="editar-clase-{{ $clase->id_clase }}" class="edit-row {{ $editando ? '' : 'hidden' }}">
                                    <td colspan="4">
                                        <form method="POST" action="{{ route('clases.update', $clase) }}">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="_id" value="{{ $clase->id_clase }}">
                                            @include('admin.partials.campos-clase', ['clase' => $clase, 'editando' => $editando])
                                            @if ($editando)
                                                @include('admin.partials.errores', ['bag' => 'clase'])
                                            @endif
                                            <div class="form-actions"><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button></div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="4" class="empty-state"><i class="fas fa-calendar-xmark"></i> {{ $activos->isEmpty() ? 'Registra primero un coach para crear clases.' : 'Aún no hay clases en el horario.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header"><h3>Coaches <span class="count">({{ $entrenadores->count() }})</span></h3></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Coach</th><th>Clases</th><th>Clientes</th><th class="text-right">Acciones</th></tr></thead>
                    <tbody>
                        @forelse ($entrenadores as $e)
                            @php $editando = $errors->entrenador->any() && (int) old('_id') === $e->id_entrenador; @endphp
                            <tr style="{{ $e->activo ? '' : 'opacity:.55' }}">
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">{{ strtoupper(mb_substr($e->nombre, 0, 1)) }}</div>
                                        <div>{{ $e->nombre }} @if ($e->usuario)<i class="fas fa-key" title="Tiene acceso: {{ $e->usuario->email }}" style="color:#94a3b8; font-size:.7rem;"></i>@endif
                                            <small>{{ $e->especialidad ?? 'General' }}{{ $e->sucursal ? ' · '.$e->sucursal->nombre : '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $e->clases_count }}</td>
                                <td>{{ $e->clientes_count }}</td>
                                <td>
                                    @if ($puedeGestionar)
                                        <div class="row-actions">
                                            <button type="button" class="btn btn-light btn-sm btn-icon" title="Editar" data-toggle-target="editar-coach-{{ $e->id_entrenador }}"><i class="fas fa-pen"></i></button>
                                            <form method="POST" action="{{ route('entrenadores.destroy', $e) }}" data-confirm="¿Eliminar a {{ $e->nombre }}?">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @if ($puedeGestionar)
                                <tr id="editar-coach-{{ $e->id_entrenador }}" class="edit-row {{ $editando ? '' : 'hidden' }}">
                                    <td colspan="4">
                                        <form method="POST" action="{{ route('entrenadores.update', $e) }}">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="_id" value="{{ $e->id_entrenador }}">
                                            <div class="form-grid">
                                                <div class="form-group"><label>Nombre</label><input type="text" name="nombre" value="{{ $editando ? old('nombre') : $e->nombre }}" required></div>
                                                <div class="form-group"><label>Especialidad</label><input type="text" name="especialidad" value="{{ $editando ? old('especialidad') : $e->especialidad }}"></div>
                                                <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" value="{{ $editando ? old('telefono') : $e->telefono }}"></div>
                                                <div class="form-group"><label>Correo</label><input type="email" name="email" value="{{ $editando ? old('email') : $e->email }}"></div>
                                                <div class="form-group">
                                                    <label>Sucursal</label>
                                                    <select name="id_sucursal">
                                                        <option value="">Todas</option>
                                                        @foreach ($sucursales as $s)<option value="{{ $s->id_sucursal }}" @selected(($editando ? old('id_sucursal') : $e->id_sucursal) == $s->id_sucursal)>{{ $s->nombre }}</option>@endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Usuario de acceso</label>
                                                    <select name="user_id">
                                                        <option value="">Sin acceso</option>
                                                        @foreach ($usuariosEntrenador as $u)<option value="{{ $u->id }}" @selected(($editando ? old('user_id') : $e->user_id) == $u->id)>{{ $u->name }}</option>@endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <label class="form-check" style="margin-top:.75rem;"><input type="checkbox" name="activo" value="1" @checked($editando ? old('activo') : $e->activo)> Coach activo</label>
                                            @if ($editando)
                                                @include('admin.partials.errores', ['bag' => 'entrenador'])
                                            @endif
                                            <div class="form-actions"><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button></div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="4" class="empty-state"><i class="fas fa-user-tie"></i> Aún no hay coaches registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
