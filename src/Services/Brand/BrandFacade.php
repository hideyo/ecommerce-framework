<?php

namespace Hideyo\Ecommerce\Framework\Services\Brand;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class BrandFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\Brand\BrandService';
    }
}