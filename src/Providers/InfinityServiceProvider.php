<?php

namespace KyleWLawrence\Infinity\Providers;

use Illuminate\Support\ServiceProvider;
use KyleWLawrence\Infinity\Services\InfinityService;
use KyleWLawrence\Infinity\Services\NullService;

class InfinityServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider and merge config.
     *
     * @return void
     */
    public function register()
    {
        $packageName = 'infinity-laravel';
        $configPath = __DIR__.'/../../config/infinity-laravel.php';

        $this->mergeConfigFrom(
            $configPath, $packageName
        );

        $this->publishes([
            $configPath => config_path(sprintf('%s.php', $packageName)),
        ]);
    }

    /**
     * Bind service to 'Infinity' for use with Facade.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('Infinity', function () {
            $driver = config('infinity-laravel.driver', 'api');
            if (is_null($driver) || $driver === 'log') {
                return new NullService($driver === 'log');
            }

            return new InfinityService;
        });
    }
}
