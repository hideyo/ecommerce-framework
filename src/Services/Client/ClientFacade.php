<?php

namespace Hideyo\Ecommerce\Framework\Services\Client;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class ClientFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\Client\ClientService';
    }
}