<?php

namespace Hideyo\Ecommerce\Framework\Services\Product;
 
use App\Product;

use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductTagGroupRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ProductTagGroupService extends BaseService
{
	public function __construct(ProductTagGroupRepository $productTagGroup)
	{
		$this->repo = $productTagGroup;
	} 

	public function selectAllByTagAndShopId($shopId, $tag) {
		return $this->repo->selectAllByTagAndShopId($shopId, $tag);
	}
}