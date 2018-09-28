<?php

namespace Hideyo\Ecommerce\Framework\Services\Shop;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class ShopFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\Shop\ShopService';
    }
}