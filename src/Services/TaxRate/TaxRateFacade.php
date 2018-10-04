<?php

namespace Hideyo\Ecommerce\Framework\Services\TaxRate;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class TaxRateFacade extends Facade
{
    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\TaxRate\TaxRateService';
    }
}