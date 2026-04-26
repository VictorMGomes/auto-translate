<?php

declare(strict_types=1);

namespace Victormgomes\AutoTranslate;

use Illuminate\Support\ServiceProvider;

class AutoTranslateServiceProvider extends ServiceProvider
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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/migrations/2026_04_25_000000_create_auto_translations_table.php' => database_path('migrations/2026_04_25_000000_create_auto_translations_table.php'),
            ], 'auto-translate-migrations');
        }
    }
}
