<x-admin-layout titulo="Sucursales" icono="fa-store" modulo="sucursales"
    subtitulo="Sedes de la empresa. El personal y las operaciones se asignan a una sucursal.">

    <x-slot name="acciones">
        <button type="button" class="btn btn-primary" data-toggle-target="form-sucursal">
            <i class="fas fa-plus"></i> Nueva sucursal
        </button>
    </x-slot>

    @php $creando = $errors->sucursal->any() && ! old('_id'); @endphp

    <form id="form-sucursal" method="POST" action="{{ route('sucursales.store') }}" class="form-card {{ $creando ? '' : 'hidden' }}">
        @csrf
        <h3><i class="fas fa-store"></i> Nueva sucursal</h3>
        <div class="form-grid" style="grid-template-columns: 2fr 3fr 1.5fr;">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" value="{{ $creando ? old('nombre') : '' }}" placeholder="Sede Miraflores" required>
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ $creando ? old('direccion') : '' }}">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="{{ $creando ? old('telefono') : '' }}">
            </div>
        </div>
        @if ($creando)
            @include('admin.partials.errores', ['bag' => 'sucursal'])
        @endif
        <div class="form-actions">
            <button type="button" class="btn btn-light" data-toggle-target="form-sucursal">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear sucursal</button>
        </div>
    </form>

    <div class="table-card">
        <div class="table-header">
            <h3>Sucursales <span class="count">({{ $sucursales->count() }})</span></h3>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sucursal</th>
                        <th>Teléfono</th>
                        <th>Personal</th>
                        <th>Ventas del mes</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sucursales as $sucursal)
                        @php $editando = $errors->sucursal->any() && (int) old('_id') === $sucursal->id_sucursal; @endphp
                        <tr>
                            <td>
                                <strong>{{ $sucursal->nombre }}</strong>
                                <div class="muted">{{ $sucursal->direccion ?? 'Sin dirección' }}</div>
                            </td>
                            <td>{{ $sucursal->telefono ?? '—' }}</td>
                            <td>{{ $sucursal->usuarios_count }}</td>
                            <td class="money-in">${{ number_format((float) ($ventas[$sucursal->id_sucursal] ?? 0), 2) }}</td>
                            <td>
                                <span class="status-badge {{ $sucursal->activo ? 'status-active' : 'status-cancelled' }}">{{ $sucursal->activo ? 'Activa' : 'Inactiva' }}</span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="btn btn-light btn-sm btn-icon" title="Editar" data-toggle-target="editar-{{ $sucursal->id_sucursal }}"><i class="fas fa-pen"></i></button>
                                    <form method="POST" action="{{ route('sucursales.destroy', $sucursal) }}" data-confirm="¿Eliminar la sucursal {{ $sucursal->nombre }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr id="editar-{{ $sucursal->id_sucursal }}" class="edit-row {{ $editando ? '' : 'hidden' }}">
                            <td colspan="6">
                                <form method="POST" action="{{ route('sucursales.update', $sucursal) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="_id" value="{{ $sucursal->id_sucursal }}">
                                    <div class="form-grid" style="grid-template-columns: 2fr 3fr 1.5fr;">
                                        <div class="form-group"><label>Nombre</label><input type="text" name="nombre" value="{{ $editando ? old('nombre') : $sucursal->nombre }}" required></div>
                                        <div class="form-group"><label>Dirección</label><input type="text" name="direccion" value="{{ $editando ? old('direccion') : $sucursal->direccion }}"></div>
                                        <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" value="{{ $editando ? old('telefono') : $sucursal->telefono }}"></div>
                                    </div>
                                    <label class="form-check" style="margin-top:.75rem;">
                                        <input type="checkbox" name="activo" value="1" @checked($editando ? old('activo') : $sucursal->activo)> Sucursal activa
                                    </label>
                                    @if ($editando)
                                        @include('admin.partials.errores', ['bag' => 'sucursal'])
                                    @endif
                                    <div class="form-actions">
                                        <button type="button" class="btn btn-light btn-sm" data-toggle-target="editar-{{ $sucursal->id_sucursal }}">Cancelar</button>
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
