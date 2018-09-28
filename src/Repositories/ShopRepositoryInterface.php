<?php
namespace Hideyo\Ecommerce\Framework\Repositories;

interface ShopRepositoryInterface
{    
    public function selectAll();
    
    public function find($shopId);
}