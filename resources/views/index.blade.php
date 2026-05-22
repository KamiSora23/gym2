<!DOCTYPE html>
<html lang="es">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM IMPULSO - Tu mejor versión empieza aquí</title>
</head>

<body>
    <header class="navbar navbar-expand-lg fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#hero">
                <span class="text-primary">
                    {{ explode(' ', $config->nombre_gym)[0] ?? 'GYM' }}
                </span>

                <span class="text-white">
                    {{ explode(' ', $config->nombre_gym)[1] ?? 'IMPULSO' }}
                </span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#acercade">Nosotros</a></li>
                    <li class="nav-item"><a class="nav-link" href="#planes">Planes</a></li>
                    <li class="nav-item"><a class="nav-link" href="#entrenadores">Staff</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tienda">Tienda</a></li>

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

    <section id="hero" class="hero-section position-relative">
        <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel">
            <div class="carousel-inner h-100">
                <div class="carousel-item active h-100">
                    <img src="{{ asset('img/img2.jpg') }}" class="hero-image d-block w-100" alt="Gimnasio">
                    <div class="carousel-overlay"></div>
                    <div class="carousel-caption d-flex align-items-center justify-content-center">
                        <div class="text-center hero-content">
                            <h1 class="display-1 fw-bold text-white mb-3 fade-up">DOMINA TU <span
                                    class="text-primary">CUERPO</span></h1>
                            <p class="fs-4 text-white-50 mb-5 fade-up" style="animation-delay: 0.2s;">Entrenamiento de
                                élite para resultados extraordinarios.</p>
                            <div class="d-flex gap-3 justify-content-center fade-up" style="animation-delay: 0.4s;">
                                <a href="#planes" class="btn btn-primary btn-lg px-5 py-3">Empezar Ahora</a>
                                <a href="#acercade" class="btn btn-outline-light btn-lg px-5 py-3">Saber Más</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="acercade" class="py-5 bg-white">
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 reveal">
                    <div class="premium-card overflow-hidden">
                        <img src="{{ asset('img/img3.jpg') }}" class="img-fluid w-100" style="transition: transform 0.5s;"
                            onmouseover="this.style.transform='scale(1.05)'"
                            onmouseout="this.style.transform='scale(1)'" alt="Gimnasio">
                    </div>
                </div>
                <div class="col-lg-6 reveal">
                    <h6 class="text-primary fw-bold text-uppercase mb-2">Sobre Nosotros</h6>
                    <h2 class="display-5 fw-bold mb-4">MÁS DE 10 AÑOS TRANSFORMANDO VIDAS</h2>
                    <p class="text-muted mb-4">En GYM IMPULSO, el fitness es un estilo de vida. Ofrecemos herramientas
                        premium para que conquistes tu mejor versión con tecnología y soporte experto.</p>
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-primary fs-4"></i>
                                <span class="fw-bold">Elite Coaching</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-clock-history text-primary fs-4"></i>
                                <span class="fw-bold">Acceso 24/7</span>
                            </div>
                        </div>
                    </div>
                    <a href="#contactanos" class="btn btn-dark-ui px-4 py-3">Únete a la comunidad</a>
                </div>
            </div>
        </div>
    </section>

    <section id="planes" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5 reveal">
                <h2 class="display-5 fw-bold mb-3">ELIGE TU <span class="text-primary">PLAN</span></h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Planes diseñados para cada nivel de compromiso.
                    Sin letras pequeñas, solo resultados.</p>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- Plan Básico -->
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="premium-card plan-card p-4 h-100">
                        <div class="mb-4">
                            <i class="bi bi-lightning text-primary fs-2"></i>
                            <h4 class="fw-bold mt-2">Plan Básico</h4>
                            <div class="price">$29<span class="fs-6 text-muted fw-normal">/mes</span></div>
                        </div>
                        <ul class="list-unstyled mb-4 flex-grow-1">
                            <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> Acceso a máquinas</li>
                            <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> Zona de cardio</li>
                            <li class="mb-2 text-muted"><i class="bi bi-x me-2"></i> Clases grupales</li>
                        </ul>
                        <button class="btn btn-outline-dark-ui w-100 mb-2 btn-sm"
                            onclick="togglePlanDetails('basico')">Detalles</button>
                        <button class="btn btn-primary w-100 add-to-cart-plan" data-plan="basico">Agregar al
                            carrito</button>

                        <div id="details-basico" class="plan-details mt-3 text-muted small">
                            <hr>
                            <p>Incluye acceso a vestidores, duchas y locker diario. Horario: 6am - 10pm.</p>
                        </div>
                    </div>
                </div>

                <!-- Plan Fitness -->
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="premium-card plan-card popular p-4 h-100">
                        <div class="mb-4">
                            <i class="bi bi-fire text-primary fs-2"></i>
                            <h4 class="fw-bold mt-2">Plan Fitness</h4>
                            <div class="price">$49<span class="fs-6 text-muted fw-normal">/mes</span></div>
                        </div>
                        <ul class="list-unstyled mb-4 flex-grow-1">
                            <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> Acceso total</li>
                            <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> Clases grupales</li>
                            <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> App de seguimiento</li>
                        </ul>
                        <button class="btn btn-outline-dark-ui w-100 mb-2 btn-sm"
                            onclick="togglePlanDetails('fitness')">Detalles</button>
                        <button class="btn btn-primary w-100 add-to-cart-plan" data-plan="fitness">Agregar al
                            carrito</button>

                        <div id="details-fitness" class="plan-details mt-3 text-muted small">
                            <hr>
                            <p>Acceso a todas las clases de Yoga, Zumba y Funcional. Invitado gratis 1 vez al mes.</p>
                        </div>
                    </div>
                </div>

                <!-- Plan Elite -->
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="premium-card plan-card p-4 h-100">
                        <div class="mb-4">
                            <i class="bi bi-trophy text-primary fs-2"></i>
                            <h4 class="fw-bold mt-2">Plan Elite</h4>
                            <div class="price">$89<span class="fs-6 text-muted fw-normal">/mes</span></div>
                        </div>
                        <ul class="list-unstyled mb-4 flex-grow-1">
                            <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> Todo lo anterior</li>
                            <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> Coach personal</li>
                            <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> Plan nutricional</li>
                        </ul>
                        <button class="btn btn-outline-dark-ui w-100 mb-2 btn-sm"
                            onclick="togglePlanDetails('elite')">Detalles</button>
                        <button class="btn btn-primary w-100 add-to-cart-plan" data-plan="elite">Agregar al
                            carrito</button>

                        <div id="details-elite" class="plan-details mt-3 text-muted small">
                            <hr>
                            <p>Sesiones 1-a-1 cada semana, zona VIP y suplementación básica incluida.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="entrenadores" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5 reveal">
                <h2 class="display-5 fw-bold mb-3">NUESTRO <span class="text-primary">STAFF</span></h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Expertos certificados listos para llevar tu
                    rendimiento al siguiente nivel.</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-6 col-md-4 col-lg-3 reveal">
                    <div class="trainer-card">
                        <div class="trainer-img-wrapper">
                            <img src="{{ asset('img/img2.jpg') }}" alt="Coach">
                        </div>
                        <div class="trainer-info-overlay">
                            <h6 class="fw-bold mb-0">Carlos M.</h6>
                            <small class="text-primary">Powerlifting</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3 reveal">
                    <div class="trainer-card">
                        <div class="trainer-img-wrapper">
                            <img src="{{ asset('img/img3.jpg') }}" alt="Coach">
                        </div>
                        <div class="trainer-info-overlay">
                            <h6 class="fw-bold mb-0">Elena R.</h6>
                            <small class="text-primary">Yoga Expert</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3 reveal">
                    <div class="trainer-card">
                        <div class="trainer-img-wrapper">
                            <img src="{{ asset('img/img1.jpg') }}" alt="Coach">
                        </div>
                        <div class="trainer-info-overlay">
                            <h6 class="fw-bold mb-0">Marcus J.</h6>
                            <small class="text-primary">HIIT Coach</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="tienda" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5 reveal">
                <h6 class="text-primary fw-bold text-uppercase mb-2">Shop</h6>
                <h2 class="display-5 fw-bold mb-3">EQUIPAMIENTO <span class="text-primary">PRO</span></h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Selección exclusiva de productos premium para
                    potenciar tu entrenamiento diario.</p>
            </div>
            <div class="reveal row g-4">
                <div class="col-md-6 col-lg-3 fade-in">
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

            <div class="text-center mt-5 reveal">
                <a href="{{ url('/shop') }}" class="btn btn-outline-dark-ui px-5 py-3">Ver catálogo completo</a>
            </div>

    </section>
    </section>
    <section id="ubicacion" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5 reveal">
                <h6 class="text-primary fw-bold text-uppercase mb-2">Presencia</h6>
                <h2 class="display-5 fw-bold mb-3">UBÍCANOS <span class="text-primary">AHORA</span></h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Visítanos en nuestra sede central y comienza tu
                    transformación hoy mismo.</p>
            </div>

            <div class="map-container reveal">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3901.515589462347!2d-77.042793!3d-12.046374!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105c8c0f6c7f3df%3A0x1a2b3c4d5e6f7g8h!2sLima!5e0!3m2!1ses!2spe!4v1690000000000"
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </section>


    <section id="contactanos" class="py-5 bg-white">
        <div class="container py-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5 reveal">
                    <h6 class="text-primary fw-bold text-uppercase mb-2">Contacto</h6>
                    <h2 class="display-5 fw-bold mb-4">ENVÍA TU CONSULTA</h2>
                    <p class="text-muted mb-5">Rellena el formulario y conquistemos juntos cada meta de entrenamiento.
                    </p>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="step-number bg-light">
                            <i class="bi bi-geo-alt text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 small">Dirección</h6>
                            <p class="text-muted mb-0 small">
                                {{ $config->direccion ?? 'Av. Principal 123, Lima' }}
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="step-number bg-light">
                            <i class="bi bi-envelope text-primary"></i>
                        </div>
                        <div class="text-xs text-gray-400 break-all leading-4 max-w-[180px]>
                            <h6 class=" fw-bold mb-0 small">Email</h6>
                            <div class="text-xs text-gray-400 break-all leading-4 max-w-[180px]">
                                <p class="text-muted mb-0 small ">
                                    {{ $config->email ?? 'info@gymimpulso.com' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="step-number bg-light">
                            <i class="bi bi-telephone text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 small">Teléfono</h6>
                            <p class="text-muted mb-0 small">
                                {{ $config->telefono ?? '+51 961 179 515' }}
                            </p>
                        </div>
                    </div>

                    <!-- HORARIO PROFESIONAL -->
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="step-number bg-light">
                            <i class="bi bi-clock text-primary"></i>
                        </div>

                        <div>
                            <h6 class="fw-bold mb-0 small">Horario</h6>

                            <div class="text-muted small">

                                <p>Lunes: {{ $config->horario_lunes ?? 'No definido' }}</p>
                                <p>Martes: {{ $config->horario_martes ?? 'No definido' }}</p>
                                <p>Miércoles: {{ $config->horario_miercoles ?? 'No definido' }}</p>
                                <p>Jueves: {{ $config->horario_jueves ?? 'No definido' }}</p>
                                <p>Viernes: {{ $config->horario_viernes ?? 'No definido' }}</p>
                                <p>Sábado: {{ $config->horario_sabado ?? 'No definido' }}</p>
                                <p>Domingo: {{ $config->horario_domingo ?? 'No definido' }}</p>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 reveal">
                    <div class="premium-card p-4 p-md-5">
                        <form id="contactForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" class="form-control" placeholder="Nombre">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Apellido</label>
                                    <input type="text" class="form-control" placeholder="Apellido">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" placeholder="tu@email.com">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Mensaje</label>
                                    <textarea class="form-control" rows="4"
                                        placeholder="¿En qué podemos ayudarte?"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100 py-3 mt-2">Enviar
                                        Mensaje</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-5 bg-dark">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <a class="navbar-brand fw-bold mb-3 d-block" href="#hero">
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
                        <li class="mb-2"><a href="#acercade" class="text-white-50 text-decoration-none">Nosotros</a>
                        </li>
                        <li class="mb-2"><a href="#planes" class="text-white-50 text-decoration-none">Planes</a></li>
                        <li class="mb-2"><a href="{{ url('/shop') }}" class="text-white-50 text-decoration-none">Tienda</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="text-white fw-bold mb-4 small text-uppercase">Soporte</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Privacidad</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Términos</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="text-white fw-bold mb-4 small text-uppercase">Contacto</h6>

                    <ul class="list-unstyled text-white-50 small">

                        <li class="mb-2">
                            <i class="bi bi-geo-alt me-2"></i>
                            {{ $config->direccion ?? 'Av. Principal 123' }}
                        </li>

                        <li class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            {{ $config->telefono ?? '+1 (234) 567-8900' }}
                        </li>

                        <li class="mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            {{ $config->email ?? 'info@gymimpulso.com' }}
                        </li>

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