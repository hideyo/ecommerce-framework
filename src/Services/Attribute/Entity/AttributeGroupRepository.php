<?php
namespace Hideyo\Ecommerce\Framework\Services\Attribute\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Attribute\Entity\AttributeGroup;
use Validator;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class AttributeGroupRepository extends BaseRepository
{
    protected $model;

    public function __construct(AttributeGroup $model)
    {
        $this->model = $model;
    }  
}