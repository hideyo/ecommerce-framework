<?php

namespace Hideyo\Ecommerce\Framework\Services\SendingMethod;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity\SendingMethodRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class SendingMethodService extends BaseService
{
	public function __construct(SendingMethodRepository $sendingMethod)
	{
		$this->repo = $sendingMethod;
	} 

	public function selectAllActiveByShopId($shopId)
    {
    	return $this->repo->selectAllActiveByShopId($shopId);
    }

	public function selectOneByShopIdAndId($shopId, $sendingMethodId)
    {
    	$result = $this->repo->selectOneByShopIdAndId($shopId, $sendingMethodId);

        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    	
    }

}