<?php
namespace Hideyo\Repositories;

interface InvoiceRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();

    public function selectAllByAllProductsAndProductCategoryId($productCategoryId);

    public function generateInvoiceFromOrder($orderId);
    
    public function find($id);
}
