<?php
namespace Hideyo\Repositories;

interface AttributeRepositoryInterface
{
    public function create(array $attributes, $attributeGroupId);

    public function updateById(array $attributes, $attributeGroupId, $id);

    public function destroy($id);

    public function selectAll();
    
    public function find($id);

    public function getModel();
}
