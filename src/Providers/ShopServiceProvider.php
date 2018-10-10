<?php
namespace Hideyo\Ecommerce\Framework\Providers;

use Hideyo\Ecommerce\Framework\Shop\ShopService;
use Illuminate\Support\ServiceProvider;
use Hideyo\Ecommerce\Framework\Services\Shop\Entity\ShopRepositoryInterface;

/**
 * Registering User service
 */
class ShopServiceProvider extends ServiceProvider
{
    /**
     * Binding User service
     */
    public function register()
    {
        $this->app->bind('ShopService', function ($app) {
            return new ShopService(
                // Injecting user interface to be used as user repository
                $app->make('Hideyo\Ecommerce\Framework\Services\Shop\Entity\ShopRepositoryInterface'));
        });
    }
}