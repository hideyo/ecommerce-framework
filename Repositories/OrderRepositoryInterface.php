<?php
namespace Hideyo\Repositories;

interface OrderRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();

    public function selectAllByCompany();

    public function selectAllByAllProductsAndProductCategoryId($productCategoryId);
    
    public function find($id);
}
