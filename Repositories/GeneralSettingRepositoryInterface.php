<?php
namespace Hideyo\Repositories;

interface GeneralSettingRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();
    
    public function find($id);

    public function selectOneByShopIdAndName($shopId, $name);
}
