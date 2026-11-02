@php
    $dinero = fn ($valor) => '$'.number_format((float) $valor, 2);
    $pagina = fn (string $nombre) => 'page-content'.($seccion === $nombre ? '' : ' hidden');
    $estadoClase = fn (?string $estado) => match ($estado) {
        'completado', 'pagado', 'activo' => 'status-active',
        'pendiente' => 'status-pending',
        'anulado', 'cancelado' => 'status-cancelled',
        default => 'status-info',
    };
@endphp

{{-- ================= RESUMEN ================= --}}
<section id="page-reportes" class="{{ $pagina('reportes') }}">
    <div class="page-header">
        <div>
            <h2><span class="icon"><i class="fas fa-chart-line"></i></span> Hola, {{ strtok($usuario->name, ' ') }}</h2>
            <p>Este es el resumen de la semana en {{ $config->nombre_gym }}.</p>
        </div>
        <div class="date-display" id="current-date">{{ now()->locale('es')->translatedFormat('l, j \d\e F \d\e Y') }}</div>
    </div>

    <div class="dashboard-layout">
        <div>
            <div class="stats-grid">
                <div class="stat-card cyan">
                    <div class="stat-title">Clientes activos</div>
                    <div class="stat-value">{{ $stats['clientes_activos'] }}</div>
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-hint">{{ $stats['clientes_total'] }} registrados</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-title">Ingresos semana</div>
                    <div class="stat-value">{{ $dinero($stats['ingresos_semana']) }}</div>
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-hint">Bruto</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-title">Gastos semana</div>
                    <div class="stat-value">{{ $dinero($stats['gastos_semana']) }}</div>
                    <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                    <div class="stat-hint">Egresos</div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-title">Ganancia neta</div>
                    <div class="stat-value">{{ $dinero($stats['neto_semana']) }}</div>
                    <div class="stat-icon"><i class="fas fa-chart-area"></i></div>
                    <div class="stat-hint">Ingresos − gastos</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-title">Inscritos semana</div>
                    <div class="stat-value">{{ $stats['inscritos_semana'] }}</div>
                    <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="stat-hint">Nuevos clientes</div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-title">Pagos pendientes</div>
                    <div class="stat-value">{{ $stats['pagos_pendientes'] }}</div>
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-hint">Por cobrar</div>
                </div>
                <div class="stat-card cyan">
                    <div class="stat-title">Asistencias hoy</div>
                    <div class="stat-value">{{ $stats['asistencias_hoy'] }}</div>
                    <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-hint">Check-ins</div>
                </div>
                <div class="stat-card pink">
                    <div class="stat-title">Membresías del mes</div>
                    <div class="stat-value">{{ $stats['renovaciones_mes'] }}</div>
                    <div class="stat-icon"><i class="fas fa-rotate"></i></div>
                    <div class="stat-hint">Altas y renovaciones</div>
                </div>
            </div>

            <div class="charts-section">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Asistencias de la semana</h3>
                        <button type="button" class="btn btn-light btn-sm" data-page="asistencias">Ver detalle</button>
                    </div>
                    <div class="chart-box">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Balance semanal</h3>
                    </div>
                    <div class="donut-box">
                        <canvas id="donutChart"></canvas>
                        <div class="donut-center">
                            <small>NETO</small>
                            <strong style="color: {{ $stats['neto_semana'] >= 0 ? '#059669' : '#dc2626' }}">{{ $dinero($stats['neto_semana']) }}</strong>
                        </div>
                    </div>
                    <ul class="chart-legend">
                        <li><span><i class="dot" style="background:#10b981"></i>Ingresos</span> <span class="val">{{ $dinero($stats['ingresos_semana']) }}</span></li>
                        <li><span><i class="dot" style="background:#ef4444"></i>Gastos</span> <span class="val">{{ $dinero($stats['gastos_semana']) }}</span></li>
                        <li class="total">Ganancia neta <span class="val">{{ $dinero($stats['neto_semana']) }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <div class="summary-card">
                <div class="summary-title">RESUMEN OPERATIVO</div>
                <div class="summary-grid">
                    <div class="summary-item">
                        <h2>{{ $stats['asistencias_hoy'] }}</h2>
                        <p>Check-ins hoy</p>
                    </div>
                    <div class="summary-item">
                        <h2>{{ $stats['pagos_pendientes'] }}</h2>
                        <p>Pendientes</p>
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <h4>Últimas transacciones</h4>
                @if ($ultimasVentas->isEmpty())
                    <p class="empty-state" style="padding:1.5rem 0 !important;">
                        <i class="fas fa-receipt"></i> Sin transacciones recientes
                    </p>
                @else
                    <ul class="tx-list">
                        @foreach ($ultimasVentas as $venta)
                            <li>
                                <span>
                                    {{ $venta->cliente ?? 'Cliente' }}
                                    <small>#{{ str_pad($venta->id_venta, 5, '0', STR_PAD_LEFT) }} · {{ \Illuminate\Support\Carbon::parse($venta->fecha)->format('d/m/Y') }}</small>
                                </span>
                                <strong>{{ $dinero($venta->total) }}</strong>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ================= ASISTENCIAS ================= --}}
