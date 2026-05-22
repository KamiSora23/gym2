
<aside class="sidebar">

   <div class="profile-section">

    <div class="profile-avatar">
        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
    </div>

    <div class="profile-info">
        <h4>{{ Auth::user()->name }}</h4>
        <p>{{ Auth::user()->email }}</p>
    </div>

</div>
    <ul class="sidebar-menu">

        <div class="menu-category">Operaciones</div>

        <li class="active" onclick="showPage('reportes')">
            <i class="fas fa-chart-line"></i>
            Reportes
        </li>

        <li onclick="showPage('asistencias')">
            <i class="fas fa-calendar-check"></i>
            Asistencias
        </li>

        <li onclick="showPage('ventas')">
            <i class="fas fa-shopping-cart"></i>
            Ventas
        </li>

        <li onclick="showPage('gastos')">
            <i class="fas fa-file-invoice-dollar"></i>
            Gastos
        </li>

        <div class="menu-category">Gestión</div>

        <li onclick="showPage('catalogo')">
            <i class="fas fa-box-open"></i>
            Catálogo
        </li>

        <li onclick="showPage('entrenamiento')">
            <i class="fas fa-dumbbell"></i>
            Entrenamiento
        </li>

        <li onclick="showPage('marketing')">
            <i class="fas fa-bullhorn"></i>
            Marketing
        </li>
        <li onclick="showPage('config')">
            <i class="fas fa-cog"></i>
            Configuración del Sistema
        </li>

    </ul>

    <ul class="sidebar-menu" style="flex-grow:0; border-top:1px solid #eaeaea;">

        <li>

            <form method="POST" action="{{ route('logout') }}">
    @csrf

    <button type="submit" style="
        width:100%;
        border:none;
        background:none;
        cursor:pointer;
        padding:1rem 1.5rem;
        text-align:left;
        color:#6b7280;
        font-size:1.1rem;
        font-weight:500;
        display:flex;
        align-items:center;
        gap:15px;
    ">
        <i class="fas fa-sign-out-alt"></i>
        Salir
    </button>
</form>

        </li>

    </ul>

</aside>