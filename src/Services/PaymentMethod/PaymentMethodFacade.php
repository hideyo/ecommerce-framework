<?php

namespace Hideyo\Ecommerce\Framework\Services\PaymentMethod;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class PaymentMethodFacade extends Facade
{
    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\PaymentMethod\PaymentMethodService';
    }
}