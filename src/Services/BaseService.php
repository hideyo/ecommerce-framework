<?php

namespace Hideyo\Ecommerce\Framework\Services;
 
class BaseService
{
    public function find($shopId)
    {
        return $this->repo->find($shopId);
    }

    public function selectAll()
    {
        return $this->repo->selectAll();
    }

    public function getModel()
    {
        return $this->repo->getModel();
    }
}