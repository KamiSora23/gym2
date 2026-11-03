<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    @include('partials.tema-web')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $config->nombre_gym }} - Checkout</title>
</head>
<body>
    <!-- Navbar Compacto -->
    <header class="navbar navbar-expand-lg fixed-top scrolled" id="mainNav">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                @if ($config->logo)<img src="{{ $config->logo_url }}" alt="" style="height:34px; width:auto; margin-right:.4rem; vertical-align:middle;">@endif
                <span class="text-primary">
                    {{ $config->nombrePartido()[0] }}
                </span>

                <span class="text-white">
                    {{ $config->nombrePartido()[1] }}
                </span>
            </a>
            <a href="{{ url('/carrito') }}" class="nav-link text-white ms-auto me-3">
                <i class="bi bi-arrow-left me-1"></i> Volver al carrito
            </a>
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
        </div>
    </header>

    <section class="py-5 bg-light" style="margin-top: 80px; min-height: 80vh;">
        <div class="container py-5">
            <div class="mb-4 reveal active">
                <h2 class="display-6 fw-bold">CONFIRMAR <span class="text-primary">PEDIDO</span></h2>
                <p class="text-muted mb-0">Reserva ahora, paga y recoge en recepción. No pedimos datos de tarjeta en la web.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row g-4 justify-content-center">
                <div class="col-lg-7">
                    @guest
                        <div class="premium-card p-4">
                            <h5 class="fw-bold mb-2"><i class="bi bi-person-circle text-primary me-2"></i>Inicia sesión para continuar</h5>
                            <p class="text-muted">Tu carrito se guarda en este dispositivo. Ingresa o crea tu cuenta y vuelve aquí para confirmar el pedido.</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('login') }}" class="btn btn-primary px-4">Iniciar sesión</a>
                                <a href="{{ route('register') }}" class="btn btn-outline-dark px-4">Crear cuenta</a>
                            </div>
                        </div>
                    @else
                        @if (! $esCliente)
                            <div class="alert alert-warning">Estás con una cuenta del personal. Los pedidos web los hacen los clientes desde su cuenta; para vender usa el panel administrativo.</div>
                        @else
                            <form method="POST" action="{{ route('pedidos.store') }}" class="premium-card p-4" id="formPedido">
                                @csrf
                                <input type="hidden" name="items" id="pedidoItems" value="[]">

                                <h5 class="fw-bold mb-3"><i class="bi bi-person-check text-primary me-2"></i>Tus datos</h5>
                                <p class="mb-4">{{ Auth::user()->name }} · <span class="text-muted">{{ Auth::user()->email }}</span></p>

                                <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt text-primary me-2"></i>¿Dónde lo recoges?</h5>
                                <select name="id_sucursal" class="form-select mb-4" required>
                                    @foreach ($sucursales as $s)
                                        <option value="{{ $s->id_sucursal }}" @selected(old('id_sucursal', $sedePreferida) == $s->id_sucursal)>{{ $s->nombre }}{{ $s->direccion ? ' · '.$s->direccion : '' }}</option>
                                    @endforeach
                                </select>

                                <h5 class="fw-bold mb-3"><i class="bi bi-wallet2 text-primary me-2"></i>¿Cómo pagarás en recepción?</h5>
                                <div class="row g-2 mb-4">
                                    @foreach (['efectivo' => ['bi-cash', 'Efectivo'], 'tarjeta' => ['bi-credit-card', 'Tarjeta'], 'yape' => ['bi-phone', 'Yape / Plin'], 'transferencia' => ['bi-bank', 'Transferencia']] as $valor => [$icono, $texto])
                                        <div class="col-6 col-md-3">
                                            <label class="premium-card p-3 text-center d-block h-100" style="cursor:pointer;">
                                                <input type="radio" name="metodo_pago" value="{{ $valor }}" class="form-check-input mb-2" @checked(old('metodo_pago', 'efectivo') === $valor)>
                                                <i class="bi {{ $icono }} fs-4 text-primary d-block"></i>
                                                <span class="small fw-bold">{{ $texto }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                <p class="text-muted small">Al confirmar, reservamos los productos por ti. Si eliges una membresía, se activa cuando pagues en recepción. Puedes cancelar el pedido desde «Mi cuenta» mientras esté pendiente.</p>
                                <button type="submit" class="btn btn-primary w-100 py-3" id="confirmarPedido" disabled>Confirmar pedido</button>
                            </form>
                        @endif
                    @endguest
                </div>

                <div class="col-lg-4">
                    <div class="premium-card p-4">
                        <h6 class="fw-bold mb-4">Tu pedido</h6>
                        <div id="checkoutItems"></div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span data-checkout-total>{{ dinero(0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total a pagar en recepción</span>
                            <span class="fw-bold text-primary fs-5" data-checkout-total>{{ dinero(0) }}</span>
                        </div>
                        <a href="{{ url('/carrito') }}" class="btn btn-outline-dark w-100 btn-sm">Editar carrito</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-5">
        <div class="container text-center">
            <p class="text-white-50 small mb-0">&copy; {{ date('Y') }} {{ $config->nombre_gym }}. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/javaScript.js') }}"></script>

</body>
</html>
