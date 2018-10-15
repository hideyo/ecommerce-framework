<?php

namespace Hideyo\Ecommerce\Framework\Services\HtmlBlock\Entity;

use Hideyo\Ecommerce\Framework\Services\HtmlBlock\Entity\HtmlBlock;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;

class HtmlBlockRepository extends BaseRepository 
{
    protected $model;

    public function __construct(HtmlBlock $model)
    {
        $this->model = $model;
    }

    function selectAllActiveByShopId($shopId)
    {
         return $this->model->where('shop_id', $shopId)->get();
    }

    function selectOneByShopIdAndSlug($shopId, $slug)
    {
        $result = $this->model->where('shop_id', $shopId)->where('slug', $slug)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }

    public function selectOneByShopIdAndPosition($position, $shopId)
    {
        $result = $this->model->where('shop_id', $shopId)->where('position', $position)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    } 
}