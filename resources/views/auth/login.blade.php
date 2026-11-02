<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $config->nombre_gym }} - Iniciar sesión</title>
    <link rel="icon" href="{{ $config->favicon_url }}">

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
                        <img src="{{ $config->logo_url }}" alt="Logo" class="mb-2" style="width:64px;height:64px;object-fit:contain;">
                        <h4 class="fw-bold text-dark">
                            <span class="text-primary">{{ $config->nombrePartido()[0] }}</span> {{ $config->nombrePartido()[1] }}
                        </h4>
                    </a>

                    <p class="text-muted small">
                        Bienvenido de nuevo
                    </p>
                </div>

                <!-- STATUS -->
                @if (session('status'))
                    <div class="alert alert-success small">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger small">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- FORM LOGIN -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- EMAIL -->
                    <div class="mb-3">

                        <label class="form-label small fw-bold">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="tu@email.com"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >

                        @error('email')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- PASSWORD -->
                    <div class="mb-4">

                        <div class="d-flex justify-content-between">

                            <label class="form-label small fw-bold">
                                Contraseña
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                   class="small text-primary text-decoration-none">
                                    ¿Olvidaste?
                                </a>
                            @endif

                        </div>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="••••••••"
                            required
                        >

                        @error('password')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- REMEMBER -->
                    <div class="form-check mb-3">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="remember"
                            id="remember"
                        >

                        <label class="form-check-label small" for="remember">
                            Recordarme
                        </label>
                    </div>

                    <!-- BUTTON -->
                    <button type="submit"
                            class="btn btn-primary w-100 py-2 mb-3">
                        Entrar
                    </button>

                    <!-- REGISTER -->
                    <p class="text-center small text-muted">

                        ¿No tienes cuenta?

                        <a href="{{ route('register') }}"
                           class="text-primary text-decoration-none fw-bold">

                            Regístrate

                        </a>
                    </p>

                </form>

            </div>

            <div class="text-center mt-4">

                <a href="{{ url('/') }}"
                   class="text-white-50 small text-decoration-none">

                    <i class="bi bi-arrow-left me-1"></i>

                    Volver al inicio

                </a>

            </div>

        </div>

    </div>
</div>

<script src="{{ asset('js/javaScript.js') }}"></script>

</body>
</html>