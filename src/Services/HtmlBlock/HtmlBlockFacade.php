<?php

namespace Hideyo\Ecommerce\Framework\Services\HtmlBlock;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class HtmlBlockFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\HtmlBlock\HtmlBlockService';
    }
}