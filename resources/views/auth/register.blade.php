<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GYM IMPULSO - Registro</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .premium-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .gym-logo {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .gym-logo span {
            color: #ffc107;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #d1d5db;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #ffc107;
        }

        .btn-register {
            background: #ffc107;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-register:hover {
            background: #ffc107;
        }

        .text-link {
            color: #ffc107;
            text-decoration: none;
            font-weight: 600;
        }

        .text-link:hover {
            color: #ffc107;
        }

        .input-error {
            color: #ffc107;
            font-size: 0.85rem;
            margin-top: 5px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">

            <div class="col-md-5 col-lg-4">

                <div class="premium-card p-4 p-md-5">

                    <!-- LOGO -->
                    <div class="text-center mb-4">
                        <center>
                            <img src="{{ $config->logo 
                            ? asset('storage/' . $config->logo)
                            : asset('img/logo.png')
                            }}"
                                class="w-14 h-14 object-contain">
                        </center>

                        <a href="{{ url('/') }}" class="text-decoration-none">
                            <h1 class="gym-logo">
                                <span>GYM</span> IMPULSO
                            </h1>
                        </a>

                        <p class="text-muted">
                            Crea tu cuenta de entrenamiento
                        </p>

                    </div>

                    <!-- FORMULARIO -->
                    <form method="POST" action="{{ route('register') }}">

                        @csrf

                        <!-- NOMBRE -->
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Nombre Completo
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                placeholder="Ej. Juan Pérez"
                                value="{{ old('name') }}"
                                required
                                autofocus>

                            @error('name')
                            <div class="input-error">{{ $message }}</div>
                            @enderror

                        </div>

                        <!-- EMAIL -->
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Correo Electrónico
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="tu@email.com"
                                value="{{ old('email') }}"
                                required>

                            @error('email')
                            <div class="input-error">{{ $message }}</div>
                            @enderror

                        </div>

                        <!-- PASSWORD -->
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Contraseña
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Mínimo 8 caracteres"
                                required>

                            @error('password')
                            <div class="input-error">{{ $message }}</div>
                            @enderror

                        </div>

                        <!-- CONFIRM PASSWORD -->
                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Confirmar Contraseña
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                placeholder="Repite tu contraseña"
                                required>

                        </div>

                        <!-- BOTÓN -->
                        <button type="submit" class="btn btn-danger btn-register w-100 mb-3">

                            <i class="bi bi-person-plus-fill me-2"></i>
                            Crear Cuenta

                        </button>

                        <!-- LOGIN -->
                        <p class="text-center text-muted small mb-0">

                            ¿Ya tienes cuenta?

                            <a href="{{ route('login') }}" class="text-link">
                                Inicia sesión
                            </a>

                        </p>

                    </form>

                </div>

            </div>

        </div>
    </div>

</body>

</html>