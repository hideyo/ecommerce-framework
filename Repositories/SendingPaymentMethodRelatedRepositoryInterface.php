<?php
namespace Hideyo\Repositories;

interface SendingPaymentMethodRelatedRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();

    public function selectAllActiveByShopId($shopId);

    public function selectOneByShopIdAndId($shopId, $id);

    public function selectOneByShopIdAndPaymentMethodIdAndSendingMethodId($shopId, $paymentMethodId, $sendingMethodId);

    public function selectOneByPaymentMethodIdAndSendingMethodId($sendingPaymentMethodId, $paymentMethodId);
    
    public function selectOneByPaymentMethodIdAndSendingMethodIdAdmin($sendingPaymentMethodId, $paymentMethodId);

    public function find($id);
}
