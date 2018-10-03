<?php

namespace Hideyo\Ecommerce\Framework\Services\Faq;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class FaqFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\Faq\FaqService';
    }
}