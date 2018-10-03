<?php

namespace Hideyo\Ecommerce\Framework\Helpers;
use Hideyo\Ecommerce\Framework\Services\HtmlBlock\Entity\HtmlBlock;
use DbView;

class HtmlBlockHelper
{
    public static function findByPosition($position)
    {
        $htmlBlock = new HtmlBlock();

        $result = $htmlBlock->where('shop_id', '=', config()->get('app.shop_id'))->where('active', '=', 1)->where('position', '=', $position)->first();
        
        if ($result) {
            return DbView::make($result)->with($result->toArray())->render();
        }
        
        return false;        
    }
}