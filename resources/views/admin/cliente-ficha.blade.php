<x-admin-layout :titulo="$cliente->nombre" icono="fa-id-card-clip" modulo="clientes"
    subtitulo="DNI {{ $cliente->dni }} · {{ $cliente->telefono ?? $cliente->correo ?? 'sin contacto' }}">

    <x-slot name="acciones">
        <a href="{{ route('clientes.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver</a>
        @if (Auth::user()->permite('ventas.vender'))
            <a href="{{ route('ventas.create', ['cliente' => $cliente->id_cliente]) }}" class="btn btn-primary"><i class="fas fa-id-card"></i> Vender membresía</a>
        @endif
    </x-slot>

    @php
        $m = $cliente->membresiaActiva;
        $activa = $cliente->rutinas->firstWhere('activa', true);
        $otras = $cliente->rutinas->reject(fn ($r) => $r->activa);
        $ultimo = $cliente->progresos->first();
        $primero = $cliente->progresos->last();
        $creandoRutina = $errors->rutina->any() && ! old('_id');
    @endphp

    <div class="stats-grid">
        <div class="stat-card {{ $m ? 'green' : 'red' }}">
            <div class="stat-title">Membresía</div>
            <div class="stat-value" style="font-size:1.2rem;">{{ $m?->plan?->nombre ?? 'Sin membresía' }}</div>
            <div class="stat-icon"><i class="fas fa-id-card"></i></div>
            <div class="stat-hint">{{ $m ? 'Hasta '.$m->fecha_fin->format('d/m/Y') : 'Debe renovar' }}</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-title">Coach personal</div>
            <div class="stat-value" style="font-size:1.2rem;">{{ $cliente->entrenador?->nombre ?? '—' }}</div>
            <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
            <div class="stat-hint">{{ $cliente->clases->count() }} clases grupales</div>
        </div>
        <div class="stat-card cyan">
            <div class="stat-title">Peso actual</div>
            <div class="stat-value">{{ $ultimo?->peso ? rtrim(rtrim($ultimo->peso, '0'), '.').' kg' : '—' }}</div>
            <div class="stat-icon"><i class="fas fa-weight-scale"></i></div>
            @if ($ultimo?->peso && $primero?->peso && $cliente->progresos->count() > 1)
                @php $dif = (float) $ultimo->peso - (float) $primero->peso; @endphp
                <div class="stat-hint" style="color: {{ $dif <= 0 ? '#059669' : '#b45309' }}">{{ $dif > 0 ? '+' : '' }}{{ number_format($dif, 1) }} kg desde el inicio</div>
            @endif
        </div>
        <div class="stat-card orange">
            <div class="stat-title">Visitas recientes</div>
            <div class="stat-value">{{ $asistencias->count() }}</div>
            <div class="stat-icon"><i class="fas fa-person-running"></i></div>
            <div class="stat-hint">{{ $asistencias->first()?->fecha?->locale('es')->diffForHumans() ?? 'Sin visitas' }}</div>
        </div>
    </div>

    <div class="grid-2">
        {{-- RUTINA --}}
        <div>
            <div class="table-card">
                <div class="table-header">
                    <h3><i class="fas fa-list-check" style="color:var(--primary)"></i> Rutina activa</h3>
                    @if ($puedeRutinas)
                        <div style="display:flex; gap:.4rem;">
                            @if ($activa)
                                <button type="button" class="btn btn-light btn-sm" data-toggle-target="editar-rutina"><i class="fas fa-pen"></i> Editar</button>
                            @endif
                            <button type="button" class="btn btn-primary btn-sm" data-toggle-target="nueva-rutina"><i class="fas fa-plus"></i> Nueva rutina</button>
                        </div>
                    @endif
                </div>

                @if ($puedeRutinas)
                    <form id="nueva-rutina" method="POST" action="{{ route('rutinas.store', $cliente) }}" class="{{ $creandoRutina ? '' : 'hidden' }}" style="padding:1.25rem; border-bottom:1px solid var(--line); background:#fafbff;">
                        @csrf
                        @include('admin.partials.campos-rutina', ['rutina' => null, 'editando' => $creandoRutina])
                        @if ($creandoRutina)
                            @include('admin.partials.errores', ['bag' => 'rutina'])
                        @endif
                        <div class="form-actions">
                            <button type="button" class="btn btn-light btn-sm" data-toggle-target="nueva-rutina">Cancelar</button>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Asignar rutina</button>
                        </div>
                    </form>

                    @if ($activa)
                        @php $editandoRutina = $errors->rutina->any() && (int) old('_id') === $activa->id_rutina; @endphp
                        <form id="editar-rutina" method="POST" action="{{ route('rutinas.update', $activa) }}" class="{{ $editandoRutina ? '' : 'hidden' }}" style="padding:1.25rem; border-bottom:1px solid var(--line); background:#fafbff;">
                            @csrf @method('PUT')
                            <input type="hidden" name="_id" value="{{ $activa->id_rutina }}">
                            @include('admin.partials.campos-rutina', ['rutina' => $activa, 'editando' => $editandoRutina])
                            @if ($editandoRutina)
                                @include('admin.partials.errores', ['bag' => 'rutina'])
                            @endif
                            <div class="form-actions">
                                <button type="button" class="btn btn-light btn-sm" data-toggle-target="editar-rutina">Cancelar</button>
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar cambios</button>
                            </div>
                        </form>
                    @endif
                @endif

                @if ($activa)
                    <div style="padding:1rem 1.5rem 0;">
                        <strong style="font-size:1.05rem;">{{ $activa->nombre }}</strong>
                        <div class="muted">{{ $activa->objetivo ?? 'Sin objetivo definido' }} · por {{ $activa->entrenador?->nombre ?? 'administración' }} · {{ $activa->updated_at->format('d/m/Y') }}</div>
                        @if ($activa->notas)<p style="font-size:.85rem; color:var(--text-gray); margin:.5rem 0 0;">{{ $activa->notas }}</p>@endif
                    </div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>Día</th><th>Ejercicio</th><th>Series</th><th>Reps</th><th>Descanso</th></tr></thead>
                            <tbody>
                                @foreach ($activa->ejercicios as $ej)
                                    <tr><td>{{ $ej->dia ?? '—' }}</td><td><strong>{{ $ej->ejercicio }}</strong></td><td>{{ $ej->series ?? '—' }}</td><td>{{ $ej->repeticiones ?? '—' }}</td><td>{{ $ej->descanso ?? '—' }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="empty-state" style="margin:0;"><i class="fas fa-list-check"></i> El alumno no tiene una rutina asignada.</p>
                @endif

                @if ($otras->isNotEmpty())
                    <div style="padding:1rem 1.5rem; border-top:1px solid var(--line);">
                        <div class="section-label">Rutinas anteriores</div>
                        <ul class="tx-list">
                            @foreach ($otras as $r)
                                <li>
                                    <span>{{ $r->nombre }}<small>{{ $r->ejercicios->count() }} ejercicios · {{ $r->updated_at->format('d/m/Y') }}</small></span>
                                    @if ($puedeRutinas)
                                        <span style="display:flex; gap:.35rem;">
                                            <form method="POST" action="{{ route('rutinas.activar', $r) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-light btn-sm">Reactivar</button></form>
                                            <form method="POST" action="{{ route('rutinas.destroy', $r) }}" data-confirm="¿Eliminar la rutina {{ $r->nombre }}?">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm btn-icon"><i class="fas fa-trash"></i></button></form>
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="panel-card">
                <h4>Clases inscritas</h4>
                <ul class="tx-list">
                    @forelse ($cliente->clases as $clase)
                        <li><span>{{ $clase->nombre }}<small>{{ \App\Models\Clase::DIAS[$clase->dia_semana] }} · {{ $clase->horario }}</small></span><span class="chip">{{ $clase->entrenador?->nombre ?? 'Sin coach' }}</span></li>
                    @empty
                        <li class="muted">No está inscrito en clases.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- PROGRESO --}}
        <div>
            <div class="chart-card" style="margin-bottom:1.5rem;">
                <div class="chart-header"><h3>Evolución</h3><span class="muted">{{ $cliente->progresos->count() }} mediciones</span></div>
                @if ($cliente->progresos->whereNotNull('peso')->count() >= 2)
                    <div class="chart-box" style="height:220px;"><canvas id="graficoProgreso"></canvas></div>
                @else
                    <p class="muted" style="margin:0;">Registra al menos dos mediciones de peso para ver la gráfica.</p>
                @endif
            </div>

            @if ($puedeRutinas)
                <form method="POST" action="{{ route('progreso.store', $cliente) }}" class="form-card">
                    @csrf
                    <h3><i class="fas fa-ruler"></i> Registrar medición</h3>
                    <div class="form-grid cols-4">
                        <div class="form-group"><label>Fecha</label><input type="date" name="fecha" value="{{ old('fecha', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required></div>
                        <div class="form-group"><label>Peso (kg)</label><input type="number" step="0.1" name="peso" value="{{ old('peso') }}"></div>
                        <div class="form-group"><label>Grasa (%)</label><input type="number" step="0.1" name="grasa" value="{{ old('grasa') }}"></div>
                        <div class="form-group"><label>Cintura (cm)</label><input type="number" step="0.1" name="cintura" value="{{ old('cintura') }}"></div>
                    </div>
                    <div class="form-group" style="margin-top:1rem;"><label>Notas</label><input type="text" name="notas" value="{{ old('notas') }}" placeholder="Sensaciones, lesiones, objetivos…"></div>
                    @include('admin.partials.errores', ['bag' => 'progreso'])
                    <div class="form-actions"><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar medición</button></div>
                </form>
            @endif

            <div class="table-card">
                <div class="table-header"><h3>Historial de mediciones</h3></div>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>Fecha</th><th>Peso</th><th>Grasa</th><th>Cintura</th><th>Notas</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($cliente->progresos as $p)
                                <tr>
                                    <td>{{ $p->fecha->format('d/m/Y') }}</td>
                                    <td>{{ $p->peso ? $p->peso.' kg' : '—' }}</td>
                                    <td>{{ $p->grasa ? $p->grasa.' %' : '—' }}</td>
                                    <td>{{ $p->cintura ? $p->cintura.' cm' : '—' }}</td>
                                    <td style="white-space:normal; max-width:220px;">{{ $p->notas ?? '—' }}</td>
                                    <td class="text-right">
                                        @if ($puedeRutinas)
                                            <form method="POST" action="{{ route('progreso.destroy', $p) }}" data-confirm="¿Eliminar esta medición?">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm btn-icon"><i class="fas fa-trash"></i></button></form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="empty-state"><i class="fas fa-ruler"></i> Sin mediciones todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            (function () {
                const canvas = document.getElementById('graficoProgreso');
                if (!canvas || typeof Chart === 'undefined') return;
                const primario = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
                const datos = @json($cliente->progresos->whereNotNull('peso')->sortBy('fecha')->values()->map(fn ($p) => ['f' => $p->fecha->format('d/m'), 'p' => (float) $p->peso]));
                new Chart(canvas, {
                    type: 'line',
                    data: { labels: datos.map(d => d.f), datasets: [{ label: 'Peso (kg)', data: datos.map(d => d.p), borderColor: primario, backgroundColor: primario + '22', fill: true, tension: .3, pointRadius: 4 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } },
                });
            })();
        </script>
    </x-slot>
</x-admin-layout>
