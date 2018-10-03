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

}