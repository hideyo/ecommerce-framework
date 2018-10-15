<?php

namespace Hideyo\Ecommerce\Framework\Services\Exception;

use Hideyo\Ecommerce\Framework\Services\Exception\Entity\ExceptionItemRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ExceptionService extends BaseService
{
	public function __construct(ExceptionItemRepository $exception)
	{
		$this->repo = $exception;
	} 
}