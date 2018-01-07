<?php
namespace Hideyo\Ecommerce\Framework\Repositories;

interface CouponRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();

    public function selectOneByShopIdAndCode($shopId, $code);

    public function find($id);
}
