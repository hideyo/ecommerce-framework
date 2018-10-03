<?php

namespace Hideyo\Ecommerce\Framework\Services\ProductCategory;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class ProductCategoryFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\ProductCategory\ProductCategoryService';
    }
}