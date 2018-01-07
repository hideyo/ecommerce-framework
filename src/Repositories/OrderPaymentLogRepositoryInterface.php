<?php
namespace Hideyo\Ecommerce\Framework\Repositories;

interface OrderPaymentLogRepositoryInterface
{
    public function create(array $attributes, $orderId);
    
    public function updateById(array $attributes, $orderId, $id);

    public function selectAll();

    public function selectAllByShopId($shopId);

    public function selectAllByOrderId($orderId);
    
    public function find($id);
}
