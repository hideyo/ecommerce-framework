<?php

namespace Hideyo\Ecommerce\Framework\Services\Shop;
 
use App\Product;

use Hideyo\Ecommerce\Framework\Repositories\ShopRepositoryInterface;
 
class ShopService
{
	public function __construct(ShopRepositoryInterface $shop)
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