<?php

namespace Hideyo\Ecommerce\Framework\Services\ExtraField;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class ExtraFieldFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\ExtraField\ExtraFieldService';
    }
}