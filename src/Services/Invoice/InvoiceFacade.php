<?php

namespace Hideyo\Ecommerce\Framework\Services\Invoice;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class InvoiceFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\Invoice\InvoiceService';
    }
}