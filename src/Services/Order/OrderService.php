<?php

namespace Hideyo\Ecommerce\Framework\Services\Order;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class OrderService extends BaseService
{
	public function __construct(OrderRepository $order)
	{
		$this->repo = $order;
	} 

	public function createByUserAndShopId(array $attributes, $shopId, $noAccountUser)
    {
    	return $this->repo->createByUserAndShopId($attributes, $shopId, $noAccountUser);
    }

    public function updateStatus($id, $orderStatusId)
    {
        $model = $this->find($id);

        $attributes['order_status_id'] = $orderStatusId;
        if (count($attributes) > 0) {
            $model->fill($attributes);
            $model->save();
        }

        return $model;
    }


}