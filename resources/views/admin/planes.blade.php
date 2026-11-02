<x-admin-layout titulo="Planes y membresías" icono="fa-id-card" modulo="planes"
    subtitulo="Planes que se venden en caja y las membresías de tus clientes.">

    <x-slot name="acciones">
        @if (Auth::user()->permite('planes.editar'))
            <button type="button" class="btn btn-primary" data-toggle-target="form-plan"><i class="fas fa-plus"></i> Nuevo plan</button>
        @endif
    </x-slot>

    @php $creando = $errors->plan->any() && ! old('_id'); @endphp

    @if (Auth::user()->permite('planes.editar'))
        <form id="form-plan" method="POST" action="{{ route('planes.store') }}" class="form-card {{ $creando ? '' : 'hidden' }}">
            @csrf
            <h3><i class="fas fa-id-card"></i> Nuevo plan</h3>
            <div class="form-grid cols-4">
                <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" value="{{ $creando ? old('nombre') : '' }}" placeholder="Mensual" required></div>
                <div class="form-group"><label>Precio *</label><input type="number" step="0.01" min="0" name="precio" value="{{ $creando ? old('precio') : '' }}" required></div>
                <div class="form-group"><label>Duración (días) *</label><input type="number" min="1" name="duracion_dias" value="{{ $creando ? old('duracion_dias') : 30 }}" required></div>
                <div class="form-group"><label>Descripción</label><input type="text" name="descripcion" value="{{ $creando ? old('descripcion') : '' }}" placeholder="Acceso ilimitado a sala"></div>
            </div>
            @if ($creando)
                @include('admin.partials.errores', ['bag' => 'plan'])
            @endif
            <div class="form-actions">
                <button type="button" class="btn btn-light" data-toggle-target="form-plan">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear plan</button>
            </div>
        </form>
    @endif

    <div class="campaign-grid">
        @forelse ($planes as $plan)
            @php $editando = $errors->plan->any() && (int) old('_id') === $plan->id_plan; @endphp
            <div class="campaign-card" style="{{ $plan->activo ? '' : 'opacity:.6' }}">
                <div class="campaign-header">
                    <div>
                        <h3 class="campaign-title">{{ $plan->nombre }}</h3>
                        <p>{{ $plan->descripcion ?: 'Sin descripción' }}</p>
                    </div>
                    <span class="status-badge {{ $plan->activo ? 'status-active' : 'status-cancelled' }}">{{ $plan->activo ? 'Activo' : 'Inactivo' }}</span>
                </div>
                <div class="campaign-metrics">
                    <div class="campaign-metric"><div class="campaign-metric-value">{{ dinero($plan->precio) }}</div><div class="campaign-metric-label">Precio</div></div>
                    <div class="campaign-metric"><div class="campaign-metric-value">{{ $plan->duracion_dias }}</div><div class="campaign-metric-label">Días</div></div>
                    <div class="campaign-metric"><div class="campaign-metric-value">{{ $plan->activas_count }}</div><div class="campaign-metric-label">Vigentes</div></div>
                </div>
                @if (Auth::user()->permite('planes.editar'))
                    <div class="company-actions" style="margin-top:1rem;">
                        <button type="button" class="btn btn-light btn-sm" data-toggle-target="editar-plan-{{ $plan->id_plan }}"><i class="fas fa-pen"></i> Editar</button>
                        <form method="POST" action="{{ route('planes.destroy', $plan) }}" data-confirm="¿Eliminar el plan {{ $plan->nombre }}?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                    <form id="editar-plan-{{ $plan->id_plan }}" method="POST" action="{{ route('planes.update', $plan) }}" class="{{ $editando ? '' : 'hidden' }}" style="margin-top:1rem; border-top:1px solid var(--line); padding-top:1rem;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_id" value="{{ $plan->id_plan }}">
                        <div class="form-grid">
                            <div class="form-group"><label>Nombre</label><input type="text" name="nombre" value="{{ $editando ? old('nombre') : $plan->nombre }}" required></div>
                            <div class="form-group"><label>Precio</label><input type="number" step="0.01" min="0" name="precio" value="{{ $editando ? old('precio') : $plan->precio }}" required></div>
                            <div class="form-group"><label>Días</label><input type="number" min="1" name="duracion_dias" value="{{ $editando ? old('duracion_dias') : $plan->duracion_dias }}" required></div>
                            <div class="form-group"><label>Descripción</label><input type="text" name="descripcion" value="{{ $editando ? old('descripcion') : $plan->descripcion }}"></div>
                        </div>
                        <label class="form-check" style="margin-top:.75rem;"><input type="checkbox" name="activo" value="1" @checked($editando ? old('activo') : $plan->activo)> Disponible para la venta</label>
                        @if ($editando)
                            @include('admin.partials.errores', ['bag' => 'plan'])
                        @endif
                        <div class="form-actions"><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button></div>
                    </form>
                @endif
            </div>
        @empty
            <div class="table-card" style="grid-column:1/-1;"><p class="empty-state" style="margin:0;"><i class="fas fa-id-card"></i> Aún no hay planes. Crea el primero para poder vender membresías.</p></div>
        @endforelse
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Membresías recientes</h3>
            @if ($porVencer)
                <span class="status-badge status-pending"><i class="fas fa-triangle-exclamation"></i> {{ $porVencer }} vencen en los próximos 7 días</span>
            @endif
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr><th>Cliente</th><th>Plan</th><th>Inicio</th><th>Fin</th><th>Estado</th></tr></thead>
                <tbody>
                    @forelse ($membresias as $m)
                        @php
                            $vigente = $m->estado === 'activo' && $m->fecha_fin->gte(today());
                            [$clase, $texto] = match (true) {
                                $m->estado === 'anulado' => ['status-cancelled', 'Anulada'],
                                ! $vigente => ['status-cancelled', 'Vencida'],
                                $m->fecha_inicio->gt(today()) => ['status-info', 'Programada'],
                                $m->fecha_fin->lte(today()->addDays(7)) => ['status-pending', 'Por vencer'],
                                default => ['status-active', 'Vigente'],
                            };
                        @endphp
                        <tr>
                            <td>{{ $m->cliente?->nombre }}</td>
                            <td>{{ $m->plan?->nombre }}</td>
                            <td>{{ $m->fecha_inicio->format('d/m/Y') }}</td>
                            <td>{{ $m->fecha_fin->format('d/m/Y') }}</td>
                            <td><span class="status-badge {{ $clase }}">{{ $texto }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state"><i class="fas fa-id-card"></i> Aún no se han vendido membresías.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
