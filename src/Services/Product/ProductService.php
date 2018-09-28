<?php

namespace Hideyo\Ecommerce\Framework\Services\Product;
 
use App\Product;

use Hideyo\Ecommerce\Framework\Repositories\ProductRepositoryInterface;
 
class ProductService
{
	public function __construct(ProductRepositoryInterface $shop)
	{
		$this->shop = $shop;
	} 

    public function checkByUrl($shopUrl)
    {
        return $this->shop->checkByUrl($shopUrl);
    }

    public function find($shopId)
    {
        return $this->shop->find($shopId);
    }	 
}