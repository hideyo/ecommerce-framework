<?php
namespace Hideyo\Repositories;

interface ProductCategoryRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();

    public function ajaxSearchByTitle($query);

    public function selectAllByShopId($shopId);

    public function selectAllActiveByShopId($shopId);
    
    public function selectAllByShopIdAndRoot($shopId);
    
    public function find($id);

    public function entireTreeStructure($shopId);

    public function rebuild();
}
