<?php
namespace Hideyo\Ecommerce\Framework\Repositories;

interface OrderStatusEmailTemplateRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();

    public function selectAllByShopId($shopId);

    public function selectBySendingMethodIdAndPaymentMethodId($paymentMethodId, $sendingMethodId);
    
    public function find($id);
}
