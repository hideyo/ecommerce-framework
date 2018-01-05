<?php
namespace Hideyo\Repositories;

interface OrderStatusRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();
    
    public function find($id);
}
