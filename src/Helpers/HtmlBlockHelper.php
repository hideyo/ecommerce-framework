<?php

namespace Hideyo\Ecommerce\Framework\Helpers;

use Hideyo\Ecommerce\Framework\Services\HtmlBlock\HtmlBlockFacade as HtmlBlockService;
use DbView;

class HtmlBlockHelper
{
    static function findByPosition($position)
    {
        $result = HtmlBlockService::selectOneByShopIdAndPosition($position, config()->get('app.shop_id'));
        
        if ($result) {
            return DbView::make($result)->with($result->toArray())->render();
        }
        
        return '';        
    }
}