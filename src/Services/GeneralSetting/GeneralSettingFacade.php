<?php

namespace Hideyo\Ecommerce\Framework\Services\GeneralSetting;

use \Illuminate\Support\Facades\Facade;

/**
 * Facade for user service
 */
class GeneralSettingFacade extends Facade
{

    /**
     * Returning service name
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Hideyo\Ecommerce\Framework\Services\GeneralSetting\GeneralSettingService';
    }
}