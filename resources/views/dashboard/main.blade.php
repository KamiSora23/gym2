<main class="main-content">

    <div id="page-reportes" class="page-content">
        <div class="tabs-container">
            <button class="tab active"><i class="fas fa-chart-pie"></i> Indicadores</button>
            <button class="tab"><i class="fas fa-coins"></i> Ingresos</button>
            <button class="tab"><i class="fas fa-store"></i> Ventas/Día</button>
            <button class="tab"><i class="fas fa-file-alt"></i> Reportes</button>
        </div>

        <div class="dashboard-layout">
            <div>
                <div class="stats-grid">
                    <div class="stat-card cyan">
                        <div class="stat-title">Clientes Activos</div>
                        <div class="stat-value">6</div>
                        <div class="stat-icon icon-cyan"><i class="fas fa-users"></i></div>
                        <span class="badge bg-cyan">↑ activos</span>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-title">Ingresos Semana</div>
                        <div class="stat-value">$1,500.00</div>
                        <div class="stat-icon icon-green"><i class="fas fa-dollar-sign"></i></div>
                        <span class="badge bg-green">↑ bruto</span>
                    </div>
                    <div class="stat-card red">
                        <div class="stat-title">Gastos Semana</div>
                        <div class="stat-value">$440.00</div>
                        <div class="stat-icon icon-red"><i class="fas fa-receipt"></i></div>
                        <span class="badge bg-red">↓ egresos</span>
                    </div>
                    <div class="stat-card purple">
                        <div class="stat-title">Ganancias Netas</div>
                        <div class="stat-value">$1,060.00</div>
                        <div class="stat-icon icon-purple"><i class="fas fa-chart-area"></i></div>
                        <span class="badge bg-cyan">⤓ neto</span>
                    </div>

                    <div class="stat-card cyan">
                        <div class="stat-title">Inscritos Semana</div>
                        <div class="stat-value">0</div>
                        <div class="stat-icon icon-cyan"><i class="fas fa-user-plus"></i></div>
                        <span class="badge bg-cyan">+ nuevos</span>
                    </div>
                    <div class="stat-card orange">
                        <div class="stat-title">Pagos Pendientes</div>
                        <div class="stat-value">2</div>
                        <div class="stat-icon icon-orange"><i class="fas fa-clock"></i></div>
                        <span class="badge bg-cyan">⏳ por cobrar</span>
                    </div>
                    <div class="stat-card blue">
                        <div class="stat-title">Asistencias Hoy</div>
                        <div class="stat-value">0</div>
                        <div class="stat-icon icon-blue"><i class="fas fa-calendar-day"></i></div>
                        <span class="badge bg-cyan">↑ check-in</span>
                    </div>
                    <div class="stat-card purple" style="border-bottom-color: #ff9ff3;">
                        <div class="stat-title">Renovaciones Mes</div>
                        <div class="stat-value">0</div>
                        <div class="stat-icon" style="background:#fce4ec; color:#e91e63;"><i class="fas fa-sync-alt"></i></div>
                        <span class="badge bg-cyan">↻ renovado</span>
                    </div>
                </div>

                <div class="charts-section">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Asistencias de la semana</h3>
                            <button style="background:var(--primary-red); color:white; border:none; padding:5px 15px; border-radius:5px;">Ir</button>
                        </div>
                        <canvas id="barChart" height="200"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Balance semanal</h3>
                        </div>
                        <div style="position: relative; height: 150px; display:flex; justify-content:center; align-items:center;">
                            <canvas id="donutChart"></canvas>
                            <div style="position: absolute; text-align: center;">
                                <div style="font-size: 0.6rem; color: #888;">NETO</div>
                                <div style="font-size: 1.2rem; font-weight: bold; color: #10ac84;">$1,060</div>
                            </div>
                        </div>
                        <ul class="chart-legend" style="margin-top: 1rem;">
                            <li>Ingresos <span style="color: #10ac84;">$1,500.00</span></li>
                            <li>Gastos <span style="color: #ff6b6b;">$440.00</span></li>
                            <li style="border-top:1px solid #eee; padding-top:5px; margin-top:5px;">Ganancia Neta <span>$1,060.00</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="right-panel">
                <div class="date-display" id="current-date">Lunes, 6 de abril de 2026</div>

                <div class="summary-card">
                    <div class="summary-title">RESUMEN OPERATIVO</div>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <h2>0</h2>
                            <p>Check-ins hoy</p>
                        </div>
                        <div class="summary-item">
                            <h2>2</h2>
                            <p>Pendientes</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 style="font-size:0.8rem; color:var(--text-gray); margin-bottom:10px;">Últimas transacciones</h4>
                    <div style="background:white; padding:2rem; text-align:center; border-radius:var(--border-radius); color:#a3aed1; font-size:0.9rem;">
                        Sin transacciones recientes
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="page-asistencias" class="page-content hidden">
        <div class="page-header">
            <h2><i class="fas fa-calendar-check" style="color:var(--primary-red); margin-right:10px;"></i>Asistencias</h2>
            <button class="btn-primary"><i class="fas fa-plus"></i> Registrar Asistencia</button>
        </div>

        <div class="form-card">
            <div class="form-grid">
                <div class="form-group">
                    <label>Buscar Cliente</label>
                    <div class="search-box">
                        <i class="fas fa-search" style="color:#888;"></i>
                        <input type="text" placeholder="Ingresa nombre o ID...">
                    </div>
                </div>
                <div class="form-group">
                    <label>Rango de Fechas</label>
                    <input type="date" value="2026-05-11">
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3>Registro de Asistencias</h3>
                <span style="color:var(--text-gray); font-size:0.9rem;">Total: 0 asistencias</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:3rem; color:var(--text-gray);">
                            <i class="fas fa-calendar-times" style="font-size:3rem; margin-bottom:1rem; display:block;"></i>
                            No hay asistencias registradas hoy
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="page-ventas" class="page-content hidden">
        <div class="page-header">
            <h2><i class="fas fa-shopping-cart" style="color:var(--primary-red); margin-right:10px;"></i>Ventas</h2>
            <button class="btn-primary"><i class="fas fa-plus"></i> Nueva Venta</button>
        </div>

        <div class="stats-grid" style="margin-bottom:1.5rem;">
            <div class="stat-card green">
                <div class="stat-title">Ventas Hoy</div>
                <div class="stat-value">$0.00</div>
                <div class="stat-icon icon-green"><i class="fas fa-cash-register"></i></div>
            </div>
            <div class="stat-card cyan">
                <div class="stat-title">Ventas Semana</div>
                <div class="stat-value">$1,500.00</div>
                <div class="stat-icon icon-cyan"><i class="fas fa-chart-line"></i></div>
            </div>
            <div class="stat-card purple">
                <div class="stat-title">Transacciones</div>
                <div class="stat-value">0</div>
                <div class="stat-icon icon-purple"><i class="fas fa-receipt"></i></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-title">Ticket Promedio</div>
                <div class="stat-value">$0.00</div>
                <div class="stat-icon icon-orange"><i class="fas fa-ticket-alt"></i></div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3>Historial de Ventas</h3>
                <div class="search-box">
                    <i class="fas fa-search" style="color:#888;"></i>
                    <input type="text" placeholder="Buscar venta...">
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Método de Pago</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:3rem; color:var(--text-gray);">
                            <i class="fas fa-shopping-basket" style="font-size:3rem; margin-bottom:1rem; display:block;"></i>
                            No hay ventas registradas
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="page-gastos" class="page-content hidden">
        <div class="page-header">
            <h2><i class="fas fa-file-invoice-dollar" style="color:var(--primary-red); margin-right:10px;"></i>Gastos</h2>
            <button class="btn-primary"><i class="fas fa-plus"></i> Nuevo Gasto</button>
        </div>

        <div class="stats-grid" style="margin-bottom:1.5rem;">
            <div class="stat-card red">
                <div class="stat-title">Gastos Hoy</div>
                <div class="stat-value">$0.00</div>
                <div class="stat-icon icon-red"><i class="fas fa-money-bill-wave"></i></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-title">Gastos Semana</div>
                <div class="stat-value">$440.00</div>
                <div class="stat-icon icon-orange"><i class="fas fa-chart-bar"></i></div>
            </div>
            <div class="stat-card blue">
                <div class="stat-title">Gastos Mes</div>
                <div class="stat-value">$1,800.00</div>
                <div class="stat-icon icon-blue"><i class="fas fa-calendar"></i></div>
            </div>
            <div class="stat-card purple">
                <div class="stat-title">Pendientes</div>
                <div class="stat-value">2</div>
                <div class="stat-icon icon-purple"><i class="fas fa-clock"></i></div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-grid">
                <div class="form-group">
                    <label>Categoría</label>
                    <select>
                        <option>Todas las categorías</option>
                        <option>Renta</option>
                        <option>Salarios</option>
                        <option>Servicios</option>
                        <option>Insumos</option>
                        <option>Mantenimiento</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Rango de Fechas</label>
                    <input type="date" value="2026-05-11">
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3>Registro de Gastos</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Categoría</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Comprobante</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Renta Local</td>
                        <td>Renta</td>
                        <td style="color:#c62828; font-weight:600;">-$800.00</td>
                        <td>01/05/2026</td>
                        <td><i class="fas fa-file-pdf" style="color:#c62828;"></i></td>
                        <td><span class="status-badge status-active">Pagado</span></td>
                    </tr>
                    <tr>
                        <td>Servicio de Electricidad</td>
                        <td>Servicios</td>
                        <td style="color:#c62828; font-weight:600;">-$150.00</td>
                        <td>05/05/2026</td>
                        <td><i class="fas fa-file-pdf" style="color:#c62828;"></i></td>
                        <td><span class="status-badge status-active">Pagado</span></td>
                    </tr>
                    <tr>
                        <td>Limpieza y Mantenimiento</td>
                        <td>Mantenimiento</td>
                        <td style="color:#c62828; font-weight:600;">-$90.00</td>
                        <td>08/05/2026</td>
                        <td><i class="fas fa-receipt" style="color:#666;"></i></td>
                        <td><span class="status-badge status-active">Pagado</span></td>
                    </tr>
                    <tr>
                        <td>Suministros de Oficina</td>
                        <td>Insumos</td>
                        <td style="color:#c62828; font-weight:600;">-$50.00</td>
                        <td>10/05/2026</td>
                        <td><i class="fas fa-receipt" style="color:#666;"></i></td>
                        <td><span class="status-badge status-pending">Pendiente</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="page-catalogo" class="page-content hidden">
        <div class="page-header">
            <h2><i class="fas fa-box-open" style="color:var(--primary-red); margin-right:10px;"></i>Catálogo</h2>
            <button class="btn-primary"><i class="fas fa-plus"></i> Nuevo Producto</button>
        </div>

        <div class="form-card">
            <div class="form-grid">
                <div class="form-group">
                    <label>Buscar Producto</label>
                    <div class="search-box">
                        <i class="fas fa-search" style="color:#888;"></i>
                        <input type="text" placeholder="Buscar por nombre...">
                    </div>
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <select>
                        <option>Todas las categorías</option>
                        <option>Suplementos</option>
                        <option>Accesorios</option>
                        <option>Ropa</option>
                        <option>Equipamiento</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="catalog-grid">
            <div class="catalog-card">
                <div class="catalog-image"><i class="fas fa-prescription-bottle-alt"></i></div>
                <div class="catalog-body">
                    <h3 class="catalog-title">Proteína Whey 2kg</h3>
                    <p class="catalog-price">$850.00</p>
                    <p class="catalog-stock"><i class="fas fa-box" style="margin-right:5px;"></i>Stock: 15 unidades</p>
                </div>
            </div>
            <div class="catalog-card">
                <div class="catalog-image"><i class="fas fa-dumbbell"></i></div>
                <div class="catalog-body">
                    <h3 class="catalog-title">Guantes de Entrenamiento</h3>
                    <p class="catalog-price">$250.00</p>
                    <p class="catalog-stock"><i class="fas fa-box" style="margin-right:5px;"></i>Stock: 30 unidades</p>
                </div>
            </div>
            <div class="catalog-card">
                <div class="catalog-image"><i class="fas fa-tshirt"></i></div>
                <div class="catalog-body">
                    <h3 class="catalog-title">Playera Gym Pro</h3>
                    <p class="catalog-price">$180.00</p>
                    <p class="catalog-stock"><i class="fas fa-box" style="margin-right:5px;"></i>Stock: 50 unidades</p>
                </div>
            </div>
            <div class="catalog-card">
                <div class="catalog-image"><i class="fas fa-flask"></i></div>
                <div class="catalog-body">
                    <h3 class="catalog-title">Creatina Monohidratada</h3>
                    <p class="catalog-price">$320.00</p>
                    <p class="catalog-stock"><i class="fas fa-box" style="margin-right:5px;"></i>Stock: 20 unidades</p>
                </div>
            </div>
            <div class="catalog-card">
                <div class="catalog-image"><i class="fas fa-bottle-water"></i></div>
                <div class="catalog-body">
                    <h3 class="catalog-title">Shaker Premium</h3>
                    <p class="catalog-price">$120.00</p>
                    <p class="catalog-stock"><i class="fas fa-box" style="margin-right:5px;"></i>Stock: 45 unidades</p>
                </div>
            </div>
            <div class="catalog-card">
                <div class="catalog-image"><i class="fas fa-heartbeat"></i></div>
                <div class="catalog-body">
                    <h3 class="catalog-title">Pre-entreno Extreme</h3>
                    <p class="catalog-price">$450.00</p>
                    <p class="catalog-stock"><i class="fas fa-box" style="margin-right:5px;"></i>Stock: 12 unidades</p>
                </div>
            </div>
        </div>
    </div>

    <div id="page-entrenamiento" class="page-content hidden">
        <div class="page-header">
            <h2><i class="fas fa-dumbbell" style="color:var(--primary-red); margin-right:10px;"></i>Entrenamiento</h2>
            <button class="btn-primary"><i class="fas fa-plus"></i> Nueva Clase</button>
        </div>

        <div class="stats-grid" style="margin-bottom:1.5rem;">
            <div class="stat-card cyan">
                <div class="stat-title">Clases Activas</div>
                <div class="stat-value">12</div>
                <div class="stat-icon icon-cyan"><i class="fas fa-calendar-alt"></i></div>
            </div>
            <div class="stat-card green">
                <div class="stat-title">Instructores</div>
                <div class="stat-value">5</div>
                <div class="stat-icon icon-green"><i class="fas fa-user-tie"></i></div>
            </div>
            <div class="stat-card purple">
                <div class="stat-title">Inscritos Hoy</div>
                <div class="stat-value">45</div>
                <div class="stat-icon icon-purple"><i class="fas fa-users"></i></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-title">Capacidad</div>
                <div class="stat-value">75%</div>
                <div class="stat-icon icon-orange"><i class="fas fa-chart-pie"></i></div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-grid">
                <div class="form-group">
                    <label>Semana</label>
                    <input type="date" value="2026-05-11">
                </div>
                <div class="form-group">
                    <label>Instructor</label>
                    <select>
                        <option>Todos los instructores</option>
                        <option>Carlos Ruiz</option>
                        <option>María López</option>
                        <option>Roberto Gómez</option>
                    </select>
                </div>
            </div>
        </div>

        <h3 style="color:var(--text-dark); margin-bottom:1rem; font-size:1.1rem;">Horario Semanal</h3>
        <div class="schedule-grid">
            <div class="schedule-day">
                <div class="schedule-day-name">Lunes</div>
                <div class="schedule-class highlight">06:00 - Yoga</div>
                <div class="schedule-class">08:00 - Spinning</div>
                <div class="schedule-class">17:00 - Crossfit</div>
                <div class="schedule-class">19:00 - Zumba</div>
            </div>
            <div class="schedule-day">
                <div class="schedule-day-name">Martes</div>
                <div class="schedule-class">07:00 - Funcional</div>
                <div class="schedule-class highlight">09:00 - Pilates</div>
                <div class="schedule-class">18:00 - Boxeo</div>
            </div>
            <div class="schedule-day">
                <div class="schedule-day-name">Miércoles</div>
                <div class="schedule-class">06:00 - Yoga</div>
                <div class="schedule-class">08:00 - Spinning</div>
                <div class="schedule-class highlight">17:00 - Crossfit</div>
            </div>
            <div class="schedule-day">
                <div class="schedule-day-name">Jueves</div>
                <div class="schedule-class highlight">07:00 - Funcional</div>
                <div class="schedule-class">09:00 - Pilates</div>
                <div class="schedule-class">19:00 - Zumba</div>
            </div>
            <div class="schedule-day">
                <div class="schedule-day-name">Viernes</div>
                <div class="schedule-class">06:00 - Yoga</div>
                <div class="schedule-class">08:00 - Spinning</div>
                <div class="schedule-class highlight">17:00 - Crossfit</div>
            </div>
            <div class="schedule-day">
                <div class="schedule-day-name">Sábado</div>
                <div class="schedule-class highlight">09:00 - Full Body</div>
                <div class="schedule-class">11:00 - Zumba</div>
            </div>
            <div class="schedule-day">
                <div class="schedule-day-name">Domingo</div>
                <div class="schedule-class">10:00 - Yoga</div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3>Lista de Instructores</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Instructor</th>
                        <th>Especialidad</th>
                        <th>Clases Asignadas</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Carlos Ruiz</td>
                        <td>Crossfit & Funcional</td>
                        <td>15</td>
                        <td>55-1234-5678</td>
                        <td><span class="status-badge status-active">Activo</span></td>
                    </tr>
                    <tr>
                        <td>María López</td>
                        <td>Yoga & Pilates</td>
                        <td>12</td>
                        <td>55-2345-6789</td>
                        <td><span class="status-badge status-active">Activo</span></td>
                    </tr>
                    <tr>
                        <td>Roberto Gómez</td>
                        <td>Spinning & Boxeo</td>
                        <td>10</td>
                        <td>55-3456-7890</td>
                        <td><span class="status-badge status-active">Activo</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="page-marketing" class="page-content hidden">
        <div class="page-header">
            <h2><i class="fas fa-bullhorn" style="color:var(--primary-red); margin-right:10px;"></i>Marketing</h2>
            <button class="btn-primary"><i class="fas fa-plus"></i> Nueva Campaña</button>
        </div>

        <div class="stats-grid" style="margin-bottom:1.5rem;">
            <div class="stat-card green">
                <div class="stat-title">Campañas Activas</div>
                <div class="stat-value">3</div>
                <div class="stat-icon icon-green"><i class="fas fa-bullhorn"></i></div>
            </div>
            <div class="stat-card cyan">
                <div class="stat-title">Leads Generados</div>
                <div class="stat-value">156</div>
                <div class="stat-icon icon-cyan"><i class="fas fa-user-plus"></i></div>
            </div>
            <div class="stat-card purple">
                <div class="stat-title">Conversiones</div>
                <div class="stat-value">28</div>
                <div class="stat-icon icon-purple"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-title">ROI</div>
                <div class="stat-value">320%</div>
                <div class="stat-icon icon-orange"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>

        <h3 style="color:var(--text-dark); margin-bottom:1rem; font-size:1.1rem;">Campañas Actuales</h3>

        <div class="campaign-card">
            <div class="campaign-header">
                <div>
                    <h3 class="campaign-title">Promoción Verano 2026</h3>
                    <p style="margin:0.3rem 0 0 0; color:var(--text-gray); font-size:0.9rem;">Válida hasta: 30/06/2026</p>
                </div>
                <span class="status-badge status-active">Activa</span>
            </div>
            <div class="campaign-metrics">
                <div class="campaign-metric">
                    <div class="campaign-metric-value">89</div>
                    <div class="campaign-metric-label">Leads</div>
                </div>
                <div class="campaign-metric">
                    <div class="campaign-metric-value">18</div>
                    <div class="campaign-metric-label">Conversiones</div>
                </div>
                <div class="campaign-metric">
                    <div class="campaign-metric-value">$4,500</div>
                    <div class="campaign-metric-label">Ingresos</div>
                </div>
            </div>
        </div>

        <div class="campaign-card">
            <div class="campaign-header">
                <div>
                    <h3 class="campaign-title">Descuento Referidos</h3>
                    <p style="margin:0.3rem 0 0 0; color:var(--text-gray); font-size:0.9rem;">Válida hasta: 31/05/2026</p>
                </div>
                <span class="status-badge status-active">Activa</span>
            </div>
            <div class="campaign-metrics">
                <div class="campaign-metric">
                    <div class="campaign-metric-value">45</div>
                    <div class="campaign-metric-label">Leads</div>
                </div>
                <div class="campaign-metric">
                    <div class="campaign-metric-value">8</div>
                    <div class="campaign-metric-label">Conversiones</div>
                </div>
                <div class="campaign-metric">
                    <div class="campaign-metric-value">$2,100</div>
                    <div class="campaign-metric-label">Ingresos</div>
                </div>
            </div>
        </div>

        <div class="campaign-card">
            <div class="campaign-header">
                <div>
                    <h3 class="campaign-title">Plan VIP - 2x1</h3>
                    <p style="margin:0.3rem 0 0 0; color:var(--text-gray); font-size:0.9rem;">Válida hasta: 15/06/2026</p>
                </div>
                <span class="status-badge status-active">Activa</span>
            </div>
            <div class="campaign-metrics">
                <div class="campaign-metric">
                    <div class="campaign-metric-value">22</div>
                    <div class="campaign-metric-label">Leads</div>
                </div>
                <div class="campaign-metric">
                    <div class="campaign-metric-value">2</div>
                    <div class="campaign-metric-label">Conversiones</div>
                </div>
                <div class="campaign-metric">
                    <div class="campaign-metric-value">$900</div>
                    <div class="campaign-metric-label">Ingresos</div>
                </div>
            </div>
        </div>

        <div class="table-card" style="margin-top:1.5rem;">
            <div class="table-header">
                <h3>Base de Datos de Leads</h3>
                <span style="color:var(--text-gray); font-size:0.9rem;">Total: 156 contactos</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Origen</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Juan Pérez</td>
                        <td>juan@email.com</td>
                        <td>55-1111-2222</td>
                        <td>Facebook</td>
                        <td>10/05/2026</td>
                        <td><span class="status-badge status-active">Caliente</span></td>
                    </tr>
                    <tr>
                        <td>Ana García</td>
                        <td>ana@email.com</td>
                        <td>55-2222-3333</td>
                        <td>Instagram</td>
                        <td>09/05/2026</td>
                        <td><span class="status-badge status-pending">Tibio</span></td>
                    </tr>
                    <tr>
                        <td>Pedro Sánchez</td>
                        <td>pedro@email.com</td>
                        <td>55-3333-4444</td>
                        <td>Referido</td>
                        <td>08/05/2026</td>
                        <td><span class="status-badge status-active">Cliente</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <form method="POST" action="{{ route('configuracion.store') }}">
        @csrf

        <div id="page-config" class="page-content hidden">

            <!-- HEADER -->
            <div class="page-header">
                <h2>
                    <i class="fas fa-cog" style="color:var(--primary-red); margin-right:10px;"></i>
                    Configuración del Sistema
                </h2>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>

            <!-- GRID PRINCIPAL -->
            <div class="config-grid">

                <!-- INFORMACIÓN GENERAL -->
                <div class="config-card">
                    <h3><i class="fas fa-sliders-h"></i> Información General</h3>

                    <div class="form-group">
                        <label>Nombre del Gimnasio</label>
                        <input type="text" name="nombre_gym"
                            value="{{ $config->nombre_gym ?? '' }}"
                            placeholder="GYM IMPULSO">
                    </div>

                    <div class="form-group">
                        <label>Correo del sistema</label>
                        <input type="email" name="email"
                            value="{{ $config->email ?? '' }}"
                            placeholder="admin@gym.com">
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono"
                            value="{{ $config->telefono ?? '' }}"
                            placeholder="999 999 999">
                    </div>

                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion"
                            value="{{ $config->direccion ?? '' }}"
                            placeholder="Av. Principal 123">
                    </div>

                    <!-- 🕒 HORARIO PROFESIONAL -->
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Horario del Gimnasio</label>

                        <div class="grid" style="display:grid; gap:10px;">

                            <input type="text" name="horario_lunes"
                                value="{{ $config->horario_lunes ?? '' }}"
                                placeholder="Lunes -> 6:00 AM - 10:00 PM">

                            <input type="text" name="horario_martes"
                                value="{{ $config->horario_martes ?? '' }}"
                                placeholder="Martes">

                            <input type="text" name="horario_miercoles"
                                value="{{ $config->horario_miercoles ?? '' }}"
                                placeholder="Miércoles">

                            <input type="text" name="horario_jueves"
                                value="{{ $config->horario_jueves ?? '' }}"
                                placeholder="Jueves">

                            <input type="text" name="horario_viernes"
                                value="{{ $config->horario_viernes ?? '' }}"
                                placeholder="Viernes">

                            <input type="text" name="horario_sabado"
                                value="{{ $config->horario_sabado ?? '' }}"
                                placeholder="Sábado">

                            <input type="text" name="horario_domingo"
                                value="{{ $config->horario_domingo ?? '' }}"
                                placeholder="Domingo">

                        </div>
                    </div>

                </div>

                <!-- BRANDING -->
                <div class="config-card">
                    <h3><i class="fas fa-image"></i> Branding</h3>

                    <div class="form-group">
                        <label>Logo del sistema</label>
                        <input type="file" name="logo">
                    </div>

                    <div class="preview-box">
                        <img src="{{ asset('img/logo.png') }}" alt="Logo preview">
                    </div>

                    <div class="form-group">
                        <label>Favicon</label>
                        <input type="file" name="favicon">
                    </div>
                </div>

                <!-- COLORES -->
                <div class="config-card">
                    <h3><i class="fas fa-palette"></i> Personalización de Colores</h3>

                    <div class="form-group">
                        <label>Color principal</label>
                        <input type="color" name="color_primario" value="#ff4d4f">
                    </div>

                    <div class="form-group">
                        <label>Color del sidebar</label>
                        <input type="color" name="color_sidebar" value="#111827">
                    </div>

                    <div class="form-group">
                        <label>Color secundario</label>
                        <input type="color" name="color_secundario" value="#ffffff">
                    </div>

                    <div class="color-preview">
                        <div class="color-box primary"></div>
                        <div class="color-box secondary"></div>
                        <div class="color-box sidebar"></div>
                    </div>
                </div>

                <!-- SEGURIDAD -->
                <div class="config-card">
                    <h3><i class="fas fa-lock"></i> Seguridad</h3>

                    <div class="form-group">
                        <label>Tiempo de sesión (minutos)</label>
                        <input type="number" name="session_time" value="60">
                    </div>

                    <div class="form-group">
                        <label>Verificación en login</label>
                        <select name="login_verification">
                            <option value="0">Desactivado</option>
                            <option value="1">Activado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Intentos de acceso</label>
                        <input type="number" name="login_attempts" value="5">
                    </div>
                </div>

                <!-- SISTEMA -->
                <div class="config-card full-width">
                    <h3><i class="fas fa-cogs"></i> Configuración del Sistema</h3>

                    <div class="system-options">

                        <label>
                            <input type="checkbox" name="maintenance_mode">
                            Modo mantenimiento
                        </label>

                        <label>
                            <input type="checkbox" name="logs_enabled">
                            Registro de logs activo
                        </label>

                        <label>
                            <input type="checkbox" name="notifications">
                            Notificaciones del sistema
                        </label>

                        <label>
                            <input type="checkbox" name="dark_mode">
                            Tema oscuro automático
                        </label>

                    </div>
                </div>

            </div>
        </div>
    </form>
</main>