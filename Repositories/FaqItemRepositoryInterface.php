<?php
namespace Hideyo\Repositories;

interface FaqItemRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();

    public function selectAllActiveByShopId($shopId);
    
    public function find($id);
}