<section id="page-asistencias" class="{{ $pagina('asistencias') }}">
    <div class="page-header">
        <div>
            <h2><span class="icon"><i class="fas fa-calendar-check"></i></span> Asistencias</h2>
            <p>Check-ins registrados hoy.</p>
        </div>
        @if ($puede['asistencias'])
            <button type="button" class="btn btn-primary" data-toggle-target="form-asistencia">
                <i class="fas fa-plus"></i> Registrar asistencia
            </button>
        @endif
    </div>

    @if ($puede['asistencias'])
        <form id="form-asistencia" method="POST" action="{{ route('asistencias.store') }}"
            class="form-card {{ $errors->asistencia->any() ? '' : 'hidden' }}">
            @csrf
            <h3><i class="fas fa-user-check"></i> Nuevo check-in</h3>
            @if ($clientes->isEmpty())
                <p style="color:var(--text-gray); margin:0;">Aún no hay clientes registrados.</p>
            @else
                <div class="form-grid">
                    <div class="form-group">
                        <label for="id_cliente">Cliente</label>
                        <select id="id_cliente" name="id_cliente" required>
                            <option value="">Selecciona un cliente…</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id_cliente }}" @selected(old('id_cliente') == $cliente->id_cliente)>
                                    {{ $cliente->nombre }} — {{ $cliente->dni }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_cliente', 'asistencia') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Fecha y hora</label>
                        <input type="text" value="{{ now()->format('d/m/Y H:i') }}" disabled>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-light" data-toggle-target="form-asistencia">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Registrar</button>
                </div>
            @endif
        </form>
    @endif

    <div class="table-card">
        <div class="table-header">
            <h3>Registro de hoy</h3>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="search" placeholder="Buscar cliente…" data-filter-table="tabla-asistencias">
            </div>
        </div>
        <div class="table-scroll">
            <table class="data-table" id="tabla-asistencias">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asistencias as $asistencia)
                        <tr>
                            <td>{{ $asistencia->cliente }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}</td>
                            <td>{{ substr($asistencia->hora_entrada, 0, 5) }}</td>
                            <td>{{ $asistencia->hora_salida ? substr($asistencia->hora_salida, 0, 5) : '—' }}</td>
                            <td>
                                @if ($asistencia->hora_salida)
                                    <span class="status-badge status-info">Finalizada</span>
                                @else
                                    <span class="status-badge status-active">En el gym</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                <i class="fas fa-calendar-xmark"></i>
                                No hay asistencias registradas hoy
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- ================= VENTAS ================= --}}
<section id="page-ventas" class="{{ $pagina('ventas') }}">
    <div class="page-header">
        <div>
            <h2><span class="icon"><i class="fas fa-cart-shopping"></i></span> Ventas</h2>
            <p>Historial de ventas y métricas de la semana.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card green">
            <div class="stat-title">Ventas hoy</div>
            <div class="stat-value">{{ $dinero($stats['ventas_hoy']) }}</div>
            <div class="stat-icon"><i class="fas fa-cash-register"></i></div>
        </div>
        <div class="stat-card cyan">
            <div class="stat-title">Ventas semana</div>
            <div class="stat-value">{{ $dinero($stats['ventas_semana']) }}</div>
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
        </div>
        <div class="stat-card purple">
            <div class="stat-title">Transacciones</div>
            <div class="stat-value">{{ $stats['num_ventas_semana'] }}</div>
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
        </div>
        <div class="stat-card orange">
            <div class="stat-title">Ticket promedio</div>
            <div class="stat-value">{{ $dinero($stats['ticket_promedio']) }}</div>
            <div class="stat-icon"><i class="fas fa-ticket"></i></div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Historial de ventas</h3>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="search" placeholder="Buscar venta…" data-filter-table="tabla-ventas">
            </div>
        </div>
        <div class="table-scroll">
            <table class="data-table" id="tabla-ventas">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ventas as $venta)
                        <tr>
                            <td>#{{ str_pad($venta->id_venta, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $venta->cliente ?? '—' }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($venta->fecha)->format('d/m/Y') }}</td>
                            <td class="money-in">{{ $dinero($venta->total) }}</td>
                            <td><span class="status-badge {{ $estadoClase($venta->estado) }}">{{ ucfirst($venta->estado) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                <i class="fas fa-basket-shopping"></i>
                                No hay ventas registradas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- ================= GASTOS ================= --}}
