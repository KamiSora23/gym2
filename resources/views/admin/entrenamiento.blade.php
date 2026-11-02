<x-admin-layout titulo="Entrenamiento" icono="fa-dumbbell" modulo="entrenamiento"
    :subtitulo="$miPerfil ? 'Tu horario de clases, tus inscritos y tu disponibilidad.' : 'Coaches, horario semanal de clases e inscripciones de clientes.'">

    <x-slot name="acciones">
        @if ($miPerfil)
            <a href="{{ route('entrenamiento.index', $soloMias ? ['todas' => 1] : []) }}" class="btn btn-light">
                <i class="fas fa-filter"></i> {{ $soloMias ? 'Ver horario completo' : 'Solo mis clases' }}
            </a>
            <a href="{{ route('entrenadores.disponibilidad', $miPerfil) }}" class="btn btn-light"><i class="fas fa-user-clock"></i> Mi disponibilidad</a>
        @endif
        @if ($puedeCoaches)
            <button type="button" class="btn btn-light" data-toggle-target="form-entrenador"><i class="fas fa-user-tie"></i> Nuevo coach</button>
        @endif
        @if ($puedeTodas || $puedePropias)
            <button type="button" class="btn btn-primary" data-toggle-target="form-clase" @disabled($puedeTodas && $entrenadores->where('activo', true)->isEmpty())><i class="fas fa-plus"></i> Nueva clase</button>
        @endif
    </x-slot>

    @php
        $creandoClase = $errors->clase->any() && ! old('_id');
        $creandoCoach = $errors->entrenador->any() && ! old('_id');
        $activos = $entrenadores->where('activo', true);
        $inscritosTotal = $clases->sum(fn ($c) => $c->clientes->count());
    @endphp

    @if (Auth::user()->hasRole('entrenador') && ! $miPerfil)
        <div class="alert alert-info"><i class="fas fa-circle-info"></i> Tu usuario aún no está vinculado a un perfil de coach. Pide al administrador que te vincule para crear tus clases.</div>
    @endif

    <div class="stats-grid">
        <div class="stat-card cyan"><div class="stat-title">{{ $soloMias ? 'Mis clases' : 'Clases semanales' }}</div><div class="stat-value">{{ $clases->count() }}</div><div class="stat-icon"><i class="fas fa-calendar-alt"></i></div></div>
        <div class="stat-card green"><div class="stat-title">Coaches activos</div><div class="stat-value">{{ $activos->count() }}</div><div class="stat-icon"><i class="fas fa-user-tie"></i></div></div>
        <div class="stat-card purple"><div class="stat-title">Inscripciones</div><div class="stat-value">{{ $inscritosTotal }}</div><div class="stat-icon"><i class="fas fa-users"></i></div></div>
        <div class="stat-card orange"><div class="stat-title">Ocupación</div><div class="stat-value">{{ $clases->sum('cupo') ? round($inscritosTotal * 100 / $clases->sum('cupo')) : 0 }}%</div><div class="stat-icon"><i class="fas fa-chart-pie"></i></div></div>
    </div>

    @if ($puedeCoaches)
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
            <p class="muted" style="margin:.75rem 0 0;">Con un usuario de acceso, el coach entra al sistema, crea sus clases y define su disponibilidad.</p>
            @if ($creandoCoach)
                @include('admin.partials.errores', ['bag' => 'entrenador'])
            @endif
            <div class="form-actions">
                <button type="button" class="btn btn-light" data-toggle-target="form-entrenador">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Registrar coach</button>
            </div>
        </form>
    @endif

    @if ($puedeTodas || $puedePropias)
        <form id="form-clase" method="POST" action="{{ route('clases.store') }}" class="form-card {{ $creandoClase ? '' : 'hidden' }}">
            @csrf
            <h3><i class="fas fa-calendar-plus"></i> Nueva clase</h3>
            @if (! $puedeTodas && $miPerfil)
                <p class="muted" style="margin:-.5rem 0 1rem;">La clase se crea a tu nombre en tu sucursal.
                    @if ($miPerfil->disponibilidad->isNotEmpty()) Debe estar dentro de tu disponibilidad. @endif
                </p>
            @endif
            @include('admin.partials.campos-clase', ['clase' => null, 'editando' => $creandoClase, 'conCoach' => $puedeTodas])
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
            @php $franjasDia = $miPerfil?->disponibilidad->where('dia_semana', $num); @endphp
            <div class="schedule-day" style="{{ now()->dayOfWeekIso === $num ? 'border-color:var(--primary); box-shadow:0 0 0 2px var(--primary-soft);' : '' }}">
                <div class="schedule-day-name">{{ $dia }}</div>
                @if ($soloMias && $franjasDia !== null)
                    <div class="class-meta" style="margin-bottom:.35rem;">
                        <i class="fas fa-user-clock"></i> {{ $franjasDia->isNotEmpty() ? $franjasDia->map->horario->implode(' · ') : ($miPerfil->disponibilidad->isEmpty() ? 'Sin definir' : 'No disponible') }}
                    </div>
                @endif
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
                            @php
                                $editando = $errors->clase->any() && (int) old('_id') === $clase->id_clase;
                                $editable = $puedeEditarClase($clase);
                                $inscribible = $puedeInscribir($clase);
                            @endphp
                            <tr id="clase-{{ $clase->id_clase }}">
                                <td><strong>{{ $clase->nombre }}</strong><div class="muted">{{ \App\Models\Clase::DIAS[$clase->dia_semana] }} · {{ $clase->horario }}</div></td>
                                <td>{{ $clase->entrenador?->nombre ?? '—' }}</td>
                                <td><span class="status-badge {{ $clase->clientes->count() >= $clase->cupo ? 'status-cancelled' : 'status-active' }}">{{ $clase->clientes->count() }}/{{ $clase->cupo }}</span></td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button" class="btn btn-light btn-sm btn-icon" title="Inscritos" data-toggle-target="inscritos-{{ $clase->id_clase }}"><i class="fas fa-users"></i></button>
                                        @if ($editable)
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
                                            @if ($inscribible)
                                                <form method="POST" action="{{ route('clases.desinscribir', [$clase, $inscrito]) }}" class="chip" data-confirm="¿Retirar a {{ $inscrito->nombre }} de la clase?">
                                                    @csrf @method('DELETE')
                                                    {{ $inscrito->nombre }}
                                                    <button type="submit" style="border:0; background:none; cursor:pointer; color:#dc2626; padding:0;" title="Retirar"><i class="fas fa-xmark"></i></button>
                                                </form>
                                            @else
                                                <span class="chip">{{ $inscrito->nombre }}</span>
                                            @endif
                                        @empty
                                            <span class="muted">Nadie inscrito todavía.</span>
                                        @endforelse
                                    </div>
                                    @if ($inscribible)
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
                                    @endif
                                </td>
                            </tr>
                            @if ($editable)
                                <tr id="editar-clase-{{ $clase->id_clase }}" class="edit-row {{ $editando ? '' : 'hidden' }}">
                                    <td colspan="4">
                                        <form method="POST" action="{{ route('clases.update', $clase) }}">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="_id" value="{{ $clase->id_clase }}">
                                            @include('admin.partials.campos-clase', ['clase' => $clase, 'editando' => $editando, 'conCoach' => $puedeTodas])
                                            @if ($editando)
                                                @include('admin.partials.errores', ['bag' => 'clase'])
                                            @endif
                                            <div class="form-actions"><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button></div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="4" class="empty-state"><i class="fas fa-calendar-xmark"></i>
                                {{ $miPerfil ? 'Aún no tienes clases. Crea la primera con «Nueva clase».' : ($activos->isEmpty() ? 'Registra primero un coach para crear clases.' : 'Aún no hay clases en el horario.') }}
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header"><h3>Coaches <span class="count">({{ $entrenadores->count() }})</span></h3></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Coach</th><th>Disponibilidad</th><th>Clases</th><th class="text-right">Acciones</th></tr></thead>
                    <tbody>
                        @forelse ($entrenadores as $e)
                            @php $editando = $errors->entrenador->any() && (int) old('_id') === $e->id_entrenador; @endphp
                            <tr style="{{ $e->activo ? '' : 'opacity:.55' }}">
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">{{ strtoupper(mb_substr($e->nombre, 0, 1)) }}</div>
                                        <div>{{ $e->nombre }} @if ($e->usuario)<i class="fas fa-key" title="Tiene acceso: {{ $e->usuario->email }}" style="color:#94a3b8; font-size:.7rem;"></i>@endif
                                            <small>{{ $e->especialidad ?? 'General' }}{{ $e->sucursal ? ' · '.$e->sucursal->nombre : '' }} · {{ $e->clientes_count }} alumnos</small>
                                        </div>
                                    </div>
                                </td>
                                <td style="white-space:normal; font-size:.78rem; color:var(--text-gray);">
                                    @forelse ($e->disponibilidad->groupBy('dia_semana') as $dia => $franjas)
                                        <div><strong>{{ mb_substr(\App\Models\Clase::DIAS[$dia], 0, 3) }}</strong> {{ $franjas->map->horario->implode(', ') }}</div>
                                    @empty
                                        <span class="muted">Sin definir</span>
                                    @endforelse
                                </td>
                                <td>{{ $e->clases_count }}</td>
                                <td>
                                    <div class="row-actions">
                                        @if (Auth::user()->permite('trainers.availability') || $miPerfil?->is($e))
                                            <a href="{{ route('entrenadores.disponibilidad', $e) }}" class="btn btn-light btn-sm btn-icon" title="Perfil y disponibilidad"><i class="fas fa-user-clock"></i></a>
                                        @endif
                                        @if ($puedeCoaches)
                                            <button type="button" class="btn btn-light btn-sm btn-icon" title="Editar" data-toggle-target="editar-coach-{{ $e->id_entrenador }}"><i class="fas fa-pen"></i></button>
                                            <form method="POST" action="{{ route('entrenadores.destroy', $e) }}" data-confirm="¿Eliminar a {{ $e->nombre }}?">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @if ($puedeCoaches)
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
