<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Empresa;
use App\Observers\EmpresaObserver;
use Illuminate\Pagination\Paginator;

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
        Empresa::observe(EmpresaObserver::class);

        // Usar el partial personalizado para toda la paginación de la app
        Paginator::defaultView('partials.pagination');
    }
}