<section id="page-gastos" class="{{ $pagina('gastos') }}">
    <div class="page-header">
        <div>
            <h2><span class="icon"><i class="fas fa-file-invoice-dollar"></i></span> Gastos</h2>
            <p>Control de egresos del gimnasio.</p>
        </div>
        @if ($puede['gastos'])
            <button type="button" class="btn btn-primary" data-toggle-target="form-gasto">
                <i class="fas fa-plus"></i> Nuevo gasto
            </button>
        @endif
    </div>

    <div class="stats-grid">
        <div class="stat-card red">
            <div class="stat-title">Gastos hoy</div>
            <div class="stat-value">{{ $dinero($stats['gastos_hoy']) }}</div>
            <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
        <div class="stat-card orange">
            <div class="stat-title">Gastos semana</div>
            <div class="stat-value">{{ $dinero($stats['gastos_semana']) }}</div>
            <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-title">Gastos mes</div>
            <div class="stat-value">{{ $dinero($stats['gastos_mes']) }}</div>
            <div class="stat-icon"><i class="fas fa-calendar"></i></div>
        </div>
        <div class="stat-card purple">
            <div class="stat-title">Registros</div>
            <div class="stat-value">{{ $gastos->count() }}</div>
            <div class="stat-icon"><i class="fas fa-list"></i></div>
        </div>
    </div>

    @if ($puede['gastos'])
        <form id="form-gasto" method="POST" action="{{ route('gastos.store') }}"
            class="form-card {{ $errors->gasto->any() ? '' : 'hidden' }}">
            @csrf
            <h3><i class="fas fa-file-circle-plus"></i> Registrar gasto</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="concepto">Concepto</label>
                    <input id="concepto" type="text" name="concepto" value="{{ old('concepto') }}" placeholder="Ej. Renta del local" required>
                    @error('concepto', 'gasto') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="monto">Monto</label>
                    <input id="monto" type="number" step="0.01" min="0.01" name="monto" value="{{ old('monto') }}" placeholder="0.00" required>
                    @error('monto', 'gasto') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input id="fecha" type="date" name="fecha" value="{{ old('fecha', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required>
                    @error('fecha', 'gasto') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-light" data-toggle-target="form-gasto">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar gasto</button>
            </div>
        </form>
    @endif

    <div class="table-card">
        <div class="table-header">
            <h3>Registro de gastos</h3>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="search" placeholder="Buscar gasto…" data-filter-table="tabla-gastos">
            </div>
        </div>
        <div class="table-scroll">
            <table class="data-table" id="tabla-gastos">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gastos as $gasto)
                        <tr>
                            <td>{{ $gasto->concepto }}</td>
                            <td class="money-out">-{{ $dinero($gasto->monto) }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($gasto->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $gasto->registrado_por ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="fas fa-file-invoice"></i>
                                No hay gastos registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- ================= CATÁLOGO ================= --}}
