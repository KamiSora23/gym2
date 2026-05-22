<style>
    :root {
        --bg-dash: #f4f7fe;
        --sidebar-bg: var(--sidebar-color);
        --text-dark: #2b3674;
        --text-gray: #a3aed1;
        --primary-red: var(--primary-color);
        --primary-red-light: #fff1f0;
        --pub-primary: #2563eb;
        --pub-bg: #ffffff;
        --pub-gray: #f8fafc;
        --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.03);
        --shadow-md: 0 4px 15px rgba(0, 0, 0, 0.05);
        --border-radius: 12px;
    }

    body {
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 0;
        background-color: var(--pub-bg);
        color: #333;
    }

    .hidden {
        display: none !important;
    }

    * {
        box-sizing: border-box;
    }

    #public-view header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 5%;
        background: white;
        border-bottom: 1px solid #eaeaea;
    }

    #public-view .logo {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--pub-primary);
        text-transform: uppercase;
    }

    .btn-public {
        background-color: var(--pub-primary);
        color: white;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    .hero-public {
        text-align: center;
        padding: 4rem 2rem;
        background-color: var(--pub-gray);
        margin: 2rem 5%;
        border-radius: 12px;
    }

    .hero-public h1 {
        font-size: 2.5rem;
        color: #1e293b;
        margin-bottom: 1rem;
    }

    .hero-public p {
        color: #64748b;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .planes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        padding: 2rem 5%;
    }

    .plan-card {
        border: 1px solid #eaeaea;
        padding: 2rem;
        border-radius: 8px;
        text-align: center;
    }

    .plan-card h3 {
        color: #1e293b;
        margin-top: 0;
    }

    .plan-card p {
        color: #64748b;
        font-size: 0.9rem;
    }

    .modal-overlay {

        background: rgba(0, 0, 0, 0.4);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal-content {
        background: white;
        padding: 2.5rem;
        border-radius: 12px;
        width: 100%;
        max-width: 380px;
        text-align: center;
        box-shadow: var(--shadow-md);
    }

    .btn-demo {
        display: block;
        width: 100%;
        padding: 0.8rem;
        margin-bottom: 10px;
        border: 1px solid #eaeaea;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        color: #333;
    }

    .btn-demo:hover {
        background: var(--pub-gray);
    }

    .btn-demo.admin {
        border-color: var(--primary-red);
        color: var(--primary-red);
    }

    #dashboard-view {
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 250px;
        background: var(--sidebar-bg);
        display: flex;
        flex-direction: column;
        border-right: 1px solid #eaeaea;
    }

    .profile-section {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    .profile-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #1e293b;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
    }

    .profile-info h4 {
        margin: 0;
        font-size: 1.2rem;
        color: var(--text-dark);
    }

    .profile-info p {
        margin: 0;
        font-size: 0.9rem;
        color: var(--text-gray);
    }

    .sidebar-menu {
        list-style: none;
        padding: 1rem 0;
        margin: 0;
        overflow-y: auto;
        flex-grow: 1;
    }

    .menu-category {
        font-size: 0.9rem;
        color: var(--primary-red);
        font-weight: 700;
        text-transform: uppercase;
        padding: 0.8rem 1.5rem;
        letter-spacing: 0.5px;
        margin-top: 15px;
    }

    .sidebar-menu li {
        padding: 1rem 1.5rem;
        margin: 0.3rem 1rem;
        border-radius: 12px;
        cursor: pointer;
        color: #6b7280;
        font-size: 1.1rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: 0.2s;
    }

    .sidebar-menu li i {
        font-size: 1.3rem;
        width: 25px;
        text-align: center;
    }

    .sidebar-menu li:hover {
        background: #f9fafb;
        color: var(--text-dark);
    }

    .sidebar-menu li.active {
        background: var(--primary-red-light);
        color: var(--primary-red);
        font-weight: 600;
        border-left: 4px solid var(--primary-red);
        border-radius: 0 12px 12px 0;
        margin-left: 0;
        padding-left: calc(1.5rem + 1rem - 4px);
    }

    .main-content {
        flex: 1;
        padding: 1.5rem 2rem;
        overflow-y: auto;
    }

    .tabs-container {
        background: white;
        padding: 0.5rem;
        border-radius: 12px;
        display: inline-flex;
        gap: 5px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
        width: fit-content;
    }

    .tab {
        padding: 0.6rem 1.5rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        color: #6b7280;
        cursor: pointer;
        border: none;
        background: transparent;
    }

    .tab.active {
        background: white;
        color: var(--text-dark);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .page-content {
        flex: 1;
        overflow-y: auto;
        padding-right: 5px;
    }

    .dashboard-layout {
        display: grid;
        grid-template-columns: 3fr 1fr;
        gap: 1.5rem;
        padding-bottom: 2rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.2rem;
        box-shadow: var(--shadow-sm);
        border-bottom: 3px solid #eee;
        position: relative;
    }

    .stat-card.cyan {
        border-bottom-color: #00d2d3;
    }

    .stat-card.green {
        border-bottom-color: #10ac84;
    }

    .stat-card.red {
        border-bottom-color: #ff6b6b;
    }

    .stat-card.purple {
        border-bottom-color: #9b59b6;
    }

    .stat-card.orange {
        border-bottom-color: #f39c12;
    }

    .stat-card.blue {
        border-bottom-color: #54a0ff;
    }

    .stat-title {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-gray);
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0;
    }

    .stat-icon {
        position: absolute;
        top: 1.2rem;
        right: 1.2rem;
        width: 35px;
        height: 35px;
        border-radius: 8px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.1rem;
    }

    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-top: 0.8rem;
    }

    .bg-cyan {
        background: #e0f7fa;
        color: #00acc1;
    }

    .bg-green {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .bg-red {
        background: #ffebee;
        color: #c62828;
    }

    .bg-purple {
        background: #f3e5f5;
        color: #6a1b9a;
    }

    .icon-cyan {
        background: #e0f7fa;
        color: #00bcd4;
    }

    .icon-green {
        background: #e8f5e9;
        color: #4caf50;
    }

    .icon-red {
        background: #ffebee;
        color: #f44336;
    }

    .icon-purple {
        background: #f3e5f5;
        color: #9c27b0;
    }

    .icon-orange {
        background: #fff3e0;
        color: #ff9800;
    }

    .icon-blue {
        background: #e3f2fd;
        color: #2196f3;
    }

    .right-panel {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .date-display {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .summary-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        text-align: center;
    }

    .summary-title {
        font-size: 0.7rem;
        color: var(--text-gray);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .summary-item h2 {
        margin: 0;
        font-size: 1.8rem;
        color: var(--text-dark);
    }

    .summary-item p {
        margin: 0;
        font-size: 0.7rem;
        color: var(--text-gray);
        text-transform: uppercase;
    }

    .charts-section {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }

    .chart-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .chart-header h3 {
        margin: 0;
        font-size: 1rem;
        color: var(--text-dark);
    }

    .chart-legend {
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 0.8rem;
        color: var(--text-gray);
    }

    .chart-legend li {
        margin-bottom: 0.5rem;
        display: flex;
        justify-content: space-between;
    }

    .chart-legend span {
        font-weight: 700;
        color: var(--text-dark);
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .page-header h2 {
        margin: 0;
        color: var(--text-dark);
        font-size: 1.5rem;
    }

    .btn-primary {
        background: var(--primary-red);
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary:hover {
        opacity: 0.9;
    }

    .table-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .table-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-header h3 {
        margin: 0;
        color: var(--text-dark);
        font-size: 1rem;
    }

    .search-box {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f9fafb;
        padding: 0.8rem 1rem;
        border-radius: 14px;
        min-width: 320px;
        border: 1px solid #e5e7eb;
    }

    .search-box input {
        border: none;
        outline: none;
        background: transparent;
        width: 100%;
        font-size: 0.95rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 1rem 1.5rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    th {
        background: #fafafa;
        font-weight: 600;
        color: var(--text-gray);
        font-size: 0.8rem;
        text-transform: uppercase;
    }

    td {
        color: var(--text-dark);
        font-size: 0.95rem;
    }

    tr:hover {
        background: #fafafa;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-active {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .status-pending {
        background: #fff3e0;
        color: #f57c00;
    }

    .status-cancelled {
        background: #ffebee;
        color: #c62828;
    }

    .form-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1.5rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-gray);
    }

    .form-group input,
    .form-group select {
        padding: 0.7rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: 'Inter', sans-serif;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary-red);
    }

    .catalog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .catalog-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: transform 0.2s;
    }

    .catalog-card:hover {
        transform: translateY(-5px);
    }

    .catalog-image {
        width: 100%;
        height: 150px;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #888;
    }

    .catalog-body {
        padding: 1.2rem;
    }

    .catalog-title {
        margin: 0 0 0.5rem 0;
        color: var(--text-dark);
        font-size: 1rem;
    }

    .catalog-price {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--primary-red);
        margin: 0 0 0.5rem 0;
    }

    .catalog-stock {
        font-size: 0.85rem;
        color: var(--text-gray);
    }

    .schedule-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .schedule-day {
        background: white;
        border-radius: var(--border-radius);
        padding: 1rem;
        text-align: center;
        box-shadow: var(--shadow-sm);
    }

    .schedule-day-name {
        font-weight: 700;
        color: var(--primary-red);
        font-size: 0.85rem;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .schedule-class {
        padding: 0.5rem;
        margin: 0.3rem 0;
        border-radius: 6px;
        font-size: 0.8rem;
        background: #f5f5f5;
        color: var(--text-dark);
    }

    .schedule-class.highlight {
        background: var(--primary-red-light);
        color: var(--primary-red);
        font-weight: 600;
    }

    .campaign-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 1rem;
    }

    .campaign-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .campaign-title {
        margin: 0;
        color: var(--text-dark);
        font-size: 1.1rem;
    }

    .campaign-metrics {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .campaign-metric {
        text-align: center;
    }

    .campaign-metric-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary-red);
    }

    .campaign-metric-label {
        font-size: 0.8rem;
        color: var(--text-gray);
        text-transform: uppercase;
        margin-top: 0.3rem;
    }

    css de configuracion

    .config-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-top: 20px;
    }

    .config-card {
        background: #fff;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .config-card h3 {
        margin-bottom: 15px;
        font-size: 16px;
        color: var(--text-dark);
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        margin-bottom: 5px;
        color: var(--text-gray);
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px;
        border-radius: 10px;
        border: 1px solid #ddd;
        outline: none;
    }

    .preview-box img {
        width: 80px;
        margin-top: 10px;
    }

    .color-preview {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .color-box {
        width: 30px;
        height: 30px;
        border-radius: 8px;
    }

    .full-width {
        grid-column: span 2;
    }

    .system-options {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .dashboard-layout {
            grid-template-columns: 1fr;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .schedule-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    


</style>