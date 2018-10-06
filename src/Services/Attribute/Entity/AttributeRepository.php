<?php
namespace Hideyo\Ecommerce\Framework\Services\Attribute\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Attribute\Entity\Attribute;
use Validator;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class AttributeRepository extends BaseRepository 
{
    protected $model;

    public function __construct(Attribute $model)
    {
        $this->model = $model;
    }
}