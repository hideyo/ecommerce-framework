<?php
namespace Hideyo\Repositories;

interface ProductRelatedProductRepositoryInterface
{
    public function create(array $attributes, $productParentId);
    
    public function selectAll();

    public function selectAllByShopId($shopId);

    public function selectAllByProductId($productId);
    
    public function find($id);
}
