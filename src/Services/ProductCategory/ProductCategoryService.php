<?php

namespace Hideyo\Ecommerce\Framework\Services\ProductCategory;
 
use App\Product;
use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\ProductCategory\Entity\ProductCategoryRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ProductCategoryService extends BaseService
{
	public function __construct(ProductCategoryRepository $shop)
	{
		$this->repo = $shop;
	} 

    public function selectOneByShopIdAndSlug($shopId, $slug, $imageTag = false)
    { 
    	return $this->repo->selectOneByShopIdAndSlug($shopId, $slug, $imageTag);
    }


    public function selectAllByShopIdAndRoot($shopId)
    {
    	return $this->repo->selectAllByShopIdAndRoot($shopId);
    }

    public function selectRootCategories($shopId, $imageTag)
    {
    	return $this->repo->selectRootCategories($shopId, $imageTag);
    }

}