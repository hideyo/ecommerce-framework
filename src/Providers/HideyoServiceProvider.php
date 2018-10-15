<?php

namespace Hideyo\Ecommerce\Framework\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class HideyoServiceProvider.
 */
class HideyoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
	    $this->publishes([
	        __DIR__.'/../database/migrations/' => database_path('migrations')
	    ], 'migrations');

	    $this->publishes([
	        __DIR__.'/../database/seeds/' => database_path('seeds')
	    ], 'seeds');

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('hideyo', function () {
            return true;
        });
    }
}
