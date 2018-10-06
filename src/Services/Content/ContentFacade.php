<?php

namespace Hideyo\Ecommerce\Framework\Services\Content;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class ContentFacade extends Facade
{
    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\Content\ContentService';
    }
}