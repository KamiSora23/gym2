<x-admin-layout titulo="Clientes" icono="fa-users" modulo="clientes"
    subtitulo="Socios del gimnasio, su membresía y su coach personal.">

    <x-slot name="acciones">
        <button type="button" class="btn btn-primary" data-toggle-target="form-cliente">
            <i class="fas fa-user-plus"></i> Nuevo cliente
        </button>
    </x-slot>

    @php $creando = $errors->cliente->any() && ! old('_id'); @endphp

    <div class="stats-grid">
        <div class="stat-card blue"><div class="stat-title">Clientes</div><div class="stat-value">{{ $resumen['total'] }}</div><div class="stat-icon"><i class="fas fa-users"></i></div></div>
        <div class="stat-card green"><div class="stat-title">Con membresía vigente</div><div class="stat-value">{{ $resumen['activos'] }}</div><div class="stat-icon"><i class="fas fa-id-card"></i></div></div>
        <div class="stat-card purple"><div class="stat-title">Nuevos este mes</div><div class="stat-value">{{ $resumen['nuevos'] }}</div><div class="stat-icon"><i class="fas fa-user-plus"></i></div></div>
        <div class="stat-card orange"><div class="stat-title">Con coach personal</div><div class="stat-value">{{ $resumen['con_coach'] }}</div><div class="stat-icon"><i class="fas fa-dumbbell"></i></div></div>
    </div>

    <form id="form-cliente" method="POST" action="{{ route('clientes.store') }}" class="form-card {{ $creando ? '' : 'hidden' }}">
        @csrf
        <h3><i class="fas fa-user-plus"></i> Nuevo cliente</h3>
        <div class="form-grid cols-4">
            <div class="form-group"><label>Nombre completo *</label><input type="text" name="nombre" value="{{ $creando ? old('nombre') : '' }}" required></div>
            <div class="form-group"><label>DNI *</label><input type="text" name="dni" value="{{ $creando ? old('dni') : '' }}" required></div>
            <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" value="{{ $creando ? old('telefono') : '' }}"></div>
            <div class="form-group"><label>Correo</label><input type="email" name="correo" value="{{ $creando ? old('correo') : '' }}"></div>
            <div class="form-group">
                <label>Coach personal</label>
                <select name="id_entrenador">
                    <option value="">Sin coach</option>
                    @foreach ($entrenadores as $e)
                        <option value="{{ $e->id_entrenador }}" @selected($creando && old('id_entrenador') == $e->id_entrenador)>{{ $e->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @if ($creando)
            @include('admin.partials.errores', ['bag' => 'cliente'])
        @endif
        <div class="form-actions">
            <button type="button" class="btn btn-light" data-toggle-target="form-cliente">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Registrar cliente</button>
        </div>
    </form>

    <div class="table-card">
        <div class="table-header">
            <h3>Listado <span class="count">({{ $clientes->total() }})</span></h3>
            <form method="GET" class="search-box">
                <i class="fas fa-search"></i>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, DNI o correo…">
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
                                    <div>{{ $cliente->nombre }}<small>{{ $cliente->telefono ?? $cliente->correo ?? '—' }}</small></div>
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
                                    @if (\App\Support\Permisos::puede(Auth::user(), 'ventas'))
                                        <a href="{{ route('ventas.create', ['cliente' => $cliente->id_cliente]) }}" class="btn btn-light btn-sm btn-icon" title="Vender membresía"><i class="fas fa-cash-register"></i></a>
                                    @endif
                                    <button type="button" class="btn btn-light btn-sm btn-icon" title="Editar" data-toggle-target="editar-{{ $cliente->id_cliente }}"><i class="fas fa-pen"></i></button>
                                    <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" data-confirm="¿Eliminar a {{ $cliente->nombre }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
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
                                        <div class="form-group">
                                            <label>Coach personal</label>
                                            <select name="id_entrenador">
                                                <option value="">Sin coach</option>
                                                @foreach ($entrenadores as $e)
                                                    <option value="{{ $e->id_entrenador }}" @selected(($editando ? old('id_entrenador') : $cliente->id_entrenador) == $e->id_entrenador)>{{ $e->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
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
                    @empty
                        <tr><td colspan="6" class="empty-state"><i class="fas fa-users"></i>{{ request('q') ? 'Ningún cliente coincide con la búsqueda.' : 'Aún no hay clientes registrados.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.partials.paginacion', ['items' => $clientes])
</x-admin-layout>
