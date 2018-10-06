<?php

namespace Hideyo\Ecommerce\Framework\Services\Coupon;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class CouponFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\Coupon\CouponService';
    }
}