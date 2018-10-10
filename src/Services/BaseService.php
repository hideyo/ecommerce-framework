<?php

namespace Hideyo\Ecommerce\Framework\Services;
 
class BaseService
{
    public function find($id)
    {
        return $this->repo->find($id);
    }

    public function selectAll()
    {
        return $this->repo->selectAll();
    }

    public function getModel()
    {
        return $this->repo->getModel();
    }

    public function updateOrAddModel($model, $attributes) 
    {
        if (count($attributes) > 0) {
            $model->fill($attributes);
            $model->save();
        }
        return $model;  

    }
}