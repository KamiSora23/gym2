<x-admin-layout titulo="Gestión de cajas" icono="fa-cash-register" modulo="caja"
    subtitulo="Crea una caja por cada puesto de cobro y asigna sus responsables. Cada responsable abre su propio turno.">

    <x-slot name="acciones">
        <a href="{{ route('caja.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Caja</a>
        <button type="button" class="btn btn-primary" data-toggle-target="form-nueva-caja"><i class="fas fa-plus"></i> Nueva caja</button>
    </x-slot>

    <div class="alert alert-info">
        <i class="fas fa-circle-info"></i>
        <span>
            <strong>¿Varios recepcionistas?</strong> Crea una caja por persona (o por puesto) y asígnala: así cada uno cuadra su propio dinero.
            Una caja <strong>sin responsables</strong> la puede abrir cualquier empleado de su sucursal; una caja <strong>con responsables</strong> solo ellos.
            Una caja solo puede tener un turno abierto a la vez.
        </span>
    </div>

    <form id="form-nueva-caja" method="POST" action="{{ route('cajas.store') }}" class="form-card {{ $errors->nuevaCaja->any() ? '' : 'hidden' }}">
        @csrf
        <h3><i class="fas fa-plus"></i> Nueva caja</h3>
        <div class="form-grid">
            <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Caja 2 · Recepción tarde" required></div>
            <div class="form-group">
                <label>Sucursal *</label>
                <select name="id_sucursal" data-sucursal-caja>
                    @foreach ($sucursales as $s)<option value="{{ $s->id_sucursal }}">{{ $s->nombre }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="section-label" style="margin-top:1rem;">Responsables <span class="muted">(opcional)</span></div>
        @include('admin.partials.responsables-caja', ['caja' => null])
        @include('admin.partials.errores', ['bag' => 'nuevaCaja'])
        <div class="form-actions">
            <button type="button" class="btn btn-light" data-toggle-target="form-nueva-caja">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear caja</button>
        </div>
    </form>

    <div class="catalog-grid" style="grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));">
        @forelse ($cajas as $caja)
            <form method="POST" action="{{ route('cajas.update', $caja) }}" class="company-card {{ $caja->activo ? '' : 'inactive' }}">
                @csrf @method('PUT')
                <div class="company-head">
                    <div class="company-logo"><i class="fas fa-cash-register" style="font-size:1rem;"></i></div>
                    <div style="flex:1; min-width:0;">
                        <h3>{{ $caja->nombre }}</h3>
                        <p>{{ $caja->sucursal?->nombre }}</p>
                    </div>
                    @if ($caja->sesionAbierta)
                        <span class="status-badge status-active" title="Desde {{ $caja->sesionAbierta->abierta_en->format('H:i') }}"><i class="fas fa-lock-open"></i> {{ strtok($caja->sesionAbierta->usuario?->name ?? '', ' ') }}</span>
                    @else
                        <span class="status-badge {{ $caja->activo ? 'status-info' : 'status-cancelled' }}">{{ $caja->activo ? 'Cerrada' : 'Inactiva' }}</span>
                    @endif
                </div>

                <div class="form-group" style="margin:0;"><label>Nombre</label><input type="text" name="nombre" value="{{ $caja->nombre }}" required></div>

                <div>
                    <div class="section-label">Responsables</div>
                    @include('admin.partials.responsables-caja', ['caja' => $caja])
                    <p class="muted" style="margin:.4rem 0 0;">
                        {{ $caja->responsables->isEmpty() ? 'Sin responsables: cualquier empleado de la sucursal puede abrirla.' : 'Solo los marcados pueden abrirla.' }}
                    </p>
                </div>

                <label class="form-check"><input type="checkbox" name="activo" value="1" @checked($caja->activo)> Caja activa</label>
                @include('admin.partials.errores', ['bag' => 'caja'.$caja->id_caja])
                <div class="company-actions"><button type="submit" class="btn btn-primary btn-sm" style="width:100%;"><i class="fas fa-save"></i> Guardar</button></div>
            </form>
        @empty
            <div class="table-card" style="grid-column:1/-1;"><p class="empty-state" style="margin:0;"><i class="fas fa-cash-register"></i> No hay cajas. Crea la primera.</p></div>
        @endforelse
    </div>
</x-admin-layout>
