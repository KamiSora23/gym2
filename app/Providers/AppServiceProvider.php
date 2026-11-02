<?php

namespace App\Providers;

use App\Models\Configuracion;
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
        View::composer('*', function ($view) {
            $view->with('config', Configuracion::actual());
        });
    }
}
