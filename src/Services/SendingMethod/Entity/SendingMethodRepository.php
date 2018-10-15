<?php
namespace Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity;
 
use Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity\SendingMethod;
use Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity\SendingMethodCountryPrice;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class SendingMethodRepository extends BaseRepository 
{

    protected $model;

    public function __construct(SendingMethod $model, SendingMethodCountryPrice $countryModel)
    {
        $this->model = $model;
        $this->countryModel = $countryModel;

    }

    public function selectOneByShopIdAndId($shopId, $sendingMethodId)
    {
        return $this->model->with(
            array('relatedPaymentMethods' => function ($query) {
                $query->where('active', 1);
            })
        )->where('shop_id', $shopId)->where('active', 1)
        ->where('id', $sendingMethodId)->get();
    } 

    public function getCountryModel() {
        return $this->countryModel;
    }

    public function findCountry($countryId) {
        return $this->countryModel->find($countryId);
    }

}
