<?php

namespace Hideyo\Ecommerce\Framework\Services\TaxRate\Entity;
 
use Hideyo\Ecommerce\Framework\Services\TaxRate\Entity\TaxRate;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class TaxRateRepository extends BaseRepository 
{
    protected $model;

    public function __construct(TaxRate $model)
    {
        $this->model = $model;
    } 
}