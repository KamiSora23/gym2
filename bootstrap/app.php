<?php

use App\Http\Middleware\EnsureEmpresaSeleccionada;
use App\Http\Middleware\EnsurePermiso;
use App\Http\Middleware\EnsurePersonal;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUsuarioActivo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'empresa' => EnsureEmpresaSeleccionada::class,
            'permiso' => EnsurePermiso::class,
            'personal' => EnsurePersonal::class,
        ]);

        // Un usuario bloqueado pierde la sesión en su siguiente petición.
        $middleware->appendToGroup('web', EnsureUsuarioActivo::class);

        // Usuarios ya autenticados que vuelven a /login o /register.
        $middleware->redirectUsersTo(fn ($request) => $request->user()?->homeRoute() ?? '/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
