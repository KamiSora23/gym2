<x-admin-layout titulo="Nutrición" icono="fa-apple-whole" modulo="nutricion"
    subtitulo="Planes nutricionales de tus pacientes. Esta información es privada: el resto del personal no la ve.">

    <div class="table-card">
        <div class="table-header">
            <h3>Pacientes <span class="count">({{ $pacientes->count() }})</span></h3>
            <div class="search-box"><i class="fas fa-search"></i><input type="search" placeholder="Buscar paciente…" data-filter-table="tabla-pacientes"></div>
        </div>
        <div class="table-scroll">
            <table class="data-table" id="tabla-pacientes">
                <thead><tr><th>Paciente</th><th>Nutricionista</th><th>Plan activo</th><th>Calorías</th><th class="text-right"></th></tr></thead>
                <tbody>
                    @forelse ($pacientes as $p)
                        @php $plan = $p->planesNutricionales->first(); @endphp
                        <tr>
                            <td>{{ $p->nombre }}<div class="muted">{{ $p->dni }}</div></td>
                            <td>{{ $p->nutricionista?->name ?? '—' }}</td>
                            <td>{{ $plan?->objetivo ?? 'Sin plan' }}</td>
                            <td>{{ $plan?->calorias ? $plan->calorias.' kcal' : '—' }}</td>
                            <td class="text-right"><a href="{{ route('clientes.show', $p) }}#nutricion" class="btn btn-light btn-sm">{{ $puedeGestionar ? 'Gestionar' : 'Ver' }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state"><i class="fas fa-apple-whole"></i> No tienes pacientes asignados. Recepción o administración los asignan desde la ficha del cliente.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
