<?php

namespace Hideyo\Ecommerce\Framework\Services\Product;
 
use App\Product;

use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ProductService extends BaseService
{
	public function __construct(ProductRepository $product)
	{
		$this->repo = $product;
	} 

    public function selectOneByShopIdAndId($shopId, $productId, $attributeId = false)
    {
       return $this->repo->selectOneByShopIdAndId($shopId, $productId, $attributeId);
    }

    public function ajaxProductImages($product, $combinationsIds, $productAttributeId = false) 
    {
        return $this->repo->ajaxProductImages($product, $combinationsIds, $productAttributeId);
    }

    public  function selectAllByShopIdAndProductCategoryId($shopId, $productCategoryId, $filters = false)
    {
        return $this->repo->selectAllByShopIdAndProductCategoryId($shopId, $productCategoryId, $filters);
    }

}