<section id="page-catalogo" class="{{ $pagina('catalogo') }}">
    <div class="page-header">
        <div>
            <h2><span class="icon"><i class="fas fa-box-open"></i></span> Catálogo</h2>
            <p>Productos e inventario disponible.</p>
        </div>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="search" placeholder="Buscar producto…" data-filter-cards="catalog-grid">
        </div>
    </div>

    @if ($productos->isEmpty())
        <div class="table-card">
            <p class="empty-state" style="margin:0;">
                <i class="fas fa-box-open"></i>
                Aún no hay productos en el catálogo
            </p>
        </div>
    @else
        <div class="catalog-grid" id="catalog-grid">
            @foreach ($productos as $producto)
                <div class="catalog-card" data-search="{{ \Illuminate\Support\Str::lower($producto->nombre.' '.$producto->tipo) }}">
                    <div class="catalog-image"><i class="fas fa-dumbbell"></i></div>
                    <div class="catalog-body">
                        <div class="catalog-type">{{ $producto->tipo ?? 'Sin categoría' }}</div>
                        <h3 class="catalog-title">{{ $producto->nombre }}</h3>
                        <p class="catalog-price">{{ $dinero($producto->precio) }}</p>
                        <p class="catalog-stock">
                            <i class="fas fa-box" style="margin-right:5px;"></i>
                            Stock: {{ (int) $producto->stock }} {{ (int) $producto->stock === 1 ? 'unidad' : 'unidades' }}
                            @if ((int) $producto->stock <= 5)
                                <span class="status-badge status-pending" style="margin-left:4px;">Bajo</span>
                            @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

{{-- ================= ENTRENAMIENTO (DEMO) ================= --}}
<section id="page-entrenamiento" class="{{ $pagina('entrenamiento') }}">
    <div class="page-header">
        <div>
            <h2><span class="icon"><i class="fas fa-dumbbell"></i></span> Entrenamiento</h2>
            <p>Horario semanal de clases e instructores.</p>
        </div>
        <span class="demo-tag"><i class="fas fa-flask"></i> Datos de ejemplo</span>
    </div>

    @php
        $horario = [
            'Lunes' => [['06:00', 'Yoga', true], ['08:00', 'Spinning'], ['17:00', 'Crossfit'], ['19:00', 'Zumba']],
            'Martes' => [['07:00', 'Funcional'], ['09:00', 'Pilates', true], ['18:00', 'Boxeo']],
            'Miércoles' => [['06:00', 'Yoga'], ['08:00', 'Spinning'], ['17:00', 'Crossfit', true]],
            'Jueves' => [['07:00', 'Funcional', true], ['09:00', 'Pilates'], ['19:00', 'Zumba']],
            'Viernes' => [['06:00', 'Yoga'], ['08:00', 'Spinning'], ['17:00', 'Crossfit', true]],
            'Sábado' => [['09:00', 'Full Body', true], ['11:00', 'Zumba']],
            'Domingo' => [['10:00', 'Yoga']],
        ];
    @endphp

    <div class="schedule-grid">
        @foreach ($horario as $dia => $clases)
            <div class="schedule-day">
                <div class="schedule-day-name">{{ $dia }}</div>
                @foreach ($clases as $clase)
                    <div class="schedule-class {{ ($clase[2] ?? false) ? 'highlight' : '' }}">{{ $clase[0] }} · {{ $clase[1] }}</div>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Instructores</h3>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Instructor</th>
                        <th>Especialidad</th>
                        <th>Clases asignadas</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ([['Carlos Ruiz', 'Crossfit & Funcional', 15], ['María López', 'Yoga & Pilates', 12], ['Roberto Gómez', 'Spinning & Boxeo', 10]] as [$nombre, $especialidad, $clases])
                        <tr>
                            <td>{{ $nombre }}</td>
                            <td>{{ $especialidad }}</td>
                            <td>{{ $clases }}</td>
                            <td><span class="status-badge status-active">Activo</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- ================= MARKETING (DEMO) ================= --}}
