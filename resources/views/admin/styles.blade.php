<style>
    :root {
        --bg-dash: var(--bg-color, #f5f7fb);
        --sidebar-bg: var(--sidebar-color);
        --text-dark: #1e293b;
        --text-gray: #64748b;
        --text-soft: #94a3b8;
        --line: #e2e8f0;
        --primary: var(--primary-color);
        --primary-soft: color-mix(in srgb, var(--primary-color) 12%, white);
        --secondary-soft: color-mix(in srgb, var(--secondary-color) 14%, white);
        --shadow-sm: 0 1px 2px rgba(15, 23, 42, .04), 0 2px 8px rgba(15, 23, 42, .04);
        --shadow-md: 0 8px 24px rgba(15, 23, 42, .08);
        --radius: 16px;
        --nav-h: 4rem;
    }

    .hidden { display: none !important; }

    /* ---------- Estructura ---------- */
    #dashboard-view {
        display: flex;
        min-height: calc(100vh - var(--nav-h));
        background: var(--bg-dash);
    }

    .sidebar {
        width: 264px;
        flex-shrink: 0;
        background: var(--sidebar-bg);
        color: var(--sidebar-text);
        border-right: 1px solid var(--sidebar-line);
        display: flex;
        flex-direction: column;
        position: sticky;
        top: var(--nav-h);
        height: calc(100vh - var(--nav-h));
        overflow-y: auto;
        z-index: 30;
    }

    .profile-section {
        margin: 1.25rem 1rem .5rem;
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 12px;
        border-radius: 14px;
        background: var(--sidebar-hover);
    }

    .profile-avatar {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border-radius: 12px;
        background: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-text);
        font-size: 1.1rem;
        font-weight: 800;
    }

    .profile-info { min-width: 0; }
    .profile-info h4 {
        margin: 0;
        font-size: .95rem;
        font-weight: 700;
        color: var(--sidebar-strong);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .profile-info p {
        margin: 2px 0 0;
        font-size: .75rem;
        color: var(--sidebar-muted);
    }

    .sidebar-menu {
        list-style: none;
        padding: .5rem .75rem;
        margin: 0;
        flex-grow: 1;
    }

    .menu-category {
        font-size: .7rem;
        color: var(--sidebar-muted);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        padding: 1rem .75rem .4rem;
    }

    .menu-item {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: .7rem .9rem;
        margin: 2px 0;
        border: 0;
        border-radius: 12px;
        background: transparent;
        color: var(--sidebar-text);
        font-size: .92rem;
        font-weight: 500;
        text-align: left;
        cursor: pointer;
        transition: background .15s, color .15s;
    }

    .menu-item i { width: 20px; text-align: center; font-size: 1rem; opacity: .85; }
    a.menu-item { text-decoration: none; }
    .menu-item:hover { background: var(--sidebar-hover); color: var(--sidebar-strong); }
    .menu-item.active {
        background: var(--primary);
        color: var(--primary-text);
        font-weight: 600;
        box-shadow: 0 6px 16px color-mix(in srgb, var(--primary-color) 35%, transparent);
    }
    .menu-item .menu-tag {
        margin-left: auto;
        font-size: .6rem;
        padding: 2px 6px;
        border-radius: 6px;
        background: var(--sidebar-hover);
        color: var(--sidebar-text);
        letter-spacing: .04em;
    }

    .sidebar-footer {
        padding: .75rem;
        border-top: 1px solid var(--sidebar-line);
    }

    .sidebar-backdrop { display: none; }

    .main-content {
        flex: 1;
        min-width: 0;
        padding: 1.75rem 2rem 3rem;
    }

    /* ---------- Cabeceras ---------- */
    .page-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .page-header h2 {
        margin: 0;
        color: var(--text-dark);
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -.02em;
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .page-header h2 .icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 1rem;
    }
    .page-header p { margin: .25rem 0 0; color: var(--text-gray); font-size: .9rem; }

    .demo-tag {
        font-size: .7rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
        background: #fef3c7;
        color: #92400e;
    }

    /* ---------- Alertas ---------- */
    .alert {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .9rem 1.1rem;
        border-radius: 12px;
        margin-bottom: 1.25rem;
        font-size: .9rem;
        font-weight: 500;
        animation: slideIn .25s ease-out;
    }
    .alert-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .alert ul { margin: 0; padding-left: 1.1rem; }
    .alert-close { margin-left: auto; background: none; border: 0; color: inherit; cursor: pointer; opacity: .6; }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: none; }
    }

    /* ---------- Tarjetas de estadísticas ---------- */
    .dashboard-layout {
        display: grid;
        grid-template-columns: minmax(0, 3fr) minmax(260px, 1fr);
        gap: 1.5rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        --accent: #64748b;
        background: #fff;
        border-radius: var(--radius);
        padding: 1.2rem 1.25rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--line);
        position: relative;
        overflow: hidden;
        transition: transform .15s, box-shadow .15s;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: var(--accent);
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }

    .stat-card.cyan { --accent: #06b6d4; }
    .stat-card.green { --accent: #10b981; }
    .stat-card.red { --accent: #ef4444; }
    .stat-card.purple { --accent: #8b5cf6; }
    .stat-card.orange { --accent: #f59e0b; }
    .stat-card.blue { --accent: #3b82f6; }
    .stat-card.pink { --accent: #ec4899; }

    .stat-title {
        font-size: .72rem;
        font-weight: 700;
        color: var(--text-soft);
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .5rem;
        padding-right: 44px;
    }
    .stat-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -.02em;
    }
    .stat-icon {
        position: absolute;
        top: 1.1rem;
        right: 1.1rem;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        background: color-mix(in srgb, var(--accent) 12%, white);
        color: var(--accent);
    }
    .stat-hint { margin-top: .45rem; font-size: .75rem; color: var(--text-soft); }

    /* ---------- Panel derecho ---------- */
    .right-panel { display: flex; flex-direction: column; gap: 1rem; }

    .date-display {
        font-size: .9rem;
        font-weight: 600;
        color: var(--text-gray);
    }

    .summary-card {
        border-radius: var(--radius);
        padding: 1.5rem;
        color: #fff;
        background: linear-gradient(135deg, color-mix(in srgb, var(--primary-color) 55%, #0f172a), color-mix(in srgb, var(--secondary-color) 45%, #0f172a));
        box-shadow: var(--shadow-md);
    }
    .summary-title { font-size: .72rem; letter-spacing: .08em; font-weight: 700; opacity: .7; margin-bottom: 1rem; }
    .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .summary-item h2 { margin: 0; font-size: 2rem; font-weight: 800; }
    .summary-item p { margin: 0; font-size: .72rem; opacity: .75; text-transform: uppercase; }

    .panel-card {
        background: #fff;
        border-radius: var(--radius);
        border: 1px solid var(--line);
        box-shadow: var(--shadow-sm);
        padding: 1.25rem;
    }
    .panel-card h4 { margin: 0 0 .75rem; font-size: .85rem; font-weight: 700; color: var(--text-dark); }

    .tx-list { list-style: none; margin: 0; padding: 0; }
    .tx-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        padding: .6rem 0;
        border-bottom: 1px dashed var(--line);
        font-size: .85rem;
    }
    .tx-list li:last-child { border-bottom: 0; }
    .tx-list small { display: block; color: var(--text-soft); font-size: .72rem; }
    .tx-list strong { color: #059669; white-space: nowrap; }

    /* ---------- Gráficos ---------- */
    .charts-section {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        gap: 1.5rem;
    }
    .chart-card {
        background: #fff;
        border-radius: var(--radius);
        border: 1px solid var(--line);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
    }
    .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .chart-header h3 { margin: 0; font-size: 1rem; font-weight: 700; color: var(--text-dark); }
    .chart-box { position: relative; height: 240px; }
    .donut-box { position: relative; height: 170px; display: flex; justify-content: center; align-items: center; }
    .donut-center { position: absolute; text-align: center; pointer-events: none; }
    .donut-center small { display: block; font-size: .65rem; color: var(--text-soft); letter-spacing: .08em; }
    .donut-center strong { font-size: 1.2rem; }
    .chart-legend { list-style: none; padding: 0; margin: 1rem 0 0; font-size: .85rem; color: var(--text-gray); }
    .chart-legend li { margin-bottom: .5rem; display: flex; justify-content: space-between; align-items: center; }
    .chart-legend .dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
    .chart-legend span.val { font-weight: 700; color: var(--text-dark); }
    .chart-legend .total { border-top: 1px solid var(--line); padding-top: .5rem; margin-top: .5rem; }

    /* ---------- Botones ---------- */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: .65rem 1.15rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: .9rem;
        border: 1px solid transparent;
        cursor: pointer;
        transition: transform .1s, box-shadow .15s, background .15s, opacity .15s;
        text-decoration: none;
    }
    .btn:active { transform: scale(.98); }
    .btn-primary {
        background: var(--primary);
        color: var(--primary-text);
        box-shadow: 0 6px 16px color-mix(in srgb, var(--primary-color) 30%, transparent);
    }
    .btn-primary:hover { opacity: .92; }
    .btn-light { background: #fff; color: var(--text-dark); border-color: var(--line); }
    .btn-light:hover { background: #f8fafc; }
    .btn-danger { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
    .btn-danger:hover { background: #fee2e2; }
    .btn-sm { padding: .4rem .7rem; font-size: .8rem; border-radius: 9px; }
    .btn-icon { width: 34px; height: 34px; padding: 0; }

    /* ---------- Tablas ---------- */
    .table-card {
        background: #fff;
        border-radius: var(--radius);
        border: 1px solid var(--line);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .table-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--line);
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        justify-content: space-between;
        align-items: center;
    }
    .table-header h3 { margin: 0; color: var(--text-dark); font-size: 1rem; font-weight: 700; }
    .table-header .count { color: var(--text-soft); font-size: .85rem; }
    .table-scroll { overflow-x: auto; }

    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th,
    .data-table td { padding: .85rem 1.5rem; text-align: left; border-bottom: 1px solid #f1f5f9; white-space: nowrap; }
    .data-table th {
        background: #f8fafc;
        font-weight: 700;
        color: var(--text-soft);
        font-size: .72rem;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .data-table td { color: var(--text-dark); font-size: .9rem; }
    .data-table tbody tr:hover { background: #fafbff; }
    .data-table tbody tr:last-child td { border-bottom: 0; }
    .data-table .money-out { color: #dc2626; font-weight: 700; }
    .data-table .money-in { color: #059669; font-weight: 700; }

    .empty-state { text-align: center; padding: 3rem 1rem !important; color: var(--text-soft) !important; white-space: normal !important; }
    .empty-state i { font-size: 2.5rem; margin-bottom: .75rem; display: block; opacity: .5; }

    .search-box {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        padding: .55rem .9rem;
        border-radius: 12px;
        min-width: min(280px, 100%);
        border: 1px solid var(--line);
    }
    .search-box i { color: var(--text-soft); }
    .search-box input { border: 0; outline: 0; background: transparent; width: 100%; font-size: .9rem; padding: 0; box-shadow: none; }
    .search-box input:focus { box-shadow: none; }

    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 600;
    }
    .status-active { background: #ecfdf5; color: #047857; }
    .status-pending { background: #fffbeb; color: #b45309; }
    .status-cancelled { background: #fef2f2; color: #b91c1c; }
    .status-info { background: #eff6ff; color: #1d4ed8; }
    .status-dark { background: #0f172a; color: #fff; }

    /* ---------- Formularios ---------- */
    .form-card {
        background: #fff;
        border-radius: var(--radius);
        border: 1px solid var(--line);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
    }
    .form-card h3 { margin: 0 0 1rem; font-size: 1rem; font-weight: 700; color: var(--text-dark); display: flex; gap: .5rem; align-items: center; }
    .form-card h3 i { color: var(--primary); }

    .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    .form-grid.cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .form-group { display: flex; flex-direction: column; gap: .4rem; margin-bottom: 1rem; }
    .form-grid .form-group { margin-bottom: 0; }
    .form-group label { font-size: .8rem; font-weight: 600; color: var(--text-gray); }
    .form-group input:not([type=checkbox]):not([type=color]),
    .form-group select {
        width: 100%;
        padding: .65rem .8rem;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: .9rem;
        background: #fff;
        color: var(--text-dark);
    }
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-soft);
    }
    .form-group input[type=color] { width: 56px; height: 40px; padding: 2px; border-radius: 10px; border: 1px solid #cbd5e1; cursor: pointer; }
    .form-group input[type=file] { font-size: .85rem; }
    .form-actions { display: flex; justify-content: flex-end; gap: .5rem; margin-top: 1rem; }
    .field-error { color: #dc2626; font-size: .75rem; }

    /* ---------- Catálogo ---------- */
    .catalog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.25rem; }
    .catalog-card {
        background: #fff;
        border-radius: var(--radius);
        border: 1px solid var(--line);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: transform .2s, box-shadow .2s;
    }
    .catalog-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
    .catalog-image {
        height: 120px;
        background: linear-gradient(135deg, var(--primary-soft), #f1f5f9);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--primary);
    }
    .catalog-body { padding: 1.1rem; }
    .catalog-type { font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: var(--text-soft); font-weight: 700; }
    .catalog-title { margin: .25rem 0 .5rem; color: var(--text-dark); font-size: 1rem; font-weight: 700; }
    .catalog-price { font-size: 1.25rem; font-weight: 800; color: var(--primary); margin: 0 0 .4rem; }
    .catalog-stock { font-size: .8rem; color: var(--text-gray); margin: 0; }

    /* ---------- Entrenamiento ---------- */
    .schedule-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: .6rem; margin-bottom: 1.5rem; }
    .schedule-day { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: .9rem; text-align: center; }
    .schedule-day-name { font-weight: 800; color: var(--primary); font-size: .75rem; letter-spacing: .05em; text-transform: uppercase; margin-bottom: .5rem; }
    .schedule-class { padding: .45rem; margin: .3rem 0; border-radius: 8px; font-size: .75rem; background: #f1f5f9; color: var(--text-dark); }
    .schedule-class.highlight { background: var(--primary-soft); color: var(--primary); font-weight: 700; }

    /* ---------- Marketing ---------- */
    .campaign-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .campaign-card { background: #fff; border-radius: var(--radius); border: 1px solid var(--line); padding: 1.25rem; box-shadow: var(--shadow-sm); }
    .campaign-header { display: flex; justify-content: space-between; align-items: flex-start; gap: .5rem; margin-bottom: 1rem; }
    .campaign-title { margin: 0; color: var(--text-dark); font-size: 1rem; font-weight: 700; }
    .campaign-header p { margin: .25rem 0 0; color: var(--text-soft); font-size: .8rem; }
    .campaign-metrics { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
    .campaign-metric { text-align: center; background: #f8fafc; border-radius: 10px; padding: .6rem .25rem; }
    .campaign-metric-value { font-size: 1.15rem; font-weight: 800; color: var(--primary); }
    .campaign-metric-label { font-size: .65rem; color: var(--text-soft); text-transform: uppercase; margin-top: .15rem; }

    /* ---------- Configuración ---------- */
    .config-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; }
    .config-card { background: #fff; border: 1px solid var(--line); padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow-sm); }
    .config-card h3 { margin: 0 0 1.1rem; font-size: 1rem; font-weight: 700; color: var(--text-dark); display: flex; gap: .5rem; align-items: center; }
    .config-card h3 i { color: var(--primary); }
    .horario-grid { display: grid; grid-template-columns: 90px 1fr; gap: .5rem .75rem; align-items: center; }
    .horario-grid span { font-size: .85rem; color: var(--text-gray); font-weight: 600; }
    .preview-box { display: flex; align-items: center; gap: 1rem; padding: .75rem; background: #f8fafc; border-radius: 12px; margin-bottom: 1rem; }
    .preview-box img { width: 64px; height: 64px; object-fit: contain; }
    .color-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
    .full-width { grid-column: 1 / -1; }

    /* ---------- Usuarios ---------- */
    .user-cell { display: flex; align-items: center; gap: .75rem; }
    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--primary-soft);
        color: var(--primary);
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .user-cell small { display: block; color: var(--text-soft); font-size: .75rem; }
    .row-actions { display: flex; gap: .4rem; justify-content: flex-end; }
    .edit-row td { background: #f8fafc; white-space: normal; }

    /* ---------- Responsive ---------- */
    @media (max-width: 1280px) {
        .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dashboard-layout { grid-template-columns: 1fr; }
        .schedule-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .form-grid.cols-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 1023px) {
        .sidebar {
            position: fixed;
            left: 0;
            top: var(--nav-h);
            transform: translateX(-100%);
            transition: transform .25s ease;
            box-shadow: var(--shadow-md);
            z-index: 50;
        }
        body.sidebar-open .sidebar { transform: none; }
        body.sidebar-open .sidebar-backdrop {
            display: block;
            position: fixed;
            inset: var(--nav-h) 0 0 0;
            background: rgba(15, 23, 42, .4);
            z-index: 45;
        }
        .main-content { padding: 1.25rem 1rem 2.5rem; }
        .charts-section,
        .config-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 640px) {
        .stats-grid,
        .form-grid,
        .form-grid.cols-4 { grid-template-columns: 1fr; }
        .schedule-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .page-header h2 { font-size: 1.25rem; }
    }

    /* ---------- Barra superior ---------- */
    .topbar {
        position: sticky;
        top: 0;
        z-index: 40;
        height: var(--nav-h);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0 1.25rem;
        background: rgba(255, 255, 255, .92);
        backdrop-filter: blur(8px);
        border-bottom: 1px solid var(--line);
    }
    .topbar-brand { display: flex; align-items: center; gap: .75rem; text-decoration: none; min-width: 0; }
    .topbar-brand img { width: 38px; height: 38px; object-fit: contain; border-radius: 10px; }
    .topbar-brand strong { display: block; color: var(--text-dark); font-size: .95rem; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .topbar-brand small { display: block; color: var(--text-soft); font-size: .72rem; }
    .topbar-right { display: flex; align-items: center; gap: .6rem; }
    .context-select {
        display: flex;
        align-items: center;
        gap: .4rem;
        background: #f8fafc;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: .2rem .25rem .2rem .7rem;
        font-size: .8rem;
        color: var(--text-gray);
    }
    .context-select select {
        border: 0;
        background-color: transparent;
        font-size: .85rem;
        font-weight: 600;
        color: var(--text-dark);
        padding: .35rem 2rem .35rem .25rem;
        max-width: 190px;
        box-shadow: none;
    }
    .context-select select:focus { box-shadow: none; }
    .icon-btn {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        border: 0;
        background: transparent;
        color: var(--text-gray);
        cursor: pointer;
    }
    .icon-btn:hover { background: #f1f5f9; }
    .user-chip { display: flex; align-items: center; gap: .6rem; padding: .25rem .6rem .25rem .25rem; border-radius: 14px; border: 0; background: transparent; cursor: pointer; }
    .user-chip:hover { background: #f1f5f9; }
    .user-chip .avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--primary); color: var(--primary-text); font-weight: 800; display: flex; align-items: center; justify-content: center; }
    .user-chip strong { display: block; font-size: .85rem; color: var(--text-dark); text-align: left; }
    .user-chip small { display: block; font-size: .72rem; color: var(--text-soft); text-align: left; }
    .dropdown-panel { position: absolute; right: 0; top: calc(100% + 6px); width: 230px; background: #fff; border: 1px solid var(--line); border-radius: 14px; box-shadow: var(--shadow-md); padding: .4rem; z-index: 60; }
    .dropdown-panel a, .dropdown-panel button { display: flex; width: 100%; align-items: center; gap: .6rem; padding: .6rem .7rem; border-radius: 10px; font-size: .88rem; color: var(--text-dark); text-decoration: none; border: 0; background: none; cursor: pointer; }
    .dropdown-panel a:hover, .dropdown-panel button:hover { background: #f8fafc; }
    .dropdown-panel i { width: 18px; color: var(--text-soft); }
    .alert-info { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .mode-banner {
        display: flex;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
        padding: .75rem 1rem;
        margin-bottom: 1.25rem;
        border-radius: 14px;
        background: linear-gradient(90deg, #312e81, #4338ca);
        color: #e0e7ff;
        font-size: .85rem;
    }
    .mode-banner strong { color: #fff; }
    .mode-banner span { flex: 1; min-width: 220px; }
    .menu-back { border: 1px dashed var(--sidebar-line); margin-bottom: .25rem; }

    /* ---------- Varios ---------- */
    .grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; }
    .muted { color: var(--text-soft); font-size: .8rem; }
    .chip { display: inline-flex; align-items: center; gap: .35rem; padding: 3px 10px; border-radius: 999px; background: #f1f5f9; color: var(--text-gray); font-size: .75rem; font-weight: 600; }
    .text-right { text-align: right !important; }
    .form-group textarea { width: 100%; padding: .65rem .8rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: .9rem; font-family: inherit; }
    .form-check { display: flex; align-items: center; gap: .5rem; font-size: .85rem; color: var(--text-gray); }
    .company-card { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 1.25rem; display: flex; flex-direction: column; gap: .9rem; }
    .company-card.inactive { opacity: .65; }
    .company-head { display: flex; align-items: center; gap: .8rem; }
    .company-logo { width: 46px; height: 46px; border-radius: 12px; background: var(--primary-soft); color: var(--primary); font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .company-head h3 { margin: 0; font-size: 1rem; font-weight: 700; color: var(--text-dark); }
    .company-head p { margin: 0; font-size: .78rem; color: var(--text-soft); }
    .company-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
    .company-stats div { background: #f8fafc; border-radius: 10px; padding: .55rem; text-align: center; }
    .company-stats strong { display: block; font-size: 1.1rem; color: var(--text-dark); }
    .company-stats span { font-size: .68rem; color: var(--text-soft); text-transform: uppercase; }
    .company-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
    .pos-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); gap: 1.5rem; align-items: start; }
    .pos-total { font-size: 2rem; font-weight: 800; color: var(--text-dark); }
    .pos-line { display: grid; grid-template-columns: minmax(0, 1fr) 90px 110px 40px; gap: .5rem; align-items: center; margin-bottom: .5rem; }
    .pos-line select, .pos-line input { width: 100%; padding: .55rem .6rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: .88rem; }
    .pos-line .sub { text-align: right; font-weight: 700; color: var(--text-dark); font-size: .9rem; }
    .summary-row { display: flex; justify-content: space-between; padding: .45rem 0; font-size: .9rem; color: var(--text-gray); }
    .summary-row strong { color: var(--text-dark); }
    .class-card { border-left: 3px solid var(--primary); }
    .class-meta { font-size: .7rem; color: var(--text-soft); margin-top: 2px; }
    .schedule-class { text-align: left; }
    .schedule-class form { display: inline; }

    @media (max-width: 1023px) {
        .pos-grid, .grid-2 { grid-template-columns: 1fr; }
        .context-select span { display: none; }
        .user-chip .who { display: none; }
    }
    @media (max-width: 640px) {
        .context-select select { max-width: 120px; }
        .topbar-brand small { display: none; }
        .pos-line { grid-template-columns: 1fr 70px 40px; }
        .pos-line .sub { display: none; }
    }
    /* ---------- Configuración / editor de tema ---------- */
    .section-label { font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--text-soft); margin-bottom: .6rem; }
    .settings-layout { display: grid; grid-template-columns: 230px minmax(0, 1fr); gap: 1.5rem; align-items: start; }
    .settings-nav { position: sticky; top: calc(var(--nav-h) + 1rem); background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: .5rem; box-shadow: var(--shadow-sm); }
    .settings-nav a { display: flex; align-items: center; gap: .65rem; padding: .7rem .8rem; border-radius: 10px; color: var(--text-gray); text-decoration: none; font-size: .9rem; font-weight: 600; }
    .settings-nav a i { width: 18px; text-align: center; }
    .settings-nav a:hover { background: #f8fafc; color: var(--text-dark); }
    .settings-nav a.active { background: var(--primary-soft); color: var(--primary); }
    .settings-section { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 1.5rem; margin-bottom: 1.5rem; scroll-margin-top: calc(var(--nav-h) + 1rem); }
    .settings-section > header { display: flex; align-items: flex-start; gap: .9rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--line); }
    .settings-section > header .icon { width: 42px; height: 42px; border-radius: 12px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .settings-section > header h3 { margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--text-dark); }
    .settings-section > header p { margin: .2rem 0 0; font-size: .85rem; color: var(--text-gray); }
    .settings-actions { position: sticky; bottom: 0; z-index: 20; display: flex; justify-content: flex-end; gap: .5rem; padding: .9rem 1rem; background: rgba(255,255,255,.92); backdrop-filter: blur(6px); border: 1px solid var(--line); border-radius: var(--radius); box-shadow: var(--shadow-md); }

    .theme-editor { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(280px, 1fr); gap: 1.5rem; }
    .preset-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(118px, 1fr)); gap: .6rem; }
    .preset { display: flex; flex-direction: column; gap: .45rem; padding: .6rem; border: 2px solid var(--line); border-radius: 12px; background: #fff; cursor: pointer; font-size: .78rem; font-weight: 600; color: var(--text-dark); text-align: left; transition: border-color .15s, transform .15s; }
    .preset:hover { transform: translateY(-2px); }
    .preset.selected { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
    .preset-swatches { display: flex; height: 26px; border-radius: 8px; overflow: hidden; }
    .preset-swatches i { flex: 1; }
    .color-fields { display: grid; gap: .6rem; }
    .color-field { display: flex; align-items: center; gap: .8rem; padding: .6rem .75rem; border: 1px solid var(--line); border-radius: 12px; cursor: pointer; }
    .color-field:hover { border-color: #cbd5e1; }
    .color-field input[type=color] { width: 44px; height: 44px; border: 0; padding: 0; background: none; cursor: pointer; flex-shrink: 0; }
    .color-field input[type=color]::-webkit-color-swatch-wrapper { padding: 0; }
    .color-field input[type=color]::-webkit-color-swatch { border: 1px solid rgba(0,0,0,.1); border-radius: 10px; }
    .color-field span { flex: 1; min-width: 0; }
    .color-field strong { display: block; font-size: .88rem; color: var(--text-dark); }
    .color-field small { display: block; font-size: .75rem; color: var(--text-soft); }
    .hex-input { width: 92px; padding: .45rem .55rem; border: 1px solid #cbd5e1; border-radius: 9px; font-family: ui-monospace, monospace; font-size: .82rem; text-transform: lowercase; }

    .theme-preview { --p: #ff4d4f; --p-text: #fff; --a: #f59e0b; --s: #111827; --s-text: #cbd5e1; --bg: #f5f7fb;
        display: flex; height: 300px; border-radius: 14px; overflow: hidden; border: 1px solid var(--line); box-shadow: var(--shadow-md); font-size: .72rem; }
    .tp-sidebar { width: 38%; background: var(--s); padding: .75rem .5rem; display: flex; flex-direction: column; gap: .3rem; transition: background .2s; }
    .tp-logo { height: 26px; border-radius: 8px; background: color-mix(in srgb, var(--s-text) 18%, transparent); margin-bottom: .4rem; }
    .tp-item { padding: .45rem .55rem; border-radius: 8px; color: var(--s-text); display: flex; gap: .45rem; align-items: center; white-space: nowrap; overflow: hidden; }
    .tp-item.active { background: var(--p); color: var(--p-text); font-weight: 700; }
    .tp-main { flex: 1; background: var(--bg); padding: .8rem; display: flex; flex-direction: column; gap: .6rem; transition: background .2s; }
    .tp-title { font-weight: 800; font-size: .85rem; color: #1e293b; display: flex; align-items: center; gap: .4rem; }
    .tp-icon { width: 22px; height: 22px; border-radius: 6px; background: color-mix(in srgb, var(--p) 15%, white); color: var(--p); display: inline-flex; align-items: center; justify-content: center; font-size: .6rem; }
    .tp-cards { display: grid; grid-template-columns: 1fr 1fr; gap: .45rem; }
    .tp-card { background: #fff; border-radius: 8px; padding: .5rem; border-left: 3px solid var(--p); }
    .tp-card.accent { border-left-color: var(--a); }
    .tp-card small { display: block; color: #94a3b8; font-size: .55rem; font-weight: 700; }
    .tp-card b { font-size: .9rem; color: #1e293b; }
    .tp-hero { flex: 1; border-radius: 10px; color: #fff; padding: .6rem; font-weight: 700; background: linear-gradient(135deg, color-mix(in srgb, var(--p) 55%, #0f172a), color-mix(in srgb, var(--a) 45%, #0f172a)); }
    .tp-btn { padding: .35rem .7rem; border-radius: 7px; background: var(--p); color: var(--p-text); font-weight: 700; }
    .tp-btn.ghost { background: #fff; color: #1e293b; border: 1px solid #e2e8f0; }

    .web-preview { --wp: #ffc107; --wd: #0f172a; --wp-text: #111827; border-radius: 14px; overflow: hidden; border: 1px solid var(--line); box-shadow: var(--shadow-md); }
    .wp-nav { background: var(--wd); color: #fff; padding: .6rem .9rem; display: flex; justify-content: space-between; align-items: center; font-weight: 800; font-size: .8rem; }
    .wp-nav b { color: var(--wp); }
    .wp-hero { background: linear-gradient(180deg, color-mix(in srgb, var(--wd) 85%, transparent), var(--wd)); color: #fff; text-align: center; padding: 1.6rem 1rem; }
    .wp-hero h4 { margin: 0 0 .7rem; font-size: 1.15rem; font-weight: 800; letter-spacing: -.01em; }
    .wp-hero h4 span { color: var(--wp); }
    .wp-btn { display: inline-block; padding: .45rem 1rem; border-radius: 8px; background: var(--wp); color: var(--wp-text); font-weight: 700; font-size: .78rem; }

    .branch-colors { display: grid; gap: .6rem; }
    .branch-row { display: grid; grid-template-columns: minmax(0, 1fr) auto auto auto; gap: .75rem; align-items: center; padding: .75rem; border: 1px solid var(--line); border-radius: 12px; }
    .branch-row input[type=color] { width: 40px; height: 36px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 2px; cursor: pointer; }
    .branch-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: .4rem; vertical-align: middle; }

    .upload-box { display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 2px dashed #cbd5e1; border-radius: 14px; background: #f8fafc; }
    .upload-box img { width: 72px; height: 72px; object-fit: contain; border-radius: 12px; background: #fff; border: 1px solid var(--line); padding: 6px; }

    .matrix th, .matrix td { text-align: center !important; }
    .matrix td:first-child, .matrix th:first-child { text-align: left !important; }
    .matrix .si { color: #059669; }
    .matrix .no { color: #cbd5e1; }

    @media (max-width: 1100px) {
        .theme-editor, .settings-layout { grid-template-columns: 1fr; }
        .settings-nav { position: static; display: flex; overflow-x: auto; }
        .settings-nav a { white-space: nowrap; }
    }
    @media (max-width: 640px) {
        .branch-row { grid-template-columns: 1fr auto auto; }
        .branch-row .muted { display: none; }
    }
</style>
