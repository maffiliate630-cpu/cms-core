<?php

namespace CMSCore\Providers;

use CMSCore\Console\Commands\SwitchTenant;
use CMSCore\DataSection\DataSectionResolver;
use CMSCore\Models\ApiToken;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class CMSCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/cms.php',
            'cms'
        );

        // Singleton so per-request resolver instances are reused across calls.
        $this->app->singleton(DataSectionResolver::class);
    }

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(ApiToken::class);

        $this->publishes([
            __DIR__.'/../../config/cms.php' => config_path('cms.php'),
        ], 'cms-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SwitchTenant::class,
            ]);
        }
    }
}
