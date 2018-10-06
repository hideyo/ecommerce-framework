<?php

namespace Hideyo\Ecommerce\Framework\Services\Redirect;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class RedirectFacade extends Facade
{
    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\Redirect\RedirectService';
    }
}