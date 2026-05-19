<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM IMPULSO - Iniciar Sesión</title>
    <style>
        body {
            background: var(--dark);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="premium-card p-4 p-md-5 reveal active">
                    <div class="text-center mb-4">
                        <a href="{{ url('/') }}" class="text-decoration-none">
                            <h4 class="fw-bold text-dark"><span class="text-primary">GYM</span> IMPULSO</h4>
                        </a>
                        <p class="text-muted small">Bienvenido de nuevo</p>
                    </div>

                    <form id="loginForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" class="form-control" placeholder="tu@email.com" required>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label class="form-label small fw-bold">Contraseña</label>
                                <a href="#" class="small text-primary text-decoration-none">¿Olvidaste?</a>
                            </div>
                            <input type="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Entrar</button>
                        <p class="text-center small text-muted">
                            ¿No tienes cuenta? <a href="{{ url('/registro') }}" class="text-primary text-decoration-none fw-bold">Regístrate</a>
                        </p>
                    </form>
                </div>
                
                <div class="text-center mt-4">
                    <a href="{{ url('/') }}" class="text-white-50 small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Volver al inicio
                    </a>
                    <!-- Link para Futuro Panel Administrativo -->
                    <!-- <a href="admin/login.html" class="text-white-50 ms-3 small text-decoration-none"><i class="bi bi-shield-lock"></i></a> -->
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/javaScript.js') }}"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            window.location.href = '{{ url('/dashboard') }}';
        });
    </script>
</body>
</html>
