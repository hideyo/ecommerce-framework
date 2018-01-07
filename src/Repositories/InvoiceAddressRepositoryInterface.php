<?php
namespace Hideyo\Ecommerce\Framework\Repositories;

interface InvoiceAddressRepositoryInterface
{
    public function create(array $attributes, $orderId);
    
    public function updateById(array $attributes, $orderId, $id);

    public function selectAll();

    public function selectAllByShopId($shopId);

    public function selectAllByInvoiceId($orderId);
    
    public function find($id);
}
