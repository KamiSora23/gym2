<x-admin-layout :titulo="$esCoach ? 'Mis alumnos' : 'Clientes'" icono="fa-users" modulo="clientes"
    :subtitulo="$esCoach ? 'Clientes que entrenas en persona o que están inscritos en tus clases.' : 'Socios del gimnasio, su membresía y su coach personal.'">

    @if ($puedeCrear)
        <x-slot name="acciones">
            <button type="button" class="btn btn-primary" data-toggle-target="form-cliente">
                <i class="fas fa-user-plus"></i> Nuevo cliente
            </button>
        </x-slot>
    @endif

    @php $creando = $errors->cliente->any() && ! old('_id'); @endphp

    <div class="stats-grid">
        <div class="stat-card blue"><div class="stat-title">Clientes</div><div class="stat-value">{{ $resumen['total'] }}</div><div class="stat-icon"><i class="fas fa-users"></i></div></div>
        <div class="stat-card green"><div class="stat-title">Con membresía vigente</div><div class="stat-value">{{ $resumen['activos'] }}</div><div class="stat-icon"><i class="fas fa-id-card"></i></div></div>
        <div class="stat-card purple"><div class="stat-title">Nuevos este mes</div><div class="stat-value">{{ $resumen['nuevos'] }}</div><div class="stat-icon"><i class="fas fa-user-plus"></i></div></div>
        <div class="stat-card orange"><div class="stat-title">Con coach personal</div><div class="stat-value">{{ $resumen['con_coach'] }}</div><div class="stat-icon"><i class="fas fa-dumbbell"></i></div></div>
    </div>

    @if ($puedeEditar)
    <form id="form-cliente" method="POST" action="{{ route('clientes.store') }}" class="form-card {{ $creando ? '' : 'hidden' }}">
        @csrf
        <h3><i class="fas fa-user-plus"></i> Nuevo cliente</h3>
        <div class="form-grid cols-4">
            <div class="form-group"><label>Nombre completo *</label><input type="text" name="nombre" value="{{ $creando ? old('nombre') : '' }}" required></div>
            <div class="form-group"><label>DNI *</label><input type="text" name="dni" value="{{ $creando ? old('dni') : '' }}" required></div>
            <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" value="{{ $creando ? old('telefono') : '' }}"></div>
            <div class="form-group"><label>Correo</label><input type="email" name="correo" value="{{ $creando ? old('correo') : '' }}"></div>
            @if ($puedeAsignarCoach)
            <div class="form-group">
                <label>Coach personal</label>
                <select name="id_entrenador">
                    <option value="">Sin coach</option>
                    @foreach ($entrenadores as $e)
                        <option value="{{ $e->id_entrenador }}" @selected($creando && old('id_entrenador') == $e->id_entrenador)>{{ $e->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            @if ($puedeAsignarCoach && $nutricionistas->isNotEmpty())
            <div class="form-group">
                <label>Nutricionista</label>
                <select name="id_nutricionista">
                    <option value="">Sin nutricionista</option>
                    @foreach ($nutricionistas as $n)
                        <option value="{{ $n->id }}" @selected($creando && old('id_nutricionista') == $n->id)>{{ $n->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
        @if ($creando)
            @include('admin.partials.errores', ['bag' => 'cliente'])
        @endif
        <div class="form-actions">
            <button type="button" class="btn btn-light" data-toggle-target="form-cliente">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Registrar cliente</button>
        </div>
    </form>
    @endif

    <div class="table-card">
        <div class="table-header">
            <h3>
                {{ request('estado') === 'inactivos' ? 'Clientes inactivos' : 'Listado' }} <span class="count">({{ $clientes->total() }})</span>
                @if (request('estado') === 'inactivos')
                    <a href="{{ route('clientes.index') }}" class="btn btn-light btn-sm" style="margin-left:.5rem;">Ver activos</a>
                @elseif ($resumen['inactivos'] > 0)
                    <a href="{{ route('clientes.index', ['estado' => 'inactivos']) }}" class="btn btn-light btn-sm" style="margin-left:.5rem;"><i class="fas fa-user-slash"></i> Inactivos ({{ $resumen['inactivos'] }})</a>
                @endif
            </h3>
            <form method="GET" class="search-box">
                <i class="fas fa-search"></i>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, DNI o correo…">
                @if (request('estado'))<input type="hidden" name="estado" value="{{ request('estado') }}">@endif
            </form>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>DNI</th>
                        <th>Membresía</th>
                        <th>Coach</th>
                        <th>Clases</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientes as $cliente)
                        @php
                            $editando = $errors->cliente->any() && (int) old('_id') === $cliente->id_cliente;
                            $m = $cliente->membresiaActiva;
                        @endphp
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">{{ strtoupper(mb_substr($cliente->nombre, 0, 1)) }}</div>
                                    <div><a href="{{ route('clientes.show', $cliente) }}" style="color:var(--text-dark); font-weight:600; text-decoration:none;">{{ $cliente->nombre }}</a><small>{{ $cliente->telefono ?? $cliente->correo ?? '—' }}</small></div>
                                </div>
                            </td>
                            <td>{{ $cliente->dni }}</td>
                            <td>
                                @if ($m)
                                    <span class="status-badge {{ $m->fecha_fin->diffInDays(today(), true) <= 7 ? 'status-pending' : 'status-active' }}">{{ $m->plan?->nombre }}</span>
                                    <div class="muted">hasta {{ $m->fecha_fin->format('d/m/Y') }}</div>
                                @else
                                    <span class="status-badge status-cancelled">Sin membresía</span>
                                @endif
                            </td>
                            <td>{{ $cliente->entrenador?->nombre ?? '—' }}</td>
                            <td>{{ $cliente->clases_count }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-light btn-sm btn-icon" title="Ver ficha"><i class="fas fa-eye"></i></a>
                                    @if (\App\Support\Permisos::puede(Auth::user(), 'ventas'))
                                        <a href="{{ route('ventas.create', ['cliente' => $cliente->id_cliente]) }}" class="btn btn-light btn-sm btn-icon" title="Vender membresía"><i class="fas fa-cash-register"></i></a>
                                    @endif
                                    @if ($puedeEditar)
                                        <button type="button" class="btn btn-light btn-sm btn-icon" title="Editar" data-toggle-target="editar-{{ $cliente->id_cliente }}"><i class="fas fa-pen"></i></button>
                                    @endif
                                    @if ($puedeEliminar)
                                        @if ($cliente->activo)
                                            <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" data-confirm="¿Dar de baja a {{ $cliente->nombre }}? Si tiene ventas, membresías o asistencias se desactivará y conservará su historial.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Dar de baja"><i class="fas fa-user-slash"></i></button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('clientes.estado', $cliente) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-primary btn-sm btn-icon" title="Reactivar"><i class="fas fa-user-check"></i></button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if ($puedeEditar)
                        <tr id="editar-{{ $cliente->id_cliente }}" class="edit-row {{ $editando ? '' : 'hidden' }}">
                            <td colspan="6">
                                <form method="POST" action="{{ route('clientes.update', [$cliente] + request()->only('q', 'page')) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="_id" value="{{ $cliente->id_cliente }}">
                                    <div class="form-grid cols-4">
                                        <div class="form-group"><label>Nombre</label><input type="text" name="nombre" value="{{ $editando ? old('nombre') : $cliente->nombre }}" required></div>
                                        <div class="form-group"><label>DNI</label><input type="text" name="dni" value="{{ $editando ? old('dni') : $cliente->dni }}" required></div>
                                        <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" value="{{ $editando ? old('telefono') : $cliente->telefono }}"></div>
                                        <div class="form-group"><label>Correo</label><input type="email" name="correo" value="{{ $editando ? old('correo') : $cliente->correo }}"></div>
                                        @if ($puedeAsignarCoach)
                                        <div class="form-group">
                                            <label>Coach personal</label>
                                            <select name="id_entrenador">
                                                <option value="">Sin coach</option>
                                                @foreach ($entrenadores as $e)
                                                    <option value="{{ $e->id_entrenador }}" @selected(($editando ? old('id_entrenador') : $cliente->id_entrenador) == $e->id_entrenador)>{{ $e->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                        @if ($puedeAsignarCoach && $nutricionistas->isNotEmpty())
                                        <div class="form-group">
                                            <label>Nutricionista</label>
                                            <select name="id_nutricionista">
                                                <option value="">Sin nutricionista</option>
                                                @foreach ($nutricionistas as $n)
                                                    <option value="{{ $n->id }}" @selected(($editando ? old('id_nutricionista') : $cliente->id_nutricionista) == $n->id)>{{ $n->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                    </div>
                                    @if ($editando)
                                        @include('admin.partials.errores', ['bag' => 'cliente'])
                                    @endif
                                    <div class="form-actions">
                                        <button type="button" class="btn btn-light btn-sm" data-toggle-target="editar-{{ $cliente->id_cliente }}">Cancelar</button>
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr><td colspan="6" class="empty-state"><i class="fas fa-users"></i>{{ request('q') ? 'Ningún cliente coincide con la búsqueda.' : 'Aún no hay clientes registrados.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.partials.paginacion', ['items' => $clientes])
</x-admin-layout>
