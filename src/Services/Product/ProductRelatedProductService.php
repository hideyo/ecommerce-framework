<?php

namespace Hideyo\Ecommerce\Framework\Services\Product;
 
use App\Product;

use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductRelatedProductRepository;
use Hideyo\Ecommerce\Framework\Services\Product\ProductFacade as ProductService;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ProductRelatedProductService extends BaseService
{
	public function __construct(ProductRelatedProductRepository $productRelated)
	{
		$this->repo = $productRelated;
	} 

    public function create(array $attributes, $productParentId)
    {
        $parentProduct = ProductService::find($productParentId);
   
        if (isset($attributes['products'])) {
            $parentProduct->relatedProducts()->attach($attributes['products']);
        }

        return $parentProduct->save();
    }

}