<?php
namespace Hideyo\Ecommerce\Framework\Services\Faq\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Faq\Entity\FaqItem;
use Hideyo\Ecommerce\Framework\Services\Faq\Entity\FaqItemGroup;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class FaqItemRepository extends BaseRepository 
{

    protected $model;

    public function __construct(FaqItem $model, FaqItemGroup $modelFaqItemGroup)
    {
        $this->model = $model;
        $this->modelFaqItemGroup = $modelFaqItemGroup;
    }

    public function selectAllGroups()
    {
        return $this->modelFaqItemGroup->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->get();
    }

    function selectOneById($faqItemId)
    {
        $result = $this->model->with(array('relatedPaymentMethods'))->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->where('active', '=', 1)->where('id', '=', $faqItemId)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }

    function selectAllActiveByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->get();
    }
        
}
