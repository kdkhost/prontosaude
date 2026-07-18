<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Força drivers baseados em arquivo para o instalador funcionar 
        // mesmo se o .env ou o servidor tiverem variáveis antigas de banco.
        if (!file_exists(storage_path('app/installed.lock'))) {
            config(['session.driver' => 'file']);
            config(['cache.default' => 'file']);
            config(['queue.default' => 'sync']);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
