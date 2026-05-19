<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM IMPULSO - Carrito</title>
</head>
<body>
    <header class="navbar navbar-expand-lg fixed-top scrolled" id="mainNav">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                <span class="text-primary">GYM</span> <span class="text-white">IMPULSO</span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#acercade">Nosotros</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#planes">Planes</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/shop') }}">Tienda</a></li>
                    <li class="nav-item ms-2">
                        <a href="{{ url('/carrito') }}" class="nav-link position-relative active">
                            <i class="bi bi-bag fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartCount">0</span>
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="{{ url('/login') }}" class="nav-link">
                            <i class="bi bi-person-circle fs-5"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <section class="py-5 bg-light" style="margin-top: 80px; min-height: 80vh;">
        <div class="container py-5">
            <div class="mb-5 reveal active">
                <h2 class="display-6 fw-bold">TU <span class="text-primary">CARRITO</span></h2>
                <p class="text-muted">Gestiona tus productos antes de finalizar la compra.</p>
            </div>

            <div id="cartContainer">
                <!-- Se llena con JS -->
            </div>
        </div>
    </section>

    <footer class="py-5 bg-dark">
        <div class="container text-center">
            <a class="navbar-brand fw-bold mb-3 d-block" href="{{ url('/') }}">
                <span class="text-primary">GYM</span> <span class="text-white">IMPULSO</span>
            </a>
            <p class="text-white-50 small mb-0">&copy; 2026 GYM IMPULSO. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/javaScript.js') }}"></script>
</body>
</html>
