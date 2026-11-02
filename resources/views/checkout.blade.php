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
            <a class="navbar-brand fw-bold" href="#hero">
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

    <section class="py-5 bg-light" style="margin-top: 80px;">
        <div class="container py-5">
            <div class="row g-4 justify-content-center">
                
                <!-- Columna Izquierda: Pasos de Pago -->
                <div class="col-lg-7">
                    
                    <!-- Paso 1: Email -->
                    <div class="checkout-step active" id="step-1">
                        <div class="step-header" onclick="goToStep(1)">
                            <div class="d-flex align-items-center">
                                <div class="step-number">1</div>
                                <h6 class="fw-bold mb-0">Información de Contacto</h6>
                            </div>
                            <i class="bi bi-check-lg text-success d-none step-check"></i>
                        </div>
                        <div class="step-content">
                            <div class="mb-3">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="checkout-email" placeholder="ejemplo@gym.com">
                            </div>
                            <button class="btn btn-primary w-100" onclick="nextStep(2)">Continuar</button>
                        </div>
                    </div>

                    <!-- Paso 2: Datos y Pago -->
                    <div class="checkout-step" id="step-2">
                        <div class="step-header" onclick="goToStep(2)">
                            <div class="d-flex align-items-center">
                                <div class="step-number">2</div>
                                <h6 class="fw-bold mb-0">Envío y Pago</h6>
                            </div>
                            <i class="bi bi-check-lg text-success d-none step-check"></i>
                        </div>
                        <div class="step-content">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" class="form-control" placeholder="Nombre">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Apellido</label>
                                    <input type="text" class="form-control" placeholder="Apellido">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Dirección</label>
                                    <input type="text" class="form-control" placeholder="Calle, Número, Dpto">
                                </div>
                            </div>
                            
                            <h6 class="fw-bold mb-3 small text-uppercase">Método de Pago</h6>
                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <div class="premium-card p-3 text-center border-primary bg-light" style="cursor: pointer;">
                                        <i class="bi bi-credit-card fs-4 text-primary"></i>
                                        <div class="small fw-bold">Tarjeta</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="premium-card p-3 text-center" style="cursor: pointer;">
                                        <i class="bi bi-qr-code-scan fs-4 text-muted"></i>
                                        <div class="small fw-bold">Yape / Plin</div>
                                    </div>
                                </div>
                            </div>
                            
                            <button class="btn btn-primary w-100" onclick="nextStep(3)">Continuar</button>
                        </div>
                    </div>

                    <!-- Paso 3: Confirmación -->
                    <div class="checkout-step" id="step-3">
                        <div class="step-header">
                            <div class="d-flex align-items-center">
                                <div class="step-number">3</div>
                                <h6 class="fw-bold mb-0">Revisión Final</h6>
                            </div>
                        </div>
                        <div class="step-content">
                            <p class="text-muted small">Al hacer clic en "Finalizar Compra", aceptas nuestros términos y condiciones.</p>
                            <button class="btn btn-primary w-100 py-3" id="confirmPurchaseBtn">Finalizar Compra</button>
                        </div>
                    </div>

                </div>

                <!-- Columna Derecha: Resumen -->
                <div class="col-lg-4">
                    <div class="premium-card p-4">
                        <h6 class="fw-bold mb-4">Tu Pedido</h6>
                        <div id="checkoutItems">
                            <!-- JS fill -->
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span id="subtotal-step3">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold text-primary fs-5" id="total-step3">$0.00</span>
                        </div>
                        <div class="p-3 bg-light rounded-3 text-center small text-muted">
                            <i class="bi bi-shield-check me-1"></i> Pago 100% seguro
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <footer class="py-5">
        <div class="container text-center">
            <p class="text-white-50 small mb-0">&copy; 2026 GYM IMPULSO. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/javaScript.js') }}"></script>
    <script>
        // Override JS for checkout page specifically if needed
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('checkoutItems');
            const cart = JSON.parse(localStorage.getItem('gym_cart')) || [];
            let total = 0;
            
            if (container) {
                container.innerHTML = cart.map(item => {
                    total += item.price * item.qty;
                    return `
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="small">
                                <div class="fw-bold">${item.name}</div>
                                <div class="text-muted">Cant: ${item.qty}</div>
                            </div>
                            <span class="small fw-bold">$${(item.price * item.qty).toFixed(2)}</span>
                        </div>
                    `;
                }).join('');
                
                document.getElementById('subtotal-step3').textContent = `$${total.toFixed(2)}`;
                document.getElementById('total-step3').textContent = `$${total.toFixed(2)}`;
            }

            const confirmBtn = document.getElementById('confirmPurchaseBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', () => {
                    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando...';
                    confirmBtn.disabled = true;
                    setTimeout(() => {
                        alert('¡Compra realizada con éxito! Recibirás un correo en breve.');
                        localStorage.removeItem('gym_cart');
                        window.location.href = '{{ url('/') }}';
                    }, 2000);
                });
            }
        });
    </script>
</body>
</html>
