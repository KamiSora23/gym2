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
        /* --- ESTILOS GLOBALES --- */
        :root {
            /* Colores Dashboard Privado */
            --bg-dash: #f4f7fe;
            --sidebar-bg: #ffffff;
            --text-dark: #2b3674;
            --text-gray: #a3aed1;
            --primary-red: #ff4d4f;
            --primary-red-light: #fff1f0;
            /* Colores Vista Pública */
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

        /* ==========================================
           VISTA PÚBLICA (Estilo limpio, minimalista)
           ========================================== */
        #public-view header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.5rem 5%; background: white; border-bottom: 1px solid #eaeaea;
        }
        #public-view .logo { font-size: 1.5rem; font-weight: 800; color: var(--pub-primary); text-transform: uppercase; }
        .btn-public {
            background-color: var(--pub-primary); color: white; border: none;
            padding: 0.6rem 1.5rem; border-radius: 6px; font-weight: 600; cursor: pointer;
        }
        .hero-public { text-align: center; padding: 4rem 2rem; background-color: var(--pub-gray); margin: 2rem 5%; border-radius: 12px; }
        .hero-public h1 { font-size: 2.5rem; color: #1e293b; margin-bottom: 1rem; }
        .hero-public p { color: #64748b; max-width: 600px; margin: 0 auto; line-height: 1.6; }
        
        .planes-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; padding: 2rem 5%; }
        .plan-card { border: 1px solid #eaeaea; padding: 2rem; border-radius: 8px; text-align: center; }
        .plan-card h3 { color: #1e293b; margin-top: 0; }
        .plan-card p { color: #64748b; font-size: 0.9rem; }

        /* ==========================================
           MODAL DE LOGIN
           ========================================== */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4); display: flex; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal-content { background: white; padding: 2.5rem; border-radius: 12px; width: 100%; max-width: 380px; text-align: center; box-shadow: var(--shadow-md); }
        .btn-demo {
            display: block; width: 100%; padding: 0.8rem; margin-bottom: 10px;
            border: 1px solid #eaeaea; background: white; border-radius: 6px; cursor: pointer; font-weight: 600; color: #333;
        }
        .btn-demo:hover { background: var(--pub-gray); }
        .btn-demo.admin { border-color: var(--primary-red); color: var(--primary-red); }

        /* ==========================================
           VISTA PRIVADA (DASHBOARD ESTILO IMAGEN 2)
           ========================================== */
        #dashboard-view { display: flex; height: 100vh; background-color: var(--bg-dash); overflow: hidden; }
        
        /* SIDEBAR */
        .sidebar { width: 250px; background: var(--sidebar-bg); display: flex; flex-direction: column; border-right: 1px solid #eaeaea; }
        .profile-section { padding: 1.5rem; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #f0f0f0; }
        .profile-avatar { width: 45px; height: 45px; border-radius: 50%; background: #ccc; object-fit: cover; }
        .profile-info h4 { margin: 0; font-size: 0.95rem; color: var(--text-dark); }
        .profile-info p { margin: 0; font-size: 0.75rem; color: var(--text-gray); }
        
        .sidebar-menu { list-style: none; padding: 1rem 0; margin: 0; overflow-y: auto; flex-grow: 1; }
        .menu-category { font-size: 0.7rem; color: var(--primary-red); font-weight: 700; text-transform: uppercase; padding: 0.5rem 1.5rem; letter-spacing: 0.5px; margin-top: 10px; }
        .sidebar-menu li { padding: 0.7rem 1.5rem; margin: 0.2rem 1rem; border-radius: 8px; cursor: pointer; color: #6b7280; font-size: 0.9rem; font-weight: 500; display: flex; align-items: center; gap: 12px; transition: 0.2s; }
        .sidebar-menu li i { font-size: 1.1rem; width: 20px; text-align: center; }
        .sidebar-menu li:hover { background: #f9fafb; color: var(--text-dark); }
        .sidebar-menu li.active { background: var(--primary-red-light); color: var(--primary-red); font-weight: 600; border-left: 3px solid var(--primary-red); border-radius: 0 8px 8px 0; margin-left: 0; padding-left: calc(1.5rem + 1rem - 3px); }

        /* CONTENIDO PRINCIPAL */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; padding: 1.5rem 2rem; }
        
        /* TABS SUPERIORES */
        .tabs-container { background: white; padding: 0.5rem; border-radius: 12px; display: inline-flex; gap: 5px; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; width: fit-content; }
        .tab { padding: 0.6rem 1.5rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; color: #6b7280; cursor: pointer; border: none; background: transparent; }
        .tab.active { background: white; color: var(--text-dark); box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

        .dashboard-layout { display: grid; grid-template-columns: 3fr 1fr; gap: 1.5rem; overflow-y: auto; padding-right: 5px; padding-bottom: 2rem; }
        
        /* GRID DE TARJETAS (INDICADORES) */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: white; border-radius: var(--border-radius); padding: 1.2rem; box-shadow: var(--shadow-sm); border-bottom: 3px solid #eee; position: relative; }
        .stat-card.cyan { border-bottom-color: #00d2d3; } .stat-card.green { border-bottom-color: #10ac84; }
        .stat-card.red { border-bottom-color: #ff6b6b; } .stat-card.purple { border-bottom-color: #9b59b6; }
        .stat-card.orange { border-bottom-color: #f39c12; } .stat-card.blue { border-bottom-color: #54a0ff; }
        
        .stat-title { font-size: 0.7rem; font-weight: 700; color: var(--text-gray); text-transform: uppercase; margin-bottom: 0.5rem; }
        .stat-value { font-size: 1.6rem; font-weight: 800; color: var(--text-dark); margin: 0; }
        .stat-icon { position: absolute; top: 1.2rem; right: 1.2rem; width: 35px; height: 35px; border-radius: 8px; display: flex; justify-content: center; align-items: center; font-size: 1.1rem; }
        
        /* Badge (Pastillas inferiores) */
        .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; margin-top: 0.8rem; }
        .bg-cyan { background: #e0f7fa; color: #00acc1; } .bg-green { background: #e8f5e9; color: #2e7d32; }
        .bg-red { background: #ffebee; color: #c62828; } .bg-purple { background: #f3e5f5; color: #6a1b9a; }
        .icon-cyan { background: #e0f7fa; color: #00bcd4; } .icon-green { background: #e8f5e9; color: #4caf50; }
        .icon-red { background: #ffebee; color: #f44336; } .icon-purple { background: #f3e5f5; color: #9c27b0; }
        .icon-orange { background: #fff3e0; color: #ff9800; } .icon-blue { background: #e3f2fd; color: #2196f3; }

        /* PANEL DERECHO */
        .right-panel { display: flex; flex-direction: column; gap: 1rem; }
        .date-display { font-size: 0.9rem; font-weight: 600; color: var(--text-dark); margin-bottom: 0.5rem; }
        .summary-card { background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-sm); text-align: center; }
        .summary-title { font-size: 0.7rem; color: var(--text-gray); font-weight: 700; margin-bottom: 1rem; }
        .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .summary-item h2 { margin: 0; font-size: 1.8rem; color: var(--text-dark); }
        .summary-item p { margin: 0; font-size: 0.7rem; color: var(--text-gray); text-transform: uppercase; }

        /* GRÁFICOS */
        .charts-section { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
        .chart-card { background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-sm); }
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .chart-header h3 { margin: 0; font-size: 1rem; color: var(--text-dark); }
        .chart-legend { list-style: none; padding: 0; margin: 0; font-size: 0.8rem; color: var(--text-gray); }
        .chart-legend li { margin-bottom: 0.5rem; display: flex; justify-content: space-between; }
        .chart-legend span { font-weight: 700; color: var(--text-dark); }
        
        /* Ajustes Responsive */
        @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .dashboard-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div id="public-view">
        <header>
            <div class="logo">GYM SYSTEM</div>
            <button class="btn-public" onclick="toggleModal()">Ingresar / Registro</button>
        </header>

        <section class="hero-public">
            <h1>Bienvenido a tu mejor versión</h1>
            <p><strong>Misión:</strong> Transformar vidas a través del deporte.<br>
               <strong>Visión:</strong> Ser el gimnasio líder en tecnología y bienestar.</p>
        </section>

        <section style="padding-top: 0;">
            <h2 style="text-align: center; color: #1e293b; margin-bottom: 2rem;">Nuestros Planes</h2>
            <div class="planes-grid">
                <div class="plan-card">
                    <h3>Plan Básico</h3>
                    <p style="font-size: 1.5rem; color: #333; margin: 1rem 0;">$30/mes</p>
                </div>
                <div class="plan-card">
                    <h3>Plan Pro</h3>
                    <p style="font-size: 1.5rem; color: #333; margin: 1rem 0;">$50/mes</p>
                </div>
                <div class="plan-card">
                    <h3>Plan VIP</h3>
                    <p style="font-size: 1.5rem; color: #333; margin: 1rem 0;">$80/mes</p>
                </div>
            </div>
        </section>
    </div>

    <div id="login-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <h2 style="margin-top:0; color: #1e293b;">Acceso al Sistema</h2>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem;">Selecciona un rol para ver el dashboard.</p>
            <button class="btn-demo admin" onclick="simulateLogin('admin')">Ingresar como Administrador</button>
            <button class="btn-demo" onclick="closeModal()" style="margin-top: 1rem;">Cancelar</button>
        </div>
    </div>

    <div id="dashboard-view" class="hidden">
        
        <aside class="sidebar">
            <div class="profile-section">
                <img src="https://ui-avatars.com/api/?name=Edgar&background=1e293b&color=fff" alt="Perfil" class="profile-avatar">
                <div class="profile-info">
                    <h4>Edgar</h4>
                    <p>Gladiadores Gym</p>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <div class="menu-category">Operaciones</div>
                <li class="active"><i class="fas fa-chart-line"></i> Reportes</li>
                <li><i class="fas fa-calendar-check"></i> Asistencias</li>
                <li><i class="fas fa-shopping-cart"></i> Ventas</li>
                <li><i class="fas fa-file-invoice-dollar"></i> Gastos</li>
                
                <div class="menu-category">Gestión</div>
                <li><i class="fas fa-box-open"></i> Catálogo</li>
                <li><i class="fas fa-dumbbell"></i> Entrenamiento</li>
                <li><i class="fas fa-bullhorn"></i> Marketing</li>
            </ul>
            
            <ul class="sidebar-menu" style="flex-grow: 0; border-top: 1px solid #eaeaea;">
                <li><i class="fas fa-desktop"></i> Kiosco</li>
                <li onclick="logout()"><i class="fas fa-sign-out-alt"></i> Salir</li>
            </ul>
        </aside>

        <main class="main-content">
            
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
        </main>
    </div>

    <script>
        // DOM Elements
        const publicView = document.getElementById('public-view');
        const dashboardView = document.getElementById('dashboard-view');
        const loginModal = document.getElementById('login-modal');

        // Fecha actual formato
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').innerText = new Date().toLocaleDateString('es-ES', dateOptions).replace(/^\w/, (c) => c.toUpperCase());

        // Navegación
        function toggleModal() { loginModal.classList.remove('hidden'); }
        function closeModal() { loginModal.classList.add('hidden'); }
        
        function simulateLogin(role) {
            closeModal();
            publicView.classList.add('hidden');
            dashboardView.classList.remove('hidden');
            initCharts(); // Iniciar gráficos al cargar el dashboard
        }

        function logout() {
            publicView.classList.remove('hidden');
            dashboardView.classList.add('hidden');
        }

        // Gráficos con Chart.js
        let barChartInst, donutChartInst;

        function initCharts() {
            // Destruir previos si existen
            if(barChartInst) barChartInst.destroy();
            if(donutChartInst) donutChartInst.destroy();

            // Gráfico de Barras (Asistencias)
            const ctxBar = document.getElementById('barChart').getContext('2d');
            barChartInst = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                    datasets: [{
                        label: 'Asistencias',
                        data: [0, 0, 0, 0, 0, 0, 0], // Datos en 0 según la imagen
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

            // Gráfico Donut (Balance)
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
    </script>
</body>
</html>