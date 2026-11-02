<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $config->nombre_gym }} - Carrito</title>
</head>
<body>
    <header class="navbar navbar-expand-lg fixed-top scrolled" id="mainNav">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#hero">
                <span class="text-primary">
                    {{ $config->nombrePartido()[0] }}
                </span>

                <span class="text-white">
                    {{ $config->nombrePartido()[1] }}
                </span>
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
                    <li class="nav-item ms-2">@auth
                    
                    <div class="dropdown">
                        <button class="btn border-0 bg-transparent dropdown-toggle d-flex align-items-center gap-2"
                                data-bs-toggle="dropdown">
                    
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                 style="width:40px;height:40px;font-weight:bold;">
                    
                                {{ strtoupper(substr(Auth::user()->name,0,1)) }}
                    
                            </div>
                    
                            <span class="fw-bold text-white">
                                {{ Auth::user()->name }}
                            </span>
                        </button>
                    
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                @if (Auth::user()->isStaff())
                                    <li>
                                        <a class="dropdown-item" href="{{ route('dashboard') }}">
                                            <i class="bi bi-speedometer2 me-2"></i>
                                            Panel administrativo
                                        </a>
                                    </li>
                                @endif
                    
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person-circle me-2"></i>
                                    Mi Perfil
                                </a>
                            </li>
                    
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                    
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                    
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>
                                        Cerrar sesión
                                    </button>
                                </form>
                            </li>
                    
                        </ul>
                    </div>
                    
                    @else
                    
                    <a href="{{ route('login') }}" class="nav-link">
                         <i class="bi bi-person-circle fs-5"></i>
                    </a>
                    
                    @endauth
                       
                     
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
                <!-- Contenedor para carrito vacío -->
                <div id="emptyCart" class="text-center py-5" style="display: none;">
                    <i class="bi bi-bag-x display-1 text-muted mb-4"></i>
                    <h3>Tu carrito está vacío</h3>
                    <a href="{{ url('/shop') }}" class="btn btn-primary mt-3">Ir a la tienda</a>
                </div>
                
                <!-- Contenedor para items del carrito -->
                <div id="cartItemsContainer" style="display: none;">
                    <div class="row g-4">
                        <div class="col-lg-8" id="cartItemsList"></div>
                        <div class="col-lg-4">
                            <div class="premium-card p-4 sticky-top" style="top: 100px;">
                                <h5 class="fw-bold mb-4">Resumen</h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span id="cartSubtotal">$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-4">
                                    <span class="fw-bold fs-5">Total</span>
                                    <span class="fw-bold fs-5 text-primary" id="cartTotal">$0.00</span>
                                </div>
                                <a href="{{ url('/checkout') }}" class="btn btn-primary w-100 py-3">Pagar ahora</a>
                            </div>
                        </div>
                    </div>
                </div>
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
