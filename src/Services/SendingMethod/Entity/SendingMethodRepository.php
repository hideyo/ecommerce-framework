<?php
namespace Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity;
 
use Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity\SendingMethod;
use Validator;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class SendingMethodRepository extends BaseRepository 
{

    protected $model;

    public function __construct(SendingMethod $model)
    {
        $this->model = $model;
    }

    public function selectOneByShopIdAndId($shopId, $sendingMethodId)
    {
        return $this->model->with(array('relatedPaymentMethods' => function ($query) {
            $query->where('active', '=', 1);
        }))->where('shop_id', '=', $shopId)->where('active', '=', 1)->where('id', '=', $sendingMethodId)->get();
    } 
}
