<?php

namespace OiLab\LaravelDocumentation;

use Illuminate\Support\ServiceProvider;
use OiLab\LaravelDocumentation\Console\Commands\GenDocIndex;
use OiLab\LaravelDocumentation\Console\Commands\GenDocNav;
use OiLab\LaravelDocumentation\Console\Commands\InstallDocumentation;
use OiLab\LaravelDocumentation\Services\DocumentationService;

class OiLaravelDocumentationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/oi-documentation.php',
            'oi-documentation'
        );

        $this->app->singleton(DocumentationService::class, function ($app) {
            return new DocumentationService;
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/oi-documentation.php' => config_path('oi-documentation.php'),
            ], 'oi-documentation-config');

            $this->publishes([
                __DIR__.'/../stubs/docs' => base_path('resources/docs'),
            ], 'oi-documentation-stubs');

            $this->publishes([
                __DIR__.'/../stubs/routes/documentation.php' => base_path('routes/documentation.php'),
            ], 'oi-documentation-routes');

            $this->commands([
                GenDocNav::class,
                GenDocIndex::class,
                InstallDocumentation::class,
            ]);
        }

        if (config('oi-documentation.route.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../stubs/routes/documentation.php');
        }
    }
}
