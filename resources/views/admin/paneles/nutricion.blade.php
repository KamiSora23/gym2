<x-admin-layout titulo="Mi panel de nutrición" icono="fa-apple-whole" modulo="resumen"
    subtitulo="Hola, {{ strtok(Auth::user()->name, ' ') }}. Tus pacientes y sus planes nutricionales.">

    <x-slot name="acciones">
        <a href="{{ route('nutricion.index') }}" class="btn btn-primary"><i class="fas fa-users"></i> Mis pacientes</a>
    </x-slot>

    <div class="stats-grid">
        <div class="stat-card green"><div class="stat-title">Pacientes</div><div class="stat-value">{{ $pacientes->count() }}</div><div class="stat-icon"><i class="fas fa-users"></i></div></div>
        <div class="stat-card cyan"><div class="stat-title">Con plan activo</div><div class="stat-value">{{ $conPlan }}</div><div class="stat-icon"><i class="fas fa-clipboard-list"></i></div></div>
        <div class="stat-card orange"><div class="stat-title">Sin plan</div><div class="stat-value">{{ $pacientes->count() - $conPlan }}</div><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div></div>
        <div class="stat-card purple"><div class="stat-title">Con membresía vigente</div><div class="stat-value">{{ $pacientes->filter(fn ($p) => $p->membresiaActiva)->count() }}</div><div class="stat-icon"><i class="fas fa-id-card"></i></div></div>
    </div>

    <div class="grid-2">
        <div class="table-card">
            <div class="table-header"><h3>Pacientes sin plan</h3></div>
            <div class="table-scroll">
                <table class="data-table">
                    <tbody>
                        @forelse ($pacientes->filter(fn ($p) => $p->planesNutricionales->where('activo', true)->isEmpty()) as $p)
                            <tr><td>{{ $p->nombre }}</td><td class="text-right"><a href="{{ route('clientes.show', $p) }}#nutricion" class="btn btn-primary btn-sm">Crear plan</a></td></tr>
                        @empty
                            <tr><td class="empty-state"><i class="fas fa-circle-check"></i> Todos tus pacientes tienen plan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="panel-card">
            <h4>Últimos planes</h4>
            <ul class="tx-list">
                @forelse ($ultimosPlanes as $plan)
                    <li><span>{{ $plan->objetivo }}<small>{{ $plan->created_at->format('d/m/Y') }} · {{ $plan->nutricionista?->name }}</small></span><span class="chip">{{ $plan->calorias ? $plan->calorias.' kcal' : '—' }}</span></li>
                @empty
                    <li class="muted">Aún no hay planes.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-admin-layout>
