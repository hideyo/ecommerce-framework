<?php
namespace Hideyo\Repositories; 

interface ProductRepositoryInterface
{
    public function create(array $attributes);

    public function createCopy(array $attributes, $productId);

    public function updateById(array $attributes, $productId);
    
    public function selectAll();

    public function selectAllByShopId($shopId);

    public function selectAllExport();

    public function selectAllByProductParentId($productParentId);
    
    public function find($productId);
    
    public function getModel();

    public function reduceAmounts($products);

    public function changeActive($productId);

    public function changeAmount($productId, $amount);

    public function selectByLimitAndOrderBy($shopId, $limit, $orderBy);
}