<section id="page-marketing" class="{{ $pagina('marketing') }}">
    <div class="page-header">
        <div>
            <h2><span class="icon"><i class="fas fa-bullhorn"></i></span> Marketing</h2>
            <p>Campañas activas y captación de clientes.</p>
        </div>
        <span class="demo-tag"><i class="fas fa-flask"></i> Datos de ejemplo</span>
    </div>

    <div class="campaign-grid">
        @foreach ([
            ['Promoción Verano', '30/06', 89, 18, '$4,500'],
            ['Descuento Referidos', '31/05', 45, 8, '$2,100'],
            ['Plan VIP 2x1', '15/06', 22, 2, '$900'],
        ] as [$titulo, $vigencia, $leads, $conversiones, $ingresos])
            <div class="campaign-card">
                <div class="campaign-header">
                    <div>
                        <h3 class="campaign-title">{{ $titulo }}</h3>
                        <p>Válida hasta: {{ $vigencia }}</p>
                    </div>
                    <span class="status-badge status-active">Activa</span>
                </div>
                <div class="campaign-metrics">
                    <div class="campaign-metric">
                        <div class="campaign-metric-value">{{ $leads }}</div>
                        <div class="campaign-metric-label">Leads</div>
                    </div>
                    <div class="campaign-metric">
                        <div class="campaign-metric-value">{{ $conversiones }}</div>
                        <div class="campaign-metric-label">Conversiones</div>
                    </div>
                    <div class="campaign-metric">
                        <div class="campaign-metric-value">{{ $ingresos }}</div>
                        <div class="campaign-metric-label">Ingresos</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- ================= USUARIOS (SUPER ADMIN) ================= --}}
