<?php

namespace Hideyo\Ecommerce\Framework\Services\Product;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class ProductFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\Product\ProductService';
    }
}