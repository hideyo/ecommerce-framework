<?php

namespace Hideyo\Ecommerce\Framework\Services\Order;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class OrderStatusFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\Order\OrderStatusService';
    }
}