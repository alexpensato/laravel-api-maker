<?php

namespace Pensato\Api;

use Pensato\Api\Generator\ApiMakeCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/config.php', 'laravel-api-maker'
        );

        $this->app->singleton('command.api.make', function ($app) {
            return new ApiMakeCommand($app['files']);
        });

        $this->commands('command.api.make');
    }

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('laravel-api-maker.php'),
        ]);

//        require base_path(config('laravel-api-maker.routes_file'));

        $this->publishes([
            __DIR__.'/../templates/Providers/ApiServiceProvider.php' => app_path('Providers/ApiServiceProvider.php')
        ], 'provider');

        $this->publishes([
            __DIR__.'/../templates/Http/Middleware/Cors.php' => app_path('Http/Middleware/Cors.php')
        ], 'cors');

        $this->publishes([
            __DIR__.'/../templates/Tests/GuzzleTestCase.php' => base_path('tests/GuzzleTestCase.php')
        ], 'tests');

        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
    }
}
