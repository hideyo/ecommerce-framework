<?php
namespace Hideyo\Ecommerce\Framework\Repositories;

interface ClientAddressRepositoryInterface
{
    public function create(array $attributes, $clientId);
    
    public function updateById(array $attributes, $clientId, $id);

    public function selectAll();

    public function selectAllByShopId($shopId);

    public function selectAllByClientId($clientId);

    public function selectOneByClientIdAndId($clientId, $id);

    public function find($id);
}
