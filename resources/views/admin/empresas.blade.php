<x-admin-layout titulo="Empresas" icono="fa-building" modulo="empresas"
    subtitulo="Gimnasios que usan la plataforma. Elige uno para administrar sus sucursales, personal y operaciones.">

    <x-slot name="acciones">
        <button type="button" class="btn btn-primary" data-toggle-target="form-empresa">
            <i class="fas fa-plus"></i> Nueva empresa
        </button>
    </x-slot>

    @php $creando = $errors->empresa->any() && ! old('_id'); @endphp

    <div class="stats-grid">
        <div class="stat-card purple">
            <div class="stat-title">Empresas</div>
            <div class="stat-value">{{ $totales['empresas'] }}</div>
            <div class="stat-icon"><i class="fas fa-building"></i></div>
        </div>
        <div class="stat-card green">
            <div class="stat-title">Activas</div>
            <div class="stat-value">{{ $totales['activas'] }}</div>
            <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-title">Sucursales</div>
            <div class="stat-value">{{ $totales['sucursales'] }}</div>
            <div class="stat-icon"><i class="fas fa-store"></i></div>
        </div>
        <div class="stat-card orange">
            <div class="stat-title">Usuarios</div>
            <div class="stat-value">{{ $totales['usuarios'] }}</div>
            <div class="stat-icon"><i class="fas fa-users"></i></div>
        </div>
    </div>

    <form id="form-empresa" method="POST" action="{{ route('empresas.store') }}" class="form-card {{ $creando ? '' : 'hidden' }}">
        @csrf
        <h3><i class="fas fa-building-circle-check"></i> Nueva empresa</h3>
        <div class="form-grid cols-4">
            <div class="form-group">
                <label>Nombre comercial *</label>
                <input type="text" name="nombre" value="{{ $creando ? old('nombre') : '' }}" required>
            </div>
            <div class="form-group">
                <label>RUC</label>
                <input type="text" name="ruc" value="{{ $creando ? old('ruc') : '' }}">
            </div>
            <div class="form-group">
                <label>Correo</label>
                <input type="email" name="email" value="{{ $creando ? old('email') : '' }}">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="{{ $creando ? old('telefono') : '' }}">
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ $creando ? old('direccion') : '' }}">
            </div>
            <div class="form-group">
                <label>Primera sucursal *</label>
                <input type="text" name="sucursal" value="{{ $creando ? old('sucursal') : 'Sede principal' }}" required>
            </div>
        </div>

        <h3 style="margin-top:1.25rem;"><i class="fas fa-user-tie"></i> Administrador de la empresa <span class="muted">(opcional)</span></h3>
        <div class="form-grid cols-4">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="admin_nombre" value="{{ $creando ? old('admin_nombre') : '' }}">
            </div>
            <div class="form-group">
                <label>Correo de acceso</label>
                <input type="email" name="admin_email" value="{{ $creando ? old('admin_email') : '' }}" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="admin_password" autocomplete="new-password">
            </div>
        </div>

        @if ($creando)
            @include('admin.partials.errores', ['bag' => 'empresa'])
        @endif

        <div class="form-actions">
            <button type="button" class="btn btn-light" data-toggle-target="form-empresa">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear empresa</button>
        </div>
    </form>

    @if ($empresas->isEmpty())
        <div class="table-card">
            <p class="empty-state" style="margin:0;"><i class="fas fa-building"></i> Aún no hay empresas. Crea la primera.</p>
        </div>
    @endif

    <div class="catalog-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
        @foreach ($empresas as $empresa)
            @php $editando = $errors->empresa->any() && (int) old('_id') === $empresa->id_empresa; @endphp
            <div class="company-card {{ $empresa->activo ? '' : 'inactive' }}">
                <div class="company-head">
                    <div class="company-logo">{{ strtoupper(mb_substr($empresa->nombre, 0, 1)) }}</div>
                    <div style="min-width:0; flex:1;">
                        <h3>{{ $empresa->nombre }}</h3>
                        <p>{{ $empresa->ruc ? 'RUC '.$empresa->ruc : 'Sin RUC' }} · {{ $empresa->email ?? 'sin correo' }}</p>
                    </div>
                    <span class="status-badge {{ $empresa->activo ? 'status-active' : 'status-cancelled' }}">{{ $empresa->activo ? 'Activa' : 'Inactiva' }}</span>
                </div>

                <div class="company-stats">
                    <div><strong>{{ $empresa->sucursales_count }}</strong><span>Sucursales</span></div>
                    <div><strong>{{ $empresa->usuarios_count }}</strong><span>Usuarios</span></div>
                    <div><strong>{{ $empresa->clientes_count }}</strong><span>Clientes</span></div>
                </div>

                <div class="company-actions">
                    <form method="POST" action="{{ route('contexto.empresa') }}" style="flex:1;">
                        @csrf
                        <input type="hidden" name="id_empresa" value="{{ $empresa->id_empresa }}">
                        <button type="submit" class="btn btn-primary btn-sm" style="width:100%;" @disabled(! $empresa->activo)>
                            <i class="fas fa-right-to-bracket"></i> Administrar
                        </button>
                    </form>
                    <button type="button" class="btn btn-light btn-sm" data-toggle-target="editar-empresa-{{ $empresa->id_empresa }}">
                        <i class="fas fa-pen"></i> Editar
                    </button>
                    <form method="POST" action="{{ route('empresas.destroy', $empresa) }}"
                        data-confirm="¿Eliminar la empresa {{ $empresa->nombre }}?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </form>
                </div>

                <form id="editar-empresa-{{ $empresa->id_empresa }}" method="POST" action="{{ route('empresas.update', $empresa) }}"
                    class="{{ $editando ? '' : 'hidden' }}" style="border-top:1px solid var(--line); padding-top:1rem;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_id" value="{{ $empresa->id_empresa }}">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" value="{{ $editando ? old('nombre') : $empresa->nombre }}" required>
                        </div>
                        <div class="form-group">
                            <label>RUC</label>
                            <input type="text" name="ruc" value="{{ $editando ? old('ruc') : $empresa->ruc }}">
                        </div>
                        <div class="form-group">
                            <label>Correo</label>
                            <input type="email" name="email" value="{{ $editando ? old('email') : $empresa->email }}">
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" value="{{ $editando ? old('telefono') : $empresa->telefono }}">
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:1rem;">
                        <label>Dirección</label>
                        <input type="text" name="direccion" value="{{ $editando ? old('direccion') : $empresa->direccion }}">
                    </div>
                    <label class="form-check">
                        <input type="checkbox" name="activo" value="1" @checked($editando ? old('activo') : $empresa->activo)>
                        Empresa activa (si se desactiva, su personal no puede iniciar sesión)
                    </label>
                    @if ($editando)
                        @include('admin.partials.errores', ['bag' => 'empresa'])
                    @endif
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</x-admin-layout>
