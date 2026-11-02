<x-admin-layout titulo="Historial de actividades" icono="fa-clock-rotate-left" :modulo="$global ? 'auditoria-global' : 'auditoria'"
    :subtitulo="$global ? 'Auditoría de toda la plataforma.' : 'Quién hizo qué y cuándo en la empresa.'">

    <form method="GET" class="form-card">
        <div class="form-grid cols-4" style="align-items:end;">
            @if ($global)
                <div class="form-group">
                    <label>Empresa</label>
                    <select name="empresa">
                        <option value="">Todas</option>
                        @foreach ($empresas as $e)<option value="{{ $e->id_empresa }}" @selected(request('empresa') == $e->id_empresa)>{{ $e->nombre }}</option>@endforeach
                    </select>
                </div>
            @endif
            <div class="form-group">
                <label>Usuario</label>
                <select name="usuario">
                    <option value="">Todos</option>
                    @foreach ($usuarios as $u)<option value="{{ $u->id }}" @selected(request('usuario') == $u->id)>{{ $u->name }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tipo de actividad</label>
                <select name="accion">
                    <option value="">Todas</option>
                    @foreach ($acciones as $k => $v)<option value="{{ $k }}" @selected(request('accion') === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label>Desde</label><input type="date" name="desde" value="{{ request('desde') }}"></div>
            <div class="form-group"><label>Hasta</label><input type="date" name="hasta" value="{{ request('hasta') }}"></div>
            <div class="form-group" style="flex-direction:row; gap:.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="{{ url()->current() }}" class="btn btn-light">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="table-card">
        <div class="table-header"><h3>Registros <span class="count">({{ $registros->total() }})</span></h3></div>
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr><th>Fecha</th>@if ($global)<th>Empresa</th>@endif<th>Usuario</th><th>Acción</th><th>Detalle</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse ($registros as $r)
                        <tr>
                            <td>{{ $r->created_at->format('d/m/Y H:i:s') }}</td>
                            @if ($global)<td>{{ $r->empresa?->nombre ?? 'Plataforma' }}</td>@endif
                            <td>{{ $r->usuario?->name ?? 'Sistema' }}</td>
                            <td><span class="chip">{{ $r->accion }}</span></td>
                            <td style="white-space:normal; max-width:460px;">{{ $r->descripcion }}</td>
                            <td class="muted">{{ $r->ip }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state"><i class="fas fa-clock-rotate-left"></i> No hay actividades con esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @include('admin.partials.paginacion', ['items' => $registros])
</x-admin-layout>
