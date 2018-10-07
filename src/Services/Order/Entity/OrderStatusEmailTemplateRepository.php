<?php
namespace Hideyo\Ecommerce\Framework\Services\Order\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderStatusEmailTemplate;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;

class OrderStatusEmailTemplateRepository extends BaseRepository 
{

    protected $model;

    public function __construct(OrderStatusEmailTemplate $model)
    {
        $this->model = $model;
    }


    public function selectBySendingMethodIdAndPaymentMethodId($paymentMethodId, $sendingMethodId)
    {

        $result = $this->model->with(array('sendingPaymentMethodRelated' => function ($query) use ($paymentMethodId, $sendingMethodId) {
            $query->with(array('sendingMethod' => function ($query) use ($sendingMethodId) {
                $query->where('id', '=', $sendingMethodId);
            }, 'paymentMethod' => function ($query) use ($paymentMethodId) {
                $query->where('id', '=', $paymentMethodId);
            }));
        } ))
        ->get();
        if ($result->count()) {
            if ($result->first()->sendingPaymentMethodRelated->sendingMethod and $result->first()->sendingPaymentMethodRelated->paymentMethod) {
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}