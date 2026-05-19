<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WMFIT - Sistema de Gimnasio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg-dash: #f4f7fe;
            --sidebar-bg: #ffffff;
            --text-dark: #2b3674;
            --text-gray: #a3aed1;
            --primary-red: #ff4d4f;
            --primary-red-light: #fff1f0;
            --pub-primary: #2563eb;
            --pub-bg: #ffffff;
            --pub-gray: #f8fafc;
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.03);
            --shadow-md: 0 4px 15px rgba(0, 0, 0, 0.05);
            --border-radius: 12px;
        }
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: var(--pub-bg); color: #333; }
        .hidden { display: none !important; }
        * { box-sizing: border-box; }

        #dashboard-view { display: flex; height: 100vh; background-color: var(--bg-dash); overflow: hidden; }
        
        .sidebar { width: 250px; background: var(--sidebar-bg); display: flex; flex-direction: column; border-right: 1px solid #eaeaea; }
        .profile-section { padding: 1.5rem; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #f0f0f0; }
        .profile-avatar { width: 60px; height: 60px; border-radius: 50%; background: #1e293b; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: bold; }
        .profile-info h4 { margin: 0; font-size: 1.2rem; color: var(--text-dark); }
        .profile-info p { margin: 0; font-size: 0.9rem; color: var(--text-gray); }
        
        .sidebar-menu { list-style: none; padding: 1rem 0; margin: 0; overflow-y: auto; flex-grow: 1; }
        .menu-category { font-size: 0.9rem; color: var(--primary-red); font-weight: 700; text-transform: uppercase; padding: 0.8rem 1.5rem; letter-spacing: 0.5px; margin-top: 15px; }
        .sidebar-menu li { padding: 1rem 1.5rem; margin: 0.3rem 1rem; border-radius: 12px; cursor: pointer; color: #6b7280; font-size: 1.1rem; font-weight: 500; display: flex; align-items: center; gap: 15px; transition: 0.2s; }
        .sidebar-menu li i { font-size: 1.3rem; width: 25px; text-align: center; }
        .sidebar-menu li:hover { background: #f9fafb; color: var(--text-dark); }
        .sidebar-menu li.active { background: var(--primary-red-light); color: var(--primary-red); font-weight: 600; border-left: 4px solid var(--primary-red); border-radius: 0 12px 12px 0; margin-left: 0; padding-left: calc(1.5rem + 1rem - 4px); }

        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; padding: 1.5rem 2rem; }
        
        .tabs-container { background: white; padding: 0.5rem; border-radius: 12px; display: inline-flex; gap: 5px; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; width: fit-content; }
        .tab { padding: 0.6rem 1.5rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; color: #6b7280; cursor: pointer; border: none; background: transparent; }
        .tab.active { background: white; color: var(--text-dark); box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

        .page-content { flex: 1; overflow-y: auto; padding-right: 5px; }
        
        .dashboard-layout { display: grid; grid-template-columns: 3fr 1fr; gap: 1.5rem; padding-bottom: 2rem; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: white; border-radius: var(--border-radius); padding: 1.2rem; box-shadow: var(--shadow-sm); border-bottom: 3px solid #eee; position: relative; }
        .stat-card.cyan { border-bottom-color: #00d2d3; } .stat-card.green { border-bottom-color: #10ac84; }
        .stat-card.red { border-bottom-color: #ff6b6b; } .stat-card.purple { border-bottom-color: #9b59b6; }
        .stat-card.orange { border-bottom-color: #f39c12; } .stat-card.blue { border-bottom-color: #54a0ff; }
        
        .stat-title { font-size: 0.7rem; font-weight: 700; color: var(--text-gray); text-transform: uppercase; margin-bottom: 0.5rem; }
        .stat-value { font-size: 1.6rem; font-weight: 800; color: var(--text-dark); margin: 0; }
        .stat-icon { position: absolute; top: 1.2rem; right: 1.2rem; width: 35px; height: 35px; border-radius: 8px; display: flex; justify-content: center; align-items: center; font-size: 1.1rem; }
        
        .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; margin-top: 0.8rem; }
        .bg-cyan { background: #e0f7fa; color: #00acc1; } .bg-green { background: #e8f5e9; color: #2e7d32; }
        .bg-red { background: #ffebee; color: #c62828; } .bg-purple { background: #f3e5f5; color: #6a1b9a; }
        .icon-cyan { background: #e0f7fa; color: #00bcd4; } .icon-green { background: #e8f5e9; color: #4caf50; }
        .icon-red { background: #ffebee; color: #f44336; } .icon-purple { background: #f3e5f5; color: #9c27b0; }
        .icon-orange { background: #fff3e0; color: #ff9800; } .icon-blue { background: #e3f2fd; color: #2196f3; }

        .right-panel { display: flex; flex-direction: column; gap: 1rem; }
        .date-display { font-size: 0.9rem; font-weight: 600; color: var(--text-dark); margin-bottom: 0.5rem; }
        .summary-card { background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-sm); text-align: center; }
        .summary-title { font-size: 0.7rem; color: var(--text-gray); font-weight: 700; margin-bottom: 1rem; }
        .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .summary-item h2 { margin: 0; font-size: 1.8rem; color: var(--text-dark); }
        .summary-item p { margin: 0; font-size: 0.7rem; color: var(--text-gray); text-transform: uppercase; }

        .charts-section { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
        .chart-card { background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-sm); }
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .chart-header h3 { margin: 0; font-size: 1rem; color: var(--text-dark); }
        .chart-legend { list-style: none; padding: 0; margin: 0; font-size: 0.8rem; color: var(--text-gray); }
        .chart-legend li { margin-bottom: 0.5rem; display: flex; justify-content: space-between; }
        .chart-legend span { font-weight: 700; color: var(--text-dark); }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h2 { margin: 0; color: var(--text-dark); font-size: 1.5rem; }
        .btn-primary { background: var(--primary-red); color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .btn-primary:hover { opacity: 0.9; }
        .btn-logout { background: #6c757d; margin-left: 10px; }
        
        .table-card { background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-sm); overflow: hidden; }
        .table-header { padding: 1rem 1.5rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .table-header h3 { margin: 0; color: var(--text-dark); font-size: 1rem; }
        .search-box { display: flex; align-items: center; gap: 8px; background: #f5f5f5; padding: 0.5rem 1rem; border-radius: 8px; }
        .search-box input { border: none; background: transparent; outline: none; font-size: 0.9rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem 1.5rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #fafafa; font-weight: 600; color: var(--text-gray); font-size: 0.8rem; text-transform: uppercase; }
        td { color: var(--text-dark); font-size: 0.95rem; }
        tr:hover { background: #fafafa; }
        
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-active { background: #e8f5e9; color: #2e7d32; }
        .status-pending { background: #fff3e0; color: #f57c00; }
        .status-cancelled { background: #ffebee; color: #c62828; }
        
        .form-card { background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: var(--text-gray); }
        .form-group input, .form-group select { padding: 0.7rem; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95rem; font-family: 'Inter', sans-serif; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--primary-red); }

        .catalog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; }
        .catalog-card { background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-sm); overflow: hidden; transition: transform 0.2s; }
        .catalog-card:hover { transform: translateY(-5px); }
        .catalog-image { width: 100%; height: 150px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #888; }
        .catalog-body { padding: 1.2rem; }
        .catalog-title { margin: 0 0 0.5rem 0; color: var(--text-dark); font-size: 1rem; }
        .catalog-price { font-size: 1.3rem; font-weight: 800; color: var(--primary-red); margin: 0 0 0.5rem 0; }
        .catalog-stock { font-size: 0.85rem; color: var(--text-gray); }
        
        .schedule-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem; margin-bottom: 1.5rem; }
        .schedule-day { background: white; border-radius: var(--border-radius); padding: 1rem; text-align: center; box-shadow: var(--shadow-sm); }
        .schedule-day-name { font-weight: 700; color: var(--primary-red); font-size: 0.85rem; text-transform: uppercase; margin-bottom: 0.5rem; }
        .schedule-class { padding: 0.5rem; margin: 0.3rem 0; border-radius: 6px; font-size: 0.8rem; background: #f5f5f5; color: var(--text-dark); }
        .schedule-class.highlight { background: var(--primary-red-light); color: var(--primary-red); font-weight: 600; }
        
        .campaign-card { background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1rem; }
        .campaign-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
        .campaign-title { margin: 0; color: var(--text-dark); font-size: 1.1rem; }
        .campaign-metrics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
        .campaign-metric { text-align: center; }
        .campaign-metric-value { font-size: 1.5rem; font-weight: 800; color: var(--primary-red); }
        .campaign-metric-label { font-size: 0.8rem; color: var(--text-gray); text-transform: uppercase; margin-top: 0.3rem; }

        @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .dashboard-layout { grid-template-columns: 1fr; } .form-grid { grid-template-columns: 1fr; } .schedule-grid { grid-template-columns: repeat(2, 1fr); } }
    </style>
</head>
<body>

    <div id="dashboard-view">
        
        <aside class="sidebar">
            <div class="profile-section">
                <div class="profile-avatar">ED</div>
                <div class="profile-info">
                    <h4>Edgar</h4>
                    <p>Gladiadores Gym</p>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <div class="menu-category">Operaciones</div>
                <li class="active" onclick="showPage('reportes')"><i class="fas fa-chart-line"></i> Reportes</li>
                <li onclick="showPage('asistencias')"><i class="fas fa-calendar-check"></i> Asistencias</li>
                <li onclick="showPage('ventas')"><i class="fas fa-shopping-cart"></i> Ventas</li>
                <li onclick="showPage('gastos')"><i class="fas fa-file-invoice-dollar"></i> Gastos</li>
                
                <div class="menu-category">Gestión</div>
                <li onclick="showPage('catalogo')"><i class="fas fa-box-open"></i> Catálogo</li>
                <li onclick="showPage('entrenamiento')"><i class="fas fa-dumbbell"></i> Entrenamiento</li>
                <li onclick="showPage('marketing')"><i class="fas fa-bullhorn"></i> Marketing</li>
            </ul>
            
            <ul class="sidebar-menu" style="flex-grow: 0; border-top: 1px solid #eaeaea;">
                <li><i class="fas fa-desktop"></i> Kiosco</li>
                <li onclick="window.location.href='{{ url('/') }}'"><i class="fas fa-sign-out-alt"></i> Salir</li>
            </ul>
        </aside>

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

        </main>
    </div>

    <script>
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').innerText = new Date().toLocaleDateString('es-ES', dateOptions).replace(/^\w/, (c) => c.toUpperCase());

        function showPage(page) {
            document.querySelectorAll('.page-content').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.sidebar-menu li').forEach(l => l.classList.remove('active'));
            
            document.getElementById('page-' + page).classList.remove('hidden');
            event.currentTarget.classList.add('active');
        }

        let barChartInst, donutChartInst;

        function initCharts() {
            if(barChartInst) barChartInst.destroy();
            if(donutChartInst) donutChartInst.destroy();

            const ctxBar = document.getElementById('barChart').getContext('2d');
            barChartInst = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                    datasets: [{
                        label: 'Asistencias',
                        data: [0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: '#ff4d4f',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 1.0, ticks: { stepSize: 0.1 } }
                    },
                    plugins: { legend: { display: false } }
                }
            });

            const ctxDonut = document.getElementById('donutChart').getContext('2d');
            donutChartInst = new Chart(ctxDonut, {
                type: 'doughnut',
                data: {
                    labels: ['Ingresos', 'Gastos'],
                    datasets: [{
                        data: [1500, 440],
                        backgroundColor: ['#10ac84', '#ff6b6b'],
                        borderWidth: 0,
                        cutout: '75%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
        });
    </script>
</body>
</html>
