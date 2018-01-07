<?php

namespace Hideyo\Ecommerce\Framework\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $frontendNamespace = 'App\Http\Controllers\Frontend';
    protected $backendNamespace = 'App\Http\Controllers\Backend';
    protected $apiNamespace = 'App\Http\Controllers\Api';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapBackendRoutes();
        $this->mapAuthBackendRoutes();
        $this->mapFrontendRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapBackendRoutes()
    {
        Route::group([
            'middleware' => ['hideyobackend','auth.hideyo.backend'],
            'prefix' => 'admin', 
            'namespace' => 'App\Http\Controllers\Backend'
        ], function ($router) {
            require base_path('routes/backend.php');
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapAuthBackendRoutes()
    {
        Route::group([
            'middleware' => ['hideyobackend'],
            'prefix' => 'admin', 
            'namespace' => 'App\Http\Controllers\Backend'
        ], function ($router) {
            require base_path('routes/auth_backend.php');
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapFrontendRoutes()
    {
        Route::group([
            'middleware' => ['web', 'detect.shop'],
            'namespace' => $this->frontendNamespace,
        ], function ($router) {
            require base_path('routes/frontend.php');
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->apiNamespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }    
}