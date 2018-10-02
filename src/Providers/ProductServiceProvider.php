<?php
namespace Hideyo\Ecommerce\Framework\Providers;

use Hideyo\Ecommerce\Framework\Product\ProductService;
use Illuminate\Support\ServiceProvider;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductRepository;

/**
 * Registering User service
 */
class ProductServiceProvider extends ServiceProvider
{

    /**
     * Binding User service
     */
    public function register()
    {
        $this->app->bind('ProductService', function ($app) {
            return new ProductService(
                // Injecting user interface to be used as user repository
                $app->make('Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductRepository'));
        });
    }
}