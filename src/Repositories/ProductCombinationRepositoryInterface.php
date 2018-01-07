<?php
namespace Hideyo\Ecommerce\Framework\Repositories;

interface ProductCombinationRepositoryInterface
{
    public function create(array $attributes, $productParentId);
    
    public function updateById(array $attributes, $productId, $productAttributeId);
    
    public function destroy($id);

    public function selectAllByProductId($productId);

    public function selectAllByShopIdAndProductId($shopId, $productId);

    public function selectAll();

    public function selectOneByShopIdAndSlug($shopId, $slug);

    public function selectOneByShopIdAndId($shopId, $productAttributeId);

    public function find($productAttributeId);

    public function getModel();

    public function reduceAmounts($products);
}
