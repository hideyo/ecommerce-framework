<?php
namespace Hideyo\Repositories;

interface ProductExtraFieldValueRepositoryInterface
{
    public function create(array $attributes, $productParentId);
    
    public function selectAll();
    
    public function find($id);

    public function selectOneByShopIdAndSlug($shopId, $slug);

    public function selectOneByShopIdAndId($shopId, $id);

    public function selectAllByProductId($productId);

    public function getModel();
}
