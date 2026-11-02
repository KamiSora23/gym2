<?php

namespace App\Providers;

use App\Models\Auditoria;
use App\Models\Configuracion;
use App\Support\Autorizacion;
use App\Support\CatalogoPermisos;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Se resuelve al renderizar una vista (no al arrancar la app),
        // así "php artisan migrate" funciona aunque la base de datos esté vacía.
        // @can('sales.cancel') y $user->can(...) usan el motor de autorización.
        Gate::before(function ($user, string $ability) {
            if (array_key_exists($ability, CatalogoPermisos::PERMISOS)) {
                return Autorizacion::puede($user, $ability);
            }

            return null;
        });

        Event::listen(Login::class, function (Login $evento) {
            Auditoria::registrar('sesion.login', "Inició sesión: {$evento->user->email}", null, [], $evento->user->id_empresa);
        });

        View::composer('*', function ($view) {
            $view->with('config', Configuracion::actual());
        });
    }
}
