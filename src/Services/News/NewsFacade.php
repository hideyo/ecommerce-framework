<?php

namespace Hideyo\Ecommerce\Framework\Services\News;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class NewsFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\News\NewsService';
    }
}