<!DOCTYPE html>
<html lang="es">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    @include('partials.tema-web')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $config->nombre_gym }} - Tienda Fitness</title>
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
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#acercade">Nosotros</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}#planes">Planes</a></li>
                    <li class="nav-item"><a class="nav-link active" href="{{ url('/shop') }}">Tienda</a></li>
                    
                    <li class="nav-item ms-2">
                        <a href="{{ url('/carrito') }}" class="nav-link position-relative">
                            <i class="bi bi-bag fs-5"></i>
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                id="cartCount">0</span>
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
                                @if (Auth::user()->isCliente())
                                    <li>
                                        <a class="dropdown-item" href="{{ route('mi-cuenta') }}">
                                            <i class="bi bi-person-badge me-2"></i>
                                            Mi cuenta
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->isStaff())
                                    <li>
                                        <a class="dropdown-item" href="{{ Auth::user()->homeRoute() }}">
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



    <section class="shop-section py-1 bg-light" style="margin-top: 80px;">
        <div class="container py-5">
    

            <div class="text-center mb-5 reveal active">
                <button class="btn search-toggle-btn" type="button" data-bs-toggle="collapse"
                    data-bs-target="#categoriasGym">

                    <i class="bi bi-search"></i>
                </button><br><br>

                <!-- MENU DESPLEGABLE -->
                <div class="collapse mb-4" id="categoriasGym">

                    <div class="category-menu">

                        <button class="category-btn active" data-category="all">
                            Todos
                        </button>

                        <button class="category-btn" data-category="pesas">
                            Pesas
                        </button>

                        <button class="category-btn" data-category="cardio">
                            Cardio
                        </button>

                        <button class="category-btn" data-category="suplementos">
                            Suplementos
                        </button>

                        <button class="category-btn" data-category="accesorios">
                            Accesorios
                        </button>



                    </div>

                </div>
                <div class="text-center mb-5 reveal active">
                <h2 class="display-5 fw-bold mb-3">TIENDA <span class="text-primary">OFICIAL</span></h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Equipamiento, suplementación y accesorios de
                    nivel profesional.</p>
            </div>

                <div class="reveal row g-4">
                    <div class="col-md-6 col-lg-3 fade-in product-item" data-category="pesas">
                        <div class="product-card h-100">
                            <div class="product-image">
                                <img src="{{ asset('img/img1.jpg') }}" class="img-fluid" alt="Producto">
                            </div>
                            <div class="product-info p-4">
                                <h5 class="fw-bold mb-2">Guantes de Entrenamiento</h5>
                                <p class="text-muted mb-3">Protección y comodidad para tu entrenamiento.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-4 fw-bold text-warning">$29.99</span>
                                    <button class="btn btn-dark btn-sm">Agregar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 fade-in">
                        <div class="product-card h-100">
                            <div class="product-image">
                                <img src="{{ asset('img/img2.jpg') }}" class="img-fluid" alt="Producto">
                            </div>
                            <div class="product-info p-4">
                                <h5 class="fw-bold mb-2">Shaker Proteína</h5>
                                <p class="text-muted mb-3">BPA free, capacidad de 700ml.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-4 fw-bold text-warning">$14.99</span>
                                    <button class="btn btn-dark btn-sm">Agregar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 fade-in">
                        <div class="product-card h-100">
                            <div class="product-image">
                                <img src="{{ asset('img/img3.jpg') }}" class="img-fluid" alt="Producto">
                            </div>
                            <div class="product-info p-4">
                                <h5 class="fw-bold mb-2">Muñequeras</h5>
                                <p class="text-muted mb-3">Soporte para entrenamiento de fuerza.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-4 fw-bold text-warning">$19.99</span>
                                    <button class="btn btn-dark btn-sm">Agregar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 fade-in">
                        <div class="product-card h-100">
                            <div class="product-image">
                                <img src="{{ asset('img/img1.jpg') }}" class="img-fluid" alt="Producto">
                            </div>
                            <div class="product-info p-4">
                                <h5 class="fw-bold mb-2">Mancuernas Ajustables</h5>
                                <p class="text-muted mb-3">Set de 10kg a 40kg.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-4 fw-bold text-warning">$149.99</span>
                                    <button class="btn btn-dark btn-sm">Agregar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    


                    <div class="reveal row g-4">
                        <div class="col-md-6 col-lg-3 fade-in product-item" data-category="cardio">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="{{ asset('img/img1.jpg') }}" class="img-fluid" alt="Producto">
                                </div>
                                <div class="product-info p-4">
                                    <h5 class="fw-bold mb-2">Guantes de Entrenamiento</h5>
                                    <p class="text-muted mb-3">Protección y comodidad para tu entrenamiento.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-warning">$29.99</span>
                                        <button class="btn btn-dark btn-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 fade-in">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="{{ asset('img/img2.jpg') }}" class="img-fluid" alt="Producto">
                                </div>
                                <div class="product-info p-4">
                                    <h5 class="fw-bold mb-2">Shaker Proteína</h5>
                                    <p class="text-muted mb-3">BPA free, capacidad de 700ml.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-warning">$14.99</span>
                                        <button class="btn btn-dark btn-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 fade-in">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="{{ asset('img/img3.jpg') }}" class="img-fluid" alt="Producto">
                                </div>
                                <div class="product-info p-4">
                                    <h5 class="fw-bold mb-2">Muñequeras</h5>
                                    <p class="text-muted mb-3">Soporte para entrenamiento de fuerza.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-warning">$19.99</span>
                                        <button class="btn btn-dark btn-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 fade-in">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="{{ asset('img/img1.jpg') }}" class="img-fluid" alt="Producto">
                                </div>
                                <div class="product-info p-4">
                                    <h5 class="fw-bold mb-2">Mancuernas Ajustables</h5>
                                    <p class="text-muted mb-3">Set de 10kg a 40kg.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-warning">$149.99</span>
                                        <button class="btn btn-dark btn-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>





                    <div class="reveal row g-4">
                        <div class="col-md-6 col-lg-3 fade-in product-item" data-category="suplementos">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="{{ asset('img/img1.jpg') }}" class="img-fluid" alt="Producto">
                                </div>
                                <div class="product-info p-4">
                                    <h5 class="fw-bold mb-2">Guantes de Entrenamiento</h5>
                                    <p class="text-muted mb-3">Protección y comodidad para tu entrenamiento.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-warning">$29.99</span>
                                        <button class="btn btn-dark btn-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 fade-in">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="{{ asset('img/img2.jpg') }}" class="img-fluid" alt="Producto">
                                </div>
                                <div class="product-info p-4">
                                    <h5 class="fw-bold mb-2">Shaker Proteína</h5>
                                    <p class="text-muted mb-3">BPA free, capacidad de 700ml.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-warning">$14.99</span>
                                        <button class="btn btn-dark btn-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 fade-in">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="{{ asset('img/img3.jpg') }}" class="img-fluid" alt="Producto">
                                </div>
                                <div class="product-info p-4">
                                    <h5 class="fw-bold mb-2">Muñequeras</h5>
                                    <p class="text-muted mb-3">Soporte para entrenamiento de fuerza.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-warning">$19.99</span>
                                        <button class="btn btn-dark btn-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 fade-in">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="{{ asset('img/img1.jpg') }}" class="img-fluid" alt="Producto">
                                </div>
                                <div class="product-info p-4">
                                    <h5 class="fw-bold mb-2">Mancuernas Ajustables</h5>
                                    <p class="text-muted mb-3">Set de 10kg a 40kg.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-warning">$149.99</span>
                                        <button class="btn btn-dark btn-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>






                    <div class="reveal row g-4">
                        <div class="col-md-6 col-lg-3 fade-in product-item" data-category="accesorios">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="{{ asset('img/img1.jpg') }}" class="img-fluid" alt="Producto">
                                </div>
                                <div class="product-info p-4">
                                    <h5 class="fw-bold mb-2">Guantes de Entrenamiento</h5>
                                    <p class="text-muted mb-3">Protección y comodidad para tu entrenamiento.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-warning">$29.99</span>
                                        <button class="btn btn-dark btn-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 fade-in">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="{{ asset('img/img2.jpg') }}" class="img-fluid" alt="Producto">
                                </div>
                                <div class="product-info p-4">
                                    <h5 class="fw-bold mb-2">Shaker Proteína</h5>
                                    <p class="text-muted mb-3">BPA free, capacidad de 700ml.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-warning">$14.99</span>
                                        <button class="btn btn-dark btn-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 fade-in">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="{{ asset('img/img3.jpg') }}" class="img-fluid" alt="Producto">
                                </div>
                                <div class="product-info p-4">
                                    <h5 class="fw-bold mb-2">Muñequeras</h5>
                                    <p class="text-muted mb-3">Soporte para entrenamiento de fuerza.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-warning">$19.99</span>
                                        <button class="btn btn-dark btn-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 fade-in">
                            <div class="product-card h-100">
                                <div class="product-image">
                                    <img src="{{ asset('img/img1.jpg') }}" class="img-fluid" alt="Producto">
                                </div>
                                <div class="product-info p-4">
                                    <h5 class="fw-bold mb-2">Mancuernas Ajustables</h5>
                                    <p class="text-muted mb-3">Set de 10kg a 40kg.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-4 fw-bold text-warning">$149.99</span>
                                        <button class="btn btn-dark btn-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>











                    
                </div>

                <div class="row g-4" id="productsGrid">
                    <!-- Se llenará con JS -->
                </div>

            </div>
    </section>

    <footer class="py-5 bg-dark">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <a class="navbar-brand fw-bold mb-3 d-block" href="{{ url('/') }}">
                        <span class="text-primary">GYM</span> <span class="text-white">IMPULSO</span>
                    </a>
                    <p class="text-white-50 small mb-4">Tu mejor versión empieza aquí. Únete a nuestra comunidad fitness
                        y transforma tu vida con tecnología y pasión.</p>
                    <div class="social-links d-flex gap-3">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i
                                class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i
                                class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i
                                class="bi bi-twitter-x"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="text-white fw-bold mb-4 small text-uppercase">Navegación</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ url('/') }}#acercade"
                                class="text-white-50 text-decoration-none">Nosotros</a></li>
                        <li class="mb-2"><a href="{{ url('/') }}#planes"
                                class="text-white-50 text-decoration-none">Planes</a></li>
                        <li class="mb-2"><a href="{{ url('/shop') }}" class="text-white-50 text-decoration-none">Tienda</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="text-white fw-bold mb-4 small text-uppercase">Soporte</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Envíos</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Devoluciones</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="text-white fw-bold mb-4 small text-uppercase">Contacto</h6>
                    <ul class="list-unstyled text-white-50 small">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> Av. Principal 123</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i> +1 (234) 567-8900</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> info@gymimpulso.com</li>
                    </ul>
                </div>
            </div>
            <div class="border-top border-secondary mt-5 pt-4 text-center">
                <p class="text-white-50 small mb-0">&copy; 2026 GYM IMPULSO. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/javaScript.js') }}"></script>
</body>

</html>