<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi cuenta · {{ $config->nombre_gym }}</title>
    <link rel="icon" href="{{ $config->favicon_url }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    @include('partials.tema-web')
    <style>
        body { background: #f4f6fa; }
        .cuenta-hero { background: linear-gradient(135deg, var(--dark), var(--dark-light)); color: #fff; padding: 7rem 0 4.5rem; }
        .cuenta-hero .avatar { width: 64px; height: 64px; border-radius: 18px; background: var(--primary); color: var(--primary-text); font-weight: 800; font-size: 1.6rem; display: flex; align-items: center; justify-content: center; }
        .cuenta-card { background: #fff; border-radius: 18px; padding: 1.5rem; box-shadow: 0 6px 24px rgba(15, 23, 42, .06); height: 100%; }
        .cuenta-card h5 { font-weight: 800; font-size: 1rem; margin-bottom: 1rem; display: flex; gap: .5rem; align-items: center; }
        .cuenta-card h5 i { color: var(--primary); }
        .membresia-card { margin-top: -3rem; }
        .progreso { height: 10px; border-radius: 99px; background: #e2e8f0; overflow: hidden; }
        .progreso div { height: 100%; background: var(--primary); }
        .estado { font-size: .75rem; font-weight: 700; padding: .3rem .7rem; border-radius: 99px; }
        .estado.ok { background: #ecfdf5; color: #047857; }
        .estado.no { background: #fef2f2; color: #b91c1c; }
        .lista-item { display: flex; justify-content: space-between; align-items: center; padding: .65rem 0; border-bottom: 1px dashed #e2e8f0; font-size: .92rem; }
        .lista-item:last-child { border-bottom: 0; }
        .lista-item small { display: block; color: #94a3b8; }
    </style>
</head>

<body>
    <header class="navbar navbar-expand-lg fixed-top" id="mainNav" style="background: var(--dark);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                <span class="text-primary">{{ $config->nombrePartido()[0] }}</span>
                <span class="text-white">{{ $config->nombrePartido()[1] }}</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ url('/') }}" class="btn btn-sm btn-outline-light">Ir al sitio</a>
                <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-light"><i class="bi bi-person-gear"></i></a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary">Salir</button>
                </form>
            </div>
        </div>
    </header>

    <section class="cuenta-hero">
        <div class="container d-flex align-items-center gap-3">
            <div class="avatar">{{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</div>
            <div>
                <h1 class="h3 fw-bold mb-1">Hola, {{ strtok(Auth::user()->name, ' ') }}</h1>
                <p class="mb-0 text-white-50">Tu espacio en {{ $config->nombre_gym }}</p>
            </div>
        </div>
    </section>

    <main class="container pb-5">
        @if (session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif

        @if (! $cliente)
            <div class="cuenta-card membresia-card">
                <p class="mb-0">Tu cuenta aún no tiene una ficha de cliente. Acércate a recepción para completar tu registro.</p>
            </div>
        @else
            @php
                $m = $cliente->membresiaActiva;
                $total = $m ? max(1, $m->fecha_inicio->diffInDays($m->fecha_fin) + 1) : 1;
                $restantes = $m ? (int) today()->diffInDays($m->fecha_fin, false) + 1 : 0;
                $avance = $m ? min(100, max(0, round(($total - $restantes) * 100 / $total))) : 0;
            @endphp

            <div class="cuenta-card membresia-card mb-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Mi membresía</div>
                        @if ($m)
                            <h2 class="h4 fw-bold mb-1">{{ $m->plan?->nombre }}</h2>
                            <p class="text-muted mb-0">Del {{ $m->fecha_inicio->format('d/m/Y') }} al {{ $m->fecha_fin->format('d/m/Y') }}</p>
                        @else
                            <h2 class="h4 fw-bold mb-1">Sin membresía vigente</h2>
                            <p class="text-muted mb-0">Renueva en recepción para seguir entrenando.</p>
                        @endif
                    </div>
                    <span class="estado {{ $m ? 'ok' : 'no' }}">{{ $m ? ($restantes <= 7 ? "Vence en {$restantes} días" : 'Activa') : 'Vencida' }}</span>
                </div>
                @if ($m)
                    <div class="progreso mt-3"><div style="width: {{ $avance }}%"></div></div>
                    <div class="d-flex justify-content-between small text-muted mt-1"><span>{{ $avance }}% usado</span><span>{{ $restantes }} días restantes</span></div>
                @endif
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="cuenta-card">
                        <h5><i class="bi bi-person-badge"></i> Mi coach</h5>
                        @if ($cliente->entrenador)
                            <p class="fw-bold mb-1">{{ $cliente->entrenador->nombre }}</p>
                            <p class="text-muted small mb-0">{{ $cliente->entrenador->especialidad ?? 'Entrenamiento general' }}</p>
                        @else
                            <p class="text-muted mb-0">Aún no tienes un coach personal asignado.</p>
                        @endif
                        <hr>
                        <h5><i class="bi bi-activity"></i> Este mes</h5>
                        <p class="display-6 fw-bold mb-0">{{ $asistenciasMes }}</p>
                        <p class="text-muted small mb-0">visitas al gimnasio</p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="cuenta-card">
                        <h5><i class="bi bi-calendar-week"></i> Mis clases</h5>
                        @forelse ($clases as $clase)
                            <div class="lista-item">
                                <span>{{ $clase->nombre }}<small>{{ \App\Models\Clase::DIAS[$clase->dia_semana] }} · {{ $clase->horario }}</small></span>
                                <span class="small text-muted">{{ $clase->entrenador?->nombre }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No estás inscrito en ninguna clase.</p>
                        @endforelse
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="cuenta-card">
                        <h5><i class="bi bi-clock-history"></i> Últimas visitas</h5>
                        @forelse ($asistencias as $a)
                            <div class="lista-item">
                                <span>{{ $a->fecha->locale('es')->translatedFormat('l j \d\e F') }}</span>
                                <span class="small text-muted">{{ substr($a->hora_entrada, 0, 5) }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Aún no tienes visitas registradas.</p>
                        @endforelse
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="cuenta-card">
                        <h5><i class="bi bi-card-checklist"></i> Historial de membresías</h5>
                        @forelse ($membresias as $mem)
                            <div class="lista-item">
                                <span>{{ $mem->plan?->nombre }}<small>{{ $mem->fecha_inicio->format('d/m/Y') }} → {{ $mem->fecha_fin->format('d/m/Y') }}</small></span>
                                <span class="estado {{ $mem->estado === 'activo' && $mem->fecha_fin->gte(today()) ? 'ok' : 'no' }}">{{ $mem->estado === 'anulado' ? 'Anulada' : ($mem->fecha_fin->gte(today()) ? 'Vigente' : 'Vencida') }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Sin membresías registradas.</p>
                        @endforelse
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="cuenta-card">
                        <h5><i class="bi bi-bag"></i> Mis compras</h5>
                        @forelse ($compras as $v)
                            <div class="lista-item">
                                <span>{{ $v->detalles->pluck('descripcion')->implode(', ') }}<small>{{ $v->fecha->format('d/m/Y') }} · {{ $v->estado === 'pendiente' ? 'Pendiente de pago' : 'Pagado' }}</small></span>
                                <strong>{{ dinero($v->total) }}</strong>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Aún no tienes compras.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </main>
</body>

</html>
