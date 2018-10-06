<?php

namespace Hideyo\Ecommerce\Framework\Services\Attribute;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class AttributeFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\Attribute\AttributeService';
    }
}