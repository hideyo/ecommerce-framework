<?php
namespace Hideyo\Ecommerce\Framework\Services\PaymentMethod\Entity;
 
use Hideyo\Ecommerce\Framework\Services\PaymentMethod\Entity\PaymentMethod;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class PaymentMethodRepository extends BaseRepository 
{

    protected $model;

    public function __construct(PaymentMethod $model)
    {
        $this->model = $model;
    }

    function selectOneByShopIdAndId($shopId, $paymentMethodId)
    {
        return $this->model->where('shop_id', '=', $shopId)->where('active', '=', 1)->where('id', '=', $paymentMethodId)->get();
    }

    function selectOneById($paymentMethodId)
    {
        $result = $this->model->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->where('active', '=', 1)->where('id', '=', $paymentMethodId)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }
}