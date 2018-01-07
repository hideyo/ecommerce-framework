<?php
namespace Hideyo\Ecommerce\Framework\Repositories;

interface ShopRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $shopId);
    
    public function selectAll();
    
    public function find($shopId);
}