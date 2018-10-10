<?php

namespace Hideyo\Ecommerce\Framework\Services\SendingMethod;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class SendingMethodFacade extends Facade
{
    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\SendingMethod\SendingMethodService';
    }
}