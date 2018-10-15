<?php

namespace Hideyo\Ecommerce\Framework\Services\Exception;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class ExceptionFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\Exception\ExceptionService';
    }
}