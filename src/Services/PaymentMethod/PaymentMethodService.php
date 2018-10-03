<?php

namespace Hideyo\Ecommerce\Framework\Services\PaymentMethod;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\PaymentMethod\Entity\PaymentMethodRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class PaymentMethodService extends BaseService
{
	public function __construct(PaymentMethodRepository $paymentMethod)
	{
		$this->repo = $paymentMethod;
	}

    public function selectOneByShopIdAndId($shopId, $paymentMethodId)
    {
    	$result = $this->repo->selectOneByShopIdAndId($shopId, $paymentMethodId);

        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();

    }

}