@if ($puede['usuarios'])
    <section id="page-usuarios" class="{{ $pagina('usuarios') }}">
        <div class="page-header">
            <div>
                <h2><span class="icon"><i class="fas fa-users-gear"></i></span> Usuarios y roles</h2>
                <p>Crea cuentas para tu personal y asigna sus permisos.</p>
            </div>
            <button type="button" class="btn btn-primary" data-toggle-target="form-usuario">
                <i class="fas fa-user-plus"></i> Nuevo usuario
            </button>
        </div>

        <div class="stats-grid">
            @foreach ($roles->whereIn('nombre', ['super_admin', 'admin', 'recepcionista', 'cliente']) as $rol)
                <div class="stat-card {{ ['super_admin' => 'purple', 'admin' => 'red', 'recepcionista' => 'blue', 'cliente' => 'green'][$rol->nombre] }}">
                    <div class="stat-title">{{ $rol->etiqueta }}</div>
                    <div class="stat-value">{{ $usuarios->where('role_id', $rol->id)->count() }}</div>
                    <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
                </div>
            @endforeach
        </div>

        @php $creando = $errors->usuario->any() && ! old('_usuario_id'); @endphp
        <form id="form-usuario" method="POST" action="{{ route('admin.usuarios.store') }}"
            class="form-card {{ $creando ? '' : 'hidden' }}">
            @csrf
            <h3><i class="fas fa-user-plus"></i> Nuevo usuario</h3>
            <div class="form-grid cols-4">
                <div class="form-group">
                    <label for="nu-name">Nombre</label>
                    <input id="nu-name" type="text" name="name" value="{{ $creando ? old('name') : '' }}" required>
                </div>
                <div class="form-group">
                    <label for="nu-email">Correo</label>
                    <input id="nu-email" type="email" name="email" value="{{ $creando ? old('email') : '' }}" required>
                </div>
                <div class="form-group">
                    <label for="nu-password">Contraseña</label>
                    <input id="nu-password" type="password" name="password" autocomplete="new-password" required>
                </div>
                <div class="form-group">
                    <label for="nu-password2">Confirmar contraseña</label>
                    <input id="nu-password2" type="password" name="password_confirmation" autocomplete="new-password" required>
                </div>
                <div class="form-group">
                    <label for="nu-role">Rol</label>
                    <select id="nu-role" name="role_id" required>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}" @selected($creando ? old('role_id') == $rol->id : $rol->nombre === 'recepcionista')>{{ $rol->etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if ($creando)
                <div class="alert alert-error" style="margin:1rem 0 0;">
                    <i class="fas fa-circle-exclamation"></i>
                    <ul>
                        @foreach ($errors->usuario->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="form-actions">
                <button type="button" class="btn btn-light" data-toggle-target="form-usuario">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear usuario</button>
            </div>
        </form>

        <div class="table-card">
            <div class="table-header">
                <h3>Usuarios del sistema <span class="count">({{ $usuarios->count() }})</span></h3>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="search" placeholder="Buscar usuario…" data-filter-table="tabla-usuarios">
                </div>
            </div>
            <div class="table-scroll">
                <table class="data-table" id="tabla-usuarios">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Registrado</th>
                            <th style="text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $u)
                            @php $editando = $errors->usuario->any() && (int) old('_usuario_id') === $u->id; @endphp
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</div>
                                        <div>
                                            {{ $u->name }} @if ($u->is($usuario)) <span class="status-badge status-info">Tú</span> @endif
                                            <small>{{ $u->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge {{ $u->isSuperAdmin() ? 'status-dark' : ($u->isStaff() ? 'status-info' : 'status-active') }}">
                                        {{ $u->role?->etiqueta ?? 'Sin rol' }}
                                    </span>
                                </td>
                                <td>{{ $u->created_at?->format('d/m/Y') }}</td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button" class="btn btn-light btn-sm btn-icon" title="Editar"
                                            data-toggle-target="editar-{{ $u->id }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        @unless ($u->is($usuario))
                                            <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}"
                                                data-confirm="¿Eliminar a {{ $u->name }}? Esta acción no se puede deshacer.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                            <tr id="editar-{{ $u->id }}" class="edit-row {{ $editando ? '' : 'hidden' }}">
                                <td colspan="4">
                                    <form method="POST" action="{{ route('admin.usuarios.update', $u) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="_usuario_id" value="{{ $u->id }}">
                                        <div class="form-grid cols-4">
                                            <div class="form-group">
                                                <label>Nombre</label>
                                                <input type="text" name="name" value="{{ $editando ? old('name') : $u->name }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Correo</label>
                                                <input type="email" name="email" value="{{ $editando ? old('email') : $u->email }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Rol</label>
                                                <select name="role_id" required>
                                                    @foreach ($roles as $rol)
                                                        <option value="{{ $rol->id }}" @selected(($editando ? old('role_id') : $u->role_id) == $rol->id)>{{ $rol->etiqueta }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Nueva contraseña <small style="font-weight:400;">(opcional)</small></label>
                                                <input type="password" name="password" autocomplete="new-password">
                                            </div>
                                            <div class="form-group">
                                                <label>Confirmar contraseña</label>
                                                <input type="password" name="password_confirmation" autocomplete="new-password">
                                            </div>
                                        </div>
                                        @if ($editando)
                                            <div class="alert alert-error" style="margin:1rem 0 0;">
                                                <i class="fas fa-circle-exclamation"></i>
                                                <ul>
                                                    @foreach ($errors->usuario->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <div class="form-actions">
                                            <button type="button" class="btn btn-light btn-sm" data-toggle-target="editar-{{ $u->id }}">Cancelar</button>
                                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar cambios</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endif

{{-- ================= CONFIGURACIÓN ================= --}}
@if ($puede['config'])
    <section id="page-config" class="{{ $pagina('config') }}">
        <form method="POST" action="{{ route('configuracion.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="page-header">
                <div>
                    <h2><span class="icon"><i class="fas fa-gear"></i></span> Configuración del sistema</h2>
                    <p>Identidad, contacto y horarios que se muestran en el sitio público.</p>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>

            @if ($errors->default->any())
                <div class="alert alert-error">
                    <i class="fas fa-circle-exclamation"></i>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="config-grid">

                <div class="config-card">
                    <h3><i class="fas fa-sliders"></i> Información general</h3>

                    <div class="form-group">
                        <label for="nombre_gym">Nombre del gimnasio</label>
                        <input id="nombre_gym" type="text" name="nombre_gym" required
                            value="{{ old('nombre_gym', $config->nombre_gym) }}" placeholder="GYM IMPULSO">
                    </div>

                    <div class="form-group">
                        <label for="slogan">Slogan</label>
                        <input id="slogan" type="text" name="slogan"
                            value="{{ old('slogan', $config->slogan) }}" placeholder="Tu mejor versión empieza aquí">
                    </div>

                    <div class="form-group">
                        <label for="email">Correo de contacto</label>
                        <input id="email" type="email" name="email"
                            value="{{ old('email', $config->email) }}" placeholder="info@gym.com">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input id="telefono" type="text" name="telefono"
                            value="{{ old('telefono', $config->telefono) }}" placeholder="999 999 999">
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input id="direccion" type="text" name="direccion"
                            value="{{ old('direccion', $config->direccion) }}" placeholder="Av. Principal 123">
                    </div>
                </div>

                <div class="config-card">
                    <h3><i class="fas fa-clock"></i> Horario de atención</h3>

                    <div class="horario-grid">
                        @foreach (\App\Models\Configuracion::DIAS as $clave => $dia)
                            <span>{{ $dia }}</span>
                            <div class="form-group" style="margin:0;">
                                <input type="text" name="horario_{{ $clave }}"
                                    value="{{ old('horario_'.$clave, $config->{'horario_'.$clave}) }}"
                                    placeholder="6:00 AM - 10:00 PM / Cerrado">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="config-card">
                    <h3><i class="fas fa-image"></i> Branding</h3>

                    <div class="preview-box">
                        <img src="{{ $config->logo_url }}" alt="Logo actual">
                        <div style="font-size:.85rem; color:var(--text-gray);">Logo actual<br><small>PNG, JPG, WEBP o SVG · máx. 2 MB</small></div>
                    </div>

                    <div class="form-group">
                        <label for="logo">Cambiar logo</label>
                        <input id="logo" type="file" name="logo" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="favicon">Favicon <small style="font-weight:400;">(PNG, ICO o SVG)</small></label>
                        <input id="favicon" type="file" name="favicon" accept=".png,.ico,.svg">
                    </div>
                </div>

                <div class="config-card">
                    <h3><i class="fas fa-palette"></i> Colores</h3>

                    @foreach (['color_primario' => 'Color principal', 'color_sidebar' => 'Color del menú lateral', 'color_secundario' => 'Color secundario'] as $campo => $etiqueta)
                        <div class="form-group">
                            <div class="color-row">
                                <label for="{{ $campo }}">{{ $etiqueta }}</label>
                                <input id="{{ $campo }}" type="color" name="{{ $campo }}"
                                    value="{{ old($campo, $config->{$campo}) }}">
                            </div>
                        </div>
                    @endforeach

                    <p style="font-size:.8rem; color:var(--text-soft); margin:0;">Los colores se aplican al panel al guardar.</p>
                </div>

                <div class="config-card full-width">
                    <h3><i class="fas fa-share-nodes"></i> Redes sociales</h3>

                    <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                        @foreach (['facebook' => 'fa-facebook', 'instagram' => 'fa-instagram', 'tiktok' => 'fa-tiktok'] as $red => $icono)
                            <div class="form-group">
                                <label for="{{ $red }}"><i class="fab {{ $icono }}"></i> {{ ucfirst($red) }}</label>
                                <input id="{{ $red }}" type="url" name="{{ $red }}"
                                    value="{{ old($red, $config->{$red}) }}" placeholder="https://{{ $red }}.com/tugym">
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </form>
    </section>
@endif
