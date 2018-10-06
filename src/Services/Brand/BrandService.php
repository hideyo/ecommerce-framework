<?php

namespace Hideyo\Ecommerce\Framework\Services\Brand;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Brand\Entity\BrandRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class BrandService extends BaseService
{
	public function __construct(BrandRepository $brand)
	{
		$this->repo = $brand;
	} 

}